<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class RepairAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x6e;

    /**
     * IDs of units that should start repairing the target.
     *
     * @var int[]
     */
    public $units;

    /**
     * ID of the object to repair.
     *
     * @var int
     */
    public $targetId;

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
            'Repair(targetId=%d, units[%d]={%s})',
            $this->targetId,
            count($this->units),
            implode(', ', $this->units)
        );
    }
}
