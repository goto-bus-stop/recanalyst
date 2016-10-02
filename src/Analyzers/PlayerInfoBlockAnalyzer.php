<?php

namespace RecAnalyst\Analyzers;

use RecAnalyst\Player;
use RecAnalyst\Unit;
use RecAnalyst\GameInfo;

class PlayerInfoBlockAnalyzer extends Analyzer
{
    private $analysis;
    private $version;

    public function __construct($analysis)
    {
        $this->analysis = $analysis;
    }

    protected function run()
    {
        $this->version = $this->get(VersionAnalyzer::class)->analysis;
        try {
            return $this->analyzeExtended();
        } catch (Exception $e) {
            return $this->analyzeSimple($e);
        }
    }

    private function analyzeExtended()
    {
        $existObjectSeparator = pack('c*', 0x0B, 0x00, 0x08, 0x00, 0x00, 0x00, 0x02, 0x00, 0x00);
        $objectEndSeparator =
            pack('c*', 0xFF, 0xFF, 0xFF, 0xFF, 0x00, 0x00, 0x80, 0xBF, 0x00, 0x00, 0x80, 0xBF)
            . pack('c*', 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF)
            . pack('c*', 0x00, 0x00, 0x00, 0x00, 0x00, 0x00);
        $aokObjectEndSeparator =
            pack('c*', 0xFF, 0xFF, 0xFF, 0xFF, 0x00, 0x00, 0x80, 0xBF, 0x00)
            . pack('c*', 0x00, 0x80, 0xBF, 0x00, 0x00, 0x00, 0x00, 0x00);
        $playerInfoEndSeparator = pack('c*', 0x00, 0x0B, 0x00, 0x02, 0x00, 0x00, 0x00, 0x02, 0x00, 0x00, 0x00, 0x0B);
        $objectsMidSeparatorGaia = pack('c*', 0x00, 0x0B, 0x00, 0x40, 0x00, 0x00, 0x00, 0x20, 0x00, 0x00);

        list ($mapSizeX, $mapSizeY) = $this->analysis->mapSize;

        $version = $this->version;
        $players = $this->analysis->players;
        $playersByIndex = [];
        foreach ($players as $p) {
            $playersByIndex[$p->index] = $p;
        }
        for ($i = 0; $i < count($players); $i++) { // first is GAIA
            // skip GAIA playername
            $player = $players[$i];
            // skip cooping player, they have no data in Player_info
            $coopPlayer = $playersByIndex[$player->index];

            if ($coopPlayer && ($coopPlayer !== $player) && $coopPlayer->civId) {
                $player->civId = $coopPlayer->civId;
                $player->colorId = $coopPlayer->colorId;
                $player->team = $coopPlayer->team;
                $player->isCooping = true;
                continue;
            }
            if ($version->isTrial) {
                $this->position += 4;
            }
            $this->position += $this->analysis->numPlayers + 43;

            // skip playername
            $playerNameLen = $this->readHeader('v', 2);
            $this->position += $playerNameLen + 6;

            // Civ header
            $food = $this->readHeader('f', 4);
            $wood = $this->readHeader('f', 4);
            $stone = $this->readHeader('f', 4);
            $gold = $this->readHeader('f', 4);
            // headroom = (house capacity - population)
            $headroom = $this->readHeader('f', 4);
            $this->position += 4;
            // Starting Age. Note: PostImperial Age = Imperial Age here
            $data6 = $this->readHeader('f', 4);
            $this->position += 16;
            $population = $this->readHeader('f', 4);
            $this->position += 100;
            $civilianPop = $this->readHeader('f', 4);
            $this->position += 8;
            $militaryPop = $this->readHeader('f', 4);
            $this->position += $version->isMgx ? 629 : 593;
            $initCameraX = $this->readHeader('f', 4);
            $initCameraY = $this->readHeader('f', 4);
            $this->position += $version->isMgx ? 9 : 5;
            $civilization = ord($this->header[$this->position]);
            if (!$civilization) {
                $civilization = 1;
            }
            $this->position += 1 + 3;
            $playerColor = ord($this->header[$this->position]);
            $this->position += 1;

            $player->civId = $civilization;
            $player->colorId = $playerColor;
            $player->initialState->position = [round($initCameraX), round($initCameraY)];
            $player->initialState->food = round($food);
            $player->initialState->wood = round($wood);
            $player->initialState->stone = round($stone);
            $player->initialState->gold = round($gold);
            $player->initialState->startingAge = round($data6);
            $player->initialState->houseCapacity = round($headroom) + round($population);
            $player->initialState->population = round($population);
            $player->initialState->civilianPop = round($civilianPop);
            $player->initialState->militaryPop = round($militaryPop);
            $player->initialState->extraPop = $player->initialState->population -
                ($player->initialState->civilianPop + $player->initialState->militaryPop);

            // GAIA
            if ($version->isTrial) {
                $this->position += 4;
            }
            $this->position += $this->analysis->numPlayers + 70;
            $this->position += $version->isMgx ? 792 : 756;
            $this->position += $version->isMgx ? 41249 : 34277;
            $this->position += $mapSizeX * $mapSizeY;

            // getting exist_object_pos
            $existObjectPos = strpos($this->header, $existObjectSeparator, $this->position);
            if ($existObjectPos === false) {
                return false;
            } else {
                $this->position = $existObjectPos + strlen($existObjectSeparator);
            }

            $done = false;
            while (!$done) {
                $objectType = ord($this->header[$this->position]);
                $owner = ord($this->header[$this->position + 1]);
                $this->position += 2;
                $unitId = $this->readHeader('v', 2);

                switch ($objectType) {
                    case 10:
                        switch ($unitId) {
                            case Unit::GOLDMINE:
                            case Unit::STONEMINE:
                            case Unit::CLIFF1:
                            case Unit::CLIFF2:
                            case Unit::CLIFF3:
                            case Unit::CLIFF4:
                            case Unit::CLIFF5:
                            case Unit::CLIFF6:
                            case Unit::CLIFF7:
                            case Unit::CLIFF8:
                            case Unit::CLIFF9:
                            case Unit::CLIFF10:
                            case Unit::FORAGEBUSH:
                                $this->position += 19;
                                $posX = $this->readHeader('f', 4);
                                $posY = $this->readHeader('f', 4);
                                $go = new Unit();
                                $go->id = $unitId;
                                $go->position = [round($posX), round($posY)];
                                $this->gaiaObjects[] = $go;
                                $this->position -= 27;
                                break;
                        }
                        $this->position += 63 - 4;
                        if ($version->isMgl) {
                            $this->position += 1;
                        }
                        break;
                    case 20:
                        if ($version->isMgx) {
                            $this->position += 59;
                            $b = ord($this->header[$this->position]);
                            $this->position -= 59;
                            $this->position += 68 - 4;
                            if ($b == 2) {
                                $this->position += 34;
                            }
                        } else {
                            $this->position += 103 - 4;
                        }
                        break;
                    case 30:
                        if ($version->isMgx) {
                            $b = ord($this->header[$this->position + 59]);
                            $this->position += 204 - 4;
                            if ($b == 2) {
                                $this->position += 17;
                            }
                        } else {
                            $b = ord($this->header[$this->position + 60]);
                            $this->position += 205 - 4;
                            if ($b == 2) {
                                $this->position += 17;
                            }
                        }
                        break;
                    case 60:
                        $b = ord($this->header[$this->position + 204]);
                        $this->position += 233 - 4;
                        if ($b) {
                            $this->position += 67;
                        }
                        break;
                    case 70:
                        switch ($unitId) {
                            case Unit::RELIC:
                            case Unit::DEER:
                            case Unit::BOAR:
                            case Unit::JAVELINA:
                            case Unit::TURKEY:
                            case Unit::SHEEP:
                                $this->position += 19;
                                $posX = $this->readHeader('f', 4);
                                $posY = $this->readHeader('f', 4);
                                $go = new Unit();
                                $go->id = $unitId;
                                $go->position = [round($posX), round($posY)];
                                $this->gaiaObjects[] = $go;
                                break;
                        }
                        if ($owner && $unitId != Unit::TURKEY && $unitId != Unit::SHEEP) {
                            // exclude convertable objects
                            $this->position += 19;
                            $posX = $this->readHeader('f', 4);
                            $posY = $this->readHeader('f', 4);
                            $uo = new Unit();
                            $uo->id = $unitId;
                            $uo->owner = $owner;
                            $uo->position = [round($posX), round($posY)];
                            $this->playerObjects[] = $uo;
                        }
                        if ($version->isMgx) {
                            $separatorPos = strpos($this->header, $objectEndSeparator, $this->position);
                            $this->position = $separatorPos + strlen($objectEndSeparator);
                        } else {
                            $separatorPos = strpos($this->header, $aokObjectEndSeparator, $this->position);
                            $this->position = $separatorPos + strlen($aokObjectEndSeparator);
                        }
                        if ($separatorPos == -1) {
                            return false;
                        }
                        break;
                    case 80:
                        if ($owner) {
                            $this->position += 19;
                            $posX = $this->readHeader('f', 4);
                            $posY = $this->readHeader('f', 4);
                            $uo = new Unit();
                            $uo->id = $unitId;
                            $uo->owner = $owner;
                            $uo->position = [round($posX), round($posY)];
                            $this->playerObjects[] = $uo;
                        }
                        if ($version->isMgx) {
                            $separatorPos = strpos($this->header, $objectEndSeparator, $this->position);
                            $this->position = $separatorPos + strlen($objectEndSeparator);
                        } else {
                            $separatorPos = strpos($this->header, $aokObjectEndSeparator, $this->position);
                            $this->position = $separatorPos + strlen($aokObjectEndSeparator);
                        }
                        if ($separatorPos == -1) {
                            return false;
                        }
                        $this->position += 126;
                        if ($version->isMgx) {
                            $this->position += 1;
                        }
                        break;
                    case 00:
                        $this->position -= 4;
                        $buff = $this->readHeaderRaw(strlen($playerInfoEndSeparator));
                        if ($buff === $playerInfoEndSeparator) {
                            $done = true;
                            break;
                        }
                        $this->position -= strlen($playerInfoEndSeparator);

                        if ($buff[0] === $objectsMidSeparatorGaia[0] && $buff[1] === $objectsMidSeparatorGaia[1]) {
                            $this->position += strlen($objectsMidSeparatorGaia);
                        } else {
                            return false;
                        }
                        break;
                    default:
                        return false;
                }
            }
        }
        return true;
    }

    private function analyzeSimple($e = null)
    {
        throw new \Exception('Unimplemented');
    }
}
