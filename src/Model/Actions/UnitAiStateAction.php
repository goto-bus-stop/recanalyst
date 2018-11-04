<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class UnitAiStateAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x12;

    const AGGRESSIVE = 0x00;
    const DEFENSIVE = 0x01;
    const STAND_GROUND = 0x02;
    const NO_ATTACK = 0x03;

    /**
     * Current stance.
     *
     * @var int
     */
    public $state;

    /**
     * Units.
     *
     * @var int[]
     */
    public $units = [];

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $state, $units)
    {
        parent::__construct($rec, $time);

        $this->state = $state;
        $this->units = $units;
    }

    /**
     * Check whether the new stance is aggressive.
     *
     * @return bool True if the new stance is aggressive, false otherwise.
     */
    public function isAggressive()
    {
        return $this->state === self::AGGRESSIVE;
    }

    /**
     * Check whether the new stance is defensive.
     *
     * @return bool True if the new stance is defensive, false otherwise.
     */
    public function isDefensive()
    {
        return $this->state === self::DEFENSIVE;
    }

    /**
     * Check whether the new stance is standing ground.
     *
     * @return bool True if the new stance is standing ground, false otherwise.
     */
    public function isStandGround()
    {
        return $this->state === self::STAND_GROUND;
    }

    /**
     * Check whether the new stance is "No Attack".
     *
     * @return bool True if the new stance is passive, false otherwise.
     */
    public function isNoAttack()
    {
        return $this->state === self::NO_ATTACK;
    }

    /**
     * Get a string representation of the action.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            'UnitAiState(state=%d, units[%d]={%s})',
            $this->state,
            count($this->units),
            implode(', ', $this->units)
        );
    }
}
