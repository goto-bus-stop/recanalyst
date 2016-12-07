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

    // BuySellAttr(uId=%d, pId=%d, attr=%d, amt=%d)
    private $unitId;
    private $playerId;
    private $attribute;
    private $amount;

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
}
