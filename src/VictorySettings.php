<?php

namespace RecAnalyst;

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

    /** @var int Time limit. */
    public $_timeLimit;

    /** @var int Score limit. */
    public $_scoreLimit;

    /** @var int Victory condition. */
    public $_victoryCondition;

    /**
     * Class constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_timeLimit = $this->_scoreLimit = 0;
        $this->_victoryCondition = self::STANDARD;
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
