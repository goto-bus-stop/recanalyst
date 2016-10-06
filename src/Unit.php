<?php
namespace RecAnalyst;

/**
 * Unit represents a unit object in the game.
 */
class Unit
{
    /**
     * Id of the player who owns this unit. Zero if GAIA.
     *
     * @var int
     */
    public $owner = 0;

    /**
     * Unit type ID.
     *
     * @var int
     */
    public $id = 0;

    /**
     * Unit location.
     *
     * @var array
     */
    public $position = [0, 0];

    public function __construct($id, $position = [0, 0])
    {
        $this->id = $id;
        $this->position = $position;
    }
}
