<?php

namespace RecAnalyst\Analyzers;

/**
 * Analyze a UserPatch post-game data block, containing achievements.
 */
class PostgameDataAnalyzer extends Analyzer
{
    protected function run()
    {
        // Prize for ugliest, most boring method of the project goes to…
        $data = new \stdClass;

        $this->position += 3; // Skip body command metadata.
        $data->scenarioFilename = rtrim($this->readBodyRaw(32));
        $data->numPlayers = $this->readBody('l', 4);
        $data->duration = $this->readBody('l', 4);
        $data->allowCheats = ord($this->body[$this->position++]);
        $data->complete = ord($this->body[$this->position++]);
        $this->position += 10; // Always zeros?
        $data->u0 = $this->readBody('f', 4); // Always 2.0?
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
        $data->isDeatchMatch = ord($this->body[$this->position++]);
        $data->isRegicide = ord($this->body[$this->position++]);
        $data->u1 = ord($this->body[$this->position++]);
        $data->lockTeams = ord($this->body[$this->position++]);
        $data->lockSpeed = ord($this->body[$this->position++]);
        $data->u2 = ord($this->body[$this->position++]);

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
            $playerStats->alliesCount = ord($this->body[$this->position++]);
            $this->position += 1; // Always -1?
            $playerStats->mvp = ord($this->body[$this->position++]);
            $this->position += 3; // Padding?
            $playerStats->result = ord($this->body[$this->position++]);
            $this->position += 3; // Padding?

            $militaryStats = new \stdClass;
            $militaryStats->score = $this->readBody('v', 2);
            $militaryStats->unitsKilled = $this->readBody('v', 2);
            $militaryStats->hitPointsKilled = $this->readBody('v', 2);
            $militaryStats->unitsLost = $this->readBody('v', 2);
            $militaryStats->buildingsRazed = $this->readBody('v', 2);
            $militaryStats->hitPointsRazed = $this->readBody('v', 2);
            $militaryStats->buildingsLost = $this->readBody('v', 2);
            $militaryStats->unitsConverted = $this->readBody('v', 2);
            // Amount of units killed and buildings razed against each player.
            $militaryStats->playerUnitsKilled = [];
            for ($other = 1; $other <= 8; $other++) {
                $militaryStats->playerUnitsKilled[$other] = $this->readBody('v', 2);
            }
            $militaryStats->playerBuildingsRazed = [];
            for ($other = 1; $other <= 8; $other++) {
                $militaryStats->playerBuildingsRazed[$other] = $this->readBody('v', 2);
            }
            $playerStats->militaryStats = $militaryStats;

            $economyStats = new \stdClass;
            $economyStats->score = $this->readBody('v', 2);
            $economyStats->u0 = $this->readBody('v', 2); // Probably padding?
            $economyStats->foodCollected = $this->readBody('l', 4);
            $economyStats->woodCollected = $this->readBody('l', 4);
            $economyStats->stoneCollected = $this->readBody('l', 4);
            $economyStats->goldCollected = $this->readBody('l', 4);
            $economyStats->tributeSent = $this->readBody('v', 2);
            $economyStats->tributeReceived = $this->readBody('v', 2);
            $economyStats->tradeProfit = $this->readBody('v', 2);
            $economyStats->relicGold = $this->readBody('v', 2);
            // Tribute sent to each player.
            $economyStats->playerTributeSent = [];
            for ($other = 1; $other <= 8; $other++) {
                $economyStats->playerTributeSent[$other] = $this->readBody('v', 2);
            }
            $playerStats->economyStats = $economyStats;

            $techStats = new \stdClass;
            $techStats->score = $this->readBody('v', 2);
            $techStats->u0 = $this->readBody('v', 2); // Probably padding?
            $techStats->feudalTime = $this->readBody('l', 4);
            $techStats->castleTime = $this->readBody('l', 4);
            $techStats->imperialTime = $this->readBody('l', 4);
            $techStats->mapExploration = ord($this->body[$this->position++]);
            $techStats->researchCount = ord($this->body[$this->position++]);
            $techStats->researchPercent = ord($this->body[$this->position++]);
            $playerStats->techStats = $techStats;

            $this->position += 1; // Padding

            $societyStats = new \stdClass;
            $societyStats->score = $this->readBody('v', 2);
            $societyStats->totalWonders = ord($this->body[$this->position++]);
            $societyStats->totalCastles = ord($this->body[$this->position++]);
            $societyStats->relicsCaptured = ord($this->body[$this->position++]);
            $societyStats->u0 = ord($this->body[$this->position++]);
            $societyStats->villagerHigh = $this->readBody('v', 2);
            $playerStats->societyStats = $societyStats;

            // Padding.
            $this->position += 84;

            $players[] = $playerStats;
        }
        $data->players = $players;

        $this->position += 4;
        return $data;
    }
}
