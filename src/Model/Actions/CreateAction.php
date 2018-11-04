<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class CreateAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x4;

    // Create( obj_cat=%d, pId=%d, loc=%.10f,%.10f,%.10f )
    private $playerId;
    private $objectCategory;
    private $x;
    private $y;
    private $z;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $playerId, $objectCategory, $x, $y, $z)
    {
        parent::__construct($rec, $time);

        $this->playerId = $playerId;
        $this->objectCategory = $objectCategory;
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }

    /**
     * Get a string representation of the action.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            'Create(playerId=%d, objectCategory=%d, loc=%.10f,%.10f,%.10f)',
            $this->playerId,
            $this->objectCategory,
            $this->x,
            $this->y,
            $this->z
        );
    }
}
