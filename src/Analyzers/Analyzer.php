<?php

namespace RecAnalyst\Analyzers;

use RecAnalyst\RecordedGame;

abstract class Analyzer
{
    protected $rec;
    public $position = 0;

    public function analyze(RecordedGame $game)
    {
        $this->rec = $game;
        $this->header = $game->getHeaderContents();
        $this->body = $game->getBodyContents();

        return $this->run();
    }

    protected function get($analyzer, $arg = null)
    {
        return $this->rec->getAnalysis($analyzer, $arg);
    }

    protected function read($analyzer, $arg = null)
    {
        $result = $this->rec->getAnalysis($analyzer, $arg, $this->position);
        $this->position = $result->position;
        return $result->analysis;
    }

    protected function readHeader($type, $size)
    {
        $data = unpack($type, substr($this->header, $this->position, $size));
        $this->position += $size;
        return $data[1];
    }

    protected function readHeaderRaw($size)
    {
        $data = substr($this->header, $this->position, $size);
        $this->position += $size;
        return $data;
    }

    protected function readBody($type, $size)
    {
        $data = unpack($type, substr($this->body, $this->position, 4));
        $this->position += $size;
        return $data[1];
    }

    protected function readBodyRaw($size)
    {
        $data = substr($this->body, $this->position, $size);
        $this->position += $size;
        return $data;
    }
}
