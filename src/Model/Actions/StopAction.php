<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class StopAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x01;

    /**
     * Units that are affected by this action.
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
    public function __construct(RecordedGame $rec, $time, $units)
    {
        parent::__construct($rec, $time);

        $this->units = $units;
    }

    /**
     * Get a string representation of the action.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('Stop(units[%d]={%s})', count($this->units), implode(', ', $this->units));
    }
}
