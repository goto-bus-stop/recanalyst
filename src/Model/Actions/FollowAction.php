<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class FollowAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x14;

    // Follow(num=%d, target=%d)
    /**
     * ID of the target unit to follow.
     *
     * @var int
     */
    public $targetId;

    /**
     * IDs of the units that should follow the target.
     *
     * @var int[]
     */
    public $units;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $targetId, $units)
    {
        parent::__construct($rec, $time);

        $this->targetId = $targetId;
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
            'Follow(targetId=%d, units[%d]={%s})',
            $this->targetId,
            count($this->units),
            implode(', ', $this->units)
        );
    }
}
