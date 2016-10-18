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
     * Get colors from the resources file.
     *
     * @param string  $category  Color category.
     * @return array
     */
    private static function getColors($category)
    {
        static $colors;
        if (empty($colors)) {
            $colors = require(__DIR__ . '/../../../resources/data/ageofempires/colors.php');
        }
        return $colors[$category];
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
    public static function getTerrainColor($id, $variation = 1)
    {
        $terrainColors = self::getColors('terrain');
        return $terrainColors[$id][$variation];
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
        $playerColors = self::getColors('players');
        return $playerColors[$id];
    }
}
