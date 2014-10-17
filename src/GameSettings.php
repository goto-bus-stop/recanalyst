<?php
/**
 * Defines GameSettings class.
 *
 * @package RecAnalyst
 */

namespace RecAnalyst;

/**
 * Guess what the GameSettings class contains.
 *
 * @package RecAnalyst
 */
class GameSettings
{

    /* Game Types */
    const TYPE_RANDOMMAP  = 0;
    const TYPE_REGICIDE   = 1;
    const TYPE_DEATHMATCH = 2;
    const TYPE_SCENARIO   = 3;
    const TYPE_CAMPAIGN   = 4;

    /* Map Styles */
    const MAPSTYLE_STANDARD  = 0;
    const MAPSTYLE_REALWORLD = 1;
    const MAPSTYLE_CUSTOM    = 2;

    /* Difficulty Level */
    const LEVEL_HARDEST  = 0;
    const LEVEL_HARD     = 1;
    const LEVEL_MODERATE = 2;
    const LEVEL_STANDARD = 3;
    const LEVEL_EASIEST  = 4;

    /* Game Speed */
    const SPEED_SLOW   = 100;
    const SPEED_NORMAL = 150;
    const SPEED_FAST   = 200;

    /* Reveal Map */
    const REVEAL_NORMAL     = 0;
    const REVEAL_EXPLORED   = 1;
    const REVEAL_ALLVISIBLE = 2;

    /* Map Size */
    const SIZE_TINY   = 0;
    const SIZE_SMALL  = 1;
    const SIZE_MEDIUM = 2;
    const SIZE_NORMAL = 3;
    const SIZE_LARGE  = 4;
    const SIZE_GIANT  = 5;

    /* Game Mode */
    const MODE_SINGLEPLAYER = 0;
    const MODE_MULTIPLAYER = 1;

    /**
     * RecAnalyst owner instance.
     * @var RecAnalyst
     */
    protected $ra;

    /**
     * Game type.
     * @var int
     */
    public $gameType;

    /**
     * Map style.
     * @var int
     */
    public $mapStyle;

    /**
     * Difficulty level.
     * @var int
     */
    public $difficultyLevel;

    /**
     * Game speed.
     * @var int
     */
    public $gameSpeed;

    /**
     * Reveal Map setting.
     * @var int
     */
    public $revealMap;

    /**
     * Map size.
     * @var int
     */
    public $mapSize;

    /**
     * Map id.
     * @var int
     * @see Map
     */
    public $mapId;

    /**
     * Map.
     * @var string
     */
    public $map;

    /**
     * Population limit.
     * @var int
     */
    public $popLimit;

    /**
     * Diplomacy lock status.
     * @var bool
     */
    public $lockDiplomacy;

    /**
     * Victory settings.
     * @var Victory
     */
    public $victory;

    /**
     * Class constructor.
     *
     * @param RecAnalyst $recanalyst The RA instance that this will belong to.
     *
     * @return void
     */
    public function __construct(RecAnalyst $recanalyst)
    {
        $this->ra = $recanalyst;
        $this->gameType = GameSettings::TYPE_RANDOMMAP;
        $this->mapStyle = self::MAPSTYLE_STANDARD;
        $this->difficultyLevel = self::LEVEL_HARDEST;
        $this->gameSpeed = self::SPEED_NORMAL;
        $this->revealMap = self::REVEAL_NORMAL;
        $this->mapSize = self::SIZE_TINY;
        $this->map = '';
        $this->mapId = $this->popLimit = 0;
        $this->lockDiplomacy = false;
        $this->victory = new VictorySettings();
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
        switch ($this->ra->gameInfo->_gameVersion) {
        case GameInfo::VERSION_AOC:
        case GameInfo::VERSION_AOC10:
        case GameInfo::VERSION_AOC10C:
        case GameInfo::VERSION_AOCTRIAL:
            return RecAnalystConst::$DIFFICULTY_LEVELS[$this->difficultyLevel];
            break;
        case GameInfo::VERSION_AOK:
        case GameInfo::VERSION_AOK20:
        case GameInfo::VERSION_AOK20A:
        case GameInfo::VERSION_AOKTRIAL:
            return RecAnalystConst::$AOK_DIFFICULTY_LEVELS[$this->difficultyLevel];
            break;
        case GameInfo::VERSION_UNKNOWN:
        default:
            return '';
            break;
        }
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
