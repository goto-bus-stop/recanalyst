<?php

namespace RecAnalyst\Analyzers;

use RecAnalyst\Model\Unit;
use RecAnalyst\RecordedGame;
use RecAnalyst\Model\Tribute;
use RecAnalyst\Model\ChatMessage;
use RecAnalyst\Model\Actions;
use RecAnalyst\Model\Actions\Action;
use RecAnalyst\ResourcePacks\AgeOfEmpires\Civilization;

/**
 * Analyzer for most things in a recorded game file body.
 */
class BodyAnalyzer extends Analyzer
{
    /**
     * Operation ID of in-game commands.
     *
     * @var int
     */
    const OP_COMMAND = 0x01;
    /**
     * Operation ID of sync packets.
     *
     * @var int
     */
    const OP_SYNC = 0x02;
    /**
     * Operation ID of "meta" operations like the start of the game or chat
     * messages.
     *
     * @var int
     */
    const OP_META = 0x03;
    /**
     * Same as OP_META, but not quite?
     *
     * @var int
     */
    const OP_META2 = 0x04;

    /**
     * Game start identifier.
     *
     * @var int
     */
    const META_GAME_START = 0x01F4;
    /**
     * Chat message identifier.
     *
     * @var int
     */
    const META_CHAT = -1;

    // Command IDs.

    const COMMAND_INTERACT = 0x00;
    const COMMAND_STOP = 0x01;
    const COMMAND_WORK = 0x02;
    const COMMAND_MOVE = 0x03;
    const COMMAND_CREATE = 0x04;
    const COMMAND_ADD_ATTRIBUTE = 0x05;
    const COMMAND_GIVE_ATTRIBUTE = 0x07;
    const COMMAND_AI_ORDER = 0x0A;
    const COMMAND_RESIGN = 0x0B;
    const COMMAND_ADD_WAYPOINT = 0x0C;
    const COMMAND_PAUSE = 0x0D;
    const COMMAND_GROUP_WAYPOINT = 0x10;
    const COMMAND_GROUP_AI_ORDER = 0x11;
    const COMMAND_UNIT_AI_STATE = 0x12;
    const COMMAND_GUARD = 0x13;
    const COMMAND_FOLLOW = 0x14;
    const COMMAND_PATROL = 0x15;
    const COMMAND_SCOUT = 0x16;
    const COMMAND_FORM_FORMATION = 0x17;
    const COMMAND_BREAK_FORMATION = 0x18;
    const COMMAND_WHEEL_FORMATION = 0x19;
    const COMMAND_ABOUT_FACE_FORMATION = 0x1A;
    const COMMAND_SAVE = 0x1B;
    const COMMAND_FORMATION_PARAMETERS = 0x1C;
    const COMMAND_AUTO_FORMATIONS = 0x1D;
    const COMMAND_LOCK_FORMATION = 0x1E;
    const COMMAND_GROUP_MULTI_WAYPOINTS = 0x1F;
    const COMMAND_CHAPTER = 0x20;
    const COMMAND_ATTACK_MOVE = 0x21;
    const COMMAND_ATTACK_MOVE_TARGET = 0x22;
    const COMMAND_MAKE = 0x64;
    const COMMAND_RESEARCH = 0x65;
    const COMMAND_BUILD = 0x66;
    const COMMAND_GAME = 0x67;
    const COMMAND_EXPLORE = 0x68;
    const COMMAND_BUILD_WALL = 0x69;
    const COMMAND_CANCEL_BUILD = 0x6A;
    const COMMAND_ATTACK_GROUND = 0x6B;
    // I think the normal COMMAND_GIVE_ATTRIBUTE is from the Genie Engine, and
    // COMMAND_GIVE_ATTRIBUTE2 is from the game. Not sure that the Genie version
    // is ever used atm.
    const COMMAND_GIVE_ATTRIBUTE2 = 0x6C;
    const COMMAND_TRADE_ATTRIBUTE = 0x6D;
    const COMMAND_REPAIR = 0x6E;
    const COMMAND_UNLOAD = 0x6F;
    const COMMAND_MULTI_QUEUE = 0x70;
    const COMMAND_GATE = 0x72;
    const COMMAND_FLARE = 0x73;
    const COMMAND_SPECIAL = 0x74;
    const COMMAND_UNIT_ORDER = 0x75;
    const COMMAND_DIPLOMACY = 0x76;
    const COMMAND_QUEUE = 0x77;
    const COMMAND_SET_GATHER_POINT = 0x78;
    const COMMAND_SET_RETREAT_POINT = 0x79;
    const COMMAND_SELL_COMMODITY = 0x7A;
    const COMMAND_BUY_COMMODITY = 0x7B;
    const COMMAND_OFFBOARD_TRADE = 0x7C;
    const COMMAND_UNIT_TRANSFORM = 0x7D;
    const COMMAND_DROP_RELIC = 0x7E;
    const COMMAND_TOWN_BELL = 0x7F;
    const COMMAND_GO_BACK_TO_WORK = 0x80;

    /**
     * UserPatch post-game lobby data command ID.
     *
     * @var int
     */
    const COMMAND_POSTGAME = 0xFF;

    /**
     * Feudal age research ID.
     *
     * @var int
     */
    const RESEARCH_FEUDAL = 101;
    /**
     * Castle age research ID.
     *
     * @var int
     */
    const RESEARCH_CASTLE = 102;
    /**
     * Imperial age research ID.
     *
     * @var int
     */
    const RESEARCH_IMPERIAL = 103;

    /**
     * Game version information.
     *
     * @var object
     */
    private $version = null;

    /**
     * Current game time in ms.
     *
     * @var int
     */
    private $currentTime = 0;

    /**
     * Tributes sent during the game.
     *
     * @var \RecAnalyst\Model\Tribute[]
     */
    private $tributes = [];

    /**
     * Chat messages sent during the game.
     *
     * @var \RecAnalyst\Model\ChatMessage[]
     */
    private $chatMessages = [];

    /**
     * Amount of units of each type built during the game.
     *
     * @var array
     */
    private $units = [];

    /**
     * Amount of buildings of each type built during the game.
     *
     * @var array
     */
    private $buildings = [];

    /**
     * Post-game data, such as achievements.
     *
     * @var object|null
     */
    private $postGameData = null;

    /**
     * Unit selection used for the previous command.
     *
     * @var int[]
     */
    private $lastUnits = [];

    /**
     * Run the analysis.
     *
     * @return object
     */
    protected function run()
    {
        $pack = $this->rec->getResourcePack();
        $this->version = $this->get(VersionAnalyzer::class);
        $version = $this->version;

        $players = $this->get(PlayerMetaAnalyzer::class);

        // The player number is used for chat messages.
        $playersByNumber = [];
        // The player index is used for game actions.
        $playersByIndex = [];
        foreach ($players as $player) {
            $playersByNumber[$player->number] = $player;
            $playersByIndex[$player->index] = $player;
        }

        $this->playersByNumber = $playersByNumber;

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
                // There are a lot of sync packets, so we get a significant
                // speedup just from doing this inline (and not in a separate
                // method), and by using `unpack` and manual position increments
                // instead of `readBody`.
                $data = unpack('l2', substr($this->body, $this->position, 8));
                $this->currentTime += $data[1]; // $this->readBody('l', 4);
                $unknown = $data[2]; // $this->readBody('L', 4);
                if ($unknown === 0) {
                    $this->position += 28;
                }
                $this->position += 20;
            } else if ($operationType === self::OP_COMMAND) {
                $length = $this->readBody('l', 4);
                $next = $this->position + $length;
                $command = ord($this->body[$this->position]);
                $this->position++;

                switch ($command) {
                    case self::COMMAND_INTERACT:
                        $playerId = ord($this->body[$this->position++]);
                        $this->position += 2;
                        $targetId = $this->readBody('l', 4);
                        $unitCount = $this->readBody('l', 4);
                        $this->push(new Actions\InteractAction(
                            $this->rec,
                            $this->currentTime,
                            $playerId,
                            $targetId,
                            $this->readBody('f', 4),
                            $this->readBody('f', 4),
                            $this->readUnits($unitCount)
                        ));
                        break;
                    case self::COMMAND_STOP:
                        $count = ord($this->body[$this->position++]);
                        $this->push(new Actions\StopAction(
                            $this->rec,
                            $this->currentTime,
                            $this->readUnits($count)
                        ));
                        break;
                    case self::COMMAND_WORK:
                        $this->position += 3;
                        $targetId = $this->readBody('l', 4);
                        $count = ord($this->body[$this->position++]);
                        $this->position += 3;
                        $x = $this->readBody('f', 4);
                        $y = $this->readBody('f', 4);
                        $this->push(new Actions\WorkAction(
                            $this->rec,
                            $this->currentTime,
                            $targetId,
                            $x,
                            $y,
                            $this->readUnits($count)
                        ));
                        break;
                    case self::COMMAND_MOVE:
                        $playerId = ord($this->body[$this->position++]);
                        $this->position += 2;
                        $this->position += 4;
                        $count = $this->readBody('l', 4);
                        $this->push(new Actions\MoveAction(
                            $this->rec,
                            $this->currentTime,
                            $playerId,
                            $this->readBody('f', 4),
                            $this->readBody('f', 4),
                            $this->readUnits($count)
                        ));
                        break;
                    case self::COMMAND_CREATE:
                        $this->position++;
                        $objectCategory = $this->readBody('v', 2);
                        $playerId = ord($this->body[$this->position++]);
                        $this->position += 3;
                        $x = $this->readBody('f', 4);
                        $y = $this->readBody('f', 4);
                        $z = $this->readBody('f', 4);
                        $this->push(new Actions\CreateAction(
                            $this->rec,
                            $this->currentTime,
                            $playerId,
                            $objectCategory,
                            $x,
                            $y,
                            $z
                        ));
                        break;
                    case self::COMMAND_ADD_ATTRIBUTE:
                        $playerId = ord($this->body[$this->position++]);
                        $resourceType = ord($this->body[$this->position++]);
                        $this->position += 1;
                        $amount = $this->readBody('f', 4);

                        $this->push(new Actions\AddAttributeAction(
                            $this->rec,
                            $this->currentTime,
                            $playerId,
                            $resourceType,
                            $amount
                        ));
                        break;
                    // Old-style tributing.
                    case self::COMMAND_GIVE_ATTRIBUTE:
                        $playerIdFrom = ord($this->body[$this->position++]);
                        $playerIdTo = ord($this->body[$this->position++]);
                        $resourceId = ord($this->body[$this->position++]);
                        $amount = $this->readBody('f', 4);
                        // Market fees only apply to COMMAND_GIVE_ATTRIBUTE2.
                        $marketFee = 0.0;

                        $this->push(new Actions\GiveAttributeAction(
                            $this->rec,
                            $this->currentTime,
                            $playerIdFrom,
                            $playerIdTo,
                            $resourceId,
                            $amount,
                            $marketFee
                        ));
                        break;
                    // player resign
                    case self::COMMAND_RESIGN:
                        $playerIndex = ord($this->body[$this->position++]);
                        $playerNumber = ord($this->body[$this->position++]);
                        $dropped = ord($this->body[$this->position++]);

                        $this->push(new Actions\ResignAction(
                            $this->rec,
                            $this->currentTime,
                            $playerIndex,
                            $playerNumber,
                            $dropped
                        ));

                        $player = $playersByIndex[$playerIndex];
                        if ($player && $player->resignTime === 0) {
                            $player->resignTime = $this->currentTime;
                            $message = sprintf('%s resigned', $player->name);
                            $this->chatMessages[] = new ChatMessage($this->currentTime, null, $message);
                        }
                        break;
                    case self::COMMAND_UNIT_AI_STATE:
                        $numUnits = ord($this->body[$this->position++]);
                        $stance = ord($this->body[$this->position++]);
                        $this->push(new Actions\UnitAiStateAction(
                            $this->rec,
                            $this->currentTime,
                            $stance,
                            $this->readUnits($numUnits)
                        ));
                        break;
                    case self::COMMAND_FOLLOW:
                        $numUnits = ord($this->body[$this->position++]);
                        $this->position += 2;
                        $target = $this->readBody('l', 4);

                        $this->push(new Actions\FollowAction(
                            $this->rec,
                            $this->currentTime,
                            $target,
                            $this->readUnits($numUnits)
                        ));
                        break;
                    case self::COMMAND_PATROL:
                        $numUnits = ord($this->body[$this->position++]);
                        $numWaypoints = ord($this->body[$this->position++]);
                        $this->position++;
                        $waypoints = [];
                        // bytes for 10 waypoints are allocated. however, not
                        // all of them are used, so we skip reading the ones
                        // that are certainly empty.
                        for ($i = 0; $i < $numWaypoints; $i++) {
                            $waypoints[$i][0] = $this->readBody('f', 4);
                        }
                        $this->position += (10 - $numWaypoints) * 4;
                        for ($i = 0; $i < $numWaypoints; $i++) {
                            $waypoints[$i][1] = $this->readBody('f', 4);
                        }
                        $this->position += (10 - $numWaypoints) * 4;

                        $this->push(new Actions\PatrolAction(
                            $this->rec,
                            $this->currentTime,
                            $this->readUnits($numUnits),
                            $waypoints
                        ));
                        break;
                    case self::COMMAND_FORM_FORMATION:
                        $count = ord($this->body[$this->position++]);
                        $playerId = ord($this->body[$this->position++]);
                        $this->position += 1;
                        $formation = $this->readBody('l', 4);

                        $this->push(new Actions\FormFormationAction(
                            $this->rec,
                            $this->currentTime,
                            $playerId,
                            $formation,
                            $this->readUnits($count)
                        ));
                        break;
                    // researches
                    case self::COMMAND_RESEARCH:
                        $this->position += 3;
                        $buildingId = $this->readBody('l', 4);
                        $playerId = $this->readBody('v', 2);
                        $researchId = $this->readBody('v', 2);

                        $this->push(new Actions\ResearchAction(
                            $this->rec,
                            $this->currentTime,
                            $playerId,
                            $buildingId,
                            $researchId
                        ));

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
                        $player->addResearch($researchId, $this->currentTime);
                        break;
                    // training unit
                    case self::COMMAND_QUEUE:
                        $this->position += 3;
                        $buildingId = $this->readBody('l', 4);
                        $unitType = $this->readBody('v', 2);
                        $amount = $this->readBody('v', 2);

                        $this->push(new Actions\QueueAction(
                            $this->rec,
                            $this->currentTime,
                            $buildingId,
                            $unitType,
                            $amount
                        ));

                        if (!isset($this->units[$unitType])) {
                            $this->units[$unitType] = $amount;
                        } else {
                            $this->units[$unitType] += $amount;
                        }
                        break;
                    case self::COMMAND_MULTI_QUEUE:
                        $this->position += 3;
                        $unitType = $this->readBody('v', 2);
                        $numBuildings = ord($this->body[$this->position++]);
                        $amount = ord($this->body[$this->position++]);
                        $buildings = [];

                        for ($i = 0; $i < $numBuildings; $i++) {
                            $buildings[] = $this->readBody('l', 4);
                        }

                        $this->push(new Actions\MultiQueueAction(
                            $this->rec,
                            $this->currentTime,
                            $buildings,
                            $unitType,
                            $amount
                        ));
                        break;
                    case self::COMMAND_GAME:
                        $this->processGameAction();
                        break;
                    case self::COMMAND_BUILD_WALL:
                        $count = ord($this->body[$this->position++]);
                        $playerId = ord($this->body[$this->position++]);
                        $x1 = ord($this->body[$this->position++]);
                        $y1 = ord($this->body[$this->position++]);
                        $x2 = ord($this->body[$this->position++]);
                        $y2 = ord($this->body[$this->position++]);
                        $this->position += 1; // Padding
                        $objectType = $this->readBody('v', 2);
                        $this->position += 2; // Padding
                        $this->position += 4; // Always -1

                        $this->push(new Actions\BuildWallAction(
                            $this->rec,
                            $this->currentTime,
                            $playerId,
                            $objectType,
                            [$x1, $y1],
                            [$x2, $y2],
                            $this->readUnits($count)
                        ));
                        break;
                    case self::COMMAND_CANCEL_BUILD:
                        $this->position += 3;
                        $objectId = $this->readBody('l', 4);
                        $playerId = $this->readBody('l', 4);
                        $this->push(new Actions\CancelBuildAction(
                            $this->rec,
                            $this->currentTime,
                            $playerId,
                            $objectId
                        ));
                        break;
                    case self::COMMAND_ATTACK_GROUND:
                        $count = ord($this->body[$this->position++]);
                        $this->position += 2;
                        $x = $this->readBody('f', 4);
                        $y = $this->readBody('f', 4);
                        $this->push(new Actions\AttackGroundAction(
                            $this->rec,
                            $this->currentTime,
                            $x,
                            $y,
                            $this->readUnits($count)
                        ));
                        break;
                    // AI trains unit
                    case self::COMMAND_MAKE:
                        $this->position += 3;
                        $objectId = $this->readBody('l', 4);
                        $playerId = $this->readBody('v', 2);
                        $unitType = $this->readBody('v', 2);

                        $this->push(new Actions\MakeAction(
                            $this->rec,
                            $this->currentTime,
                            $playerId,
                            $unitType,
                            $objectId
                        ));

                        if (!isset($this->units[$unitType])) {
                            $this->units[$unitType] = 1;
                        } else {
                            $this->units[$unitType] += 1;
                        }
                        break;
                    // building
                    case self::COMMAND_BUILD:
                        $numVillagers = ord($this->body[$this->position++]);
                        $playerId = $this->readBody('v', 2);
                        $x = $this->readBody('f', 4);
                        $y = $this->readBody('f', 4);
                        $buildingType = $this->readBody('l', 4);
                        $this->readBody('l', 4);
                        $this->readBody('l', 4);

                        $buildingType = $pack->normalizeUnit($buildingType);

                        $this->push(new Actions\BuildAction(
                            $this->rec,
                            $this->currentTime,
                            $playerId,
                            $x,
                            $y,
                            $buildingType,
                            $this->readUnits($numVillagers)
                        ));

                        if (!isset($this->buildings[$playerId][$buildingType])) {
                            $this->buildings[$playerId][$buildingType] = 1;
                        } else {
                            $this->buildings[$playerId][$buildingType]++;
                        }
                        break;
                    // tributing
                    case self::COMMAND_GIVE_ATTRIBUTE2:
                        $playerIdFrom = ord($this->body[$this->position++]);
                        $playerIdTo = ord($this->body[$this->position++]);
                        $resourceId = ord($this->body[$this->position++]);
                        $amount = $this->readBody('f', 4);
                        $marketFee = $this->readBody('f', 4);

                        $this->push(new Actions\GiveAttributeAction(
                            $this->rec,
                            $this->currentTime,
                            $playerIdFrom,
                            $playerIdTo,
                            $resourceId,
                            $amount,
                            $marketFee
                        ));

                        $playerFrom = $playersByIndex[$playerIdFrom];
                        $playerTo = $playersByIndex[$playerIdTo];

                        if ($playerFrom && $playerTo) {
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
                    case self::COMMAND_REPAIR:
                        $count = ord($this->body[$this->position++]);
                        $this->position += 2;
                        $targetId = $this->readBody('l', 4);
                        $this->push(new Actions\RepairAction(
                            $this->rec,
                            $this->currentTime,
                            $targetId,
                            $this->readUnits($count)
                        ));
                        break;
                    case self::COMMAND_UNLOAD:
                        $count = ord($this->body[$this->position++]);
                        $this->position += 2;
                        $x = $this->readBody('f', 4);
                        $y = $this->readBody('f', 4);
                        $flag = ord($this->body[$this->position++]);
                        $this->position += 3;
                        $unitType = $this->readBody('l', 4);

                        $this->push(new Actions\UnloadAction(
                            $this->rec,
                            $this->currentTime,
                            $x,
                            $y,
                            $flag,
                            $unitType,
                            $this->readUnits($count)
                        ));
                        break;
                    case self::COMMAND_FLARE:
                        $this->position += 3;
                        // Is this always 0xFF FF FF FF?
                        $ffffffff = $this->readBody('l', 4);
                        $this->position += 1;

                        // Whether each player can receive the flare.
                        $visible = [];
                        for ($i = 0; $i < 8; $i++) {
                            $visible[$i] = ord($this->body[$this->position++]) !== 0;
                        }

                        $this->position += 3;

                        // Flare location.
                        $x = $this->readBody('f', 4);
                        $y = $this->readBody('f', 4);

                        // Multiple players have the same player number (and color)
                        // in coop games.
                        $playerNumber = ord($this->body[$this->position++]);
                        // But everyone has a unique player index.
                        $playerIndex = ord($this->body[$this->position++]);

                        $this->push(new Actions\FlareAction(
                            $this->rec,
                            $this->currentTime,
                            $playerNumber,
                            $playerIndex,
                            $x,
                            $y,
                            $visible
                        ));
                        break;
                    case self::COMMAND_UNIT_ORDER:
                        $count = ord($this->body[$this->position++]);
                        $this->position += 2;
                        $targetId = $this->readBody('l', 4);
                        $action = ord($this->body[$this->position++]);
                        $this->position += 3;
                        $x = $this->readBody('f', 4);
                        $y = $this->readBody('f', 4);
                        $parameter = $this->readBody('l', 4);

                        $this->push(new Actions\UnitOrderAction(
                            $this->rec,
                            $this->currentTime,
                            $x,
                            $y,
                            $targetId,
                            $action,
                            $parameter,
                            $this->readUnits($count)
                        ));
                        break;
                    case self::COMMAND_SET_GATHER_POINT:
                        $count = ord($this->body[$this->position++]);
                        $this->position += 2;
                        $targetId = $this->readBody('l', 4);
                        $targetType = $this->readBody('l', 4);
                        $x = $this->readBody('f', 4);
                        $y = $this->readBody('f', 4);

                        $this->push(new Actions\SetGatherPointAction(
                            $this->rec,
                            $this->currentTime,
                            $targetId,
                            $targetType,
                            $x,
                            $y,
                            $this->readUnits($count)
                        ));
                        break;
                    case self::COMMAND_SELL_COMMODITY:
                        $playerId = ord($this->body[$this->position++]);
                        $resourceType = ord($this->body[$this->position++]);
                        $amount = ord($this->body[$this->position++]);
                        $marketId = $this->readBody('l', 4);

                        $this->push(new Actions\SellCommodityAction(
                            $this->rec,
                            $this->currentTime,
                            $playerId,
                            $resourceType,
                            $amount,
                            $marketId
                        ));
                        break;
                    case self::COMMAND_BUY_COMMODITY:
                        $playerId = ord($this->body[$this->position++]);
                        $resourceType = ord($this->body[$this->position++]);
                        $amount = ord($this->body[$this->position++]);
                        $marketId = $this->readBody('l', 4);

                        $this->push(new Actions\BuyCommodityAction(
                            $this->rec,
                            $this->currentTime,
                            $playerId,
                            $resourceType,
                            $amount,
                            $marketId
                        ));
                        break;
                    case self::COMMAND_TOWN_BELL:
                        $this->position += 3;
                        $unitId = $this->readBody('l', 4);
                        $active = $this->readBody('l', 4);

                        $this->push(new Actions\TownBellAction(
                            $this->rec,
                            $this->currentTime,
                            $unitId,
                            $active
                        ));
                        break;
                    case self::COMMAND_GO_BACK_TO_WORK:
                        $this->position += 3;
                        $unitId = $this->readBody('l', 4);

                        $this->push(new Actions\GoBackToWorkAction(
                            $this->rec,
                            $this->currentTime,
                            $unitId
                        ));
                    // multiplayer postgame data in UP1.4 RC2+
                    case self::COMMAND_POSTGAME:
                        $this->postGameData = $this->read(PostgameDataAnalyzer::class);
                        break;
                    default:
                        printf("Unknown action %02x (%d)\n", $command, $length);
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

        foreach ($this->actions as $action) {
            echo '[' . \RecAnalyst\Utils::formatGameTime($action->time) . '] ' . $action . "\n";
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

    /**
     * Process the game start data. Not much here right now.
     */
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

    /**
     * Read a chat message.
     */
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
            } else if (!empty($this->playersByNumber[$chat[2]])) {
                $player = $this->playersByNumber[$chat[2]];
            } else {
                // Shouldn't happen, but we'll let the ChatMessage factory
                // create a fake player for this message.
                // TODO that auto-create behaviour is probably not desirable…
                $player = null;
            }
            $this->chatMessages[] = ChatMessage::create(
                $this->currentTime,
                $player,
                substr($chat, 3)
            );
        }
    }

    private function processGameAction()
    {
        $action = ord($this->body[$this->position++]);
        $playerId = ord($this->body[$this->position++]);
        switch ($action) {
            case Actions\GameAction::CHEAT:
                $this->position++;
                $cheatId = ord($this->body[$this->position++]);
                $this->push(new Actions\Game\CheatAction(
                    $this->rec,
                    $this->currentTime,
                    $action,
                    $playerId,
                    $cheatId
                ));
                break;
            default:
                $this->push(new Actions\GameAction(
                    $this->rec,
                    $this->currentTime,
                    $action,
                    $playerId
                ));
                break;
        }
    }

    private function push(Action $action)
    {
        $this->actions[] = $action;
    }

    private function readUnits($num)
    {
        if ($num === 0xFF) {
            return $this->lastUnits;
        }
        $units = [];
        for ($i = 0; $i < $num; $i++) {
            $units[] = $this->readBody('l', 4);
        }
        $this->lastUnits = $units;
        return $units;
    }
}
