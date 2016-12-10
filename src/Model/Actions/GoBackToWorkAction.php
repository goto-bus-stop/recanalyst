<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class GoBackToWorkAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x80;

    /**
     * ID of the building that should ungarrison its units and send them back
     * to work.
     *
     * @var int
     */
    public $unitId;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $unitId)
    {
        parent::__construct($rec, $time);

        $this->unitId = $unitId;
    }

    /**
     * Get a string representation of the action.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('GoBackToWork(unitId=%d)', $this->unitId);
    }
}
