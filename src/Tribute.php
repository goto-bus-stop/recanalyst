<?php
/**
 * Defines Tribute class.
 *
 * @package recAnalyst
 */

namespace RecAnalyst;

/**
 * Class Tribute.
 *
 * Tribute represents a tribute.
 * @package recAnalyst
 */
class Tribute {

    /* Resources */
    const FOOD  = 0;
    const WOOD  = 1;
    const STONE = 2;
    const GOLD  = 3;

    /**
     * Time this tribute was sent.
     * @var int
     */
    public $time;

    /**
     * Player this tribute was sent from.
     * @var Player
     */
    public $playerFrom;

    /**
     * Player this tribute was sent to.
     * @var Player
     */
    public $playerTo;

    /**
     * Id of the resource this tribute was sent.
     * @var int
     */
    public $resourceId;

    /**
     * Amount of the resource.
     * @var int
     */
    public $amount;

    /**
     * Market fee.
     * @var float
     */
    public $fee;

    /**
     * Class constructor.
     * @return void
     */
    public function __construct() {
        $this->time = $this->amount = 0;
        $this->playerFrom = $this->playerTo = null;
        $this->resourceId = self::FOOD;
        $this->fee = 0.0;
    }
}
