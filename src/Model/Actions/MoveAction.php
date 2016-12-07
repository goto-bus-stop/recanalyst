<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class MoveAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x3;

    /**
     * ID of the player that is executing this action.
     *
     * @var int
     */
    public $playerId;

    /**
     * X-coordinate to move to.
     *
     * @var float
     */
    public $x;

    /**
     * Y-coordinate to move to.
     *
     * @var float
     */
    public $y;

    /**
     * Unit IDs to move.
     *
     * @var int[]
     */
    public $units = [];

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $playerId, $x, $y, $units)
    {
        parent::__construct($rec, $time);

        $this->playerId = $playerId;
        $this->x = $x;
        $this->y = $y;
        $this->units = $units;
    }

    /**
     * Get a string representation of the action.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            'Move(playerId=%d, x=%.2f, y=%.2f, units[%d]={%s})',
            $this->playerId,
            $this->x,
            $this->y,
            count($this->units),
            implode(', ', $this->units)
        );
    }
}
