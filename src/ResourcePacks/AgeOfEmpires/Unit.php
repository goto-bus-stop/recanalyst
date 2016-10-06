<?php

namespace RecAnalyst\ResourcePacks\AgeOfEmpires;

/**
 * Utilities for working with Age of Empires unit types.
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

    /**
     * Checks whether a unit type ID is a Gate unit.
     *
     * @param int  $id  Unit type ID.
     * @return bool True if the unit type is a gate, false otherwise.
     */
    public static function isGateUnit($id)
    {
        return in_array($id, [
            self::GATE,
            self::GATE2,
            self::GATE3,
            self::GATE4,
        ]);
    }

    /**
     * Checks whether a unit type ID is a Palisade Gate unit.
     *
     * @param int  $id  Unit type ID.
     * @return bool True if the unit type is a palisade gate, false otherwise.
     */
    public static function isPalisadeGateUnit($id)
    {
        return in_array($id, [
            self::PALISADE_GATE,
            self::PALISADE_GATE2,
            self::PALISADE_GATE3,
            self::PALISADE_GATE4,
        ]);
    }

    /**
     * Checks whether a unit type ID is a cliff. (Yes! Cliffs are units!)
     *
     * @param int  $id  Unit type ID.
     * @return bool True if the unit type is a cliff, false otherwise.
     */
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

    /**
     * Checks whether a unit type ID is a GAIA object type. Used to determine
     * which objects to draw on a map.
     *
     * @param int  $id  Unit type ID.
     * @return bool True if the unit type is a GAIA object, false otherwise.
     */
    public static function isGaiaObject($id)
    {
        return self::isCliffUnit($id) || in_array($id, [
            self::GOLDMINE,
            self::STONEMINE,
            self::FORAGEBUSH,
        ]);
    }

    /**
     * Checks whether a unit type ID is a GAIA unit. Used to determine which
     * units to draw on a map as not belonging to any player.
     *
     * @param int  $id  Unit type ID.
     * @return bool True if the unit type is a GAIA unit, false otherwise.
     */
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

    /**
     * Normalize a unit type ID. Turns some groups of unit IDs (such as gates in
     * four directions) into a single unit ID, so it's easier to work with.
     *
     * @param int  $id  Unit type ID.
     * @return int Normalized unit type ID.
     */
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
