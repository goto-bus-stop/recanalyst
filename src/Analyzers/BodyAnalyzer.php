<?php

namespace RecAnalyst\Analyzers;

use RecAnalyst\Unit;
use RecAnalyst\GameInfo;
use RecAnalyst\ChatMessage;
use RecAnalyst\RecordedGame;
use RecAnalyst\ResourcePacks\AgeOfEmpires\Civilization;

class BodyAnalyzer extends Analyzer
{
    const OP_COMMAND = 0x01;
    const OP_SYNC = 0x02;
    const OP_META = 0x03;
    const OP_META2 = 0x04;

    const META_GAME_START = 0x01F4;
    const META_CHAT = -1;

    const COMMAND_RESIGN = 0x0B;
    const COMMAND_RESEARCH = 0x65;
    const COMMAND_TRAIN = 0x77;
    const COMMAND_TRAIN_SINGLE = 0x64;
    const COMMAND_BUILD = 0x66;
    const COMMAND_TRIBUTE = 0x6C;
    const COMMAND_POSTGAME = 0xFF;

    const RESEARCH_FEUDAL = 101;
    const RESEARCH_CASTLE = 102;
    const RESEARCH_IMPERIAL = 103;

    private $version = null;
    private $currentTime = 0;
    private $tributes = [];
    private $chatMessages = [];
    private $units = [];
    private $buildings = [];
    private $postGameData = null;

    protected function run()
    {
        $pack = $this->rec->getResourcePack();
        $this->version = $this->get(VersionAnalyzer::class);
        $version = $this->version;

        $players = $this->get(PlayerMetaAnalyzer::class);
        $playersByIndex = [];
        foreach ($players as $player) {
            $playersByIndex[$player->index] = $player;
        }

        $size = strlen($this->body);
        $this->position = 0;
        while ($this->position < $size - 3) {
            $operationType = 0;
            if ($version->isMgl && $this->position === 0) {
                $operationType = self::OP_META2;
            } else {
                $operationType = $this->readBody('l', 4);
            }

            if ($operationType === self::OP_META || $operationType === self::OP_META2) {
                $command = $this->readBody('l', 4);
                if ($command === self::META_GAME_START) {
                    $this->processGameStart();
                } elseif ($command === self::META_CHAT) {
                    $this->processChatMessage();
                }
            } else if ($operationType === self::OP_SYNC) {
                $this->processSync();
            } else if ($operationType === self::OP_COMMAND) {
                $length = $this->readBody('l', 4);
                $next = $this->position + $length;
                $command = ord($this->body[$this->position]);
                $this->position++;

                switch ($command) {
                    // player resign
                    case self::COMMAND_RESIGN:
                        $playerIndex = ord($this->body[$this->position]);
                        $playerNumber = ord($this->body[$this->position + 1]);
                        $disconnected = ord($this->body[$this->position + 2]);
                        $this->position += 3;
                        $player = $playersByIndex[$playerIndex];
                        if ($player && $player->resignTime === 0) {
                            $player->resignTime = $this->currentTime;
                            $message = sprintf('%s resigned', $player->name);
                            $this->chatMessages[] = new ChatMessage($this->currentTime, null, $message);
                        }
                        break;
                    // researches
                    case self::COMMAND_RESEARCH:
                        $this->position += 3;
                        $buildingId = $this->readBody('l', 4);
                        $playerId = $this->readBody('v', 4);
                        $researchId = $this->readBody('v', 4);
                        $player = $playersByIndex[$playerId];
                        if (!$player) {
                            break;
                        }
                        switch ($researchId) {
                            case self::RESEARCH_FEUDAL:
                                $researchDuration = 130000;
                                $player->feudalTime = $this->currentTime + $researchDuration;
                                break;
                            case self::RESEARCH_CASTLE:
                                // persians have faster research time
                                $researchDuration = 160000;
                                if ($player->civId === Civilization::PERSIANS) {
                                    $researchDuration /= 1.10;
                                }
                                $player->castleTime = $this->currentTime + round($researchDuration);
                                break;
                            case self::RESEARCH_IMPERIAL:
                                $researchDuration = 190000;
                                if ($player->civId === Civilization::PERSIANS) {
                                    $researchDuration /= 1.15;
                                }
                                $player->imperialTime = $this->currentTime + round($researchDuration);
                                break;
                        }
                        $player->researches[$researchId] = $this->currentTime;
                        break;
                    // training unit
                    case self::COMMAND_TRAIN:
                        $this->position += 3;
                        $buildingId = $this->readBody('l', 4);
                        $unitType = $this->readBody('v', 2);
                        $amount = $this->readBody('v', 2);

                        if (!isset($this->units[$unitType])) {
                            $this->units[$unitType] = $amount;
                        } else {
                            $this->units[$unitType] += $amount;
                        }
                        break;
                    // AI trains unit
                    case self::COMMAND_TRAIN_SINGLE:
                        $this->position += 9;
                        $unitType = $this->readBody('v', 2);
                        if (!isset($this->units[$unitType])) {
                            $this->units[$unitType] = 1;
                        } else {
                            $this->units[$unitType] += 1;
                        }
                        break;
                    // building
                    case self::COMMAND_BUILD:
                        $this->position += 1;
                        $playerId = $this->readBody('v', 2);
                        $this->position += 8;
                        $buildingType = $this->readBody('v', 2);

                        $buildingType = $pack->normalizeUnit($buildingType);

                        if (!isset($this->buildings[$playerId][$buildingType])) {
                            $this->buildings[$playerId][$buildingType] = 1;
                        } else {
                            $this->buildings[$playerId][$buildingType]++;
                        }
                        break;
                    // tributing
                    case self::COMMAND_TRIBUTE:
                        $playerIdFrom = ord($this->body[$this->position]);
                        $playerIdTo = ord($this->body[$this->position]);
                        $resourceId = ord($this->body[$this->position]);
                        $this->position += 3;

                        $playerFrom = $playersByIndex[$playerIdFrom];
                        $playerTo = $playersByIndex[$playerIdTo];

                        if ($playerFrom && $playerTo) {
                            $amount = $this->readBody('f', 4);
                            $marketFee = $this->readBody('f', 4);

                            $tribute = new Tribute();
                            $tribute->time = $this->currentTime;
                            $tribute->playerFrom = $playerFrom;
                            $tribute->playerTo = $playerTo;
                            $tribute->resourceId = $resourceId;
                            $tribute->amount = floor($amount);
                            $tribute->fee = $marketFee;
                            $this->tributes[] = $tribute;
                        } else {
                            $this->position += 8;
                        }
                        break;
                    // multiplayer postgame data in UP1.4 RC2+
                    case self::COMMAND_POSTGAME:
                        $this->postGameData = $this->read(PostgameDataAnalyzer::class);
                        break;
                    default:
                        break;
                }

                $this->position = $next;
            }
        }

        if (!empty($this->chatMessages)) {
            usort($this->chatMessages, function ($a, $b) {
                return $a->time - $b->time;
            });
        }

        if (!empty($this->buildings)) {
            ksort($this->buildings);
        }

        $analysis = new \StdClass;
        $analysis->duration = $this->currentTime;
        $analysis->tributes = $this->tributes;
        $analysis->chatMessages = $this->chatMessages;
        $analysis->units = $this->units;
        $analysis->buildings = $this->buildings;
        $analysis->postGameData = $this->postGameData;

        return $analysis;
    }

    private function processGameStart()
    {
        if ($this->version->isMgl) {
            $this->position += 28;
            $ver = ord($this->body[$this->position]);
            $this->position += 4;
        } else {
            $this->position += 20;
        }
    }

    private function processChatMessage()
    {
        $length = $this->readBody('l', 4);
        if ($length <= 0) {
            return;
        }
        $chat = $this->readBodyRaw($length);

        // Chat messages are stored as "@#%dPlayerName: Message", where %d is a
        // digit from 1 to 8 indicating player's index (or colour).
        if ($chat[0] == '@' && $chat[1] == '#' && $chat[2] >= '1' && $chat[2] <= '8') {
            $chat = rtrim($chat); // Remove null terminator
            if (substr($chat, 3, 2) == '--' && substr($chat, -2) == '--') {
                // Skip messages like "--Warning: You are under attack... --"
                return;
            } else if (!empty($this->players[$chat[2]])) {
                $player = $this->players[$chat[2]];
            } else {
                // This player left before the game started.
                $player = null;
            }
            $this->chatMessages[] = ChatMessage::create(
                $this->currentTime,
                $player,
                substr($chat, 3)
            );
        }
    }

    private function processSync()
    {
        $this->currentTime += $this->readBody('l', 4);
        $unknown = $this->readBody('L', 4);
        if ($unknown === 0) {
            $this->position += 28;
        }
        $this->position += 12;
    }
}
