<?php

namespace RecAnalyst\Model;

use RecAnalyst\RecordedGame;

/**
 * GameInfo holds metadata about the analyzed game.
 */
class GameInfo
{

    /**
     * Recorded game instance.
     *
     * @var \RecAnalyst\RecordedGame
     */
    private $rec;

    /**
     * Objectives string.
     *
     * @var string
     * @api private
     */
    public $objectivesString;

    /**
     * Original Scenario filename.
     *
     * @var string
     * @api private
     */
    public $scFileName;

    /**
     * Class constructor.
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     *
     * @return void
     */
    public function __construct(RecordedGame $rec)
    {
        $this->rec = $rec;
        $this->objectivesString = $this->scFileName = '';
    }

    /**
     * Returns the players string (1v1, FFA, etc.)
     *
     * @return string
     */
    public function getPlayersString()
    {
        $teams = $this->rec->teams();

        $teamMembers = array_map($teams, function (&$team) {
            // Count non-cooping players.
            return array_reduce($team->players(), function ($count, &$player) {
                return $player->isCooping ? $count : $count + 1;
            }, 0);
        });

        // Remove teams without players.
        $teamMembers = array_diff($teamMembers, [0]);
        if (array_sum($teamMembers) === count($teams) && count($teams) > 2) {
            return 'FFA';
        } else {
            return implode('v', $teamMembers);
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

        $names = [];
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
     * Returns the objectives string.
     *
     * @return string The objectives.
     */
    public function objectives()
    {
        return $this->objectivesString;
    }

    /**
     * Returns the Scenario file name.
     *
     * @return string The Scenario file name.
     */
    public function scenarioFilename()
    {
        return $this->scFileName;
    }
}
