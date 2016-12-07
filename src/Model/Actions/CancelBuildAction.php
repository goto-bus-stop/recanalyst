<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class CancelBuildAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x6a;

    // CancelBuild( uId=%d, pId=%d)
    private $playerId;
    private $unitId;

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
