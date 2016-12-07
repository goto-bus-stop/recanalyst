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

    // GiveAttr(from=%d, to=%d, attr=%d, amount=%d)
    // GiveAttr(from=%d to=%d, attr=%d, amt=%.2f, cost=%.2f)
    private $fromId;
    private $toId;
    private $attribute;
    private $amount;
    private $cost;

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
}
