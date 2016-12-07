<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents a game pause.
 * (This one may not actually exist in recorded game files.)
 */
class PauseAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x0D;

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
        return 'Pause()';
    }
}
