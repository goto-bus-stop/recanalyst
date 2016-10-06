<?php

namespace RecAnalyst\ResourcePacks\AgeOfEmpires;

/**
 * Unit represents a unit object in the game.
 */
class Unit
{
    // Unit IDs that we might draw on maps
    // GAIA (needed for colours when drawing)
    const GOLDMINE   = 66;
    const STONEMINE  = 102;
    const CLIFF1     = 264;
    const CLIFF2     = 265;
    const CLIFF3     = 266;
    const CLIFF4     = 267;
    const CLIFF5     = 268;
    const CLIFF6     = 269;
    const CLIFF7     = 270;
    const CLIFF8     = 271;
    const CLIFF9     = 272;
    const CLIFF10    = 273;
    const RELIC      = 285;
    const TURKEY     = 833;
    const SHEEP      = 594;
    const DEER       = 65;
    const BOAR       = 48;
    const JAVELINA   = 822;
    const FORAGEBUSH = 59;
    // Gates (needed for directions when drawing)
    const GATE  = 487;
    const GATE2 = 490;
    const GATE3 = 665;
    const GATE4 = 673;
    const PALISADE_GATE  = 792;
    const PALISADE_GATE2 = 796;
    const PALISADE_GATE3 = 800;
    const PALISADE_GATE4 = 804;

    public static function isGateUnit($id)
    {
        return in_array($id, [
            self::GATE,
            self::GATE2,
            self::GATE3,
            self::GATE4,
        ]);
    }

    public static function isPalisadeGateUnit($id)
    {
        return in_array($id, [
            self::PALISADE_GATE,
            self::PALISADE_GATE2,
            self::PALISADE_GATE3,
            self::PALISADE_GATE4,
        ]);
    }

    public static function isCliffUnit($id)
    {
        return in_array($id, [
            self::CLIFF1,
            self::CLIFF2,
            self::CLIFF3,
            self::CLIFF4,
            self::CLIFF5,
            self::CLIFF6,
            self::CLIFF7,
            self::CLIFF8,
            self::CLIFF9,
            self::CLIFF10,
        ]);
    }

    public static function isGaiaObject($id)
    {
        return self::isCliffUnit($id) || in_array($id, [
            self::GOLDMINE,
            self::STONEMINE,
            self::FORAGEBUSH,
        ]);
    }

    public static function isGaiaUnit($id)
    {
        return in_array($id, [
            self::RELIC,
            self::DEER,
            self::BOAR,
            self::JAVELINA,
            self::TURKEY,
            self::SHEEP,
        ]);
    }

    public static function normalizeUnit($id)
    {
        if (self::isGateUnit($id)) {
            return self::GATE;
        }
        if (self::isPalisadeGateUnit($id)) {
            return self::PALISADE_GATE;
        }
        return $id;
    }
}
