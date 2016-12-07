<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class ResearchAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x65;

    // Research(pId=%d, uId=%d, tId=%d, qId=%d)
    public $playerId;
    public $unitId;
    public $techId;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $playerId, $unitId, $techId)
    {
        parent::__construct($rec, $time);

        $this->playerId = $playerId;
        $this->unitId = $unitId;
        $this->techId = $techId;
    }
}
