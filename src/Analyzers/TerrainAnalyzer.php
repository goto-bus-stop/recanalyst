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
        $version = $this->get(VersionAnalyzer::class);

        $mapData = [];
        for ($y = 0; $y < $this->sizeY; $y += 1) {
            $mapData[$y] = [];
            for ($x = 0; $x < $this->sizeX; $x += 1) {
                $terrainId = ord($this->header[$this->position++]);
                if ($terrainId === 0xFF) {
                    // Skip UserPatch "original terrain ID" data.
                    $this->position++;
                    $terrainId = ord($this->header[$this->position++]);
                }
                $elevation = ord($this->header[$this->position++]);

                $mapData[$y][$x] = new Tile($x, $y, $terrainId, $elevation);
            }
        }
        return $mapData;
    }
}
