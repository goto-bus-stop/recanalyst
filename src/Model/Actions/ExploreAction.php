<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class ExploreAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x68;

    // Explore(num=%d, x=%.2f, y=%.2f, pId=%d
    private $units;
    private $x;
    private $y;
    private $playerId;

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
