<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class SpecialAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x74;

    /**
     * IDs of units that should execute the action.
     *
     * @var int[]
     */
    public $units;

    /**
     * Target of the action.
     *
     * @var int
     */
    public $targetId;

    /**
     * [TODO]
     *
     * @var int
     */
    public $action;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $targetId, $action, $units)
    {
        parent::__construct($rec, $time);

        $this->targetId = $targetId;
        $this->action = $action;
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
            'Special(targetId=%d, action=%d, units[%d]={%s})',
            $this->targetId,
            $this->action,
            count($this->units),
            implode(', ', $this->units)
        );
    }
}
