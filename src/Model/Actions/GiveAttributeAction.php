<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class GiveAttributeAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x6c;

    /**
     * ID of the giving player.
     *
     * @var int
     */
    public $fromId;

    /**
     * ID of the receiving player.
     *
     * @var int
     */
    public $toId;

    /**
     * Attribute type ID.
     *
     * @var int
     */
    public $attribute;

    /**
     * Amount of $attribute to give.
     *
     * @var float
     */
    public $amount;

    /**
     * Cost of the transfer (Market fee).
     *
     * @var float
     */
    public $cost;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $fromId, $toId, $attribute, $amount, $cost)
    {
        parent::__construct($rec, $time);

        $this->fromId = $fromId;
        $this->toId = $toId;
        $this->attribute = $attribute;
        $this->amount = $amount;
        $this->cost = $cost;
    }

    /**
     * @return \RecAnalyst\Model\Player
     */
    public function fromPlayer()
    {
        return $this->rec->getPlayer($this->fromId);
    }

    /**
     * @return \RecAnalyst\Model\Player
     */
    public function toPlayer()
    {
        return $this->rec->getPlayer($this->toId);
    }

    /**
     * Get a string representation of the action.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            'GiveAttribute(fromId=%d, toId=%d, attribute=%d, amount=%.2f, cost=%.2f)',
            $this->fromId,
            $this->toId,
            $this->attribute,
            $this->amount,
            $this->cost
        );
    }
}
