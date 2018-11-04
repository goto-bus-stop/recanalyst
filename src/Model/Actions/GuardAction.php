<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class GuardAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x13;

    // Guard(num=%d, target=%d)
    public $targetId;
    public $units;

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
            'Guard(targetId=%d, units[%d]={%s})',
            $this->targetId,
            count($this->units),
            implode(', ', $this->units)
        );
    }
}
