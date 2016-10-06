<?php

namespace RecAnalyst;

/**
 * Represents a resource tribute.
 */
class Tribute
{
    const FOOD  = 0;
    const WOOD  = 1;
    const STONE = 2;
    const GOLD  = 3;

    /** @var int Time this tribute was sent. */
    public $time;

    /** @var \RecAnalyst\Player Player this tribute was sent from. */
    public $playerFrom;

    /** @var \RecAnalyst\Player Player this tribute was sent to. */
    public $playerTo;

    /** @var int ID of the resource this tribute was sent. */
    public $resourceId;

    /** @var int Amount of the resource. */
    public $amount;

    /** @var float Market fee. */
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
