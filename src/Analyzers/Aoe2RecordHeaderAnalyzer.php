<?php

namespace RecAnalyst\Analyzers;

use RecAnalyst\Analyzers\Analyzer;

class Aoe2RecordHeaderAnalyzer extends Analyzer
{
    public function run()
    {
        $data = [];

        $version = $this->readHeader('f', 4); // float 1000, 1004, 1005...
        $this->position += 4; // int 1000

        // Unknown, AoK HD.exe string "mrefDlcOptions" may be related.
        $this->position += 4;

        $datasetsCount = $this->readHeader('l', 4);
        $datasets = [];
        for ($i = 0; $i < $datasetsCount; $i++) {
            // Not sure what these stand for yet.
            $datasets[] = $this->readHeader('l', 4);
        }
        $data['datasets'] = $datasets;

        $data['difficulty'] = $this->readHeader('l', 4);
        $data['mapSize'] = $this->readHeader('l', 4);
        $data['mapId'] = $this->readHeader('l', 4);// MapType
        $data['revealMap'] = $this->readHeader('l', 4);// Visibility
        $data['victoryType'] = $this->readHeader('l', 4);
        $data['startingResources'] = $this->readHeader('l', 4);// ResourceLevel
        $data['startingAge'] = $this->readHeader('l', 4);
        $data['endingAge'] = $this->readHeader('l', 4);
        $data['gameType'] = $this->readHeader('l', 4);// GameMode

        $this->position += 4;// ExpectedMarkerValue (155555)

        if ($version < 1001.0) {// the following were moved elsewhere:
            $mapName = $this->readAoe2RecordString();// CustomRandomMapFileName
            $this->readAoe2RecordString();// CustomScenarioFileName
        }

        $this->position += 4;// MarkerValue (155555)

        $data['gameSpeed'] = $this->readHeader('f', 4);
        $data['treatyLength'] = $this->readHeader('l', 4);
        $data['popLimit'] = $this->readHeader('l', 4);// PopulationLimit
        $data['numPlayers'] = $this->readHeader('l', 4);// NumPlayersExcludingGaia

        $numPlayers = $data['numPlayers'];

        $this->position += 4;// UnusedPlayerColor
        $this->position += 4;// VictoryAmount

        $this->position += 4;// MarkerValue (155555)

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

        $this->position += 4;// MarkerValue (155555)

        // Unknowns.
        $this->position += 8;

         if ($version >= 1004.0) {
             // Version 12.49, 12.50, maybe others.
            $players = $this->readPlayers1004($version, $numPlayers);
        } else {
            $separator = pack('c*', 0xA3, 0x5F, 0x02, 0x00);
            $this->position = strpos($this->header, $separator, $this->position) + 4;
            $this->position = strpos($this->header, $separator, $this->position) + 4;
            $this->position += 10;
            return $data;
        }
        $data['players'] = $players;

        $this->position += 1;// DummyIsRestoreGame
        
        if ($version < 1001.0) {
            $this->position += 4;// CustomScenarioFileCrc
        }

        $data['fogOfWarEnabled'] = ord($this->header[$this->position++]);
        $data['cheatNotificationsEnabled'] = ord($this->header[$this->position++]);
        $data['coloredChatEnabled'] = ord($this->header[$this->position++]);

        // Separator.
        $this->position += 4;

        if ($version >= 1.22) {
            $data['isRanked'] = ord($this->header[$this->position++]);
            $data['allowSpectators'] = ord($this->header[$this->position++]);
            $data['lobbyVisibility'] = $this->readHeader('l', 4);// LobbyVisibilityId
        }
        
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

        if ($version >= 1005.0) {
            $this->readAoe2RecordString();
            $this->position += 4;
        } else if ($version >= 1004.0) {
            $this->position += 8;
        }

        // TODO decide on a format to output this stuff.
        return $data;
    }
    
    /**
     * "AoK HD.exe" (patch 5.5) sub_601BB0
     */
    private function readOldPlayerInfo($version)
    {
        if ($version < 2.0) {
            $this->position += 4;// OldCivArray
            if ($version <= 1.17) {
                $this->position += 4;// OldExtraCivElement
            }
            $this->position += 4;// OldHasRandomCivArray
            if ($version <= 1.17) {
                $this->position += 4;// OldExtraRandomCivElement
            }
            return;
        }
        if ($version < 1002.0) {
            $this->position += 4;// CivChoice
            return;
        }
    }

    private function readPlayers1004($version, $numPlayers)
    {
        $players = [];
        for ($i = 0; $i < 8; $i++) {
            if ($i >= $numPlayers) {
                // Skip empty players.
                $this->position += 48;
                if ($version >= 1005.0) {
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
            if ($version >= 1005.0) {
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
