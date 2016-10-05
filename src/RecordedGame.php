<?php

namespace RecAnalyst;

use RecAnalyst\Analyzers\Analyzer;

class RecordedGame
{
    const MGX_EXT = 'mgx';
    const MGL_EXT = 'mgl';
    const MGZ_EXT = 'mgz';
    const MGX2_EXT = 'mgx2';

    /**
     * List of teams in the game.
     *
     * @var TeamList
     */
    public $teams;

    /**
     * Chat messages sent during the game.
     *
     * @var array
     */
    public $ingameChat;

    /**
     * An associative array containing "unit_type_id - unit_num" pairs.
     *
     * @var array
     */
    public $units;

    /**
     * An associative multi-dimensional array containing building_type_id → building_num
     * pairs for each player.
     *
     * @var array
     */
    public $buildings;

    /**
     * Elapsed time for analyzing in milliseconds.
     *
     * @var int
     */
    protected $analyzeTime;

    /**
     * Whether the file being analyzed is an mgx file (AOC).
     *
     * @var bool
     */
    protected $isMgx;

    /**
     * Whether the file being analyzed is an mgl file (AOK).
     *
     * @var bool
     */
    protected $isMgl;

    /**
     * Whether the file being analyzed is an mgz file (UserPatch).
     *
     * @var bool
     */
    protected $isMgz;

    /**
     * List of GAIA objects.
     *
     * @var array
     */
    protected $gaiaObjects;

    /**
     * List of any player objects.
     *
     * @var array
     */
    protected $playerObjects;

    /**
     * List of tributes.
     *
     * @var array
     */
    public $tributes;

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
    private $_headerLen;

    /**
     * [Add documentation]
     *
     * @var int
     */
    private $_nextPos;

    /**
     * Create a recorded game analyser.
     *
     * @param  string  $filename  Path to the recorded game file.
     * @return void
     */
    public function __construct($filename = null)
    {
        $this->filename = $filename;
        if (!is_null($filename)) {
            $this->ext = strtolower(pathinfo($this->filename, PATHINFO_EXTENSION));
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
        $this->ext = strtolower(pathinfo($this->filename, PATHINFO_EXTENSION));
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
        $this->gameInfo = new GameInfo($this);
        $this->teams = [];
        $this->ingameChat = [];
        $this->units = [];
        $this->buildings = [];
        $this->analyzeTime = 0;
        $this->isMgx = false;
        $this->isMgl = false;
        $this->isMgz = false;
        $this->gaiaObjects = [];
        $this->playerObjects = [];
        $this->analyses = [];

        $this->tributes = [];
        $this->_headerLen = 0;
        $this->_nextPos = 0;
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
     * @param  string  $analyzerName
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
                $this->_headerLen = $base + $index - 4;
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
    protected function extractStreams($a = null, $b = null)
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
        $packed_data = fread($fp, 4);
        if ($packed_data === false || strlen($packed_data) < 4) {
            throw new RecAnalystException(
                'Unable to read the header length',
                RecAnalystException::HEADERLEN_READERROR
            );
        }
        $unpacked_data = unpack('V', $packed_data);
        $this->_headerLen = $unpacked_data[1];
        if (!$this->_headerLen) {
            $this->manuallyDetermineHeaderLength();
        }
        if (!$this->_headerLen) {
            throw new RecAnalystException(
                'Header length is zero',
                RecAnalystException::EMPTY_HEADER
            );
        }
        $packed_data = fread($fp, 4);
        if ($packed_data === false || strlen($packed_data) < 4) {
            $this->_nextPos = 0;
        } else {
            $unpacked_data = unpack('V', $packed_data);
            $this->_nextPos = $unpacked_data[1];
        }

        // Version detection heuristic
        // TODO find something more accurate?
        $this->isMgx = $this->_nextPos < filesize($this->filename);
        $this->isMgl = !$this->isMgx;

        $this->_headerLen -= $this->isMgx ? 8 : 4;
        if ($this->isMgl) {
            fseek($fp, -4, SEEK_CUR);
        }
        $read = 0;
        $bindata = '';
        while ($read < $this->_headerLen && ($buff = fread($fp, $this->_headerLen - $read))) {
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
     * Builds teams.
     *
     * @return void
     */
    public function buildTeams()
    {
        if (count($this->teams)) {
            // already built
            return;
        }
        $teamsByIndex = [];
        foreach ($this->players as $player) {
            if ($player->team == 0) {
                $found = false;
                foreach ($this->teams as $team) {
                    if ($team->getIndex() != $player->team) {
                        continue;
                    }
                    foreach ($team->players as $player_) {
                        if ($player_->index == $player->index) {
                            $team->addPlayer($player);
                            $found = true;
                            break;
                        }
                    }
                    if ($found) {
                        break;
                    }
                }
                if (!$found) {
                    $team = new Team();
                    $team->addPlayer($player);
                    $this->teams[] = $team;
                    $teamsByIndex[$player->team] = $team;
                }
            } else {
                if (array_key_exists($player->team, $teamsByIndex)) {
                    $teamsByIndex[$player->team]->addPlayer($player);
                } else {
                    $team = new Team();
                    $team ->addPlayer($player);
                    $this->teams[] = $team;
                    $teamsByIndex[$player->team] = $team;
                }
            }
        }
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
