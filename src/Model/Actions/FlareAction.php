<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class FlareAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x73;

    /**
     * The number of the player that sent the flare.
     *
     * @var int
     */
    public $playerNumber;

    /**
     * The index of the player that sent the flare.
     * This can differ from the player number in coop games.
     *
     * @var int
     */
    public $playerIndex;

    /**
     * X-coordinate of the flare location.
     *
     * @var float
     */
    public $x;

    /**
     * Y-coordinate of the flare location.
     *
     * @var float
     */
    public $y;

    /**
     * Determines which player numbers can see the flare.
     *
     * @var bool[]
     */
    public $visible;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $playerNumber, $playerIndex, $x, $y, $visible)
    {
        parent::__construct($rec, $time);

        $this->playerNumber = $playerNumber;
        $this->playerIndex = $playerIndex;
        $this->x = $x;
        $this->y = $y;
        $this->visible = $visible;
    }

    /**
     * Get a string representation of the action.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            'Flare(from=%d, fromIndex=%d, x=%.2f, y=%.2f, visible={%s})',
            $this->playerNumber,
            $this->playerIndex,
            $this->x,
            $this->y,
            implode(', ', array_map(function ($visible) {
                return $visible ? 'Y' : 'N';
            }, $this->visible))
        );
    }
}
