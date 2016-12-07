<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class ResignAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0xb;

    // Resign(pId=%d, commId=%d, dropped=%d)
    private $playerId;
    private $commId;
    private $dropped;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $playerId, $commId, $dropped)
    {
        parent::__construct($rec, $time);

        $this->playerId = $playerId;
        $this->commId = $commId;
        $this->dropped = $dropped;
    }
}
