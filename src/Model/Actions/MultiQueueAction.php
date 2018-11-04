<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents a multiple building queue action, from UserPatch 1.4.
 */
class MultiQueueAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x70;

    /**
     * Building IDs that the action applies to.
     *
     * @var int[]
     */
    public $buildings;

    /**
     * ID of the unit type that is being queued.
     *
     * @var int
     */
    public $typeId;

    /**
     * Amount of units that are being queued.
     *
     * @var int
     */
    public $count;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $buildings, $typeId, $count)
    {
        parent::__construct($rec, $time);

        $this->buildings = $buildings;
        $this->typeId = $typeId;
        $this->count = $count;
    }

    /**
     * Get a string representation of the action.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            'MultiQueue(typeId=%d, count=%d, buildings[%d]={%s})',
            $this->typeId,
            $this->count,
            count($this->buildings),
            implode(', ', $this->buildings)
        );
    }
}
