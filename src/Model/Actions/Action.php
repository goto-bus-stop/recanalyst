<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;
use RecAnalyst\Utils;

abstract class Action
{
    /**
     * Recorded game the action belongs to.
     *
     * @var \RecAnalyst\RecordedGame
     */
    protected $rec;

    /**
     * Time since the start of the game at which the action occurred,
     * in milliseconds.
     *
     * @var int
     */
    public $time;

    /**
     * Create a player action.
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Time in milliseconds when the action occurred.
     */
    public function __construct(RecordedGame $rec, $time)
    {
        $this->rec = $rec;
        $this->time = $time;
    }

    public function __toString()
    {
        $className = get_class($this);
        $className = substr($className, strrpos($className, '\\') + 1);
        $string = '[' . Utils::formatGameTime($this->time) . '] ' . $className;
        foreach ($this as $k => $v) {
            if ($k === 'rec' || $k === 'time') continue;
            $string .= ' ' . $k . '=';
            if (is_array($v)) {
                $string .= implode(',', $v);
            } else {
                $string .= $v;
            }
        }
        return $string;
    }
}
