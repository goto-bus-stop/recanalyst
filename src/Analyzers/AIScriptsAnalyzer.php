<?php

namespace RecAnalyst\Analyzers;

use RecAnalyst\Model\AI;
use RecAnalyst\Model\AIRule;
use RecAnalyst\Model\AIAction;
use RecAnalyst\Model\AICondition;

class AIScriptsAnalyzer extends Analyzer
{
    public function run()
    {
        // String table
        $stringTable = [];
        $aiScripts = [];

        $this->version = $this->get(VersionAnalyzer::class);
        $this->aiVersion = 0.02;

        $this->position += 2; // max strings
        $numAiStrings = $this->readHeader('v', 2);
        $this->position += 4;
        for ($i = 0; $i < $numAiStrings; $i += 1) {
            $length = $this->readHeader('l', 4);
            $stringTable[$i] = $this->readHeaderRaw($length);
        }
        $this->position += 6;

        for ($i = 0; $i < 8; $i += 1) {
            $aiScripts[] = $this->readScript();
        }

        // some data for each player
        // there _can_ be more here but I don't know what it is yet
        $this->position += 8 * 8;
        $this->readMeta();
        if ($this->version->subVersion >= 11.96) {
            $this->position += 1280; // ???
        }

        // TODO is this the correct cutoff point?
        if ($this->version->subVersion >= 12.3) {
            // The 4 bytes here are likely actually somewhere in between one
            // of the skips above.
            $this->position += 4;
        }

        return (object) [
            'version' => $this->aiVersion,
            'stringTable' => $stringTable,
            'scripts' => $aiScripts,
        ];
    }

    /**
     * Read information about a single trigger.
     *
     * @return array
     */
    protected function readScript()
    {
        $this->position += (
            4 + // int unknown
            4 + // int seq
            2 // max rules, constant
        );
        $numRules = $this->readHeader('v', 2);
        $rules = [];
        $this->position += 4;
        for ($i = 0; $i < $numRules; $i++) {
            $rules[] = $this->readRule();
        }
        return $rules;
    }

    /**
     * Read information about a trigger effect.
     *
     * @return \RecANalyst\Model\AIRule
     */
    protected function readRule()
    {
        // For HD Edition's MGX2 files.
        $actionCount = 16;
        if ($this->version->isHDPatch4) {
            // Looks like recent HD Editions support more action types!
            $actionCount += 24;
        }

        $rule = new AIRule();
        $this->position += 12;
        $rule->factsCount = ord($this->header[$this->position]);
        $rule->factsActionsCount = ord($this->header[$this->position + 1]);
        $this->position += 4;
        for ($i = 0; $i < $actionCount; $i++) {
            $action = $this->readAction();
            if ($i < $rule->factsCount) {
                $rule->addCondition($action);
            } else if ($i < $rule->factsActionsCount) {
                $rule->addAction($action);
            }
        }
        return $rule;
    }

    /**
     * Read information about a trigger condition.
     *
     * @return \RecAnalyst\Model\AIAction
     */
    protected function readAction()
    {
        $action = new AIAction();
        $action->type = $this->readHeader('l', 4);
        $action->id = $this->readHeader('v', 4);
        $this->position += 2;
        $action->params = [
            $this->readHeader('l', 4),
            $this->readHeader('l', 4),
            $this->readHeader('l', 4),
            $this->readHeader('l', 4),
        ];
        return $action;
    }

    protected function readMeta()
    {
        if ($this->version->subVersion >= 10.82) {
            $this->aiVersion = $this->readHeader('f', 4);
            if ($this->aiVersion >= 0.13) {
                $this->aiVersion = $this->readHeader('f', 4);
            }
        }
        if ($this->aiVersion >= 0.14) {
            $isDeathMatchGame = $this->readHeader('l', 4);
            $isRegicideGame = $this->readHeader('l', 4);
            $mapSize = $this->readHeader('l', 4);
            $mapType = $this->readHeader('l', 4);
            $startingResources = $this->readHeader('l', 4);
            $startingAge = $this->readHeader('l', 4);
            $cheatsEnabled = $this->readHeader('l', 4);
            $difficulty = $this->readHeader('l', 4);
        }
        if ($this->aiVersion >= 0.12) {
            // timers: 10 ints * 8 players
            $this->position += 8 * 10 * 4;
        }
        if ($this->aiVersion >= 0.09) {
            // shared goals: 256 ints
            $this->position += 256 * 4;
        }
        if ($this->aiVersion >= 0.08) {
            // signals, script goals, received taunt flags
            $this->position += 4096;
        }
    }
}
