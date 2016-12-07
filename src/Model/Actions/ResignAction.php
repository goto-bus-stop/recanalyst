<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class ResignAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0xb;

    /**
     * @var int
     */
    public $playerId;

    /**
     * [TODO]
     *
     * @var int
     */
    private $commId;

    /**
     * Whether the player dropped.
     *
     * @var bool
     */
    public $dropped;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $playerId, $commId, $dropped)
    {
        parent::__construct($rec, $time);

        $this->playerId = $playerId;
        $this->commId = $commId;
        $this->dropped = $dropped;
    }

    /**
     * Get a string representation of the action.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            'Resign(playerId=%d, commId=%d, dropped=%s)',
            $this->playerId,
            $this->commId,
            $this->dropped ? 'true' : 'false'
        );
    }
}
