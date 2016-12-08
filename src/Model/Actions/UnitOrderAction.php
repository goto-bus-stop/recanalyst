<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class UnitOrderAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x75;

    /**
     * @var int[]
     */
    public $units;

    /**
     * @var float
     */
    public $x;

    /**
     * @var float
     */
    public $y;

    /**
     * @var int
     */
    public $targetId;

    /**
     * @var int
     */
    public $qId;

    /**
     * @var int
     */
    public $action;

    /**
     * @var int
     */
    public $parameter;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $x, $y, $targetId, $qId, $action, $parameter, $units)
    {
        parent::__construct($rec, $time);

        $this->x = $x;
        $this->y = $y;
        $this->targetId = $targetId;
        $this->qId = $qId;
        $this->action = $action;
        $this->parameter = $parameter;
        $this->units = $units;
    }

    /**
     * Get a string representation of the action.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            'UnitOrder(x=%.2f, y=%.2f, targetId=%d, qId=%d, action=%d, parameter=%d, units[%d]={%s})',
            $this->x,
            $this->y,
            $this->targetId,
            $this->qId,
            $this->action,
            $this->parameter,
            count($this->units),
            implode(', ', $this->units)
        );
    }
}
