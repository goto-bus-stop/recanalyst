<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class UnitTransformAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x7d;

    // UnitTransform(num=%d, uId=%d, pId=%d, qId=%d)
    private $num;
    private $unitId;
    private $playerId;
    private $qId;

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
