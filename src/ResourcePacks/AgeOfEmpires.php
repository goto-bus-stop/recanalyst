<?php

namespace RecAnalyst\ResourcePacks;

use RecAnalyst\ResourcePacks\AgeOfEmpires\Map;
use RecAnalyst\ResourcePacks\AgeOfEmpires\Unit;
use RecAnalyst\ResourcePacks\AgeOfEmpires\Colors;
use RecAnalyst\ResourcePacks\AgeOfEmpires\Civilization;

class AgeOfEmpires extends ResourcePack
{
    public function isAoKCiv($id)
    {
        return Civilization::isAoKCiv($id);
    }

    public function isAoCCiv($id)
    {
        return Civilization::isAoCCiv($id);
    }

    public function isForgottenCiv($id)
    {
        return Civilization::isForgottenCiv($id);
    }

    public function getMapName($id)
    {
        return Map::getMapName($id);
    }

    public function isRealWorldMap($id)
    {
        return Map::isRealWorldMap($id);
    }

    public function isCustomMap($id)
    {
        return Map::isCustomMap($id);
    }

    public function isStandardMap($id)
    {
        return Map::isStandardMap($id);
    }

    public function isGateUnit($id)
    {
        return Unit::isGateUnit($id);
    }

    public function isPalisadeGateUnit($id)
    {
        return Unit::isPalisadeGateUnit($id);
    }

    public function isCliffUnit($id)
    {
        return Unit::isCliffUnit($id);
    }

    public function isGaiaObject($id)
    {
        return Unit::isGaiaObject($id);
    }

    public function isGaiaUnit($id)
    {
        return Unit::isGaiaUnit($id);
    }

    public function normalizeUnit($id)
    {
        return Unit::normalizeUnit($id);
    }

    public function getTerrainColor($id)
    {
        return Colors::getTerrainColor($id);
    }

    public function getUnitColor($id)
    {
        return Colors::getUnitColor($id);
    }

    public function getPlayerColor($id)
    {
        return Colors::getPlayerColor($id);
    }
}
