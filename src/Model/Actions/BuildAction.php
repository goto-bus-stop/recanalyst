<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class BuildAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x66;

    // Build(num=%d, x=%.2f, y=%.2f, pId=%d, oId=%d, qId=%d, frame=%d)
    public $x;
    public $y;
    public $playerId;
    public $objectId;
    public $villagers = [];

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $playerId, $x, $y, $objectId, $villagers)
    {
        parent::__construct($rec, $time);

        $this->playerId = $playerId;
        $this->x = $x;
        $this->y = $y;
        $this->objectId = $objectId;
        $this->villagers = $villagers;
    }
}
