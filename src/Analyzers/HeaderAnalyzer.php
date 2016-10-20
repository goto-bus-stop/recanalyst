<?php

namespace RecAnalyst\Analyzers;

use RecAnalyst\Map;
use RecAnalyst\Utils;
use RecAnalyst\Model\GameInfo;
use RecAnalyst\Model\Team;
use RecAnalyst\Model\Tile;
use RecAnalyst\Model\ChatMessage;
use RecAnalyst\Model\GameSettings;

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
        $this->analysis = new \StdClass;
        $analysis = $this->analysis;

        $playersByIndex = [];

        $size = strlen($this->header);
        $this->position = 0;

        $this->version = $this->read(VersionAnalyzer::class);
        $version = $this->version;
        $analysis->version = $version->version;
        $analysis->subVersion = $version->subVersion;

        $triggerInfoPos = strrpos($this->header, $constant2, $this->position) + strlen($constant2);
        $gameSettingsPos = strrpos($this->header, $separator, -($size - $triggerInfoPos)) + strlen($separator);
        $scenarioSeparator = $version->isAoK ? $aokSeparator : $scenarioConstant;
        $scenarioHeaderPos = strrpos($this->header, $scenarioSeparator, -($size - $gameSettingsPos));
        if ($scenarioHeaderPos !== -1) {
            $scenarioHeaderPos -= 4;
        }

        $this->position = $gameSettingsPos + 8;

        if (!$version->isAoK) {
            $mapId = $this->readHeader('l', 4);
        }
        $difficulty = $this->readHeader('l', 4);
        $lockTeams = $this->readHeader('L', 4);

        // TODO Is 12.3 the correct cutoff point?
        if ($version->subVersion >= 12.3) {
            // TODO what are theeeese?
            $this->position += 16;
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

        if ($version->isAoe2Record) {
            // Skip aoe2record header.
            // TODO this probably contains more version information, perhaps
            // about expansion packs or mods. Should read that in
            // VersionAnalyzer.
            $this->position = 0x0c;
            // Skip 6 separators
            $separator = pack('c*', 0xA3, 0x5F, 0x02, 0x00);
            for ($i = 0; $i < 6; $i++) {
                $this->position = strpos($this->header, $separator, $this->position);
                if ($this->position === false) {
                    throw new \Exception('Unrecognized aoe2record header format.');
                }
                $this->position += strlen($separator); // length of separator
            }
            // Some unknown stuff
            $this->position += 10;
        } else {
            $this->position = 0x0c;
        }

        $includeAi = $this->readHeader('L', 4);
        if ($includeAi !== 0) {
            $this->skipAi();
        }

        $this->position += 4;
        $gameSpeed = $this->readHeader('l', 4);
        $this->position += 37;
        $pov = $this->readHeader('v', 2);
        if (array_key_exists($pov, $playersByIndex)) {
            $owner = $playersByIndex[$pov];
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

        // If we went wrong somewhere, throw now so we don't end up in a near-
        // infinite loop later.
        if ($mapSizeX > 10000 || $mapSizeY > 10000) {
            throw new \Exception('Got invalid map size');
        }

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

        if ($scenarioHeaderPos > 0) {
            $this->position = $scenarioHeaderPos;
            $this->readScenarioHeader();
            // Set game type now if it wasn't known. (Game type data is not
            // included in MGL files.)
            if ($gameType === -1) {
                $gameType = GameSettings::TYPE_SCENARIO;
            }
        }

        $analysis->messages = $this->readMessages();

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
                'lockDiplomacy' => $lockDiplomacy,
            ]);
        }

        $gameInfo = new GameInfo($this->rec);

        $analysis->mapData = $mapData;
        $analysis->pregameChat = $pregameChat;
        $analysis->gameSettings = new GameSettings($this->rec, $gameSettings);
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
     *
     */
    protected function skipAi()
    {
        $version = $this->version;

        // String table
        $this->position += 2;
        $numAiStrings = $this->readHeader('v', 2);
        $this->position += 4;
        for ($i = 0; $i < $numAiStrings; $i += 1) {
            $length = $this->readHeader('l', 4);
            $this->position += $length;
        }
        $this->position += 6;

        // Compiled script
        // Compute size of a single AI rule. A rule can contain conditions and
        // actions, with 4 integer parameters each. A rule can have 16
        $actionSize = (
            4 + // int type
            2 + // id
            2 + // unknown
            4 * 4 // params
        );
        $ruleSize = (
            12 + // unknown
            1 + // number of facts
            1 + // number of facts + actions
            2 + // unknown
            $actionSize * 16
        );

        // For HD Edition's MGX2 files.
        if ($version->isHDPatch4) {
            // TODO what's in this? More actions, perhaps?
            $ruleSize += 0x180;
        }

        for ($i = 0; $i < 8; $i += 1) {
            $this->position += (
                4 + // int unknown
                4 + // int seq
                2 // max rules, constant
            );
            $numRules = $this->readHeader('v', 2);
            $this->position += 4;
            for ($j = 0; $j < $numRules; $j++) {
                $this->position += $ruleSize;
            }
        }
        $this->position += 104; // unknown
        $this->position += 10 * 4 * 8; // timers: 10 ints * 8 players
        $this->position += 256 * 4; // shared goals: 256 ints
        $this->position += 4096; // ???
        if ($version->subVersion >= 11.96) {
            $this->position += 1280; // ???
        }

        // TODO is this the correct cutoff point?
        if ($version->subVersion >= 12.3) {
            // The 4 bytes here are likely actually somewhere in between one
            // of the skips above.
            $this->position += 4;
        }
    }

    /**
     * Skip a scenario triggers info block. See ScenarioTriggersAnalyzer for
     * contents of a trigger block.
     */
    protected function skipTriggerInfo()
    {
        // Effects and triggers are of variable size, but conditions are
        // constant.
        $conditionSize = (
            (11 * 4) + // 11 ints
            (4 * 4) + // area (4 ints)
            (3 * 4) // 3 ints
        );

        $numTriggers = $this->readHeader('l', 4);
        for ($i = 0; $i < $numTriggers; $i += 1) {
            $this->position += 4 + (2 * 1) + (3 * 4); // int, 2 bools, 3 ints
            $descriptionLength = $this->readHeader('l', 4);
            $this->position += $descriptionLength;
            $nameLength = $this->readHeader('l', 4);
            $this->position += $nameLength;
            $numEffects = $this->readHeader('l', 4);
            for ($j = 0; $j < $numEffects; $j += 1) {
                $this->position += 6 * 4; // 6 ints
                $numSelectedObjects = $this->readHeader('l', 4);
                if ($numSelectedObjects === -1) {
                    $numSelectedObjects = 0;
                }
                $this->position += 9 * 4; // 9 ints
                $this->position += 2 * 4; // location (2 ints)
                $this->position += 4 * 4; // area (2 locations)
                $this->position += 3 * 4; // 3 ints
                $textLength = $this->readHeader('l', 4);
                $this->position += $textLength;
                $soundFileNameLength = $this->readHeader('l', 4);
                $this->position += $soundFileNameLength;
                $this->position += $numSelectedObjects * 4; // unit IDs (one int each)
            }
            $this->position += $numEffects * 4; // effect order (list of ints)
            $numConditions = $this->readHeader('l', 4);
            $this->position += $numConditions * $conditionSize;
            $this->position += $numConditions * 4; // conditions order (list of ints)
        }

        if ($numTriggers > 0) {
            $this->position += $numTriggers * 4; // trigger order (list of ints)
            // TODO perhaps also set game type to Scenario here?
        }
    }

    /**
     * Read the scenario info header. Contains information about configured
     * players and the scenario file.
     *
     * @return void
     */
    protected function readScenarioHeader()
    {
        $nextUnitId = $this->readHeader('l', 4);
        $this->position += 4;
        // Player names
        for ($i = 0; $i < 16; $i++) {
            $this->position += 256; // rtrim(readHeaderRaw(), \0)
        }
        // Player names (string table)
        for ($i = 0; $i < 16; $i++) {
            $this->position += 4; // int
        }
        for ($i = 0; $i < 16; $i++) {
            $this->position += 4; // bool isActive
            $this->position += 4; // bool isHuman
            $this->position += 4; // int civilization
            $this->position += 4; // const 0x00000004
        }
        $this->position += 5;

        $elapsedTime = $this->readHeader('f', 4);
        $nameLen = $this->readHeader('v', 2);
        $filename = $this->readHeaderRaw($nameLen);

        // These should be string IDs for messages?
        if ($this->version->isMgl) {
            $this->position += 20;
        } else {
            $this->position += 24;
        }

        $this->analysis->scenarioFilename = $filename;
    }

    /**
     * Read messages.
     *
     * @return \StdClass
     */
    protected function readMessages()
    {
        $len = $this->readHeader('v', 2);
        $instructions = rtrim($this->readHeaderRaw($len), "\0");
        $len = $this->readHeader('v', 2);
        $hints = rtrim($this->readHeaderRaw($len), "\0");
        $len = $this->readHeader('v', 2);
        $victory = rtrim($this->readHeaderRaw($len), "\0");
        $len = $this->readHeader('v', 2);
        $loss = rtrim($this->readHeaderRaw($len), "\0");
        $len = $this->readHeader('v', 2);
        $history = rtrim($this->readHeaderRaw($len), "\0");
        $len = $this->readHeader('v', 2);
        $scouts = rtrim($this->readHeaderRaw($len), "\0");
        return (object) [
            'instructions' => $instructions,
            'hints' => $hints,
            'victory' => $victory,
            'loss' => $loss,
            'history' => $history,
            'scouts' => $scouts,
        ];
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
                    if ($team->index() != $player->team) {
                        continue;
                    }
                    foreach ($team->players() as $coopPlayer) {
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
