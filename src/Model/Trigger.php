<?php

namespace RecAnalyst\Model;

/**
 * Represents a trigger.
 */
class Trigger
{
    /**
     * List of trigger conditions.
     *
     * @var \RecAnalyst\Model\TriggerCondition[]
     */
    private $conditions = [];
    /**
     * List of trigger effects.
     *
     * @var \RecAnalyst\Model\TriggerEffect[]
     */
    private $effects = [];

    /**
     * True if the trigger is enabled, false if disabled.
     *
     * @var bool
     */
    public $enabled;

    /**
     * True if the trigger is looping, false otherwise.
     *
     * @var bool
     */
    public $looping;

    /**
     * True if the trigger is part of the scenario objective, false otherwise.
     *
     * @var bool
     */
    public $objective;

    /**
     * [Todo]
     *
     * @var int
     */
    public $descOrder;

    /**
     * Trigger description.
     *
     * @var string
     */
    public $description;

    /**
     * Trigger name.
     *
     * @var string
     */
    public $name;

    /**
     * Add a condition to this trigger.
     *
     * @param \RecAnalyst\Model\TriggerCondition  $condition  Condition.
     * @return void
     */
    public function addCondition(TriggerCondition $condition)
    {
        $this->conditions[] = $condition;
    }

    /**
     * Add an effect to this trigger.
     *
     * @param \RecAnalyst\Model\TriggerEffect  $condition  Condition.
     * @return void
     */
    public function addEffect(TriggerEffect $effect)
    {
        $this->effects[] = $effect;
    }

    public function conditions()
    {
        return $this->conditions;
    }

    public function effects()
    {
        return $this->effects;
    }
}
