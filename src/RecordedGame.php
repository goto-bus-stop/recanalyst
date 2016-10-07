<?php

namespace RecAnalyst;

use RecAnalyst\Analyzers\Analyzer;
use RecAnalyst\ResourcePacks\ResourcePack;

/**
 * Represents a recorded game file.
 */
class RecordedGame
{
    /** @var int Elapsed time for analyzing in milliseconds. */
    protected $analyzeTime;

    /** @var array Completed analyses. */
    protected $analyses = [];

    /** @var resource File handle to the recorded game file. */
    private $fd;

    /** @var int Size of the compressed header block. */
    private $headerLen;

    /** @var int Something with saved chapters? ¯\_(ツ)_/¯ */
    private $nextPos;

    /** @var \RecAnalyst\ResourcePacks\ResourcePack Current resource pack. */
    private $resourcePack = null;

    /**
     * Create a recorded game analyser.
     *
     * @param  string  $filename  Path to the recorded game file.
     * @return void
     */
    public function __construct($filename = null)
    {
        $this->filename = $filename;
        $this->reset();
    }

    /**
     * Loads the file for analysis. Deprecated: provided for compatibility with
     * older RecAnalyst versions.
     *
     * @param string  $filename  File name.
     * @param mixed  $input  File handle or string contents.
     * @return void
     */
    public function load($filename, $input)
    {
        $this->filename = $filename;
        if (is_string($input)) {
            // Create file stream from input string
            $this->fd = fopen('php://memory', 'r+');
            fwrite($this->fd, $input);
            rewind($this->fd);
        } else {
            $this->fd = $input;
        }
        $this->extractStreams();
    }

    /**
     * Create a file handle for the recorded game file.
     * Not sure why this is public…
     *
     * @return void
     */
    public function open()
    {
        $this->fd = fopen($this->filename, 'r');
    }

    /**
     * Resets the internal state.
     *
     * @return void
     */
    public function reset()
    {
        $this->analyzeTime = 0;
        $this->analyses = [];

        $this->headerLen = 0;
        $this->nextPos = 0;

        $this->resourcePack = new ResourcePacks\AgeOfEmpires();
    }

    /**
     * Get the current resource pack.
     */
    public function getResourcePack()
    {
        return $this->resourcePack;
    }

    /**
     * Run an analysis on the current game.
     *
     * @param  Analyzer  $analyzer
     * @return mixed
     */
    public function runAnalyzer(Analyzer $analyzer)
    {
        if (empty($this->header)) {
            $this->extractStreams();
        }
        return $analyzer->analyze($this);
    }

    /**
     * Get an analysis result for a specific analyzer, running it if necessary.
     *
     * @param string  $analyzerName  Fully qualified name of the analyzer class.
     * @param mixed  $arg  Optional argument to the analyzer.
     * @param int  $startAt  Position to start at.
     * @return mixed
     */
    public function getAnalysis($analyzerName, $arg = null, $startAt = 0)
    {
        $key = $analyzerName . ':' . $startAt;
        if (!array_key_exists($key, $this->analyses)) {
            $analyzer = new $analyzerName($arg);
            $analyzer->position = $startAt;
            $result = new \StdClass;
            $result->analysis = $this->runAnalyzer($analyzer);
            $result->position = $analyzer->position;
            $this->analyses[$key] = $result;
        }
        return $this->analyses[$key];
    }

    /**
     * Run a v3.x-style analysis.
     *
     * @deprecated Probably not so useful anymore? Should provide different
     *     accessors that run what they need, instead.
     */
    public function analyze()
    {
        $starttime = microtime(true);
        if (empty($this->header)) {
            $this->extractStreams();
        }
        if (!$this->analyzeHeader()) {
            return false;
        }
        if (!$this->analyzeBody()) {
            return false;
        }

        $this->postAnalyze();
        $endtime = microtime(true);
        $this->analyzeTime = round(($endtime - $starttime) * 1000);
        return true;
    }

    /**
     * Determine the header length if the Header Length field was not set in the
     * file.
     */
    private function manuallyDetermineHeaderLength()
    {
        // This separator is part of the Start Game command, which is the very
        // first command in the recorded game body. It's … reasonably accurate.
        $separator = pack('c*', 0xF4, 0x01, 0x00, 0x00);
        // We need to reset the file pointer when we're done
        $initialBase = ftell($this->fd);

        $base = $initialBase;
        $buffer = '';
        while (($buffer = fread($this->fd, 8192)) !== false) {
            $index = strpos($buffer, $separator);
            if ($index !== false) {
                $this->headerLen = $base + $index - 4;
                fseek($this->fd, $initialBase);
                return;
            }
            $base += strlen($buffer);
        }
        fseek($this->fd, $initialBase);
    }

    /**
     * Extracts header and body streams from recorded game.
     *
     * @return void
     * @throws RecAnalystException
     */
    protected function extractStreams()
    {
        if (empty($this->filename)) {
            throw new RecAnalystException(
                'No file has been specified for analyzing',
                RecAnalystException::FILE_NOT_SPECIFIED
            );
        }
        if (empty($this->fd)) {
            $this->open();
        }
        $fp = $this->fd;
        $rawRead = fread($fp, 4);
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
        $rawRead = fread($fp, 4);
        if ($rawRead === false || strlen($rawRead) < 4) {
            $this->nextPos = 0;
        } else {
            list (, $this->nextPos) = unpack('V', $rawRead);
        }

        // Version detection heuristic
        // TODO find something more accurate?
        $isMgx = $this->nextPos < filesize($this->filename);

        $this->headerLen -= $isMgx ? 8 : 4;
        if (!$isMgx) {
            fseek($fp, -4, SEEK_CUR);
        }
        $read = 0;
        $bindata = '';
        while ($read < $this->headerLen && ($buff = fread($fp, $this->headerLen - $read))) {
            $read += strlen($buff);
            $bindata .= $buff;
        }
        unset($buff);

        $this->body = '';
        while (!feof($fp)) {
            $this->body .= fread($fp, 8192);
        }
        fclose($fp);

        $this->header = gzinflate($bindata, 8388608);  // 8MB
        unset($bindata);

        if (!strlen($this->header)) {
            throw new RecAnalystException(
                'Cannot decompress header section',
                RecAnalystException::HEADER_DECOMPRESSERROR
            );
        }
    }

    /**
     * Return the raw decompressed header contents.
     *
     * @return string
     */
    public function getHeaderContents()
    {
        return $this->header;
    }

    /**
     * Return the raw body contents.
     *
     * @return string
     */
    public function getBodyContents()
    {
        return $this->body;
    }

    /**
     * Analyzes header stream.
     *
     * @return bool
     */
    protected function analyzeHeader()
    {
        $version = $this->getAnalysis(Analyzers\VersionAnalyzer::class);
        return $this->getAnalysis(Analyzers\HeaderAnalyzer::class);
    }

    /**
     * Analyzes the body stream.
     *
     * @return bool
     */
    protected function analyzeBody()
    {
        return $this->getAnalysis(Analyzers\BodyAnalyzer::class);
    }

    /**
     * Returns analyze time (in ms).
     *
     * @return int
     */
    public function getAnalyzeTime()
    {
        return $this->analyzeTime;
    }
}
