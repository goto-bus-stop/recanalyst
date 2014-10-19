<?php
/**
 * Defines Player class.
 *
 * @package RecAnalyst
 */

namespace RecAnalyst;

/**
 * The Player class represents a player in the game. This includes co-op players.
 * It does not include players who joined the lobby but didn't launch into
 * the actual game.
 *
 * @package RecAnalyst
 */
class Player
{

    /**
     * Player's name.
     * @var string
     */
    public $name;

    /**
     * Player's index.
     * @var int
     */
    public $index;

    /**
     * Defines if the player is a human.
     * @var bool
     */
    public $human;
    /**
     * Defines if the player is a spectator.
     * @var bool
     */
    public $spectator;

    /**
     * Defines player's team index (0 = no team).
     * @var int
     */
    public $team;

    /**
     * Defines if player is an owner of the game.
     * @var bool
     */
    public $owner;

    /**
     * Id of player's civilization.
     * @var int
     * @see Civilization
     */
    public $civId;

    /**
     * Id of player's color.
     * @var int
     */
    public $colorId;

    /**
     * Indicates if the player is cooping in the game.
     * @var bool true if player coops, otherwise false
     */
    public $isCooping;

    /**
     * Player's feudal time (in ms, 0 if hasn't been reached).
     * @var int
     */
    public $feudalTime;

    /**
     * Player's castle time (in ms).
     * @var int
     */
    public $castleTime;

    /**
     * Player's imperial time (in ms).
     * @var int
     */
    public $imperialTime;

    /**
     * Player's resign time (in ms) or 0 if player hasn't been resigned.
     * @var int
     */
    public $resignTime;

    /**
     * An array of player's researches.
     * An associative array containing "research id - time of research" pairs.
     * @var array
     */
    public $researches;

    /**
     * Player's initial state.
     * @var InitialState
     */
    public $initialState;

    /**
     * Class constructor.
     *
     * @return void
     */
    public function __construct()
    {
         $this->name = '';
         $this->index = $this->team = $this->colorId = -1;
         $this->human = $this->owner = $this->isCooping = false;
         $this->civId = Civilization::NONE;
         $this->feudalTime = $this->castleTime = $this->imperialTime = 0;
         $this->resignTime = 0;
         $this->researches = array();
         $this->initialState = new InitialState();
    }

    /**
     * Returns civilization string.
     *
     * @return string This player's Civilization name.
     */
    public function getCivString()
    {
        return isset(RecAnalystConst::$CIVS[$this->civId][0]) ?
            RecAnalystConst::$CIVS[$this->civId][0] : '';
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
}
