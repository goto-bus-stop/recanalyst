<?php

namespace RecAnalyst\Analyzers;

use RecAnalyst\Analyzers\Analyzer;

class Aoe2RecordHeaderAnalyzer extends Analyzer
{
    public function run()
    {
        $data = [];

        $gameOptionsVersion = $this->readHeader('f', 4); // float 1000, 1004, 1005...

        $dataSetOrVersion = $this->readHeader('l', 4);
        if ($dataSetOrVersion === 0 || $dataSetOrVersion === 1) {
            $dataSet = $dataSetOrVersion;
            $dlcOptionsVersion = 0;
        } else {
            $dataSet = $this->readHeader('l', 4);
            $dlcOptionsVersion = $dataSetOrVersion;
        }

        $dlcCount = $this->readHeader('l', 4);
        $dlcs = [];
        for ($i = 0; $i < $dlcCount; $i++) {
            $dlcs[] = $this->readHeader('l', 4);
        }
        $data['dataSet'] = $dataSet;
        $data['datasets'] = $dlcs; // backwards compat
        $data['dlcs'] = $dlcs;

        $data['difficulty'] = $this->readHeader('l', 4);
        $data['mapSize'] = $this->readHeader('l', 4);
        $data['mapId'] = $this->readHeader('l', 4);
        $data['revealMap'] = $this->readHeader('l', 4);
        $data['victoryType'] = $this->readHeader('l', 4);
        $data['startingResources'] = $this->readHeader('l', 4);
        $data['startingAge'] = $this->readHeader('l', 4);
        $data['endingAge'] = $this->readHeader('l', 4);
        $data['gameType'] = $this->readHeader('l', 4);

        // Separator
        $this->position += 4;

        if ($gameOptionsVersion === 1000.0) {
            $mapName = $this->readAoe2RecordString();
            $this->readAoe2RecordString();
        }

        // Separator again
        $this->position += 4;

        $data['gameSpeed'] = $this->readHeader('f', 4);
        $data['treatyLength'] = $this->readHeader('l', 4);
        $data['popLimit'] = $this->readHeader('l', 4);
        $data['numPlayers'] = $this->readHeader('l', 4);

        $numPlayers = $data['numPlayers'];

        /* Maybe:
            unusedPlayerColor ← 8 one-byte flags?
            mVictory.getAmount() ← int?
        */
        $this->position += 8;

        // Separator.
        $this->position += 4;

        $data['tradingEnabled'] = ord($this->header[$this->position++]) !== 0;
        $data['teamBonusesDisabled'] = ord($this->header[$this->position++]) !== 0;
        $data['randomizePositions'] = ord($this->header[$this->position++]) !== 0;
        $data['fullTechTreeEnabled'] = ord($this->header[$this->position++]) !== 0;
        $data['numberOfStartingUnits'] = ord($this->header[$this->position++]) !== 0;
        $data['teamsLocked'] = ord($this->header[$this->position++]) !== 0;
        $data['speedLocked'] = ord($this->header[$this->position++]) !== 0;
        $data['isMultiPlayer'] = ord($this->header[$this->position++]) !== 0;
        $data['cheatsEnabled'] = ord($this->header[$this->position++]) !== 0;
        $data['recordGameEnabled'] = ord($this->header[$this->position++]) !== 0;
        $data['animalsEnabled'] = ord($this->header[$this->position++]) !== 0;
        $data['predatorsEnabled'] = ord($this->header[$this->position++]) !== 0;

        // Separator.
        $this->position += 4;

        // Unknowns.
        $this->position += 8;

         if ($gameOptionsVersion >= 1004.0) {
             // Version 12.49, 12.50, maybe others.
            $players = $this->readPlayers1004($gameOptionsVersion, $numPlayers);
        } else {
            $separator = pack('c*', 0xA3, 0x5F, 0x02, 0x00);
            $this->position = strpos($this->header, $separator, $this->position) + 4;
            $this->position = strpos($this->header, $separator, $this->position) + 4;
            $this->position += 10;
            return $data;
        }
        $data['players'] = $players;

        // Unknown flag.
        $this->position++;

        $data['fogOfWarEnabled'] = ord($this->header[$this->position++]);
        $data['cheatNotificationsEnabled'] = ord($this->header[$this->position++]);
        $data['coloredChatEnabled'] = ord($this->header[$this->position++]);

        // Separator.
        $this->position += 4;

        $data['isRanked'] = ord($this->header[$this->position++]);
        $data['allowSpectators'] = ord($this->header[$this->position++]);

        $data['lobbyVisibility'] = $this->readHeader('l', 4);
        $data['customRandomMapFileCrc'] = $this->readHeader('l', 4);

        // Few unknown-ishes.
        $this->readAoe2RecordString(); // customScenarioOrCampaignFile
        $this->position += 8;
        $this->readAoe2RecordString(); // customRandomMapFile
        $this->position += 8;
        $this->readAoe2RecordString(); // customRandomMapScenarioFile
        $this->position += 8;

        $data['guid'] = $this->readGuid();
        $data['gameTitle'] = $this->readAoe2RecordString();
        $data['moddedDatasetTitle'] = $this->readAoe2RecordString();
        // Not sure if this should be inside the v1005.0 `if`.
        $data['moddedDatasetWorkshopId'] = $this->readHeader('P', 8);

        if ($gameOptionsVersion >= 1005.0) {
            $this->readAoe2RecordString();
            $this->position += 4;
        } else if ($gameOptionsVersion >= 1004.0) {
            $this->position += 8;
        }

        // TODO decide on a format to output this stuff.
        return $data;
    }

    private function readPlayers1004($gameOptionsVersion, $numPlayers)
    {
        $players = [];
        for ($i = 0; $i < 8; $i++) {
            if ($i >= $numPlayers) {
                // Skip empty players.
                $this->position += 48;
                if ($gameOptionsVersion >= 1005.0) {
                    $this->position += 4;
                }
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
            $unknownName = null;
            if ($gameOptionsVersion >= 1005.0) {
                $unknownName = $this->readAoe2RecordString();
            }
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
        return $players;
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
