<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class AboutFaceFormationAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x1A;

    /**
     * ID of the player that is executing this action.
     *
     * @var int
     */
    public $playerId;

    /**
     * Formation this action applies to.
     *
     * @var int
     */
    public $formationId;

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
            'AboutFaceFormation(playerId=%d, formationId=%d)',
            $this->playerId,
            $this->formationId
        );
    }
}
