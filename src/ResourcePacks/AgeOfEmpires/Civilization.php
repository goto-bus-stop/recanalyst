<?php

namespace RecAnalyst\ResourcePacks\AgeOfEmpires;

/**
 * Some civilization constants.
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

    public static function isAoKCiv($id)
    {
        return in_array($id, [
            Civilization::BRITONS,
            Civilization::FRANKS,
            Civilization::GOTHS,
            Civilization::TEUTONS,
            Civilization::JAPANESE,
            Civilization::CHINESE,
            Civilization::BYZANTINES,
            Civilization::PERSIANS,
            Civilization::SARACENS,
            Civilization::TURKS,
            Civilization::VIKINGS,
            Civilization::MONGOLS,
            Civilization::CELTS,
        ]);
    }

    public static function isAoCCiv($id)
    {
        return in_array($id, [
            Civilization::SPANISH,
            Civilization::AZTECS,
            Civilization::MAYANS,
            Civilization::HUNS,
            Civilization::KOREANS,
        ]);
    }

    public static function isForgottenCiv($id)
    {
        return in_array($id, [
            Civilization::ITALIANS,
            Civilization::INDIANS,
            Civilization::INCAS,
            Civilization::MAGYARS,
            Civilization::SLAVS,
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
