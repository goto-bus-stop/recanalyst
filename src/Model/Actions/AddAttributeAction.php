<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents an attribute addition, for example when cheating in more
 * resources.
 */
class AddAttributeAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x05;

    /**
     * Player the action applies to.
     *
     * @var int
     */
    public $playerId;

    /**
     * Attribute number.
     *
     * @var int
     */
    public $attribute;

    /**
     * Amount of $attribute to add.
     *
     * @var int
     */
    public $amount;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time)
    {
        parent::__construct($rec, $time);
    }

    /**
     * Get a string representation of the action.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            'AddAttribute(playerId=%d, attribute=%d, amount=%d)',
            $this->playerId,
            $this->attribute,
            $this->amount
        );
    }
}
