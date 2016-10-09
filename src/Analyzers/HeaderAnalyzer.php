<?php

namespace RecAnalyst\Analyzers;

use RecAnalyst\Map;
use RecAnalyst\Team;
use RecAnalyst\Tile;
use RecAnalyst\GameInfo;
use RecAnalyst\ChatMessage;
use RecAnalyst\GameSettings;
use RecAnalyst\RecAnalystConst;

/**
 * Analyzer for most things in a recorded game file header.
 */
class HeaderAnalyzer extends Analyzer
{
    /**
     * Run the analysis.
     *
     * @return object
     */
    protected function run()
    {
        $constant2 = pack('c*', 0x9A, 0x99, 0x99, 0x99, 0x99, 0x99, 0xF9, 0x3F);
        $separator = pack('c*', 0x9D, 0xFF, 0xFF, 0xFF);
        $scenarioConstant = pack('c*', 0xF6, 0x28, 0x9C, 0x3F);
        $aokSeparator = pack('c*', 0x9A, 0x99, 0x99, 0x3F);

        $rec = $this->rec;
        $analysis = new \StdClass;

        $playersByIndex = [];

        $size = strlen($this->header);
        $this->position = 0;

        $version = $this->read(VersionAnalyzer::class);
        $analysis->version = $version->version;
        $analysis->subVersion = $version->subVersion;

        $triggerInfoPos = strrpos($this->header, $constant2, $this->position) + strlen($constant2);
        $gameSettingsPos = strrpos($this->header, $separator, -($size - $triggerInfoPos)) + strlen($separator);
        $scenarioSeparator = $version->isAoK ? $aokSeparator : $scenarioConstant;
        $scenarioHeaderPos = strrpos($this->header, $scenarioSeparator, -($size - $gameSettingsPos));

        $this->position = $gameSettingsPos + 8;

        if (!$version->isAoK) {
            $mapId = $this->readHeader('l', 4);
        }
        $difficulty = $this->readHeader('l', 4);
        $lockTeams = $this->readHeader('L', 4);

        // TODO what are theeeese?
        if ($version->isHDPatch4) {
            $this->position += 12;
            // TODO Is 12.3 the correct cutoff point?
            if ($version->subVersion >= 12.3) {
                $this->position += 4;
            }
        }

        $players = $this->read(PlayerMetaAnalyzer::class);
        foreach ($players as $player) {
            $playersByIndex[$player->index] = $player;
        }
        $analysis->players = $players;

        $this->position = $triggerInfoPos - strlen($constant2);
        if ($version->isMgx) {
            $this->position -= 7;
        }
        $this->position -= 110;
        $victoryCondition = $this->readHeader('l', 4);
        $this->position += 8;
        $isTimeLimit = ord($this->header[$this->position]);
        $this->position += 1;
        if ($isTimeLimit) {
            $timeLimit = $this->readHeader('f', 4);
        }

        $this->position = $triggerInfoPos + 1;
        $this->skipTriggerInfo();

        $teamIndices = [];
        for ($i = 0; $i < 8; $i += 1) {
            $teamIndices[$i] = ord($this->header[$this->position + $i]);
        }
        $this->position += 8;

        foreach ($analysis->players as $i => $player) {
            $player->team = $teamIndices[$i] - 1;
        }

        if ($version->subVersion < 12.3) {
            $this->position += 1;
        }
        $revealMap = $this->readHeader('l', 4);

        $this->position += 4;
        $mapSize = $this->readHeader('l', 4);
        $popLimit = $this->readHeader('l', 4);

        $gameType = -1;
        if ($version->isMgx) {
            $gameType = ord($this->header[$this->position]);
            $lockDiplomacy = ord($this->header[$this->position + 1]);
            $this->position += 2;
        }

        if ($version->subVersion >= 11.96) {
            $this->position += 1;
        }

        $pregameChat = [];
        if ($version->isMgx) {
            $pregameChat = $this->readChat($playersByIndex);
        }

        $this->position = $version->isAoe2Record ? 0x1bf : 0x0c;
        $includeAi = $this->readHeader('L', 4);
        if ($includeAi !== 0) {
            $this->position += 2;
            $numAiStrings = $this->readHeader('v', 2);
            $this->position += 4;
            for ($i = 0; $i < $numAiStrings; $i += 1) {
                $length = $this->readHeader('l', 4);
                $this->position += $length;
            }
            $this->position += 6;
            for ($i = 0; $i < 8; $i += 1) {
                $this->position += 10;
                $numRules = $this->readHeader('v', 2);
                $this->position += 4 + $numRules * 400;
            }
            $this->position += 5544;
            if ($version->subVersion >= 11.96) {
                $this->position += 1280;
            }
            // In mgx2 records...
            if ($version->subVersion >= 12) {
                // TODO Is this constant? (Probably not!)
                $this->position += 477700;
            }
        }

        $this->position += 4;
        $gameSpeed = $this->readHeader('l', 4);
        $this->position += 37;
        $recPlayerRef = $this->readHeader('v', 2);
        if (array_key_exists($recPlayerRef, $playersByIndex)) {
            $owner = $playersByIndex[$recPlayerRef];
            $owner->owner = true;
        }
        $numPlayers = ord($this->header[$this->position]);
        $this->position += 1;
        // - 1, because player #0 is GAIA.
        $analysis->numPlayers = $numPlayers - 1;
        if ($version->isMgx) {
            $this->position += 2;
        }
        $gameMode = $this->readHeader('v', 2);

        $this->position += 58;
        $mapSizeX = $this->readHeader('l', 4);
        $mapSizeY = $this->readHeader('l', 4);
        $analysis->mapSize = [$mapSizeX, $mapSizeY];

        $numUnknownData = $this->readHeader('l', 4);
        for ($i = 0; $i < $numUnknownData; $i += 1) {
            if ($version->subVersion >= 11.93) {
                $this->position += 2048 + $mapSizeX * $mapSizeY * 2;
            } else {
                $this->position += 1275 + $mapSizeX * $mapSizeY;
            }
            $numFloats = $this->readHeader('l', 4);
            $this->position += ($numFloats * 4) + 4;
        }
        $this->position += 2;
        $mapData = [];
        for ($y = 0; $y < $mapSizeY; $y += 1) {
            $mapData[$y] = [];
            for ($x = 0; $x < $mapSizeX; $x += 1) {
                $mapData[$y][$x] = new Tile(
                    $x,
                    $y,
                    /* terrainId */ ord($this->header[$this->position]),
                    /* elevation */ ord($this->header[$this->position + 1])
                );
                $this->position += 2;
            }
        }

        $numData = $this->readHeader('l', 4);
        $this->position += 4 + $numData * 4;
        for ($i = 0; $i < $numData; $i += 1) {
            $numCouples = $this->readHeader('l', 4);
            $this->position += $numCouples * 8;
        }
        $mapSizeX2 = $this->readHeader('l', 4);
        $mapSizeY2 = $this->readHeader('l', 4);
        $this->position += $mapSizeX2 * $mapSizeY2 * 4 + 4;
        $numData = $this->readHeader('l', 4);
        $this->position += $numData * 27 + 4;

        $playerInfo = $this->read(PlayerInfoBlockAnalyzer::class, $analysis);

        $analysis->teams = $this->buildTeams($players);

        $gameSettings = [
            'gameType' => $gameType,
            'gameSpeed' => $gameSpeed,
            'mapSize' => $mapSize,
            // UserPatch stores the actual population limit divided by 25.
            'popLimit' => $version->isUserPatch ? $popLimit * 25 : $popLimit,
        ];

        if (!$version->isAoK) {
            $gameSettings = array_merge($gameSettings, [
                'mapId' => $mapId,
                'mapName' => isset(RecAnalystConst::$MAPS[$mapId]) ?
                    RecAnalystConst::$MAPS[$mapId] : null,
                'mapStyle' => $this->getMapStyle($mapId),
                'lockDiplomacy' => $lockDiplomacy,
            ]);
        }

        $gameInfo = new GameInfo($this->rec);
        $gameInfo->gameVersion = $version->version;
        $gameInfo->gameSubVersion = $version->subVersion;

        $analysis->mapData = $mapData;
        $analysis->pregameChat = $pregameChat;
        $analysis->gameSettings = new GameSettings($gameSettings);
        $analysis->gameInfo = $gameInfo;
        $analysis->playerInfo = $playerInfo;

        return $analysis;
    }

    /**
     * Read a block containing chat messages.
     *
     * Chat block structure:
     *     int32 count;
     *     ChatMessage messages[count];
     * Chat message structure:
     *     int32 length;
     *     char contents[length];
     * Not much data is encoded in the chat message structure, so we derive
     * a lot of it from the `contents` string instead.
     *
     * @param  array  $players  Array of `$playerId => $playerObject`, used to
     *     associate player objects with chat messages.
     * @return array
     */
    protected function readChat(array $players)
    {
        $messages = [];
        $messageCount = $this->readHeader('l', 4);
        for ($i = 0; $i < $messageCount; $i += 1) {
            $length = $this->readHeader('l', 4);
            if ($length <= 0) {
                continue;
            }
            $chat = $this->readHeaderRaw($length);

            // pre-game chat messages are stored as "@#%dPlayerName: Message",
            // where %d is a digit from 1 to 8 indicating player's index (or
            // colour)
            if ($chat[0] == '@' && $chat[1] == '#' && $chat[2] >= '1' && $chat[2] <= '8') {
                $chat = rtrim($chat); // throw null-termination character
                if (!empty($players[$chat[2]])) {
                    $player = $players[$chat[2]];
                } else {
                    // this player left before the game started
                    $player = null;
                }
                $messages[] = ChatMessage::create(null, $player, substr($chat, 3));
            }
        }
        return $messages;
    }

    /**
     * Skip a scenario triggers info block.
     */
    protected function skipTriggerInfo()
    {
        $numTriggers = $this->readHeader('l', 4);
        if ($numTriggers > 0) {
            for ($i = 0; $i < $numTriggers; $i += 1) {
                $this->position += 18;
                $this->position += $this->readHeader('l', 4);
                $this->position += $this->readHeader('l', 4);
                $numEffects = $this->readHeader('l', 4);
                for ($j = 0; $j < $numEffects; $j += 1) {
                    $this->position += 24;
                    $numSelectedObjects = $this->readHeader('l', 4);
                    if ($numSelectedObjects === -1) {
                        $numSelectedObjects = 0;
                    }
                    $this->position += 72;
                    $this->position += $this->readHeader('l', 4);
                    $this->position += $this->readHeader('l', 4);
                    $this->position += $numSelectedObjects * 8;
                }
                $this->position += $numEffects * 8;
                $numConditions = $this->readHeader('l', 4);
                $this->position += (72 * $numConditions) + ($numConditions * 8);
            }
            $this->position += $numTriggers * 8;
            // type = scen
        }
    }

    /**
     * Get the map style for a map ID. Age of Empires categorises the builtin
     * maps into several styles in the Start Game menu, but that information
     * is not stored in the recorded game file (after all, only the map itself
     * is necessary to replay the game).
     *
     * @param  integer  $mapId
     * @return integer
     */
    protected function getMapStyle($mapId)
    {
        if ($mapId == Map::CUSTOM) {
            return GameSettings::MAPSTYLE_CUSTOM;
        } else if (in_array($mapId, RecAnalystConst::$REAL_WORLD_MAPS)) {
            return GameSettings::MAPSTYLE_REALWORLD;
        }
        // TODO add case for the "Special" maps in the HD expansion packs
        return GameSettings::MAPSTYLE_STANDARD;
    }

    /**
     * Group players into teams.
     *
     * @param \RecAnalyst\Player[]  $players  Array of players.
     *
     * @return \RecAnalyst\Team[]
     */
    protected function buildTeams($players)
    {
        $teams = [];
        $teamsByIndex = [];
        foreach ($players as $player) {
            /**
             * Team = 0 can mean two things: either this player has no team,
             * i.e. is in a team on their own, or this player is cooping with
             * another player who _is_ part of a team.
             */
            if ($player->team == 0) {
                $found = false;
                foreach ($teams as $team) {
                    if ($team->getIndex() != $player->team) {
                        continue;
                    }
                    foreach ($team->players as $coopPlayer) {
                        if ($coopPlayer->index == $player->index) {
                            $team->addPlayer($player);
                            $found = true;
                            break;
                        }
                    }
                }
                // Not a cooping player, so add them to their own team.
                if (!$found) {
                    $team = new Team();
                    $team->addPlayer($player);
                    $teams[] = $team;
                    $teamsByIndex[$player->team] = $team;
                }
            } else {
                if (array_key_exists($player->team, $teamsByIndex)) {
                    $teamsByIndex[$player->team]->addPlayer($player);
                } else {
                    $team = new Team();
                    $team ->addPlayer($player);
                    $teams[] = $team;
                    $teamsByIndex[$player->team] = $team;
                }
            }
        }

        return $teams;
    }
}
