<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class CancelBuildAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x6a;

    /**
     * Player who is canceling this object.
     *
     * @var int
     */
    public $playerId;

    /**
     * Object to cancel or delete.
     *
     * @var int
     */
    public $objectId;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $playerId, $objectId)
    {
        parent::__construct($rec, $time);

        $this->playerId = $playerId;
        $this->objectId = $objectId;
    }

    /**
     * Get a string representation of the action.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            'CancelBuild(playerId=%d, objectId=%d)',
            $this->playerId,
            $this->objectId
        );
    }
}
