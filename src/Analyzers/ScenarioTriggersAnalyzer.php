<?php

namespace RecAnalyst\Analyzers;

use RecAnalyst\Model\Trigger;
use RecAnalyst\Model\TriggerEffect;
use RecAnalyst\Model\TriggerCondition;

class ScenarioTriggersAnalyzer extends Analyzer
{
    public function run()
    {
        $triggers = [];

        $numTriggers = $this->readHeader('l', 4);
        for ($i = 0; $i < $numTriggers; $i += 1) {
            $triggers[] = $this->readTrigger();
        }

        // Trigger order
        $this->position += $numTriggers * 4;

        return $triggers;
    }

    /**
     * Read information about a single trigger.
     *
     * @return \RecAnalyst\Model\Trigger
     */
    protected function readTrigger()
    {
        $trigger = new Trigger();
        $trigger->enabled = $this->readHeader('l', 4) !== 0;
        $trigger->looping = $this->header[$this->position] !== "\0";
        $trigger->objective = $this->header[$this->position + 1] !== "\0";
        $this->position += 2;
        $trigger->descOrder = $this->readHeader('l', 4);
        $this->position += 4;
        $this->position += 4;
        $descriptionLength = $this->readHeader('l', 4);
        if ($descriptionLength > 0) {
            $trigger->description = rtrim($this->readHeaderRaw($descriptionLength), "\0");
        }
        $nameLength = $this->readHeader('l', 4);
        if ($nameLength > 0) {
            $trigger->name = rtrim($this->readHeaderRaw($nameLength), "\0");
        }
        $numEffects = $this->readHeader('l', 4);
        for ($i = 0; $i < $numEffects; $i += 1) {
            $effect = $this->readTriggerEffect();
            $trigger->addEffect($effect);
        }

        // effects order
        $this->position += $numEffects * 4;

        $numConditions = $this->readHeader('l', 4);
        for ($i = 0; $i < $numConditions; $i += 1) {
            $condition = $this->readTriggerCondition();
            $trigger->addCondition($condition);
        }
        // condition order
        $this->position += $numConditions * 4;

        return $trigger;
    }


    /**
     * Read information about a trigger effect.
     *
     * @return \RecAnalyst\Model\TriggerEffect
     */
    protected function readTriggerEffect()
    {
        $effect = new TriggerEffect();
        $effect->type = $this->readHeader('l', 4);
        $effect->check = $this->readHeader('l', 4);
        $effect->setAiGoal = $this->readHeader('l', 4);
        $effect->amount = $this->readHeader('l', 4);
        $effect->resource = $this->readHeader('l', 4);
        $effect->diplomacy = $this->readHeader('l', 4);
        $numSelectedObjects = $this->readHeader('l', 4);
        $effect->unitLocation = $this->readHeader('l', 4);
        $effect->unitType = $this->readHeader('l', 4);
        $effect->playerSource = $this->readHeader('l', 4);
        $effect->playerTarget = $this->readHeader('l', 4);
        $effect->technology = $this->readHeader('l', 4);
        $effect->textId = $this->readHeader('l', 4);
        $effect->soundId = $this->readHeader('l', 4);
        $effect->displayTime = $this->readHeader('l', 4);
        $effect->triggerIndex = $this->readHeader('l', 4);
        $effect->location = [
            $this->readHeader('l', 4),
            $this->readHeader('l', 4),
        ];
        $effect->area = [
            [$this->readHeader('l', 4), $this->readHeader('l', 4)],
            [$this->readHeader('l', 4), $this->readHeader('l', 4)],
        ];
        $effect->unitGroup = $this->readHeader('l', 4);
        $effect->objectType = $this->readHeader('l', 4);
        $effect->instructionPanel = $this->readHeader('l', 4);
        if ($this->version->isHDPatch4) {
            // HD Edition: trigger effects can change unit attack stances.
            // I'd think HD could've just reused an other field here for
            // backwards compatibility, but what do I know!
            $effect->stance = $this->readHeader('l', 4);
        }
        $textLength = $this->readHeader('l', 4);
        if ($textLength > 0) {
            $effect->text = rtrim($this->readHeaderRaw($textLength), "\0");
        }
        $soundFileNameLength = $this->readHeader('l', 4);
        if ($soundFileNameLength > 0) {
            $effect->soundFileName = rtrim($this->readHeaderRaw($soundFileNameLength), "\0");
        }
        for ($i = 0; $i < $numSelectedObjects; $i++) {
            $effect->unitIds[] = $this->readHeader('l', 4);
        }

        return $effect;
    }

    /**
     * Read information about a trigger condition.
     *
     * @return \RecAnalyst\Model\TriggerCondition
     */
    protected function readTriggerCondition()
    {
        $condition = new TriggerCondition();
        $condition->type = $this->readHeader('l', 4);
        $condition->check = $this->readHeader('l', 4);
        $condition->amount = $this->readHeader('l', 4);
        $condition->resource = $this->readHeader('l', 4);
        $condition->unitObject = $this->readHeader('l', 4);
        $condition->unitLocation = $this->readHeader('l', 4);
        $condition->unitType = $this->readHeader('l', 4);
        $condition->player = $this->readHeader('l', 4);
        $condition->technology = $this->readHeader('l', 4);
        $condition->timer = $this->readHeader('l', 4);
        $this->position += 4;
        $condition->area = [
            [$this->readHeader('l', 4), $this->readHeader('l', 4)],
            [$this->readHeader('l', 4), $this->readHeader('l', 4)],
        ];
        $condition->unitGroup = $this->readHeader('l', 4);
        $this->position += 4;
        $condition->aiSignal = $this->readHeader('l', 4);
        if ($this->version->isHDPatch4) {
            $condition->reverse = $this->readHeader('l', 4);
            $condition->unknown2 = $this->readHeader('l', 4);
        }

        return $condition;
    }
}
