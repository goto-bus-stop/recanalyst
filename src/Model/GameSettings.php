<?php

namespace RecAnalyst\Model;

use RecAnalyst\RecordedGame;
use RecAnalyst\Processors\MapName as MapNameExtractor;

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

    /**
     * Recorded game instance.
     *
     * @var \RecAnalyst\RecordedGame
     */
    private $rec;

    /**
     * Game type.
     *
     * @var int
     */
    public $gameType;

    /**
     * Difficulty level.
     *
     * @var int
     */
    public $difficultyLevel;

    /**
     * Game speed.
     *
     * @var int
     */
    public $gameSpeed;

    /**
     * Reveal Map setting.
     *
     * @var int
     */
    public $revealMap;

    /**
     * Map size.
     *
     * @var int
     */
    public $mapSize;

    /**
     * Map ID.
     *
     * @var int
     */
    public $mapId;

    /**
     * Population limit.
     *
     * @var int
     */
    public $popLimit;

    /**
     * Diplomacy lock status.
     *
     * @var bool
     */
    public $lockDiplomacy;

    /**
     * Class constructor.
     *
     * @return void
     */
    public function __construct(RecordedGame $rec, $attrs = [])
    {
        $this->rec = $rec;
        $this->difficultyLevel = isset($attrs['difficultyLevel']) ? $attrs['difficultyLevel'] : self::LEVEL_HARDEST;
        $this->gameSpeed = isset($attrs['gameSpeed']) ? $attrs['gameSpeed'] : self::SPEED_NORMAL;
        $this->revealMap = isset($attrs['revealMap']) ? $attrs['revealMap'] : self::REVEAL_NORMAL;
        $this->gameType = isset($attrs['gameType']) ? $attrs['gameType'] : self::TYPE_RANDOMMAP;
        $this->mapSize = isset($attrs['mapSize']) ? $attrs['mapSize'] : self::SIZE_TINY;
        $this->mapId = isset($attrs['mapId']) ? $attrs['mapId'] : 0;
        $this->popLimit = isset($attrs['popLimit']) ? $attrs['popLimit'] : 0;
        $this->lockDiplomacy = isset($attrs['lockDiplomacy']) ? $attrs['lockDiplomacy'] : false;
    }

    /**
     * Returns game type string.
     *
     * @return string
     */
    public function gameTypeName()
    {
        return $this->rec->trans('game_types', $this->gameType);
    }

    /**
     * Returns map style string.
     *
     * @return string
     */
    public function mapStyleName()
    {
        $mapStyle = $this->rec->getResourcePack()
            ->getMapStyle($this->mapId);
        return $this->rec->trans('map_styles', $mapStyle);
    }

    /**
     * Returns difficulty level string.
     *
     * @return string
     */
    public function difficultyName()
    {
        return $this->rec->trans('difficulties', $this->difficultyLevel);
    }

    /**
     * Returns game speed string.
     *
     * @return string
     */
    public function gameSpeedName()
    {
        return $this->rec->trans('game_speeds', $this->gameSpeed);
    }

    /**
     * Returns reveal map string.
     *
     * @return string
     */
    public function revealMapName()
    {
        return $this->rec->trans('reveal_map', $this->revealMap);
    }

    /**
     * Returns map size string.
     *
     * @return string
     */
    public function mapSizeName()
    {
        return $this->rec->trans('map_sizes', $this->mapSize);
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
     * Returns true if game type is scenario, false otherwise.
     *
     * @return bool
     */
    public function isScenario()
    {
        return $this->gameType == GameSettings::TYPE_SCENARIO;
    }

    /**
     * Get the map name.
     *
     * @param array  $options  Options.
     *   - `$options['extractRMSName']` - Whether to attempt to find the RMS
     *     file names of custom random maps. Defaults to `true`.
     *
     * @return string Map name.
     */
    public function mapName($options = [])
    {
        $extractRmsName = isset($options['extractRMSName']) ? $options['extractRMSName'] : true;
        if ($extractRmsName && $this->isCustomMap()) {
            $nameExtractor = new MapNameExtractor($this->rec);
            $likelyName = $nameExtractor->run();
            if ($likelyName) {
                return $likelyName;
            }
        }
        return $this->rec->trans('map_names', $this->mapId);
    }

    /**
     * Get the map style for a map ID. Age of Empires categorises the builtin
     * maps into several styles in the Start Game menu, but that information
     * is not stored in the recorded game file (after all, only the map itself
     * is necessary to replay the game).
     *
     * @return integer
     */
    public function mapStyle()
    {
        $resourcePack = $this->rec->getResourcePack();
        if ($resourcePack->isCustomMap($this->mapId)) {
            return GameSettings::MAPSTYLE_CUSTOM;
        } else if ($resourcePack->isRealWorldMap($this->mapId)) {
            return GameSettings::MAPSTYLE_REALWORLD;
        }
        // TODO add case for the "Special" maps in the HD expansion packs
        return GameSettings::MAPSTYLE_STANDARD;
    }

    /**
     * Check whether the game was played on a "Real World" map, such as
     * Byzantinum or Texas.
     *
     * @return bool True if the map is a "Real World" map, false otherwise.
     */
    public function isRealWorldMap()
    {
        $resourcePack = $this->rec->getResourcePack();
        return $resourcePack->isRealWorldMap($this->mapId);
    }

    /**
     * Check whether the game was played on a custom map.
     *
     * @return bool True if the map is a custom map, false if it is builtin.
     */
    public function isCustomMap()
    {
        $resourcePack = $this->rec->getResourcePack();
        return $resourcePack->isCustomMap($this->mapId);
    }

    /**
     * Check whether the game was played on a builtin map.
     *
     * @return bool True if the map is builtin, false otherwise.
     */
    public function isStandardMap()
    {
        $resourcePack = $this->rec->getResourcePack();
        return $resourcePack->isStandardMap($this->mapId);
    }
}
