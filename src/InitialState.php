<?php
/**
 * Defines InitialState class.
 *
 * @package RecAnalyst
 */

namespace RecAnalyst;

/**
 * Class InitialState.
 *
 * InitialState implements initial state of a player.
 *
 * @package RecAnalyst
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
     * @var int
     */
    public $food;

    /**
     * Initial wood
     * @var int
     */
    public $wood;

    /**
     * Initial stone.
     * @var int
     */
    public $stone;

    /**
     * Initial gold.
     * @var int
     */
    public $gold;

    /**
     * Starting age.
     * @var int
     */
    public $startingAge;

    /**
     * Initial house capacity.
     * @var int
     */
    public $houseCapacity;

    /**
     * Initial population.
     * @var int
     */
    public $population;

    /**
     * Initial civilian population.
     * @var int
     */
    public $civilianPop;

    /**
     * Initial military population.
     * @var int
     */
    public $militaryPop;

    /**
     * Initial extra population.
     * @var int
     */
    public $extraPop;

    /**
     * Initial position.
     * @var array
     */
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
        $this->position = array(0, 0);
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
