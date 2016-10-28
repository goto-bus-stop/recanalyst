<?php

namespace RecAnalyst\Model;

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

    /**
     * Initial food.
     *
     * @var int
     */
    public $food = 0;

    /**
     * Initial wood.
     *
     * @var int
     */
    public $wood = 0;

    /**
     * Initial stone.
     *
     * @var int
     */
    public $stone = 0;

    /**
     * Initial gold.
     *
     * @var int
     */
    public $gold = 0;

    /**
     * Starting age.
     *
     * @var int
     */
    public $startingAge = self::DARKAGE;

    /**
     * Initial house capacity.
     *
     * @var int
     */
    public $houseCapacity = 0;

    /**
     * Initial population.
     *
     * @var int
     */
    public $population = 0;

    /**
     * Initial civilian population.
     *
     * @var int
     */
    public $civilianPop = 0;

    /**
     * Initial military population.
     *
     * @var int
     */
    public $militaryPop = 0;

    /**
     * Initial extra population.
     *
     * @var int
     */
    public $extraPop = 0;

    /**
     * Initial position, `[$x, $y]`.
     *
     * @var float[]
     */
    public $position = [0, 0];
}
