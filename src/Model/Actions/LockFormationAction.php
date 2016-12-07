<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class LockFormationAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x1E;

    // LockFormation(pId=%d, formId=%d, formType=%d)
    private $playerId;
    private $formationId;
    private $formationType;

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
