<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class SetGatherPointAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x78;

    // SetGatherPoint(num=%d, x=%.2f, y=%.2f, tId=%d, tmId=%d)
    /**
     * Units (buildings) whose output should gather here.
     *
     * @var int[]
     */
    public $units;
    /**
     * New X-coordinate for the gather point.
     *
     * @var float
     */
    public $x;

    /**
     * New Y-coordinate for the gather point.
     *
     * @var float
     */
    public $y;

    /**
     * Optionally, the ID of a unit target to place the gather point.
     *
     * @var int
     */
    public $targetId;

    /**
     * [TODO]
     *
     * @var int
     */
    public $targetType;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $targetId, $targetType, $x, $y, $units)
    {
        parent::__construct($rec, $time);

        $this->targetId = $targetId;
        $this->targetType = $targetType;
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
            'SetGatherPoint(targetId=%d, typeId=%d, x=%.2f, y=%.2f, units[%d]={%s})',
            $this->targetId,
            $this->targetType,
            $this->x,
            $this->y,
            count($this->units),
            implode(', ', $this->units)
        );
    }
}
