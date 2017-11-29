<?php

namespace RecAnalyst\Model;

use RecAnalyst\RecordedGame;

/**
 * Victory implements game's victory settings.
 */
class VictorySettings
{
    const STANDARD   = 0;
    const CONQUEST   = 1;
    const TIMELIMIT  = 2;
    const SCORELIMIT = 3;
    const CUSTOM     = 4;

    /**
     * Recorded game instance.
     *
     * @var \RecAnalyst\RecordedGame
     */
    private $rec;

    /**
     * Time limit.
     *
     * @api private
     * @var int
     */
    public $timeLimit = 0;

    /**
     * Score limit.
     *
     * @api private
     * @var int
     */
    public $scoreLimit = 0;

    /**
     * Victory condition.
     *
     * @api private
     * @var int
     */
    public $mode = self::STANDARD;

    /**
     * Class constructor.
     *
     * @return void
     */
    public function __construct(RecordedGame $rec)
    {
        $this->rec = $rec;
    }

    /**
     * Returns victory string.
     *
     * @return string
     */
    public function getVictoryString()
    {
        if (!isset(RecAnalystConst::$VICTORY_CONDITIONS[$this->_victoryCondition])) {
            return '';
        }
        $result = RecAnalystConst::$VICTORY_CONDITIONS[$this->_victoryCondition];
        switch ($this->_victoryCondition) {
            case self::TIMELIMIT:
                if ($this->_timeLimit) {
                    return sprintf('%s (%d years)', $result, $this->_timeLimit);
                }
                break;
            case self::SCORELIMIT:
                if ($this->_scoreLimit) {
                    return sprintf('%s (%d)', $result, $this->_scoreLimit);
                }
                break;
        }
        return $result;
    }
}
