<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class BuildWallAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x69;

    // BuildWall(num=%d, x1=%d, y1=%d, x2=%d, y2=%d, pId=%d, oId=%d, qId=%d)
    public $playerId;
    public $objectId;
    public $from;
    public $to;
    public $units;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $playerId, $objectType, $from, $to, $builders)
    {
        parent::__construct($rec, $time);

        $this->playerId = $playerId;
        $this->objectType = $objectType;
        $this->from = $from;
        $this->to = $to;
        $this->builders = $builders;
    }

    /**
     * Get a string representation of the action.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            'BuildWall(playerId=%d, objectType=%d, from={%d,%d}, to={%d,%d}, builders[%d]={%s})',
            $this->playerId,
            $this->objectType,
            $this->from[0],
            $this->from[1],
            $this->to[0],
            $this->to[1],
            count($this->builders),
            implode(', ', $this->builders)
        );
    }
}
