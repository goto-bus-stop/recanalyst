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
        $this->headerSize = strlen($this->header);
        $this->bodySize = strlen($this->body);

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
        if ($this->position + $size > $this->headerSize) {
            throw new \Exception('Can\'t read ' . $size . ' bytes');
        }
        $data = unpack($type, substr($this->header, $this->position, $size));
        $this->position += $size;
        return $data[1];
    }

    protected function readHeaderRaw($size)
    {
        if ($this->position + $size > $this->headerSize) {
            throw new \Exception('Can\'t read ' . $size . ' bytes');
        }
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
