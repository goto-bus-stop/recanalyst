<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class TownBellAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x7f;

    /**
     * Unit ID of the town centre where the Town Bell is being rung.
     *
     * @var int
     */
    private $unitId;

    /**
     * @var bool
     */
    private $activate;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $unitId, $activate)
    {
        parent::__construct($rec, $time);

        $this->unitId = $unitId;
        $this->activate = $activate;
    }

    /**
     * Get a string representation of the action.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('TownBell(unitId=%d, activate=%d)', $this->unitId, $this->activate ? 'true' : 'false');
    }
}
