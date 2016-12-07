<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class BuildAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x66;

    /**
     * X-coordinate of the new building location.
     *
     * @var float
     */
    public $x;

    /**
     * Y-coordinate of the new building location.
     *
     * @var float
     */
    public $y;

    /**
     * ID of the Player who sent the action.
     *
     * @var int
     */
    public $playerId;

    /**
     * Building type ID to build.
     *
     * @var int
     */
    public $objectId;

    /**
     * IDs of the units that will be tasked to build this building.
     *
     * @var int[]
     */
    public $builders = [];

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $playerId, $x, $y, $objectId, $builders)
    {
        parent::__construct($rec, $time);

        $this->playerId = $playerId;
        $this->x = $x;
        $this->y = $y;
        $this->objectId = $objectId;
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
            'Build(playerId=%d, x=%.2f, y=%.2f, objectId=%d, builders[%d]={%s})',
            $this->playerId,
            $this->x,
            $this->y,
            $this->objectId,
            count($this->builders),
            implode(', ', $this->builders)
        );
    }
}
