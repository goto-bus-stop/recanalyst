<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class TradeAttributeAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x6d;

    // TradeAttr(num=%d, attr=%d)
    private $num;
    private $attribute;

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
