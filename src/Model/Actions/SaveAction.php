<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class SaveAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x1B;

    // MpSave(ExitAfterSave=%d, CommId=%d)
    private $commId;
    private $exitAfterSave;

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
