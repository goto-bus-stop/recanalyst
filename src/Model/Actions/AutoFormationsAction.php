<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class AutoFormationsAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x1D;

    /**
     * @var int
     */
    private $playerId;

    /**
     * @var bool
     */
    private $enable;

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

    /**
     * Get a string representation of the action.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            'AutoFormation(playerId=%d, enable=%s)',
            $this->playerId,
            $this->enable ? 'true' : 'false'
        );
    }
}
