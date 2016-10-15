<?php

namespace RecAnalyst\ResourcePacks\AgeOfEmpires;

/**
 * Utilities for colors of things in Age of Empires.
 *
 * @api private
 */
class Colors
{
    /**
     * Default terrain colors, indexed by ID.
     *
     * @var array
     */
    public static $TERRAIN_COLORS = [
        '#339727',
        '#305db6',
        '#e8b478',
        '#e4a252',
        '#5492b0',
        '#339727',
        '#e4a252',
        '#82884d',
        '#82884d',
        '#339727',
        '#157615',
        '#e4a252',
        '#339727',
        '#157615',
        '#e8b478',
        '#305db6',
        '#339727',
        '#157615',
        '#157615',
        '#157615',
        '#157615',
        '#157615',
        '#004aa1',
        '#004abb',
        '#e4a252',
        '#e4a252',
        '#ffec49',
        '#e4a252',
        '#305db6',
        '#82884d',
        '#82884d',
        '#82884d',
        '#c8d8ff',
        '#c8d8ff',
        '#c8d8ff',
        '#98c0f0',
        '#c8d8ff',
        '#98c0f0',
        '#c8d8ff',
        '#c8d8ff',
        '#e4a252',
    ];

    /**
     * Default colors of GAIA-owned objects, indexed by unit ID.
     *
     * @var array
     */
    public static $GAIA_COLORS = [
        Unit::GOLDMINE   => '#ffc700',
        Unit::STONEMINE  => '#919191',
        Unit::CLIFF1     => '#714b33',
        Unit::CLIFF2     => '#714b33',
        Unit::CLIFF3     => '#714b33',
        Unit::CLIFF4     => '#714b33',
        Unit::CLIFF5     => '#714b33',
        Unit::CLIFF6     => '#714b33',
        Unit::CLIFF7     => '#714b33',
        Unit::CLIFF8     => '#714b33',
        Unit::CLIFF9     => '#714b33',
        Unit::CLIFF10    => '#714b33',
        Unit::RELIC      => '#ffffff',
        Unit::TURKEY     => '#a5c46c',
        Unit::SHEEP      => '#a5c46c',
        Unit::DEER       => '#a5c46c',
        Unit::BOAR       => '#a5c46c',
        Unit::JAVELINA   => '#a5c46c',
        Unit::FORAGEBUSH => '#a5c46c',
    ];

    /**
     * Default player colors.
     *
     * @var array
     */
    public static $PLAYER_COLORS = [
        0 => '#0000ff',
        1 => '#ff0000',
        2 => '#00ff00',
        3 => '#ffff00',
        4 => '#00ffff',
        5 => '#ff00ff',
        6 => '#b9b9b9',
        7 => '#ff8201',
    ];

    /**
     * Get the color for a terrain type.
     *
     * @param int  $id  Terrain type ID.
     * @return string Hexadecimal representation of the terrain color,
     *    eg. "#004abb".
     */
    public static function getTerrainColor($id)
    {
        return self::$TERRAIN_COLORS[$id];
    }

    /**
     * Get the color for a unit or object type, such as sheep or boar or
     * cliffs(!).
     *
     * @param int  $id  Unit type ID.
     * @return string Hexadecimal representation of the unit color,
     *    eg. "#714b33".
     */
    public static function getUnitColor($id)
    {
        return self::$GAIA_COLORS[$id];
    }

    /**
     * Get the color for a player.
     *
     * @param int  $id  Player color ID (0-7).
     * @return string Hexadecimal representation of the player color,
     *    eg. "#ff00ff".
     */
    public static function getPlayerColor($id)
    {
        return self::$PLAYER_COLORS[$id];
    }
}
