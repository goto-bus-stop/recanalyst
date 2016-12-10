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

    /**
     * IDs of the units to be patrolled.
     *
     * @var int[]
     */
    private $units = [];

    /**
     * Array of waypoints to pass by while patrolling. Each waypoint is a pair
     * of coordinates [$x, $y].
     *
     * @var float[][]
     */
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

    /**
     * Get a string representation of the action.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            'Patrol(units[%d]={%s}, waypoints[%d]={%s})',
            count($this->units),
            implode(', ', $this->units),
            count($this->waypoints),
            implode(', ', array_map(function ($w) {
                return vsprintf('{%.2f, %.2f}', $w);
            }, $this->waypoints))
        );
    }
}
