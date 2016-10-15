<?php

namespace RecAnalyst\Model;

use RecAnalyst\RecordedGame;

/**
 * Represents a research action.
 */
class Research
{
    /**
     * Recorded game.
     *
     * @var \RecAnalyst\RecordedGame
     */
    private $rec;

    /**
     * Research ID that was researched by this action.
     *
     * @var int
     */
    public $id;

    /**
     * Time since the start of the game at which the research action occurred,
     * in milliseconds.
     *
     * @var int
     */
    public $time;

    /**
     * Create a research action.
     */
    public function __construct(RecordedGame $rec, $id, $time)
    {
        $this->rec = $rec;
        $this->id = $id;
        $this->time = $time;
    }
}
