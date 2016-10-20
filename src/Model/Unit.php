<?php

namespace RecAnalyst\Model;

use RecAnalyst\RecordedGame;

/**
 * Unit represents a unit object in the game.
 */
class Unit
{
    /**
     * @var \RecAnalyst\RecordedGame
     */
    private $rec;

    /**
     * The player that owns this unit. NULL if GAIA.
     *
     * @var \RecAnalyst\Model\Player
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
    public function __construct(RecordedGame $rec, $id, $position = [0, 0])
    {
        $this->rec = $rec;
        $this->id = $id;
        $this->position = $position;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->rec->trans('units', $this->id);
    }
}
