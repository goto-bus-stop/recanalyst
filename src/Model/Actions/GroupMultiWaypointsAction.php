<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class GroupMultiWaypointsAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x1f;

    // GroupMultiWaypoints(num=%d, waypoints=%d)
    private $num;
    private $waypoints;

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
