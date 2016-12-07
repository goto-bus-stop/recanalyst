<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class CreateAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x4;

    // Create( obj_cat=%d, pId=%d, loc=%.10f,%.10f,%.10f )
    private $objectCategory;
    private $playerId;
    private $x;
    private $y;
    private $z;

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
