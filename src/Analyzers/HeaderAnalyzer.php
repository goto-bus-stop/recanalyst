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
        $aoe2recordScenarioSeparator = pack('c*', 0xAE, 0x47, 0xA1, 0x3F);
        $aoe2recordHeaderSeparator = pack('c*', 0xA3, 0x5F, 0x02, 0x00);

        $rec = $this->rec;
        $this->analysis = new \StdClass;
        $analysis = $this->analysis;

        $playersByIndex = [];
        $gameType = -1;

        $size = strlen($this->header);
        $this->position = 0;

        $this->version = $this->read(VersionAnalyzer::class);
        $version = $this->version;

        $triggerInfoPos = strrpos($this->header, $constant2, $this->position) + strlen($constant2);
        $gameSettingsPos = strrpos($this->header, $separator, -($size - $triggerInfoPos)) + strlen($separator);
        $scenarioSeparator = $scenarioConstant;
        if ($version->isAoK) {
            $scenarioSeparator = $aokSeparator;
        }
        if ($version->isAoe2Record) {
            $scenarioSeparator = $aoe2recordScenarioSeparator;
        }
        $scenarioHeaderPos = strrpos($this->header, $scenarioSeparator, -($size - $gameSettingsPos));
        if ($scenarioHeaderPos !== false) {
            $scenarioHeaderPos -= 4;
        }

        if ($version->isAoe2Record) {
            $aoe2recordHeader = $this->read(Aoe2RecordHeaderAnalyzer::class);
        }

        $includeAi = $this->readHeader('L', 4);
        if ($includeAi !== 0) {
            $this->skipAi();
        }

        $this->position += 4;
        if ($version->isAoe2Record) {
            $this->position += 4;
            $gameSpeed = $aoe2recordHeader['gameSpeed'];
        } else {
            $gameSpeed = $this->readHeader('l', 4);
        }
        // These bytes contain the game speed again several times over, as ints
        // and as floats (On normal speed: 150, 1.5 and 0.15). Why?!
        $this->position += 37;
        $pov = $this->readHeader('v', 2);
        if ($version->isAoe2Record) {
            $numPlayers = $aoe2recordHeader['numPlayers'];
            $gameMode = $aoe2recordHeader['isMultiPlayer'];
            $this->position += 5;
        } else {
            $numPlayers = ord($this->header[$this->position++]);
            $numPlayers -= 1;
            // - 1, because player #0 is GAIA.
            if ($version->isMgx) {
                $this->position += 1; // Is instant building enabled? (cheat "aegis")
                $this->position += 1; // Are cheats enabled?
            }
            $gameMode = $this->readHeader('v', 2);
        }

        // TODO what are these?
        // Something is up here because we do `+=46` just below which makes for `+=58` anyway.
        // For some reason PHP runs out of memory if I do it differently…
        if ($version->subVersion >= 12.50) {
            $this->position += 12;
        }

        $analysis->numPlayers = $numPlayers;

        if ($version->isAoe2Record && $version->subVersion >= 12.49) {
            $this->position += 46;
        } else {
            $this->position += 58;
        }

        $mapData = $this->read(MapDataAnalyzer::class);
        $analysis->mapSize = $mapData->mapSize;

        // int. Value is 10060 in AoK recorded games, 40600 in AoC and on.
        $this->position += 4;

        $playerInfoPos = $this->position;

        $this->analysis->scenarioFilename = null;
        if ($scenarioHeaderPos > 0) {
            $this->position = $scenarioHeaderPos;
            $this->readScenarioHeader();
            // Set game type here, it will be overwritten by data from the
            // recorded game settings later. In MGL files there is no game
            // type field in the recorded game, so this one will be used.
            $gameType = GameSettings::TYPE_SCENARIO;
        }

        $analysis->messages = $this->readMessages();

        // Skip two separators to find the victory condition block.
        $this->position = strpos($this->header, $separator, $this->position);
        $this->position = strpos($this->header, $separator, $this->position + 4);

        $analysis->victory = $this->read(VictorySettingsAnalyzer::class);

        $this->position = $gameSettingsPos + 8;

        // TODO Is 12.3 the correct cutoff point?
        if ($version->subVersion >= 12.3) {
            // TODO what are theeeese?
            $this->position += 16;
        }

        if ($version->isAoe2Record) {
            // Always 0? Map ID is now in the aoe2record front matter and gets
            // parsed below.
            $this->position += 4;
            $mapId = $aoe2recordHeader['mapId'];
        } else if (!$version->isAoK) {
            $mapId = $this->readHeader('l', 4);
        }
        $difficulty = $this->readHeader('l', 4);
        $lockTeams = $this->readHeader('L', 4);

        $players = $this->read(PlayerMetaAnalyzer::class);
        foreach ($players as $player) {
            $playersByIndex[$player->index] = $player;
            if ($player->index === $pov) {
                $player->owner = true;
            }
        }

        // Merge in player from the aoe2record header if it exists.
        // In some cases (eg. civId) the places where the data was originally stored is now empty,
        // with the data instead only being stored in the aoe2record header.
        // Other player analyzers will fall back to this data in those cases.
        if ($version->isAoe2Record && array_key_exists('players', $aoe2recordHeader)) {
            foreach ($players as $i => $player) {
                foreach ($aoe2recordHeader['players'][$i] as $key => $value) {
                    $player->$key = $value;
                }
            }
        }

        $analysis->players = $players;

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

        $restore = $this->position;

        $this->position = $playerInfoPos;
        $playerInfo = $this->read(PlayerInfoBlockAnalyzer::class, $analysis);

        $this->position = $restore;

        if ($version->subVersion < 12.3) {
            $this->position += 1;
        }
        $revealMap = $this->readHeader('l', 4);

        $this->position += 4;
        $mapSize = $this->readHeader('l', 4);
        $popLimit = $this->readHeader('l', 4);

        if ($version->isMgx) {
            $gameType = ord($this->header[$this->position]);
            $lockDiplomacy = ord($this->header[$this->position + 1]);
            $this->position += 2;
        }

        if ($version->subVersion >= 11.96) {
            $this->position += 1;
        }

        if ($version->isHDEdition) {
            $this->position += 4;
        }

        $pregameChat = [];
        if ($version->isMgx) {
            $pregameChat = $this->readChat($players);
        }

        $analysis->teams = $this->buildTeams($players);

        $gameSettings = [
            'gameType' => $gameType,
            'gameSpeed' => $gameSpeed,
            'mapSize' => $mapSize,
            'difficultyLevel' => $difficulty,
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

        $analysis->mapData = $mapData->terrain;
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
        $playersByNumber = [];
        foreach ($players as $player) {
            $playersByNumber[$player->number] = $player;
        }

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
                if (!empty($playersByNumber[$chat[2]])) {
                    $player = $playersByNumber[$chat[2]];
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

        if ($this->version->isHDPatch4) {
            $conditionSize += 2 * 4; // 2 ints
        }

        $numTriggers = $this->readHeader('l', 4);
        for ($i = 0; $i < $numTriggers; $i += 1) {
            $this->position += 4 + (2 * 1) + (3 * 4); // int, 2 bools, 3 ints
            $descriptionLength = $this->readHeader('l', 4);
            // HD edition 4.x saves a length of -1 when the string is absent,
            // whereas older versions would use 0. That used to work fine
            // without this guard, but now we should only skip if the length is
            // positive.
            if ($descriptionLength > 0) {
                $this->position += $descriptionLength;
            }
            $nameLength = $this->readHeader('l', 4);
            if ($nameLength > 0) {
                $this->position += $nameLength;
            }
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
                if ($this->version->isHDPatch4) {
                    $this->position += 4; // int for the new Attack Stance effect
                }
                $textLength = $this->readHeader('l', 4);
                if ($textLength > 0) {
                    $this->position += $textLength;
                }
                $soundFileNameLength = $this->readHeader('l', 4);
                if ($soundFileNameLength > 0) {
                    $this->position += $soundFileNameLength;
                }
                $this->position += $numSelectedObjects * 4; // unit IDs (one int each)
            }
            $this->position += $numEffects * 4; // effect order (list of ints)
            $numConditions = $this->readHeader('l', 4);
            $this->position += $numConditions * $conditionSize; // conditions
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
     * @param \RecAnalyst\Model\Player[]  $players  Array of players.
     *
     * @return \RecAnalyst\Model\Team[]
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
