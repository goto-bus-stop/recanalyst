<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class FormFormationAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x17;

    // FormFormation(num=%d, pId=%d, formation=%d)
    private $num;
    private $playerId;
    private $formation;

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
