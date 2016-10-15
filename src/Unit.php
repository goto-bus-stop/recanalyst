<?php
namespace RecAnalyst;

/**
 * Unit represents a unit object in the game.
 */
class Unit
{
    /**
     * The player that owns this unit. NULL if GAIA.
     *
     * @var \RecAnalyst\Player
     */
    public $owner = null;

    /**
     * Unit type ID.
     *
     * @var int
     */
    public $id = 0;

    /**
     * Unit location, `[$x, $y]`.
     *
     * @var float[]
     */
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
