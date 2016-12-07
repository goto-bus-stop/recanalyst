<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class PatrolAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x15;

    // Patrol(num=%d, waypoints[%d]={%s})
    private $units = [];
    private $waypoints = [];

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $units, $waypoints)
    {
        parent::__construct($rec, $time);

        $this->units = $units;
        $this->waypoints = $waypoints;
    }
}
