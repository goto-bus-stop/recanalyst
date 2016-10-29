<?php

namespace RecAnalyst\Analyzers;

use RecAnalyst\Utils;
use RecAnalyst\Model\Unit;
use RecAnalyst\Model\Player;
use RecAnalyst\Model\GameInfo;

/**
 * Analyze extended player information blocks. Should only be composed with the
 * HeaderAnalyzer for now.
 */
class PlayerInfoBlockAnalyzer extends Analyzer
{
    /**
     * Parent analysis.
     *
     * @var object
     */
    private $analysis;

    /**
     * Game version information.
     *
     * @var object
     */
    private $version;

    /**
     * Units owned by GAIA at the start of the game.
     *
     * @var \RecAnalyst\Model\Unit[]
     */
    private $gaiaObjects = [];

    /**
     * Units owned by players at the start of the game.
     *
     * @var \RecAnalyst\Model\Unit[]
     */
    private $playerObjects = [];

    /**
     * @param object  $analysis  Current state of the HeaderAnalyzer.
     */
    public function __construct($analysis)
    {
        $this->analysis = $analysis;
    }

    /**
     * Run the analysis.
     *
     * @return object
     */
    protected function run()
    {
        $this->version = $this->get(VersionAnalyzer::class);
        try {
            return $this->analyzeExtended();
        } catch (Exception $e) {
            return $this->analyzeSimple($e);
        }
    }

    /**
     * Analyze an extended player info block, including unit data.
     *
     * @return object
     */
    private function analyzeExtended()
    {
        $existObjectSeparator = pack('c*', 0x0B, 0x00, 0x08, 0x00, 0x00, 0x00, 0x02, 0x00, 0x00);

        $pack = $this->rec->getResourcePack();

        list ($mapSizeX, $mapSizeY) = $this->analysis->mapSize;

        $version = $this->version;
        $players = $this->analysis->players;
        $playersByIndex = [];
        foreach ($players as $p) {
            $playersByIndex[$p->index] = $p;
        }

        // Add GAIA
        $numPlayers = $this->analysis->numPlayers + 1;
        $gaia = new Player($this->rec);
        $gaia->name = 'GAIA';

        for ($i = -1; $i < count($players); $i++) { // first is GAIA
            // skip GAIA playername
            $player = $i >= 0 ? $players[$i] : $gaia;
            // skip cooping player, they have no data in Player_info
            $coopPlayer = $i >= 0 ? $playersByIndex[$player->index] : null;

            if ($coopPlayer && ($coopPlayer !== $player)
                // TODO is this necessary? Seems like order of player infos
                // is not consistent, so we can't assume the other player
                // has been read yet.
                // && $coopPlayer->civId
            ) {
                $player->civId = $coopPlayer->civId;
                $player->colorId = $coopPlayer->colorId;
                $player->team = $coopPlayer->team;
                $player->isCooping = true;
                continue;
            }
            if ($version->isTrial) {
                $this->position += 4;
            }
            $this->position += $numPlayers + 43;

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
            // Post-Imperial Age = Imperial Age here
            $startingAge = $this->readHeader('f', 4);
            $this->position += 16;
            $population = $this->readHeader('f', 4);
            $this->position += 100;
            $civilianPop = $this->readHeader('f', 4);
            $this->position += 8;
            $militaryPop = $this->readHeader('f', 4);
            if ($version->isAoe2Record) {
                $this->position += 673;
            } else if ($version->isMgx) {
                $this->position += 629;
            } else {
                $this->position += 593;
            }
            $initCameraX = $this->readHeader('f', 4);
            $initCameraY = $this->readHeader('f', 4);
            if ($version->isMgx) {
                $this->position += 9;
            } else {
                $this->position += 5;
            }
            $civilization = ord($this->header[$this->position++]);
            if (!$civilization) {
                $civilization = 1;
            }
            $this->position += 3;
            $playerColor = ord($this->header[$this->position++]);

            $player->civId = $civilization;
            $player->colorId = $playerColor;
            $player->initialState->position = [round($initCameraX), round($initCameraY)];
            $player->initialState->food = round($food);
            $player->initialState->wood = round($wood);
            $player->initialState->stone = round($stone);
            $player->initialState->gold = round($gold);
            $player->initialState->startingAge = round($startingAge);
            $player->initialState->houseCapacity = round($headroom) + round($population);
            $player->initialState->population = round($population);
            $player->initialState->civilianPop = round($civilianPop);
            $player->initialState->militaryPop = round($militaryPop);
            $player->initialState->extraPop = $player->initialState->population -
                ($player->initialState->civilianPop + $player->initialState->militaryPop);

            if ($version->isTrial) {
                $this->position += 4;
            }
            $this->position += $numPlayers + 70;
            $this->position += $version->isMgx ? 792 : 756;
            $this->position += $version->isMgx ? 41249 : 34277;
            $this->position += $mapSizeX * $mapSizeY;

            // getting exist_object_pos
            $existObjectPos = strpos($this->header, $existObjectSeparator, $this->position);
            if ($existObjectPos === false) {
                throw new \Exception('Could not find existObjectSeparator');
            } else {
                $this->position = $existObjectPos + strlen($existObjectSeparator);
            }

            $objects = $this->read(PlayerObjectsListAnalyzer::class, [
                'players' => array_merge([
                    0 => $gaia,
                ], $playersByIndex),
            ]);

            foreach ($objects->gaiaObjects as &$object) {
                $this->gaiaObjects[] = $object;
            }
            foreach ($objects->playerObjects as &$object) {
                $this->playerObjects[] = $object;
            }
        }

        return (object) [
            'gaia' => $players[0],
            'players' => array_slice($players, 1),
            'gaiaObjects' => $this->gaiaObjects,
            'playerObjects' => $this->playerObjects,
        ];
    }

    /**
     * Analyze a simple player info block, in case the extended analysis fails.
     *
     * (Does nothing at the moment.)
     */
    private function analyzeSimple($e = null)
    {
        throw new \Exception('Unimplemented', 0, $e);
    }
}
