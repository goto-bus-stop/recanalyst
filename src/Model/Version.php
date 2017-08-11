<?php

namespace RecAnalyst\Model;

use RecAnalyst\RecordedGame;

class Version
{
    /**
     * Version ID for unknown game versions.
     *
     * @var int
     */
    const VERSION_UNKNOWN = 0;

    /**
     * Version ID for the Age of Kings base game.
     *
     * @var int
     */
    const VERSION_AOK = 1;

    /**
     * Version ID for the Age of Kings Trial version.
     *
     * @var int
     */
    const VERSION_AOKTRIAL = 2;

    /**
     * Version ID for the Age of Kings base game, patch version 2.0.
     *
     * @var int
     */
    const VERSION_AOK20 = 3;

    /**
     * Version ID for the Age Of Kings base game, patch version 2.0a.
     *
     * @var int
     */
    const VERSION_AOK20A = 4;

    /**
     * Version ID for the Age of Conquerors expansion.
     *
     * @var int
     */
    const VERSION_AOC = 5;

    /**
     * Version ID for the Age of Conquerors expansion (Trial version).
     *
     * @var int
     */
    const VERSION_AOCTRIAL = 6;

    /**
     * Version ID for the Age of Conquerors expansion.
     *
     * @var int
     */
    const VERSION_AOC10 = 7;

    /**
     * Version ID for the Age Of Conquerors expansion, patch version 1.0c.
     *
     * @var int
     */
    const VERSION_AOC10C = 8;

    /**
     * Version ID for UserPatch + Forgotten Empires v2.1.
     *
     * @var int
     */
    const VERSION_AOFE21 = 10;

    /**
     * Version ID for UserPatch v1.1.
     *
     * @var int
     */
    const VERSION_USERPATCH11 = 9;

    /**
     * Version ID for UserPatch v1.2.
     *
     * @var int
     */
    const VERSION_USERPATCH12 = 12;

    /**
     * Version ID for UserPatch v1.3.
     *
     * @var int
     */
    const VERSION_USERPATCH13 = 13;

    /**
     * Version ID for UserPatch v1.4.
     *
     * @var int
     */
    const VERSION_USERPATCH14 = 11;

    /**
     * Version ID for HD Edition.
     *
     * @var int
     */
    const VERSION_HD = 14;

    /**
     * Version ID for HD Edition patch 4.3.
     *
     * @var int
     */
    const VERSION_HD43 = 15;

    /**
     * Version ID for HD Edition patch 4.6.
     *
     * @var int
     */
    const VERSION_HD46 = 16;

    /**
     * Version ID for HD Edition patch 4.7.
     *
     * @var int
     */
    const VERSION_HD47 = 17;

    /**
     * Version ID for HD Edition patch 4.8.
     *
     * @var int
     */
    const VERSION_HD48 = 18;

    /**
     * Version ID for HD Edition patch 5.0.
     *
     * @var int
     */
    const VERSION_HD50 = 19;

    /**
     * Recorded game instance.
     *
     * @var \RecAnalyst\RecordedGame
     */
    private $rec;

    /**
     * Create a new version data instance.
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param string  $string  Version string from the game header.
     * @param float  $subVersion  Sub-version number.
     */
    public function __construct(RecordedGame $rec, $string, $subVersion)
    {
        $this->rec = $rec;
        $this->versionString = $string;
        $this->subVersion = $subVersion;
    }

    /**
     * Get a localised version name.
     *
     * @return string
     */
    public function name()
    {
        return $this->rec->trans('game_versions', $this->version);
    }
}
