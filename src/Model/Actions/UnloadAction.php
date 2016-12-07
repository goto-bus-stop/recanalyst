<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class UnloadAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x6f;

    // Unload(num=%d, x=%.2f, y=%.2f, flag=%d, unitType=%d)
    private $num;
    private $x;
    private $y;
    private $flag;
    private $unitType;

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
