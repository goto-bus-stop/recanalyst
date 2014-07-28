<?php
/**
 * Defines Unit class.
 *
 * @package recAnalyst
 */

namespace RecAnalyst;

/**
 * Class Unit.
 *
 * Unit represents a unit object in the game.
 * @package recAnalyst
 */
class Unit {

    /* Unit IDs that we might draw on maps */
    // GAIA (needed for colours when drawing)
    const GOLDMINE   = 66;
    const STONEMINE  = 102;
    const CLIFF1     = 264;
    const CLIFF2     = 265;
    const CLIFF3     = 266;
    const CLIFF4     = 267;
    const CLIFF5     = 268;
    const CLIFF6     = 269;
    const CLIFF7     = 270;
    const CLIFF8     = 271;
    const CLIFF9     = 272;
    const CLIFF10    = 273;
    const RELIC      = 285;
    const TURKEY     = 833;
    const SHEEP      = 594;
    const DEER       = 65;
    const BOAR       = 48;
    const JAVELINA   = 822;
    const FORAGEBUSH = 59;
    // Gates (needed for directions when drawing)
    const GATE  = 487;
    const GATE2 = 490;
    const GATE3 = 665;
    const GATE4 = 673;
    const PALISADE_GATE  = 792;
    const PALISADE_GATE2 = 796;
    const PALISADE_GATE3 = 800;
    const PALISADE_GATE4 = 804;

    /**
     * Id of the player who owns this unit. Zero if GAIA.
     * @var int
     */
    public $owner = 0;

    /**
     * Unit it.
     * @var int
     */
    public $id = 0;

    /**
     * Unit location.
     * @var array
     */
    public $position = array(0, 0);
}
