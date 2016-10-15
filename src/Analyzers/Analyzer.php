<?php

namespace RecAnalyst\Analyzers;

use RecAnalyst\RecordedGame;

/**
 * Base class for analyzers.
 */
abstract class Analyzer
{
    /**
     * Recorded game to run the analysis on.
     *
     * @var \RecAnalyst\RecordedGame
     */
    protected $rec;

    /**
     * Current position in the header or body streams.
     *
     * @var int
     */
    public $position = 0;

    /**
     * Run this analysis on a recorded game.
     *
     * @param \RecAnalyst\RecordedGame  $game  Recorded game to analyze.
     * @return mixed Result of the analysis.
     */
    public function analyze(RecordedGame $game)
    {
        $this->rec = $game;
        $this->header = $game->getHeaderContents();
        $this->body = $game->getBodyContents();
        $this->headerSize = strlen($this->header);
        $this->bodySize = strlen($this->body);

        return $this->run();
    }

    /**
     * Get the result of another analyzer.
     *
     * @param string  $analyzer  Analyzer class name.
     * @param mixed  $arg  Optional argument to the analyzer.
     * @return mixed Result of the analyzer.
     */
    protected function get($analyzer, $arg = null)
    {
        return $this->rec->getAnalysis($analyzer, $arg)->analysis;
    }

    /**
     * Compose another analyzer. Starts reading at the current position, and
     * uses the composed analyzer's final position as the new position.
     *
     * @param string  $analyzer  Analyzer class name.
     * @param mixed  $arg  Optional argument to the analyzer.
     * @return mixed Result of the analyzer.
     */
    protected function read($analyzer, $arg = null)
    {
        $result = $this->rec->getAnalysis($analyzer, $arg, $this->position);
        $this->position = $result->position;
        return $result->analysis;
    }

    /**
     * Read and unpack data from the header of the recorded game file.
     *
     * @see https://secure.php.net/pack For documentation on data unpack format.
     * @param string  $type  Data type.
     * @param int  $size  Size of the data.
     * @return mixed Result.
     */
    protected function readHeader($type, $size)
    {
        if ($this->position + $size > $this->headerSize) {
            throw new \Exception('Can\'t read ' . $size . ' bytes');
        }
        $data = unpack($type, substr($this->header, $this->position, $size));
        $this->position += $size;
        return $data[1];
    }

    /**
     * Read raw strings from the header of the recorded game file.
     *
     * @param int  $size  Amount of characters to read.
     * @return string Result.
     */
    protected function readHeaderRaw($size)
    {
        if ($this->position + $size > $this->headerSize) {
            throw new \Exception('Can\'t read ' . $size . ' bytes');
        }
        $data = substr($this->header, $this->position, $size);
        $this->position += $size;
        return $data;
    }

    /**
     * Read and unpack data from the body of the recorded game file.
     *
     * @see https://secure.php.net/pack For documentation on data unpack format.
     * @param string  $type  Data type.
     * @param int  $size  Size of the data.
     * @return mixed Result.
     */
    protected function readBody($type, $size)
    {
        $data = unpack($type, substr($this->body, $this->position, 4));
        $this->position += $size;
        return $data[1];
    }

    /**
     * Read raw strings from the body of the recorded game file.
     *
     * @param int  $size  Amount of characters to read.
     * @return string Result.
     */
    protected function readBodyRaw($size)
    {
        $data = substr($this->body, $this->position, $size);
        $this->position += $size;
        return $data;
    }

    /**
     *
     */
    abstract protected function run();
}
