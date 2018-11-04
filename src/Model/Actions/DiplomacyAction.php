<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class DiplomacyAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x76;

    // Diplomacy(pId1, pId2, seq=%d, status=%d, declare=%d, diplo=%d, int=%d, trade=%d, demand=%d, gold=%d, len=%d)
    private $playerId1;
    private $playerId2;
    private $seq;
    private $status;
    private $declare;
    private $diplo;
    private $int;
    private $trade;
    private $demand;
    private $gold;
    private $length;

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
