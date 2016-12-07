<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class AttackMoveTargetAction extends AttackMoveAction
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x22;

    // AttackMove(num=%d, tId=%d, waypoints[%d]={%s})
    /**
     * @var int
     */
    public $targetId;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $targetId, $waypoints, $units)
    {
        parent::__construct($rec, $time, $waypoints, $units);

        $this->targetId = $targetId;
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
            $this->targetId,
            count($this->waypoints),
            implode(', ', array_map(function ($waypoint) {
                return vsprintf('{%.2f, %.2f, %.2f}', $waypoint);
            }, $this->waypoints)),
            count($this->units),
            implode(', ', $this->units)
        );
    }
}
