<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class AttackGroundAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x6b;

    /**
     * @var float
     */
    public $x;

    /**
     * @var float
     */
    public $y;

    /**
     * @var int[]
     */
    public $units;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time)
    {
        parent::__construct($rec, $time);
    }

    /**
     * Get a string representation of the action.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            'AttackGround(x=%.2f, y=%.2f, units[%d]={%s})',
            $this->x,
            $this->y,
            count($this->units),
            implode(', ', $this->units)
        );
    }
}
