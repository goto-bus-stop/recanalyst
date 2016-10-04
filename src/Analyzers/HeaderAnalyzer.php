<?php

namespace RecAnalyst\Analyzers;

use RecAnalyst\Map;
use RecAnalyst\Tile;
use RecAnalyst\Player;
use RecAnalyst\GameInfo;
use RecAnalyst\ChatMessage;
use RecAnalyst\GameSettings;
use RecAnalyst\RecAnalystConst;

class HeaderAnalyzer extends Analyzer
{
    public function run()
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

        $players = [];
        for ($i = 0; $i <= 8; $i += 1) {
            $player = $this->readPlayerMeta();
            if ($player->humanRaw === 0 || $player->humanRaw === 1) {
                continue;
            }
            $players[] = $player;
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

        $teamIndices = array_map('ord', str_split($this->readHeaderRaw(8)));

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
                $this->position += $this->readHeader('l', 4);
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
        }

        $this->position += 4;
        $gameSpeed = $this->readHeader('l', 4);
        $this->position += 37;
        $recPlayerRef = $this->readHeader('v', 2);
        $owner = $playersByIndex[$recPlayerRef];
        if ($owner) {
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

        $gameSettings = new GameSettings([
            'gameType' => $gameType,
            'gameSpeed' => $gameSpeed,
            'mapId' => $mapId,
            'mapName' => isset(RecAnalystConst::$MAPS[$mapId]) ? RecAnalystConst::$MAPS[$mapId] : null,
            'mapStyle' => $this->getMapStyle($mapId),
            'mapSize' => $mapSize,
            // UserPatch stores the actual population limit divided by 25.
            'popLimit' => $version->isUserPatch ? $popLimit * 25 : $popLimit,
            'lockDiplomacy' => $lockDiplomacy
        ]);

        $gameInfo = new GameInfo($this->rec);
        $gameInfo->gameVersion = $version->version;
        $gameInfo->gameSubVersion = $version->subVersion;

        $analysis->mapData = $mapData;
        $analysis->gameSettings = $gameSettings;
        $analysis->gameInfo = $gameInfo;

        return $analysis;
    }

    /**
     * Reads a player meta info block for a single player. This just includes
     * their nickname, index and "human" status. More information about players
     * is stored later on in the recorded game file and is read by the
     * PlayerInfoBlockAnalyzer.
     *
     * Player meta structure:
     *     int32 index;
     *     int32 human; // indicates whether player is AI/human/spectator
     *     uint32 nameLength;
     *     char name[nameLength];
     *
     * @return \RecAnalyst\Player
     */
    protected function readPlayerMeta()
    {
        $player = new Player();
        $player->index = $this->readHeader('l', 4);
        $human = $this->readHeader('l', 4);
        $length = $this->readHeader('L', 4);
        if ($length) {
            $player->name = $this->readHeaderRaw($length);
        } else {
            $player->name = '';
        }
        $player->humanRaw = $human;
        $player->human = $human === 0x02;
        $player->spectator = $human === 0x06;
        return $player;
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
}
