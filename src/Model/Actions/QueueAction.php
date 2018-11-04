<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class QueueAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x77;

    /**
     * ID of the building that the action applies to.
     *
     * @var int
     */
    public $buildingId;

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
    public function __construct(RecordedGame $rec, $time, $buildingId, $typeId, $count)
    {
        parent::__construct($rec, $time);

        $this->buildingId = $buildingId;
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
            'Queue(buildingId=%d, typeId=%d, count=%d)',
            $this->buildingId,
            $this->typeId,
            $this->count
        );
    }
}
