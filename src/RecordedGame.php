<?php

namespace RecAnalyst;

use RecAnalyst\Analyzers\Analyzer;
use RecAnalyst\Processors\MapImage;
use RecAnalyst\Processors\Achievements;
use RecAnalyst\ResourcePacks\ResourcePack;

/**
 * Represents a recorded game file.
 */
class RecordedGame
{
    /**
     * Elapsed time for analyzing in milliseconds.
     *
     * @var int
     */
    protected $analyzeTime;

    /**
     * Completed analyses.
     *
     * @var array
     */
    protected $analyses = [];

    /**
     * File handle to the recorded game file.
     *
     * @var resource
     */
    private $fd;

    /**
     * Size of the compressed header block.
     *
     * @var int
     */
    private $headerLen;

    /**
     * Current resource pack.
     *
     * @var \RecAnalyst\ResourcePacks\ResourcePack
     */
    private $resourcePack = null;

    /**
     * @var bool
     */
    private $isLaravel = false;

    /**
     * RecAnalyst options.
     */
    private $options = [];

    /**
     * Create a recorded game analyser.
     *
     * @param  resource|string|\SplFileInfo  $filename  Path or handle to the recorded game file.
     * @param  array  $options
     * @return void
     */
    public function __construct($filename = null, array $options = [])
    {
        if (is_resource($filename)) {
            $this->fd = $filename;
            $this->filename = '';
        } else if (is_object($filename) && is_a($filename, 'SplFileInfo')) {
            $this->filename = $filename->getRealPath();
        } else {
            $this->filename = $filename;
        }

        $this->isLaravel = function_exists('app') && is_a(app(), 'Illuminate\Foundation\Application');

        $this->options = array_merge([
            'translator' => null,
        ], $options);

        if (!$this->options['translator']) {
            if ($this->isLaravel) {
                $this->options['translator'] = app('translator');
            } else {
                $this->options['translator'] = new BasicTranslator();
            }
        }

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
     * @param  \RecAnalyst\Analyzers\Analyzer  $analyzer
     * @return mixed
     */
    public function runAnalyzer(Analyzer $analyzer)
    {
        if (empty($this->headerContents)) {
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
        if (!$this->header()) {
            return false;
        }
        if (!$this->body()) {
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
        if (empty($this->filename) && empty($this->fd)) {
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

        // In MGL files, the header starts immediately after the header length
        // bytes. In MGX files, another int32 is stored first, possibly indicating
        // the position of possible further headers(? something for saved chapters,
        // at least, or perhaps saved & restored games).
        $headerStart = pack('c*', 0xEC, 0x7D, 0x09);
        $hasNextPos = substr($rawRead, 0, 3) !== $headerStart;

        $this->headerLen -= $hasNextPos ? 8 : 4;
        if (!$hasNextPos) {
            fseek($fp, -4, SEEK_CUR);
        }

        $read = 0;
        $bindata = '';
        while ($read < $this->headerLen && ($buff = fread($fp, $this->headerLen - $read))) {
            $read += strlen($buff);
            $bindata .= $buff;
        }
        unset($buff);

        $this->bodyContents = '';
        while (!feof($fp)) {
            $this->bodyContents .= fread($fp, 8192);
        }
        fclose($fp);

        $this->headerContents = gzinflate($bindata, 8388608);  // 8MB
        unset($bindata);

        if (!strlen($this->headerContents)) {
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
        if (empty($this->headerContents)) {
            $this->extractStreams();
        }
        return $this->headerContents;
    }

    /**
     * Return the raw body contents.
     *
     * @return string
     */
    public function getBodyContents()
    {
        if (empty($this->bodyContents)) {
            $this->extractStreams();
        }
        return $this->bodyContents;
    }

    /**
     * Get the game version.
     *
     * @return \StdClass
     */
    public function version()
    {
        return $this->getAnalysis(Analyzers\VersionAnalyzer::class)->analysis;
    }

    /**
     * Get the result of analysis of the recorded game header.
     *
     * @return \StdClass
     */
    public function header()
    {
        return $this->getAnalysis(Analyzers\HeaderAnalyzer::class)->analysis;
    }

    /**
     * Get the game settings used to play this recorded game.
     *
     * @return \RecAnalyst\Model\GameSettings
     */
    public function gameSettings()
    {
        return $this->header()->gameSettings;
    }

    /**
     * Get the result of analysis of the recorded game body.
     *
     * @return \StdClass
     */
    public function body()
    {
        return $this->getAnalysis(Analyzers\BodyAnalyzer::class)->analysis;
    }

    /**
     * Render a map image.
     *
     * @see \RecAnalyst\Processors\MapImage
     * @param array  $options  Rendering options.
     * @return \Intervention\Image Rendered image.
     */
    public function mapImage(array $options = [])
    {
        $proc = new MapImage($this, $options);
        return $proc->run();
    }

    /**
     * Get the teams that played in this recorded game.
     *
     * @return \RecAnalyst\Model\Team[] Teams.
     */
    public function teams()
    {
        return $this->header()->teams;
    }

    /**
     * Get the players that played in this recorded game.
     *
     * @return \RecAnalyst\Model\Player[] Players.
     */
    public function players()
    {
        return $this->header()->players;
    }

    /**
     * Get the player achievements.
     *
     * return \StdClass[] Achievements for each player.
     */
    public function achievements(array $options = [])
    {
        $proc = new Achievements($this, $options);
        return $proc->run();
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

    /**
     * Get a translate key for use with Symfony or Laravel Translations.
     *
     * @return string A translation key.
     */
    private function getTranslateKey($args)
    {
        // Game version names are in their own file, not in with resource packs.
        if ($args[0] === 'game_versions') {
            $key = implode('.', $args);
        } else {
            $pack = get_class($this->resourcePack);
            $key = $pack::NAME . '.' . implode('.', $args);
        }
        if ($this->isLaravel) {
            return 'recanalyst::' . $key;
        }
        return $key;
    }

    /**
     *
     */
    public function getTranslator()
    {
        return $this->options['translator'];
    }

    /**
     * @return string
     */
    public function trans()
    {
        $key = $this->getTranslateKey(func_get_args());
        return $this->getTranslator()->trans($key);
    }
}
