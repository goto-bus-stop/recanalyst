<?php

namespace RecAnalyst\Analyzers;

use RecAnalyst\Model\Tile;

class MapDataAnalyzer extends Analyzer
{
    protected function run()
    {
        $this->version = $this->get(VersionAnalyzer::class);

        $mapSizeX = $this->readHeader('l', 4);
        $mapSizeY = $this->readHeader('l', 4);
        $this->mapSizeX = $mapSizeX;
        $this->mapSizeY = $mapSizeY;

        // If we went wrong somewhere, throw now so we don't end up in a near-
        // infinite loop later.
        if ($mapSizeX > 10000 || $mapSizeY > 10000) {
            throw new \Exception('Got invalid map size');
        }

        $this->skipZones();

        $allVisible = $this->readHeader('c', 1);
        $fogOfWar = $this->readHeader('c', 1);

        $terrain = $this->read(TerrainAnalyzer::class, [$mapSizeX, $mapSizeY]);

        $this->skipObstructions();
        $this->skipVisibilityMap();

        $this->position += 4;
        $numData = $this->readHeader('l', 4);
        $this->position += $numData * 27;

        return (object) [
            'mapSize' => [$mapSizeX, $mapSizeY],
            'allVisible' => $allVisible,
            'fogOfWar' => $fogOfWar,
            'terrain' => $terrain,
        ];
    }

    private function skipZones()
    {
        $numMapZones = $this->readHeader('l', 4);
        $size = $this->mapSizeX * $this->mapSizeY;
        for ($i = 0; $i < $numMapZones; $i += 1) {
            if ($this->version->subVersion >= 11.93) {
                $this->position += 2048 + $size * 2;
            } else {
                $this->position += 1275 + $size;
            }
            $numFloats = $this->readHeader('l', 4);
            $this->position += ($numFloats * 4) + 4;
        }
    }

    private function skipObstructions()
    {
        $numData = $this->readHeader('l', 4);
        $this->position += 4; // Some ID relating to the previous line...
        $this->position += $numData * 4;
        for ($i = 0; $i < $numData; $i += 1) {
            $numObstructions = $this->readHeader('l', 4);
            // Two signed int32s.
            $this->position += $numObstructions * 8;
        }
    }

    private function skipVisibilityMap()
    {
        $mapSizeX = $this->readHeader('l', 4);
        $mapSizeY = $this->readHeader('l', 4);
        // Visibility map. Can we use this for something?
        $this->position += $mapSizeX * $mapSizeY * 4;
    }
}
