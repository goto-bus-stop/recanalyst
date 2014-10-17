<?php
/**
 * Defines Team class.
 *
 * @package RecAnalyst
 */

namespace RecAnalyst;

/**
 * Represents a Team of Players in the game.
 *
 * @package RecAnalyst
 */
class Team
{

    /**
     * Team's index.
     * @var int
     */
    private $index;

    /**
     * @var array
     */
    public $players;

    /**
     * Class constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->index = -1;
        $this->players = array();
    }

    /**
     * Adds a player to the team.
     *
     * @param Player $player The player we wish to add
     *
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
     * @param int An index of the player
     *
     * @return Player|bool The player at the index or false if the index is out of the range
     */
    public function getPlayer($index)
    {
        return $this->players[$index];
    }

    /**
     * Returns an index of the team.
     *
     * @return int Team index
     */
    public function getIndex()
    {
        return $this->index;
    }
}
