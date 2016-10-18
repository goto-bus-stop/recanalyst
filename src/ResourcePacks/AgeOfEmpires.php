<?php

namespace RecAnalyst\ResourcePacks;

use RecAnalyst\ResourcePacks\AgeOfEmpires\Map;
use RecAnalyst\ResourcePacks\AgeOfEmpires\Unit;
use RecAnalyst\ResourcePacks\AgeOfEmpires\Colors;
use RecAnalyst\ResourcePacks\AgeOfEmpires\Civilization;

/**
 * Resource pack for Age of Empires 2 recorded games of all stripes (base game,
 * expansions, HD Edition, Userpatch, …).
 */
class AgeOfEmpires extends ResourcePack
{
    const NAME = 'ageofempires';

    /**
     * Get the English name for a civilization.
     *
     * @param int  $id  Civilization ID.
     * @return string Civilization name.
     */
    public function getCivName($id)
    {
        return Civilization::getCivName($id);
    }

    /**
     * Checks if a civilization is included in the Age of Kings base game.
     *
     * @param int  $id  Civilization ID.
     * @return bool True if the given civilization exists in AoK, false
     *     otherwise.
     */
    public function isAoKCiv($id)
    {
        return Civilization::isAoKCiv($id);
    }

    /**
     * Checks if a civilization was added in the Age of Conquerors expansion.
     *
     * @param int  $id  Civilization ID.
     * @return bool True if the given civilization is part of AoC, false
     *     otherwise.
     */
    public function isAoCCiv($id)
    {
        return Civilization::isAoCCiv($id);
    }

    /**
     * Checks if a civilization was added in the Forgotten Empires expansion.
     *
     * @param int  $id  Civilization ID.
     * @return bool True if the given civilization is part of The Forgotten,
     *     false otherwise.
     */
    public function isForgottenCiv($id)
    {
        return Civilization::isForgottenCiv($id);
    }

    /**
     * Get the in-game name of a builtin map.
     *
     * @param int  $id  Map ID of a builtin map.
     * @return string|null Map name.
     */
    public function getMapName($id)
    {
        return Map::getMapName($id);
    }

    /**
     * Check whether a builtin map is a "Real World" map, such as Byzantinum or
     * Texas.
     *
     * @param int  $id  Map ID of a builtin map.
     * @return bool True if the map is a "Real World" map, false otherwise.
     */
    public function isRealWorldMap($id)
    {
        return Map::isRealWorldMap($id);
    }

    /**
     * Check whether a map ID denotes a custom map (i.e., not a builtin one).
     *
     * @see \RecAnalyst\ResourcePacks\AgeOfEmpires::isStandardMap
     *     For the inverse.
     *
     * @param int  $id  Map ID.
     * @return bool True if the map is a custom map, false if it is builtin.
     */
    public function isCustomMap($id)
    {
        return Map::isCustomMap($id);
    }

    /**
     * Check whether a map ID denotes a builtin map.
     *
     * @see \RecAnalyst\ResourcePacks\AgeOfEmpires::isCustomMap
     *     For the inverse.
     *
     * @param int  $id  Map ID.
     * @return bool True if the map is builtin, false otherwise.
     */
    public function isStandardMap($id)
    {
        return Map::isStandardMap($id);
    }

    /**
     * Checks whether a unit type ID is a Gate unit.
     *
     * @param int  $id  Unit type ID.
     * @return bool True if the unit type is a gate, false otherwise.
     */
    public function isGateUnit($id)
    {
        return Unit::isGateUnit($id);
    }

    /**
     * Checks whether a unit type ID is a Palisade Gate unit.
     *
     * @param int  $id  Unit type ID.
     * @return bool True if the unit type is a palisade gate, false otherwise.
     */
    public function isPalisadeGateUnit($id)
    {
        return Unit::isPalisadeGateUnit($id);
    }

    /**
     * Checks whether a unit type ID is a cliff. (Yes! Cliffs are units!)
     *
     * @param int  $id  Unit type ID.
     * @return bool True if the unit type is a cliff, false otherwise.
     */
    public function isCliffUnit($id)
    {
        return Unit::isCliffUnit($id);
    }

    /**
     * Checks whether a unit type ID is a GAIA object type. Used to determine
     * which objects to draw on a map.
     *
     * @param int  $id  Unit type ID.
     * @return bool True if the unit type is a GAIA object, false otherwise.
     */
    public function isGaiaObject($id)
    {
        return Unit::isGaiaObject($id);
    }

    /**
     * Checks whether a unit type ID is a GAIA unit. Used to determine which
     * units to draw on a map as not belonging to any player.
     *
     * @param int  $id  Unit type ID.
     * @return bool True if the unit type is a GAIA unit, false otherwise.
     */
    public function isGaiaUnit($id)
    {
        return Unit::isGaiaUnit($id);
    }

    /**
     * Normalize a unit type ID. Turns some groups of unit IDs (such as gates in
     * four directions) into a single unit ID, so it's easier to work with.
     *
     * @param int  $id  Unit type ID.
     * @return int Normalized unit type ID.
     */
    public function normalizeUnit($id)
    {
        return Unit::normalizeUnit($id);
    }

    /**
     * Get the color for a terrain type.
     *
     * @param int  $id  Terrain type ID.
     * @param int  $variation  Terrain variation: 0 for a downward slope, 2 for
     *     an upward slope, 1 for flat terrain (default).
     * @return string Hexadecimal representation of the terrain color,
     *    eg. "#004abb".
     */
    public function getTerrainColor($id, $variation = 1)
    {
        return Colors::getTerrainColor($id, $variation);
    }

    /**
     * Get the color for a unit or object type, such as sheep or boar or
     * cliffs(!).
     *
     * @param int  $id  Unit type ID.
     * @return string Hexadecimal representation of the unit color,
     *    eg. "#714b33".
     */
    public function getUnitColor($id)
    {
        return Colors::getUnitColor($id);
    }

    /**
     * Get the color for a player.
     *
     * @param int  $id  Player color ID (0-7).
     * @return string Hexadecimal representation of the player color,
     *    eg. "#ff00ff".
     */
    public function getPlayerColor($id)
    {
        return Colors::getPlayerColor($id);
    }
}
