<?php

namespace RecAnalyst\Analyzers;

use RecAnalyst\Model\Tile;

class TerrainAnalyzer extends Analyzer
{
    private $sizeX;
    private $sizeY;

    /**
     * @param object  $analysis  Current state of the HeaderAnalyzer.
     */
    public function __construct($size)
    {
        $this->sizeX = $size[0];
        $this->sizeY = $size[1];
    }

    protected function run()
    {
        $mapData = [];
        for ($y = 0; $y < $this->sizeY; $y += 1) {
            $mapData[$y] = [];
            for ($x = 0; $x < $this->sizeX; $x += 1) {
                $mapData[$y][$x] = new Tile(
                    $x,
                    $y,
                    /* terrainId */ ord($this->header[$this->position]),
                    /* elevation */ ord($this->header[$this->position + 1])
                );
                $this->position += 2;
            }
        }
        return $mapData;
    }
}
