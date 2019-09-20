<?php

namespace RecAnalyst;

/**
 * Extracts header and body parts from recorded game files.
 */
class StreamExtractor
{
    /**
     * Position of the first header byte in the file.
     *
     * @var int
     */
    private $headerStart = 0;

    /**
     * Length of the header block. The body starts at byte
     * `$headerStart + $headerLen`.
     *
     * @var int
     */
    private $headerLen = 0;

    /**
     * Header contents.
     *
     * @var string
     */
    private $headerContents = null;

    /**
     * Body contents.
     *
     * @var string
     */
    private $bodyContents = null;

    /**
     * Stream resource to the recorded game file.
     *
     * @var resource
     */
    private $fp = null;

    /**
     * Options.
     *
     * @var array
     */
    private $options = [];

    /**
     * Create a stream extractor instance.
     *
     * @param resource  $fp  Stream resource for the recorded game file.
     * @param array  $options  Stream extractor options.
     *     - `$options['memoryLimit']` - Maximum amount of bytes of memory to
     *       allocate for the decompressed header. Defaults to 16777216 (16MB).
     */
    public function __construct($fp, array $options = [])
    {
        $this->fp = $fp;
        $this->options = array_merge([
            'memoryLimit' => 16 * 1024 * 1024, // 16 MB
        ], $options);
    }

    /**
     * Determine the header length if the Header Length field was not set in the
     * file.
     */
    private function manuallyDetermineHeaderLength(): void
    {
        // This separator is part of the Start Game command, which is the very
        // first command in the recorded game body. It's … reasonably accurate.
        $separator = pack('c*', 0xF4, 0x01, 0x00, 0x00);
        // We need to reset the file pointer when we're done
        $initialBase = ftell($this->fp);

        $base = $initialBase;
        $buffer = '';
        while (($buffer = fread($this->fp, 8192)) !== false) {
            $index = strpos($buffer, $separator);
            if ($index !== false) {
                $this->headerLen = $base + $index - 4;
                break;
            }
            $base += strlen($buffer);
        }
        fseek($this->fp, $initialBase);
    }

    /**
     * Get the total size of the file.
     *
     * @return int
     */
    private function getFileSize(): int
    {
        fseek($this->fp, 0, SEEK_END);

        return ftell($this->fp);
    }

    /**
     * Find the header length.
     */
    private function determineHeaderLength(): void
    {
        $rawRead = fread($this->fp, 4);
        if ($rawRead === false || strlen($rawRead) < 4) {
            throw new RecAnalystException(
                'Unable to read the header length',
                RecAnalystException::HEADERLEN_READERROR
            );
        }
        list (, $this->headerLen) = unpack('V', $rawRead);
        if (!$this->headerLen) {
            $this->manuallyDetermineHeaderLength();
        }
        if (!$this->headerLen) {
            throw new RecAnalystException(
                'Header length is zero',
                RecAnalystException::EMPTY_HEADER
            );
        }
        // In MGL files, the header starts immediately after the header length
        // bytes. In MGX files, another int32 is stored first, possibly indicating
        // the position of possible further headers(? something for saved chapters,
        // at least, or perhaps saved & restored games).
        // The gzip-compressed header always starts with a series of bytes that
        // decodes to a really high number. So if the next 4 bytes _don't_
        // encode to a really high number, they must refer to the next header.
        $rawRead = fread($this->fp, 4);
        list (, $nextPos) = unpack('V', $rawRead);
        $hasNextPos = $nextPos === 0 || $nextPos < $this->getFileSize();

        $this->headerStart = $hasNextPos ? 8 : 4;
        $this->headerLen -= $this->headerStart;
    }

    /**
     * Read or return the Recorded Game file's header block.
     *
     * @return string
     */
    public function getHeader(): string
    {
        if ($this->headerContents) {
            return $this->headerContents;
        }

        if (!$this->headerLen) {
            $this->determineHeaderLength();
        }

        fseek($this->fp, $this->headerStart, SEEK_SET);

        $read = 0;
        $bindata = '';
        while ($read < $this->headerLen && ($buff = fread($this->fp, $this->headerLen - $read))) {
            $read += strlen($buff);
            $bindata .= $buff;
        }
        unset($buff);

        $this->headerContents = gzinflate($bindata, $this->options['memoryLimit']);
        unset($bindata);

        if (!strlen($this->headerContents)) {
            throw new RecAnalystException(
                'Cannot decompress header section',
                RecAnalystException::HEADER_DECOMPRESSERROR
            );
        }

        return $this->headerContents;
    }

    /**
     * Read or return the Recorded Game file's body.
     *
     * @return string
     */
    public function getBody(): string
    {
        if ($this->bodyContents) {
            return $this->bodyContents;
        }

        if (!$this->headerLen) {
            $this->determineHeaderLength();
        }

        fseek($this->fp, $this->headerStart + $this->headerLen, SEEK_SET);

        $this->bodyContents = '';
        while (!feof($this->fp)) {
            $this->bodyContents .= fread($this->fp, 8192);
        }
        fclose($this->fp);

        return $this->bodyContents;
    }
}
