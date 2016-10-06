<?php

namespace RecAnalyst;

/**
 * InitialState represents the initial state of a player, including resources
 * and population.
 */
class InitialState
{
    const DARKAGE         = 0;
    const FEUDALAGE       = 1;
    const CASTLEAGE       = 2;
    const IMPERIALAGE     = 3;
    const POSTIMPERIALAGE = 4;

    /** @var int Initial food. */
    public $food;

    /** @var int Initial wood. */
    public $wood;

    /** @var int Initial stone. */
    public $stone;

    /** @var int Initial gold. */
    public $gold;

    /** @var int Starting age. */
    public $startingAge;

    /** @var int Initial house capacity. */
    public $houseCapacity;

    /** @var int Initial population. */
    public $population;

    /** @var int Initial civilian population. */
    public $civilianPop;

    /** @var int Initial military population. */
    public $militaryPop;

    /** @var int Initial extra population. */
    public $extraPop;

    /** @var float[] Initial position, `[$x, $y]`. */
    public $position;

    /**
     * Class constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->food = $this->wood = $this->stone = 0;
        $this->startingAge = self::DARKAGE;
        $this->houseCapacity = 0;
        $this->population = $this->civilianPop = $this->militaryPop = $this->extraPop = 0;
        $this->position = [0, 0];
    }

    /**
     * Returns starting age string.
     *
     * @return string
     */
    public function getStartingAgeString()
    {
        return isset(RecAnalystConst::$STARTING_AGES[$this->startingAge]) ?
            RecAnalystConst::$STARTING_AGES[$this->startingAge] : '';
    }
}
