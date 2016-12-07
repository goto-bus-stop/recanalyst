<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class UnitAiStateAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x12;

    // UnitAiState(num=%d, state=%d)
    private $num;
    private $state;

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
