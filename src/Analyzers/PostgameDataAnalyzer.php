<?php

namespace RecAnalyst\Analyzers;

use RecAnalyst\Player;
use RecAnalyst\Unit;
use RecAnalyst\GameInfo;

class PostgameDataAnalyzer extends Analyzer
{
    private $analysis;

    public function __construct($analysis)
    {
        $this->analysis = $analysis;
    }

    protected function run()
    {
        // Prize for ugliest, most boring method of the project goes to…
        $data = new \stdClass;

        $this->position += 3;
        $data->scenarioFilename = rtrim($this->readBodyRaw(32));
        $this->position += 4;
        $data->duration = $this->readBody('l', 4);
        $data->allowCheats = ord($this->body[$this->position++]);
        $data->complete = ord($this->body[$this->position++]);
        $this->position += 14;
        $data->mapSize = ord($this->body[$this->position++]);
        $data->mapId = ord($this->body[$this->position++]);
        $data->population = ord($this->body[$this->position++]);
        $this->position += 1;
        $data->victory = ord($this->body[$this->position++]);
        $data->startingAge = ord($this->body[$this->position++]);
        $data->resources = ord($this->body[$this->position++]);
        $data->allTechs = ord($this->body[$this->position++]);
        $data->teamTogether = ord($this->body[$this->position++]);
        $data->revealMap = ord($this->body[$this->position++]);
        $this->position += 3;
        $data->lockTeams = ord($this->body[$this->position++]);
        $data->lockSpeed = ord($this->body[$this->position++]);
        $this->position += 1;

        $players = [];
        for ($i = 0; $i < 8; $i++) {
            $playerStats = new \stdClass;
            $playerStats->name = rtrim($this->readBodyRaw(16));
            $playerStats->totalScore = $this->readBody('v', 2);
            $totalScores = [];
            for ($j = 0; $j < 8; $j++) {
                $totalScores[$j] = $this->readBody('v', 2);
            }
            $playerStats->totalScores = $totalScores;
            $playerStats->victory = ord($this->body[$this->position++]);
            $playerStats->civId = ord($this->body[$this->position++]);
            $playerStats->colorId = ord($this->body[$this->position++]);
            $playerStats->team = ord($this->body[$this->position++]);
            $this->position += 2;
            $playerStats->mvp = ord($this->body[$this->position++]);
            $this->position += 3;
            $playerStats->result = ord($this->body[$this->position++]);
            $this->position += 3;

            $militaryStats = new \stdClass;
            $militaryStats->score = $this->readBody('v', 2);
            $militaryStats->unitsKilled = $this->readBody('v', 2);
            $militaryStats->u0 = $this->readBody('v', 2);
            $militaryStats->unitsLost = $this->readBody('v', 2);
            $militaryStats->buildingsRazed = $this->readBody('v', 2);
            $militaryStats->u1 = $this->readBody('v', 2);
            $militaryStats->buildingsLost = $this->readBody('v', 2);
            $militaryStats->unitsConverted = $this->readBody('v', 2);
            $playerStats->militaryStats = $militaryStats;

            $this->position += 32;

            $economyStats = new \stdClass;
            $economyStats->score = $this->readBody('v', 2);
            $economyStats->u0 = $this->readBody('v', 2);
            $economyStats->foodCollected = $this->readBody('l', 4);
            $economyStats->woodCollected = $this->readBody('l', 4);
            $economyStats->stoneCollected = $this->readBody('l', 4);
            $economyStats->goldCollected = $this->readBody('l', 4);
            $economyStats->tributeSent = $this->readBody('v', 2);
            $economyStats->tributeReceived = $this->readBody('v', 2);
            $economyStats->tradeProfit = $this->readBody('v', 2);
            $economyStats->relicGold = $this->readBody('v', 2);
            $playerStats->economyStats = $economyStats;

            $this->position += 16;

            $techStats = new \stdClass;
            $techStats->score = $this->readBody('v', 2);
            $techStats->u0 = $this->readBody('v', 2);
            $techStats->feudalTime = $this->readBody('l', 4);
            $techStats->castleTime = $this->readBody('l', 4);
            $techStats->imperialTime = $this->readBody('l', 4);
            $techStats->mapExploration = ord($this->body[$this->position++]);
            $techStats->researchCount = ord($this->body[$this->position++]);
            $techStats->researchPercent = ord($this->body[$this->position++]);
            $playerStats->techStats = $techStats;

            $this->position += 1;

            $societyStats = new \stdClass;
            $societyStats->score = $this->readBody('v', 2);
            $societyStats->totalWonders = ord($this->body[$this->position++]);
            $societyStats->totalCastles = ord($this->body[$this->position++]);
            $societyStats->relicsCaptured = ord($this->body[$this->position++]);
            $societyStats->u0 = ord($this->body[$this->position++]);
            $societyStats->villagerHigh = $this->readBody('v', 2);
            $playerStats->societyStats = $societyStats;

            $this->position += 84;

            $players[] = $playerStats;
        }
        $data->players = $players;

        $this->position += 4;
        return $data;
    }
}
