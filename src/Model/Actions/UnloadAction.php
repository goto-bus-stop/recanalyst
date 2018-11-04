<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class UnloadAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x6f;

    public $x;
    public $y;
    public $flag;
    public $unitType;
    public $units;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $x, $y, $flag, $unitType, $units)
    {
        parent::__construct($rec, $time);

        $this->x = $x;
        $this->y = $y;
        $this->flag = $flag;
        $this->unitType = $unitType;
        $this->units = $units;
    }

    /**
     * Get a string representation of the action.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            'Unload(x=%.2f, y=%.2f, flag=%d, unitType=%d, units[%d]={%s})',
            $this->x,
            $this->y,
            $this->flag,
            $this->unitType,
            count($this->units),
            implode(', ', $this->units)
        );
    }
}
