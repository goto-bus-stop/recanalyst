<?php

namespace RecAnalyst\Model;

/**
 * Represents a Team of Players in the game.
 */
class Team
{
    /**
     * Team's index.
     *
     * For internal purposes only. Use Team#index() instead.
     *
     * @var int
     * @api private
     */
    public $index;

    /**
     * Players in this team.
     *
     * @var \RecAnalyst\Model\Player[]
     */
    private $players;

    /**
     * Class constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->index = -1;
        $this->players = [];
    }

    /**
     * Adds a player to the team.
     *
     * @param  \RecAnalyst\Model\Player  $player  The player we wish to add
     * @return void
     */
    public function addPlayer(Player $player)
    {
        $this->players[] = $player;
        if ($this->index == -1) {
            $this->index = $player->team;
        }
    }

    /**
     * Returns a player at the specified offset.
     *
     * @param  int  An index of the player
     * @return \RecAnalyst\Model\Player|null
     */
    public function getPlayer($index)
    {
        return $this->players[$index];
    }

    /**
     * Returns an index of the team.
     *
     * @return int
     */
    public function index()
    {
        return $this->index;
    }

    /**
     * Get the players in this team.
     *
     * @return \RecAnalyst\Model\Player[]
     */
    public function players()
    {
        return $this->players;
    }
}
