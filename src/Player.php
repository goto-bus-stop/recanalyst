<?php

namespace RecAnalyst;

/**
 * The Player class represents a player in the game. This includes co-op players.
 * It does not include players who joined the lobby but didn't launch into
 * the actual game.
 */
class Player
{
    /** @var string Player's name. */
    public $name;

    /** @var int Player's index. */
    public $index;

    /** @var bool Defines if the player is a human. */
    public $human;

    /** @var bool Defines if the player is a spectator. */
    public $spectator;

    /** @var int Defines player's team index (0 = no team). */
    public $team;

    /** @var bool Defines if player is an owner of the game. */
    public $owner;

    /** @var int ID of player's civilization. */
    public $civId;

    /** @var int Player color ID. */
    public $colorId;

    /** @var bool Indicates if the player is cooping in the game. */
    public $isCooping;

    /** @var int Player's feudal time (in ms, 0 if hasn't been reached). */
    public $feudalTime;

    /** @var int Player's castle time (in ms). */
    public $castleTime;

    /** @var int Player's imperial time (in ms). */
    public $imperialTime;

    /** @var int Player's resign time (in ms) or 0 if player hasn't been resigned. */
    public $resignTime;

    /**
     * @var array An array of player's researches containing "research id =>
     *     time of research" pairs.
     */
    public $researches;

    /** @var \RecAnalyst\InitialState Player's initial state. */
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
