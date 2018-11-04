<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class GroupAiOrderAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x11;

    // GroupAIOrder(pId=%d, issuer=%d, recipientCount=%d, order=%d, pri=%d,
    //              tId=%d, tOwner, tPos=%.10f,%.10f,%.10f, range=%.10f,
    //              immediate=%d, front=%d)
    private $playerId;
    private $issuer;
    private $recipientCount;
    private $order;
    private $priority;
    private $targetId;
    private $targetOwner;
    private $targetPosition;
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
}
