<?php

namespace RecAnalyst\ResourcePacks\AgeOfEmpires;

/**
 * Some map constants.
 */
class Map
{
    const ARABIA         = 9;
    const ARCHIPELAGO    = 10;
    const BALTIC         = 11;
    const BLACKFOREST    = 12;
    const COASTAL        = 13;
    const CONTINENTAL    = 14;
    const CRATERLAKE     = 15;
    const FORTRESS       = 16;
    const GOLDRUSH       = 17;
    const HIGHLAND       = 18;
    const ISLANDS        = 19;
    const MEDITERRANEAN  = 20;
    const MIGRATION      = 21;
    const RIVERS         = 22;
    const TEAMISLANDS    = 23;
    const RANDOM         = 24;
    const SCANDINAVIA    = 25;
    const MONGOLIA       = 26;
    const YUCATAN        = 27;
    const SALTMARSH      = 28;
    const ARENA          = 29;
    const KINGOFTHEHILL  = 30;
    const OASIS          = 31;
    const GHOSTLAKE      = 32;
    const NOMAD          = 33;
    const IBERIA         = 34;
    const BRITAIN        = 35;
    const MIDEAST        = 36;
    const TEXAS          = 37;
    const ITALY          = 38;
    const CENTRALAMERICA = 39;
    const FRANCE         = 40;
    const NORSELANDS     = 41;
    const SEAOFJAPAN     = 42;
    const BYZANTINUM     = 43;
    const CUSTOM         = 44;
    const BLINDRANDOM    = 48;

    public static $MAP_NAMES = [
        self::ARABIA         => 'Arabia',
        self::ARCHIPELAGO    => 'Archipelago',
        self::BALTIC         => 'Baltic',
        self::BLACKFOREST    => 'Black Forest',
        self::COASTAL        => 'Coastal',
        self::CONTINENTAL    => 'Continental',
        self::CRATERLAKE     => 'Crater Lake',
        self::FORTRESS       => 'Fortress',
        self::GOLDRUSH       => 'Gold Rush',
        self::HIGHLAND       => 'Highland',
        self::ISLANDS        => 'Islands',
        self::MEDITERRANEAN  => 'Mediterranean',
        self::MIGRATION      => 'Migration',
        self::RIVERS         => 'Rivers',
        self::TEAMISLANDS    => 'Team Islands',
        self::RANDOM         => 'Random',
        self::SCANDINAVIA    => 'Scandinavia',
        self::MONGOLIA       => 'Mongolia',
        self::YUCATAN        => 'Yucatan',
        self::SALTMARSH      => 'Salt Marsh',
        self::ARENA          => 'Arena',
        self::KINGOFTHEHILL  => 'King of the Hill',
        self::OASIS          => 'Oasis',
        self::GHOSTLAKE      => 'Ghost Lake',
        self::NOMAD          => 'Nomad',
        self::IBERIA         => 'Iberia',
        self::BRITAIN        => 'Britain',
        self::MIDEAST        => 'Mideast',
        self::TEXAS          => 'Texas',
        self::ITALY          => 'Italy',
        self::CENTRALAMERICA => 'Central America',
        self::FRANCE         => 'France',
        self::NORSELANDS     => 'Norse Lands',
        self::SEAOFJAPAN     => 'Sea of Japan (East Sea)',
        self::BYZANTINUM     => 'Byzantinum',
        self::CUSTOM         => 'Custom',
        self::BLINDRANDOM    => 'Blind Random',
    ];

    public static function getMapName($id)
    {
        return self::$MAP_NAMES[$id];
    }

    public static function isRealWorldMap($id)
    {
        return in_array($id, [
            self::IBERIA, self::BRITAIN, self::MIDEAST, self::TEXAS,
            self::ITALY, self::CENTRALAMERICA, self::FRANCE, self::NORSELANDS,
            self::SEAOFJAPAN, self::BYZANTINUM,
        ]);
    }

    public static function isCustomMap($id)
    {
        return $id === self::CUSTOM;
    }

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
