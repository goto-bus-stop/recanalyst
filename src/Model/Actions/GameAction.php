<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class GameAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x67;

    const GAME_SPEED = 0x01;
    const INSTANT_BUILDING = 0x02;
    const CHEAT = 0x06;
    const SPY = 0x0A;
    const STRATEGIC_NUMBER = 0x0B;

    /**
     * @var int
     */
    public $action;

    /**
     * @var int
     */
    public $playerId;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $action, $playerId)
    {
        parent::__construct($rec, $time);

        $this->action = $action;
        $this->playerId = $playerId;
    }

    /**
     * Get a string representation of the action.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('Game[%d](playerId=%d)', $this->action, $this->playerId);
    }
}
