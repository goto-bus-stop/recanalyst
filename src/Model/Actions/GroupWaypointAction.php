<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class GroupWaypointAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x10;

    // GroupWaypoint(num=%d, commId=%d, x=%d, y=%d )
    private $num;
    private $commId;
    private $x;
    private $y;

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
