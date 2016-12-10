<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class BuyCommodityAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x7b;

    /**
     * Player who is buying resources.
     *
     * @var int
     */
    public $playerId;

    /**
     * Resource type to buy.
     *
     * @var int
     */
    public $resourceType;

    /**
     * Amount of the resource to buy, in hundreds.
     *
     * @var int
     */
    public $amount;

    /**
     * Market to buy the resources at.
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
    public function __construct(RecordedGame $rec, $time, $playerId, $resourceType, $amount, $objectId)
    {
        parent::__construct($rec, $time);

        $this->playerId = $playerId;
        $this->resourceType = $resourceType;
        $this->amount = $amount;
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
            'BuyCommodity(playerId=%d, resourceType=%d, amount=%d, objectId=%d)',
            $this->playerId,
            $this->resourceType,
            $this->amount,
            $this->objectId
        );
    }
}
