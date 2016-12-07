<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents an off-board trade action. This is probably from an older alpha
 * when trading was going to be much more advanced than in the final game.
 */
class OffboardTradeAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x7c;

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
}
