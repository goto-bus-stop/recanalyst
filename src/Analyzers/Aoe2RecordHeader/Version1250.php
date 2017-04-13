<?php

namespace RecAnalyst\Analyzers\Aoe2RecordHeader;

use RecAnalyst\Analyzers\Analyzer;

class Version1250 extends Analyzer
{
    public function run()
    {
        $idk = $this->readHeader('f', 4); // float 1000, 1004, 1005...
        $this->position += 4; // int 1000

        // Unknown, AoK HD.exe string "mrefDlcOptions" may be related.
        $this->position += 4;

        $datasetsCount = $this->readHeader('l', 4);
        $datasets = [];
        for ($i = 0; $i < $datasetsCount; $i++) {
            // Not sure what these stand for yet.
            $datasets[] = $this->readHeader('l', 4);
        }

        $difficulty = $this->readHeader('l', 4);
        $mapSize = $this->readHeader('l', 4);
        $mapId = $this->readHeader('l', 4);
        $revealMap = $this->readHeader('l', 4);
        $victoryType = $this->readHeader('l', 4);
        $startingResources = $this->readHeader('l', 4);
        $startingAge = $this->readHeader('l', 4);
        $endingAge = $this->readHeader('l', 4);
        $gameType = $this->readHeader('l', 4);

        // Separator, twice.
        $this->position += 4;
        $this->position += 4;

        $gameSpeed = $this->readHeader('f', 4);
        $treatyLength = $this->readHeader('l', 4);
        $popLimit = $this->readHeader('l', 4);
        $numPlayers = $this->readHeader('l', 4);

        /* Maybe:
            unusedPlayerColor ← 8 one-byte flags?
            mVictory.getAmount() ← int?
        */
        $this->position += 8;

        // Separator.
        $this->position += 4;

        $tradingEnabled = ord($this->header[$this->position++]) !== 0;
        $teamBonusesDisabled = ord($this->header[$this->position++]) !== 0;
        $randomizePositions = ord($this->header[$this->position++]) !== 0;
        $fullTechTreeEnabled = ord($this->header[$this->position++]) !== 0;
        $numberOfStartingUnits = ord($this->header[$this->position++]) !== 0;
        $teamsLocked = ord($this->header[$this->position++]) !== 0;
        $speedLocked = ord($this->header[$this->position++]) !== 0;
        $isMultiPlayer = ord($this->header[$this->position++]) !== 0;
        $cheatsEnabled = ord($this->header[$this->position++]) !== 0;
        $recordGameEnabled = ord($this->header[$this->position++]) !== 0;
        $animalsEnabled = ord($this->header[$this->position++]) !== 0;
        $predatorsEnabled = ord($this->header[$this->position++]) !== 0;

        // Separator.
        $this->position += 4;

        // Unknowns.
        $this->position += 8;

        $players = [];
        for ($i = 0; $i < 8; $i++) {
            if ($i >= $numPlayers) {
                // Skip empty players.
                $this->position += 52;
                continue;
            }

            $this->position += 2;
            // Hash of data files.
            $datCrc = $this->readHeader('l', 4);
            $mpVersion = ord($this->header[$this->position++]);
            $teamIndex = $this->readHeader('l', 4);
            $civId = $this->readHeader('l', 4);
            $aiBaseName = $this->readAoe2RecordString();
            $aiCivNameIndex = ord($this->header[$this->position++]);
            $unknownName = $this->readAoe2RecordString();
            $playerName = $this->readAoe2RecordString();
            $humanity = $this->readHeader('l', 4);
            $steamId = $this->readHeader('P', 8);
            $playerIndex = $this->readHeader('l', 4);
            $unknown = $this->readHeader('l', 4); // Seems to be constant 3 among all players so far...
            $scenarioIndex = $this->readHeader('l', 4);

            $players[] = [
                'datCrc' => $datCrc,
                'mpVersion' => $mpVersion,
                'teamIndex' => $teamIndex,
                'civId' => $civId,
                'aiBaseName' => $aiBaseName,
                'aiCivNameIndex' => $aiCivNameIndex,
                'unknownName' => $unknownName,
                'playerName' => $playerName,
                'humanity' => $humanity,
                'steamId' => $steamId,
                'playerIndex' => $playerIndex,
                'unknown' => $unknown,
                'scenarioIndex' => $scenarioIndex,
            ];
        }

        // Unknown flag.
        $this->position++;

        $fogOfWarEnabled = ord($this->header[$this->position++]);
        $cheatNotificationsEnabled = ord($this->header[$this->position++]);
        $coloredChatEnabled = ord($this->header[$this->position++]);

        // Separator.
        $this->position += 4;

        $isRanked = ord($this->header[$this->position++]);
        $allowSpectators = ord($this->header[$this->position++]);

        $lobbyVisibility = $this->readHeader('l', 4);
        $customRandomMapFileCrc = $this->readHeader('l', 4);

        // Few unknown-ishes.
        $this->readAoe2RecordString(); // customScenarioOrCampaignFile
        $this->position += 8;
        $this->readAoe2RecordString(); // customRandomMapFile
        $this->position += 8;
        $this->readAoe2RecordString(); // customRandomMapScenarioFile
        $this->position += 8;

        $guid = $this->readGuid();
        $gameTitle = $this->readAoe2RecordString();
        $moddedDatasetTitle = $this->readAoe2RecordString();
        $moddedDatasetWorkshopId = $this->readHeader('P', 8);

        $this->readAoe2RecordString();
        $this->position += 16;

        // TODO decide on a format to output this stuff.
        return [
            'datasets' => $datasets,
            'difficulty' => $difficulty,
            'mapSize' => $mapSize,
            'mapId' => $mapId,
            'revealMap' => $revealMap,
            'victoryType' => $victoryType,
            'startingResources' => $startingResources,
            'startingAge' => $startingAge,
            'endingAge' => $endingAge,
            'gameType' => $gameType,
            'gameSpeed' => $gameSpeed,
            'treatyLength' => $treatyLength,
            'popLimit' => $popLimit,
            'numPlayers' => $numPlayers,
            'tradingEnabled' => $tradingEnabled,
            'teamBonusesDisabled' => $teamBonusesDisabled,
            'randomizePositions' => $randomizePositions,
            'fullTechTreeEnabled' => $fullTechTreeEnabled,
            'numberOfStartingUnits' => $numberOfStartingUnits,
            'teamsLocked' => $teamsLocked,
            'speedLocked' => $speedLocked,
            'isMultiPlayer' => $isMultiPlayer,
            'cheatsEnabled' => $cheatsEnabled,
            'recordGameEnabled' => $recordGameEnabled,
            'animalsEnabled' => $animalsEnabled,
            'predatorsEnabled' => $predatorsEnabled,
            'fogOfWarEnabled' => $fogOfWarEnabled,
            'cheatNotificationsEnabled' => $cheatNotificationsEnabled,
            'coloredChatEnabled' => $coloredChatEnabled,
            'isRanked' => $isRanked,
            'allowSpectators' => $allowSpectators,
            'lobbyVisibility' => $lobbyVisibility,
            'customRandomMapFileCrc' => $customRandomMapFileCrc,
            'guid' => $guid,
            'gameTitle' => $gameTitle,
            'moddedDatasetTitle' => $moddedDatasetTitle,
            'moddedDatasetWorkshopId' => $moddedDatasetWorkshopId,
            'players' => $players,
        ];
    }

    private function readAoe2RecordString()
    {
        $len = $this->readHeader('v', 2);
        $this->position += 2; // short 0x60 0xA0
        return $this->readHeaderRaw($len);
    }

    private function readGuid()
    {
        $guidData = unpack('C*', $this->readHeaderRaw(16));
        $guid = '';
        foreach ($guidData as $byte) {
            $guid .= $byte < 0x10 ? '0' . dechex($byte) : dechex($byte);
        }
        return $guid;
    }
}
