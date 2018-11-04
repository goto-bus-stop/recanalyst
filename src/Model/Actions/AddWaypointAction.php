<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class AddWaypointAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x0C;

    /**
     * ID of the player that is adding a waypoint.
     *
     * @var int
     */
    public $playerId;

    /**
     * [TODO]
     *
     * @var int
     */
    public $recipient;

    /**
     * Waypoint locations. An array of [$x, $y, $z] locations.
     *
     * @var int[][]
     */
    public $waypoints = [];

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
            'AddWaypoint(playerId=%d, recipient=%d, waypoints[%d]={%s})',
            $this->playerId,
            $this->recipient,
            count($this->waypoints),
            implode(', ', array_map(function ($waypoint) {
                return vsprintf('{%.2f, %.2f, %.2f}', $waypoint);
            }, $this->waypoints))
        );
    }
}
