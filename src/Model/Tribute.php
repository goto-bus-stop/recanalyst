<?php

namespace RecAnalyst\Model;

/**
 * Represents a resource tribute.
 */
class Tribute
{
    const FOOD  = 0;
    const WOOD  = 1;
    const STONE = 2;
    const GOLD  = 3;

    /**
     * Time this tribute was sent.
     *
     * @var int
     */
    public $time;

    /**
     * Player this tribute was sent from.
     *
     * @var \RecAnalyst\Model\Player
     */
    public $playerFrom;

    /**
     * Player this tribute was sent to.
     *
     * @var \RecAnalyst\Model\Player
     */
    public $playerTo;

    /**
     * ID of the resource sent in this tribute.
     *
     * @var int
     */
    public $resourceId;

    /**
     * Amount of the resource.
     *
     * @var int
     */
    public $amount;

    /**
     * Market fee.
     *
     * @var float */
    public $fee;

    /**
     * Class constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->time = $this->amount = 0;
        $this->playerFrom = $this->playerTo = null;
        $this->resourceId = self::FOOD;
        $this->fee = 0.0;
    }
}
