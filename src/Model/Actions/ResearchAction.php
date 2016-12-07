<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class ResearchAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x65;

    /**
     * Player who is researching this tech.
     *
     * @var int
     */
    public $playerId;

    /**
     * Building or unit where the tech is being researched.
     *
     * @var int
     */
    public $unitId;

    /**
     * ID of the tech that is being researched.
     *
     * @var int
     */
    public $techId;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $playerId, $unitId, $techId)
    {
        parent::__construct($rec, $time);

        $this->playerId = $playerId;
        $this->unitId = $unitId;
        $this->techId = $techId;
    }

    /**
     * Get a string representation of the action.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            'Research(playerId=%d, unitId=%d, techId=%d, qId=%d)',
            $this->playerId,
            $this->unitId,
            $this->techId,
            -1
        );
    }
}
