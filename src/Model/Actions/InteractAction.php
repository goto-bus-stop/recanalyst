<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class InteractAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x0;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $playerId, $targetId, $x, $y, $units)
    {
        parent::__construct($rec, $time);

        $this->playerId = $playerId;
        $this->targetId = $targetId;
        $this->x = $x;
        $this->y = $y;
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
            'Interact(playerId=%d, targetId=%d, x=%.2f, y=%.2f, units[%d]={%s})',
            $this->playerId,
            $this->targetId,
            $this->x,
            $this->y,
            count($this->units),
            implode(', ', $this->units)
        );
    }
}
