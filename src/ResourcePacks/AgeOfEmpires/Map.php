<?php

namespace RecAnalyst\ResourcePacks\AgeOfEmpires;

/**
 * Some map constants.
 *
 * @api private
 */
class Map
{
    const ARABIA = 9;
    const ARCHIPELAGO = 10;
    const BALTIC = 11;
    const BLACKFOREST = 12;
    const COASTAL = 13;
    const CONTINENTAL = 14;
    const CRATERLAKE = 15;
    const FORTRESS = 16;
    const GOLDRUSH = 17;
    const HIGHLAND = 18;
    const ISLANDS = 19;
    const MEDITERRANEAN = 20;
    const MIGRATION = 21;
    const RIVERS = 22;
    const TEAMISLANDS = 23;
    const RANDOM = 24;
    const SCANDINAVIA = 25;
    const MONGOLIA = 26;
    const YUCATAN = 27;
    const SALTMARSH = 28;
    const ARENA = 29;
    const KINGOFTHEHILL = 30;
    const OASIS = 31;
    const GHOSTLAKE = 32;
    const NOMAD = 33;
    const IBERIA = 34;
    const BRITAIN = 35;
    const MIDEAST = 36;
    const TEXAS = 37;
    const ITALY = 38;
    const CENTRALAMERICA = 39;
    const FRANCE = 40;
    const NORSELANDS = 41;
    const SEAOFJAPAN = 42;
    const BYZANTINUM = 43;
    const CUSTOM = 44;
    const BLINDRANDOM = 48;
    const ACROPOLIS = 49;
    const BUDAPEST = 50;
    const CENOTES = 51;
    const CITYOFLAKES = 52;
    const GOLDENPIT = 53;
    const HIDEOUT = 54;
    const HILLFORT = 55;
    const LOMBARDIA = 56;
    const STEPPE = 57;
    const VALLEY = 58;
    const MEGARANDOM = 59;
    const HAMBURGER = 60;
    const CTR_RANDOM = 61;
    const CTR_MONSOON = 62;
    const CTR_PYRAMIDDESCENT = 63;
    const CTR_SPIRAL = 64;

    /**
     * Check whether a builtin map is a "Real World" map, such as Byzantinum or
     * Texas.
     *
     * @param int  $id  Map ID of a builtin map.
     * @return bool True if the map is a "Real World" map, false otherwise.
     */
    public static function isRealWorldMap($id)
    {
        return in_array($id, [
            self::IBERIA, self::BRITAIN, self::MIDEAST, self::TEXAS,
            self::ITALY, self::CENTRALAMERICA, self::FRANCE, self::NORSELANDS,
            self::SEAOFJAPAN, self::BYZANTINUM,
        ]);
    }

    /**
     * Check whether a map ID denotes a custom map (i.e., not a builtin one).
     *
     * @see \RecAnalyst\ResourcePacks\AgeOfEmpires::isStandardMap
     *     For the inverse.
     * @param int  $id  Map ID.
     * @return bool True if the map is a custom map, false if it is builtin.
     */
    public static function isCustomMap($id)
    {
        return $id === self::CUSTOM;
    }

    /**
     * Check whether a map ID denotes a builtin map.
     *
     * @see \RecAnalyst\ResourcePacks\AgeOfEmpires::isCustomMap
     *     For the inverse.
     * @param int  $id  Map ID.
     * @return bool True if the map is builtin, false otherwise.
     */
    public static function isStandardMap($id)
    {
        return in_array($id, [
              self::ARABIA, self::ARCHIPELAGO, self::BALTIC, self::BLACKFOREST,
              self::COASTAL, self::CONTINENTAL, self::CRATERLAKE,
              self::FORTRESS, self::GOLDRUSH, self::HIGHLAND, self::ISLANDS,
              self::MEDITERRANEAN, self::MIGRATION, self::RIVERS,
              self::TEAMISLANDS, self::SCANDINAVIA, self::MONGOLIA,
              self::YUCATAN, self::SALTMARSH, self::ARENA, self::KINGOFTHEHILL,
              self::OASIS, self::GHOSTLAKE, self::NOMAD,
              self::RANDOM,
        ]);
    }

    /**
     * Not instantiable.
     *
     * @return void
     */
    private function __construct()
    {
    }
}
