<?php

namespace RecAnalyst\Analyzers;

use RecAnalyst\Model\VictorySettings;

/**
 * Analyze an aoe2record game info header.
 */
class VictorySettingsAnalyzer extends Analyzer
{
    protected function run()
    {
        $victory = new VictorySettings($this->rec);
        $this->position += 4; // separator 9D FF FF FF

        $customConquest = $this->readHeader('L', 4) !== 0;
        $this->position += 4; // zero
        $customRelics = $this->readHeader('l', 4);
        $this->position += 4; // zero
        $customPercentExplored = $this->readHeader('l', 4);
        $this->position += 4; // zero
        $customAll = $this->readHeader('L', 4) !== 0;
        $mode = $this->readHeader('l', 4);
        $score = $this->readHeader('l', 4);
        $timeLimit = $this->readHeader('l', 4);

        $victory->mode = $mode;
        $victory->timeLimit = $timeLimit;
        $victory->scoreLimit = $score;
        // TODO add custom victory information somewhere:
        // $victory->_customRelics = $customRelics;
        // $victory->_customPercentExplored = $customPercentExplored;
        // $victory->_customAll = $customAll;

        return $victory;
    }
}
