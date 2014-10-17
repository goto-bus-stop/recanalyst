<?php
/**
 * Defines GameInfo class.
 *
 * @package RecAnalyst
 */

namespace RecAnalyst;

/**
 * GameInfo holds metadata about the analyzed game.
 *
 * @package RecAnalyst
 */
class GameInfo
{
    const VERSION_UNKNOWN     = 0;
    // AoKings
    const VERSION_AOK         = 1;
    const VERSION_AOKTRIAL    = 2;
    const VERSION_AOK20       = 3;
    const VERSION_AOK20A      = 4;
    // AoConquerors
    const VERSION_AOC         = 5;
    const VERSION_AOCTRIAL    = 6;
    const VERSION_AOC10       = 7;
    const VERSION_AOC10C      = 8;
    // AoConquerors + UserPatch (derp. Weird numbers because I suck.)
    const VERSION_AOFE21      = 10;
    const VERSION_UserPatch11 = 9;
    const VERSION_UserPatch12 = 12;
    const VERSION_UserPatch13 = 13;
    const VERSION_UserPatch14 = 11;
    // HD Edition
    const VERSION_HD = 14;

    /**
     * RecAnalyst owner instance.
     * @var RecAnalyst
     */
    protected $owner;

    /**
     * Game version.
     * @var int
     * @see Const\GameVersion
     */
    public $gameVersion;
    
    /**
     * Game sub-version. E.g., Game version = HD, Game sub-version = 3.0.
     * @var int
     */
    public $gameSubVersion;

    /**
     * Game duration.
     * @var int
     */
    public $playTime;

    /**
     * Objectives string.
     * @var string
     */
    public $objectivesString;

    /**
     * Original Scenario filename.
     * @var string
     */
    public $scFileName;

    /**
     * Class constructor.
     *
     * @param RecAnalyst $recanalyst Owner.
     *
     * @return void
     */
    public function __construct(RecAnalyst $recanalyst)
    {
        $this->owner = $recanalyst;
        $this->gameVersion = self::VERSION_UNKNOWN;
        $this->playTime = 0;
        $this->objectivesString = $this->scFileName = '';
    }

    /**
     * Returns game versions string.
     *
     * @return string
     */
    public function getGameVersionString()
    {
        return isset(RecAnalystConst::$GAME_VERSIONS[$this->gameVersion]) ?
            RecAnalystConst::$GAME_VERSIONS[$this->gameVersion] : '';
    }

    /**
     * Returns the players string (1v1, FFA, etc.)
     *
     * @return string
     */
    public function getPlayersString()
    {
        // players
        $idx = 0;
        $team_ary = array(0, 0, 0, 0, 0, 0, 0, 0);
        foreach ($this->owner->teams as $team) {
            foreach ($team->players as $player) {
                if (!$player->isCooping) {
                    $team_ary[$idx]++;
                }
            }
            $idx++;
        }
        $team_ary = array_diff($team_ary, array(0));
        if (array_sum($team_ary) === count($this->owner->teams)
            && count($this->owner->teams) > 2
        ) {
            return 'FFA';
        } else {
            return implode($team_ary, 'v');
        }
    }

    /**
     * Returns the point of view.
     *
     * @return string
     */
    public function getPOV()
    {
        foreach ($this->owner->players as $player) {
            if ($player->owner) {
                return $player->name;
            }
        }
        return '';
    }

    /**
     * Returns extended point of view (including coop players).
     *
     * @return string POV player name(s).
     */
    public function getPOVEx()
    {
        $owner = null;
        foreach ($this->owner->players as $player) {
            if ($player->owner) {
                $owner = $player;
                break;
            }
        }
        if (!$owner) {
            return '';
        }

        $names = array();
        foreach ($this->owner->players as $player) {
            if ($player === $owner) {
                continue;
            }
            if ($player->index == $owner->index) {
                $names[] = $player->name;
            }
        }
        if (empty($names)) {
            return $owner->name;
        }
        return sprintf('%s (%s)', $owner->name, implode($names, ', '));
    }

    /**
     * Determines if there is a cooping player in the game.
     *
     * @return bool True, if there is a cooping player in the game, false otherwise.
     */
    public function ingameCoop()
    {
        foreach ($this->owner->players as $player) {
            if ($player->isCooping) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the duration of the game.
     *
     * @return number The duration of the game in seconds.
     */
    public function getPlayTime()
    {
        return $this->playTime;
    }

    /**
     * Returns the objectives string.
     *
     * @return string The objectives.
     */
    public function getObjectives()
    {
        return $this->objectivesString;
    }

    /**
     * Returns the Scenario file name.
     *
     * @return string The Scenario file name.
     */
    public function getScenarioFilename()
    {
        return $this->scFileName;
    }
}
