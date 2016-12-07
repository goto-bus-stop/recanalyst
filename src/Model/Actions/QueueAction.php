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

    // Queue(bId=%d, tId=%d, count=%d)
    public $buildingId;
    public $typeId;
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
}
