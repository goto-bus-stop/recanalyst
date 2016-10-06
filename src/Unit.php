<?php
namespace RecAnalyst;

/**
 * Unit represents a unit object in the game.
 */
class Unit
{
    /** @var int ID of the player who owns this unit. Zero if GAIA. */
    public $owner = 0;

    /** @var int Unit type ID. */
    public $id = 0;

    /** @var array Unit location, `[$x, $y]`. */
    public $position = [0, 0];

    /**
     * Create a new Unit.
     *
     * @param int  $id  Unit type ID.
     * @param int[]  $position  Position as `[$x, $y]` coordinates.
     */
    public function __construct($id, $position = [0, 0])
    {
        $this->id = $id;
        $this->position = $position;
    }
}
