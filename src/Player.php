<?php

namespace RecAnalyst;

use RecAnalyst\Model\Research;

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
    public $name;

    /**
     * The player's index.
     *
     * @var int
     */
    public $index;

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
    public $team;

    /**
     * Defines if player is an owner of the game.
     *
     * @var bool
     */
    public $owner;

    /**
     * ID of the player's civilization.
     *
     * @var int
     */
    public $civId;

    /**
     * Player color ID.
     *
     * @var int
     */
    public $colorId;

    /**
     * Indicates if the player is cooping in the game.
     *
     * @var bool
     */
    public $isCooping;

    /**
     * Player's feudal time (in ms, 0 if hasn't been reached).
     *
     * @var int
     */
    public $feudalTime;

    /**
     * Player's castle time (in ms).
     *
     * @var int
     */
    public $castleTime;

    /**
     * Player's imperial time (in ms).
     *
     * @var int
     */
    public $imperialTime;

    /**
     * Player's resign time (in ms) or 0 if player hasn't been resigned.
     *
     * @var int
     */
    public $resignTime;

    /**
     * An array of player's researches containing
     * "research id => \RecAnalyst\Research instance" pairs.
     *
     * @var array
     */
    private $researchesById;

    /**
     * Contains the player's initial state, such as starting resources
     * and population.
     *
     * @var \RecAnalyst\InitialState
     */
    public $initialState;

    /**
     * Class constructor.
     *
     * @param \RecAnalyst\RecordedGame|null  $rec  Recorded game instance.
     * @return void
     */
    public function __construct($rec = null)
    {
        $this->rec = $rec;
        $this->name = '';
        $this->index = $this->team = $this->colorId = -1;
        $this->human = $this->owner = $this->isCooping = false;
        $this->civId = 0;
        $this->feudalTime = $this->castleTime = $this->imperialTime = 0;
        $this->resignTime = 0;
        $this->researchesById = [];
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
     * Returns the index of the player's team in RecAnalyst::$teams.
     *
     * @return int
     */
    public function getTeamID()
    {
        return $this->team;
    }

    /**
     * Returns the player's name.
     *
     * @return string Player name.
     */
    public function getName()
    {
        return $this->name;
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
     * Returns this player's feudal age advance time.
     *
     * @return int Feudal age time in milliseconds since the start of the game.
     */
    public function getFeudalTime()
    {
        return $this->feudalTime;
    }

    /**
     * Returns this player's castle age advance time.
     *
     * @return int Castle age time in milliseconds since the start of the game.
     */
    public function getCastleTime()
    {
        return $this->castleTime;
    }

    /**
     * Returns this player's imperial age advance time.
     *
     * @return int Imperial age time in milliseconds since the start of the game.
     */
    public function getImperialTime()
    {
        return $this->imperialTime;
    }

    /**
     * Returns this player's resign time.
     *
     * @return int Resignation time in milliseconds since the start of the game.
     */
    public function getResignTime()
    {
        return $this->resignTime;
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
     * @return Research[]
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
            $resourcePack = $this->rec->getResourcePack();
            return $resourcePack->getCivName($this->civId);
        }
        return 'Civ #' . $this->civId;
    }
}
