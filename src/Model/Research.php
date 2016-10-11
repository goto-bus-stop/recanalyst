<?php

namespace RecAnalyst\Model;

use RecAnalyst\RecordedGame;

/**
 * Represents a research action.
 */
class Research
{
    /** @var \RecAnalyst\RecordedGame */
    private $rec;

    /** @var int */
    public $id;

    /** @var int */
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
