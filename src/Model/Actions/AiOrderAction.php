<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class AiOrderAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0xa;

    // AIOrder(num=%d, pId=%d, issuer=%d, recipient=%d, order=%d, pri=%d,
    //         tId=%d, tOwner=%d, tPos=%.10f,%.10f,%.10f, range=%.10f,
    //         immediate=%d, front=%d)
    private $num;
    private $playerId;
    private $issuer;
    private $recipient;
    private $order;
    private $priority;
    private $tId;
    private $tOwner;
    private $tPosition;
    private $range;
    private $immediate;
    private $front;

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
            'AiOrder(playerId=%d, attribute=%d, amount=%d)',
            $this->playerId,
            $this->attribute,
            $this->amount
        );
    }
}
