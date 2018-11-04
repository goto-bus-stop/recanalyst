<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class AttackMoveAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x21;

    /**
     * Waypoint locations. An array of [$x, $y, $z] locations.
     *
     * @var int[][]
     */
    public $waypoints;

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
    public function __construct(RecordedGame $rec, $time, $waypoints, $units)
    {
        parent::__construct($rec, $time);

        $this->waypoints = $waypoints;
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
            'AttackMove(tId=%d, waypoints[%d]={%s}, units[%d]={%s})',
            count($this->waypoints),
            implode(', ', array_map(function ($waypoint) {
                return vsprintf('{%.2f, %.2f, %.2f}', $waypoint);
            }, $this->waypoints)),
            count($this->units),
            implode(', ', $this->units)
        );
    }
}
