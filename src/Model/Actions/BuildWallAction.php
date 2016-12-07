<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class BuildWallAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x69;

    // BuildWall(num=%d, x1=%d, y1=%d, x2=%d, y2=%d, pId=%d, oId=%d, qId=%d)
    private $num;
    private $x1;
    private $y1;
    private $x2;
    private $y2;
    private $playerId;
    private $objectId;

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
