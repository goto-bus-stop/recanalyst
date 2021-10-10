<?php

namespace RecAnalyst\Model;

class AIRule
{
    private $conditions = [];
    private $actions = [];

    public function addCondition(AIAction $condition)
    {
        $this->conditions[] = $condition;
    }

    public function addAction(AIAction $action)
    {
        $this->actions[] = $action;
    }

    public function conditions()
    {
        return $this->conditions;
    }

    public function actions()
    {
        return $this->actions;
    }
}
