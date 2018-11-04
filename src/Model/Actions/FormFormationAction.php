<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class FormFormationAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x17;

    /**
     * ID of the Line formation.
     *
     * @var int
     */
    const LINE_FORMATION = 0x02;

    /**
     * ID of the Box formation.
     *
     * @var int
     */
    const BOX_FORMATION = 0x04;

    /**
     * ID of the Staggered formation.
     *
     * @var int
     */
    const STAGGERED_FORMATION = 0x07;

    /**
     * ID of the Flank (split) formation.
     *
     * @var int
     */
    const FLANK_FORMATION = 0x08;

    /**
     * ID of the player who is changing the formation.
     *
     * @var int
     */
    public $playerId;

    /**
     * Formation type to form.
     *
     * @var int
     */
    public $formation;

    /**
     * Units that should form a formation.
     *
     * @var int[]
     */
    public $units;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $playerId, $formation, $units)
    {
        parent::__construct($rec, $time);

        $this->playerId = $playerId;
        $this->formation = $formation;
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
            'FormFormation(playerId=%d, formation=%d, units[%d]={%s})',
            $this->playerId,
            $this->formation,
            count($this->units),
            implode(', ', $this->units)
        );
    }
}
