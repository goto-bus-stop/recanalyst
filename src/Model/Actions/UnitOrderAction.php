<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class UnitOrderAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x75;

    // UnitOrder(num=%d, x=%.2f, y=%.2f, tId=%d, qId=%d, action=%d, param=%d)
    private $units;
    private $x;
    private $y;
    private $targetId;
    private $qid;
    private $action;
    private $parameter;

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
