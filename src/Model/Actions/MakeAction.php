<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class MakeAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x64;

    // Make(pId=%d, uId=%d, oId=%d, qId=%d)
    private $playerId;
    private $unitId;
    private $objectId;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $playerId, $unitId, $objectId)
    {
        parent::__construct($rec, $time);

        $this->playerId = $playerId;
        $this->unitId = $unitId;
        $this->objectId = $objectId;
    }
}
