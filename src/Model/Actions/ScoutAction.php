<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class ScoutAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x16;

    /**
     * Units.
     *
     * @var int[]
     */
    public $units;

    /**
     * X-coordinate to explore.
     *
     * @var float
     */
    public $x;

    /**
     * Y-coordinate to explore.
     *
     * @var float
     */
    public $y;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $x, $y, $units)
    {
        parent::__construct($rec, $time);

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
            'Scout(x=%.2f, y=%.2f, units[%d]={%s})',
            $this->x,
            $this->y,
            count($this->units),
            implode(', ', $this->units)
        );
    }
}
