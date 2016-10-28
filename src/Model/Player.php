<?php

namespace RecAnalyst\Model;

use RecAnalyst\Model\Team;
use RecAnalyst\RecordedGame;
use RecAnalyst\Model\Research;
use RecAnalyst\Model\InitialState;

/**
 * The Player class represents a player in the game. This includes co-op players.
 * It does not include players who joined the lobby but didn't launch into
 * the actual game.
 */
class Player
{
    /**
     * Recorded game that contains this player.
     *
     * @var \RecAnalyst\RecordedGame
     */
    private $rec;

    /**
     * The player's name.
     *
     * @var string
     */
    public $name = '';

    /**
     * The player's index.
     *
     * @var int
     */
    public $index = -1;

    /**
     * Defines if the player is a human.
     *
     * @var bool
     * @api private
     */
    public $human;

    /**
     * Defines if the player is a spectator.
     *
     * @var bool
     * @api private
     */
    public $spectator;

    /**
     * Defines player's team index (0 = no team).
     *
     * @var int
     */
    public $teamIndex = -1;

    /**
     * Defines if player is an owner of the game.
     *
     * @var bool
     */
    public $owner = false;

    /**
     * ID of the player's civilization.
     *
     * @var int
     */
    public $civId = -1;

    /**
     * Player color ID.
     *
     * @var int
     */
    public $colorId = -1;

    /**
     * Indicates if the player is cooping in the game.
     *
     * @var bool
     */
    public $isCooping = false;

    /**
     * Player's feudal time (in ms, 0 if hasn't been reached).
     *
     * @var int
     */
    public $feudalTime = 0;

    /**
     * Player's castle time (in ms).
     *
     * @var int
     */
    public $castleTime = 0;

    /**
     * Player's imperial time (in ms).
     *
     * @var int
     */
    public $imperialTime = 0;

    /**
     * Player's resign time (in ms) or 0 if player hasn't resigned.
     *
     * @var int
     */
    public $resignTime = 0;

    /**
     * An array of player's researches containing
     * "research id => \RecAnalyst\Model\Research instance" pairs.
     *
     * @var array
     */
    private $researchesById = [];

    /**
     * Contains the player's initial state, such as starting resources
     * and population.
     *
     * @var \RecAnalyst\Model\InitialState
     */
    public $initialState = null;

    /**
     * Class constructor.
     *
     * @param \RecAnalyst\RecordedGame|null  $rec  Recorded game instance.
     * @return void
     */
    public function __construct(RecordedGame $rec = null)
    {
        $this->rec = $rec;
        $this->initialState = new InitialState();
    }

    /**
     * Returns whether the player is a human player.
     *
     * @return boolean True if human, false if AI.
     */
    public function isHuman()
    {
        return $this->human;
    }

    /**
     * Get the player's team.
     *
     * @return \RecAnalyst\Model\Team|null
     */
    public function team()
    {
        $teams = $this->rec->teams();
        foreach ($teams as $team) {
            if ($team->index() === $this->teamIndex) {
                return $team;
            }
        }
    }

    /**
     * Returns whether the player is co-oping.
     *
     * @return boolean True if the player is co-oping, false otherwise.
     */
    public function isCooping()
    {
        return $this->isCooping;
    }

    public function isSpectator()
    {
        return $this->spectator;
    }

    /**
     * Get the hex color of this player.
     *
     * @return string Hexadecimal representation of this player's color.
     */
    public function color()
    {
        if (is_null($this->rec)) {
            return null;
        }
        $resourcePack = $this->rec->getResourcePack();
        return $resourcePack->getPlayerColor($this->colorId);
    }

    /**
     * @return \StdClass|null
     */
    public function achievements()
    {
        if (is_null($this->rec)) {
            return null;
        }
        $achievements = $this->rec->achievements();
        if (is_null($achievements)) {
            return null;
        }
        return $achievements[$this->index];
    }

    /**
     * Add a research action.
     *
     * @param int  $id  Research ID.
     * @param int  $time  Research completion time.
     */
    public function addResearch($id, $time)
    {
        $this->researchesById[$id] = new Research($this->rec, $id, $time);
    }

    /**
     * @return \RecAnalyst\Model\Research[]
     */
    public function researches()
    {
        return array_values($this->researchesById);
    }

    /**
     * Get the name of this player's civilization.
     *
     * @return string
     */
    public function civName()
    {
        if (!is_null($this->rec)) {
            return $this->rec->trans('civilizations', $this->civId);
        }
        return 'Civ #' . $this->civId;
    }

    /**
     * Get the player's starting age.
     *
     * @see \RecAnalyst\Model\InitialState::$startingAge
     *
     * @return string Name of the starting age.
     */
    public function startingAge()
    {
        if (!is_null($this->rec)) {
            return $this->rec->trans('ages', $this->initialState->startingAge);
        }
        return null;
    }

    /**
     * Get the player's starting position.
     *
     * @return int[]
     */
    public function position()
    {
        return $this->initialState->position;
    }
}
