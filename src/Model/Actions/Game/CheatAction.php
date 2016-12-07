<?php

namespace RecAnalyst\Model\Actions\Game;

use RecAnalyst\RecordedGame;
use RecAnalyst\Model\Actions\GameAction;

class CheatAction extends GameAction
{
    const NATURAL_WONDERS = 100;
    const CHEESE_STEAK_JIMMYS = 101;
    const LUMBERJACk = 102;
    const ROCK_ON = 103;
    const ROBIN_HOOD = 104;
    const BLACK_DEATH = 105;
    const TORPEDO1 = 106;
    const TORPEDO2 = 107;
    const TORPEDO3 = 108;
    const TORPEDO4 = 109;
    const TORPEDO5 = 110;
    const TORPEDO6 = 111;
    const TORPEDO7 = 112;
    const TORPEDO8 = 113;
    const I_R_WINNER = 114;
    const WIMPYWIMPYWIMPY = 115;
    const MARCO = 117;
    const POLO = 118;
    const AEGIS = 119;
    const HOW_DO_YOU_TURN_THIS_ON = 122;
    const I_LOVE_THE_MONKEY_HEAD = 123;
    const TO_SMITHEREENS = 124;
    const FURIOUS_THE_MONKEY_BOY = 125;
    const WOOF_WOOF = 126;

    public $cheatId;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time, $action, $playerId, $cheatId)
    {
        parent::__construct($rec, $time, $action, $playerId);

        $this->cheatId = $cheatId;
    }

    /**
     * Get a string representation of the action.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('Game[Cheat](playerId=%d, cheatId=%d)', $this->playerId, $this->cheatId);
    }
}
