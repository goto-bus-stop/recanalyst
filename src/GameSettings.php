<?php

namespace RecAnalyst;

class GameSettings
{
    const TYPE_RANDOMMAP  = 0;
    const TYPE_REGICIDE   = 1;
    const TYPE_DEATHMATCH = 2;
    const TYPE_SCENARIO   = 3;
    const TYPE_CAMPAIGN   = 4;

    const MAPSTYLE_STANDARD  = 0;
    const MAPSTYLE_REALWORLD = 1;
    const MAPSTYLE_CUSTOM    = 2;

    const LEVEL_HARDEST  = 0;
    const LEVEL_HARD     = 1;
    const LEVEL_MODERATE = 2;
    const LEVEL_STANDARD = 3;
    const LEVEL_EASIEST  = 4;

    const SPEED_SLOW   = 100;
    const SPEED_NORMAL = 150;
    const SPEED_FAST   = 200;

    const REVEAL_NORMAL     = 0;
    const REVEAL_EXPLORED   = 1;
    const REVEAL_ALLVISIBLE = 2;

    const SIZE_TINY   = 0;
    const SIZE_SMALL  = 1;
    const SIZE_MEDIUM = 2;
    const SIZE_NORMAL = 3;
    const SIZE_LARGE  = 4;
    const SIZE_GIANT  = 5;

    const MODE_SINGLEPLAYER = 0;
    const MODE_MULTIPLAYER = 1;

    /** @var int Game type. */
    public $gameType;

    /** @var int Map style. */
    public $mapStyle;

    /** @var int Difficulty level. */
    public $difficultyLevel;

    /** @var int Game speed. */
    public $gameSpeed;

    /** @var int Reveal Map setting. */
    public $revealMap;

    /** @var int Map size. */
    public $mapSize;

    /** @var int Map ID. */
    public $mapId;

    /** @var string Map name. */
    public $map;

    /** @var int Population limit. */
    public $popLimit;

    /** @var bool Diplomacy lock status. */
    public $lockDiplomacy;

    /** @var \RecAnalyst\VictorySettings Victory settings. */
    public $victory;

    /**
     * Class constructor.
     *
     * @return void
     */
    public function __construct($attrs = [])
    {
        $this->difficultyLevel = self::LEVEL_HARDEST;
        $this->gameSpeed = self::SPEED_NORMAL;
        $this->revealMap = self::REVEAL_NORMAL;
        $this->gameType = isset($attrs['gameType']) ? $attrs['gameType'] : self::TYPE_RANDOMMAP;
        $this->mapStyle = isset($attrs['mapStyle']) ? $attrs['mapStyle'] : self::MAPSTYLE_STANDARD;
        $this->mapSize = isset($attrs['mapSize']) ? $attrs['mapSize'] : self::SIZE_TINY;
        $this->mapName = isset($attrs['mapName']) ? $attrs['mapName'] : '';
        $this->mapId = isset($attrs['mapId']) ? $attrs['mapId'] : 0;
        $this->popLimit = isset($attrs['popLimit']) ? $attrs['popLimit'] : 0;
        $this->lockDiplomacy = isset($attrs['lockDiplomacy']) ? $attrs['lockDiplomacy'] : false;
        $this->victory = new VictorySettings();

        // Compatibility
        $this->map = $this->mapName;
    }

    /**
     * Returns game type string.
     *
     * @return string
     */
    public function getGameTypeString()
    {
        return isset(RecAnalystConst::$GAME_TYPES[$this->gameType]) ?
            RecAnalystConst::$GAME_TYPES[$this->gameType] : '';
    }

    /**
     * Returns map style string.
     *
     * @return string
     */
    public function getMapStyleString()
    {
        return isset(RecAnalystConst::$MAP_STYLES[$this->mapStyle]) ?
            RecAnalystConst::$MAP_STYLES[$this->mapStyle] : '';
    }

    /**
     * Returns difficulty level string.
     *
     * @return string
     */
    public function getDifficultyLevelString()
    {
        return RecAnalystConst::$DIFFICULTY_LEVELS[$this->difficultyLevel];
    }

    /**
     * Returns game speed string.
     *
     * @return string
     */
    public function getGameSpeedString()
    {
        if (isset(RecAnalystConst::$GAME_SPEEDS[$this->gameSpeed])) {
            return RecAnalystConst::$GAME_SPEEDS[$this->gameSpeed];
        } else {
            return sprintf('(%.1f)', $this->gameSpeed / 10);
        }
    }

    /**
     * Returns reveal map string.
     *
     * @return string
     */
    public function getRevealMapString()
    {
        return isset(RecAnalystConst::$REVEAL_SETTINGS[$this->revealMap]) ?
            RecAnalystConst::$REVEAL_SETTINGS[$this->revealMap] : '';
    }

    /**
     * Returns map size string.
     *
     * @return string
     */
    public function getMapSizeString()
    {
        return isset(RecAnalystConst::$MAP_SIZES[$this->mapSize]) ?
            RecAnalystConst::$MAP_SIZES[$this->mapSize] : '';
    }

    /**
     * Returns map name.
     *
     * @return string
     */
    public function getMapName()
    {
        return $this->map;
    }

    /**
     * Returns population limit.
     *
     * @return int
     */
    public function getPopLimit()
    {
        return $this->popLimit;
    }

    /**
     * Returns whether diplomacy was locked.
     *
     * @return bool
     */
    public function getLockDiplomacy()
    {
        return $this->lockDiplomacy;
    }

    /**
     * Returns victory settings.
     *
     * @return VictorySettings
     */
    public function getVictorySettings()
    {
        return $this->victory;
    }

    /**
     * Returns true if game type is scenario, false otherwise.
     *
     * @return bool
     */
    public function isScenario()
    {
        return $this->gameType == GameSettings::TYPE_SCENARIO;
    }
}
