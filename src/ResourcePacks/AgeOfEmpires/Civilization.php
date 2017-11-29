<?php

namespace RecAnalyst\ResourcePacks\AgeOfEmpires;

/**
 * Utilities for dealing with Age of Empires civilizations.
 *
 * @api private
 */
class Civilization
{
    const NONE       = 0;
    const BRITONS    = 1;
    const FRANKS     = 2;
    const GOTHS      = 3;
    const TEUTONS    = 4;
    const JAPANESE   = 5;
    const CHINESE    = 6;
    const BYZANTINES = 7;
    const PERSIANS   = 8;
    const SARACENS   = 9;
    const TURKS      = 10;
    const VIKINGS    = 11;
    const MONGOLS    = 12;
    const CELTS      = 13;
    const SPANISH    = 14;
    const AZTECS     = 15;
    const MAYANS     = 16;
    const HUNS       = 17;
    const KOREANS    = 18;
    const ITALIANS   = 19;
    const INDIANS    = 20;
    const INCAS      = 21;
    const MAGYARS    = 22;
    const SLAVS      = 23;

    public static $CIV_NAMES = [
        self::NONE => '',
        self::BRITONS => 'Britons',
        self::FRANKS => 'Franks',
        self::GOTHS => 'Goths',
        self::TEUTONS => 'Teutons',
        self::JAPANESE => 'Japanese',
        self::CHINESE => 'Chinese',
        self::BYZANTINES => 'Byzantines',
        self::PERSIANS => 'Persians',
        self::SARACENS => 'Saracens',
        self::TURKS => 'Turks',
        self::VIKINGS => 'Vikings',
        self::MONGOLS => 'Mongols',
        self::CELTS => 'Celts',
        self::SPANISH => 'Spanish',
        self::AZTECS => 'Aztecs',
        self::MAYANS => 'Mayans',
        self::HUNS => 'Huns',
        self::KOREANS => 'Koreans',
        self::ITALIANS => 'Italians',
        self::INDIANS => 'Indians',
        self::INCAS => 'Incas',
        self::MAGYARS => 'Magyars',
        self::SLAVS => 'Slavs',
    ];

    /**
     * Get the in-game name of a civilization.
     *
     * @param int  $id  Civilization ID.
     * @return string|null Civilization name.
     */
    public static function getCivName($id)
    {
        if (array_key_exists($id, self::$CIV_NAMES)) {
            return self::$CIV_NAMES[$id];
        }
    }

    /**
     * Checks if a civilization is included in the Age of Kings base game.
     *
     * @param int  $id  Civilization ID.
     * @return bool True if the given civilization exists in AoK, false
     *     otherwise.
     */
    public static function isAoKCiv($id)
    {
        return in_array($id, [
            self::BRITONS,
            self::FRANKS,
            self::GOTHS,
            self::TEUTONS,
            self::JAPANESE,
            self::CHINESE,
            self::BYZANTINES,
            self::PERSIANS,
            self::SARACENS,
            self::TURKS,
            self::VIKINGS,
            self::MONGOLS,
            self::CELTS,
        ]);
    }

    /**
     * Checks if a civilization was added in the Age of Conquerors expansion.
     *
     * @param int  $id  Civilization ID.
     * @return bool True if the given civilization is part of AoC, false
     *     otherwise.
     */
    public static function isAoCCiv($id)
    {
        return in_array($id, [
            self::SPANISH,
            self::AZTECS,
            self::MAYANS,
            self::HUNS,
            self::KOREANS,
        ]);
    }

    /**
     * Checks if a civilization was added in the Forgotten Empires expansion.
     *
     * @param int  $id  Civilization ID.
     * @return bool True if the given civilization is part of The Forgotten,
     *     false otherwise.
     */
    public static function isForgottenCiv($id)
    {
        return in_array($id, [
            self::ITALIANS,
            self::INDIANS,
            self::INCAS,
            self::MAGYARS,
            self::SLAVS,
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
