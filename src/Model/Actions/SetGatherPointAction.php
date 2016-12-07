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
    public $tmid;

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
}
