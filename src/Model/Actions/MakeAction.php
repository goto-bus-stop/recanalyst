<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class MakeAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x64;

    /**
     * Player who is "making" a unit.
     *
     * @var int
     */
    private $playerId;

    /**
     * Unit type ID to create.
     *
     * @var int
     */
    private $typeId;

    /**
     * Object ID where the unit will be created. Usually a building.
     *
     * @var int
     */
    private $objectId;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $playerId, $typeId, $objectId)
    {
        parent::__construct($rec, $time);

        $this->playerId = $playerId;
        $this->typeId = $typeId;
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
            'Make(playerId=%d, typeId=%d, objectId=%d)',
            $this->playerId,
            $this->typeId,
            $this->objectId
        );
    }
}
