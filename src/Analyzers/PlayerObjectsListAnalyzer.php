<?php

namespace RecAnalyst\Analyzers;

use RecAnalyst\Model\Unit;

/**
 * Reads a player objects list.
 *
 * TODO The different object types stack, so the data for a flag can be read by
 * first calling the readEyeCandy() method, and then reading some extra data.
 * Currently this class duplicates some reading logic in multiple methods, but
 * they could reuse each other instead.
 */
class PlayerObjectsListAnalyzer extends Analyzer
{
    /**
     * Unit type ID.
     *
     * @var int
     */
    const UT_EYECANDY = 10;

    /**
     * Unit type ID.
     *
     * @var int
     */
    const UT_FLAG = 20;

    /**
     * Unit type ID.
     *
     * @var int
     */
    const UT_DEAD_FISH = 30;

    /**
     * Unit type ID.
     *
     * @var int
     */
    const UT_BIRD = 40;

    /**
     * Unit type ID.
     *
     * @var int
     */
    const UT_UNKNOWN = 50;

    /**
     * Unit type ID.
     *
     * @var int
     */
    const UT_PROJECTILE = 60;

    /**
     * Unit type ID.
     *
     * @var int
     */
    const UT_CREATABLE = 70;

    /**
     * Unit type ID.
     *
     * @var int
     */
    const UT_BUILDING = 80;

    /**
     * Game version.
     *
     * @var \RecAnalyst\Model\Version
     */
    private $version = null;

    /**
     * Resource Pack.
     *
     * @var \RecAnalyst\ResourcePacks\ResourcePack
     */
    private $pack = null;

    /**
     * Current unit object type. Always one of the UT_ constants above.
     *
     * @var int
     */
    private $objectType = -1;

    /**
     * Player ID of the owner of the current object.
     *
     * @var int
     */
    private $ownerId = -1;

    /**
     * Player model of the owner of the current object.
     *
     * @var \RecAnalyst\Model\Player
     */
    private $owner = null;

    /**
     * The current object's unit ID.
     *
     * @var int
     */
    private $unitId = -1;

    /**
     * GAIA objects that have been found. This currently only tracks objects
     * that will be drawn by the MapImage processor.
     *
     * @var \RecAnalyst\Model\Unit[]
     */
    private $gaiaObjects = [];

    /**
     * Player's units.
     *
     * @var \RecAnalyst\Model\Unit[]
     */
    private $playerObjects = [];

    /**
     * Players in this game, by index. Should be passed in as an argument to
     * this analyzer.
     *
     * @var \RecAnalyst\Model\Player[]
     */
    private $players;

    /**
     * Magic byte string signifying the end of the data for a creatable or
     * building object data.
     *
     * @var string
     */
    private $objectEndSeparator;

    /**
     * Magic byte string signifying the end of the data for a creatable or
     * building object data in Age of Kings.
     *
     * @var string
     */
    private $aokObjectEndSeparator;

    /**
     * Magic byte string signifying the end of the list of player objects.
     *
     * @var string
     */
    private $playerInfoEndSeparator;

    /**
     * Magic byte string for... something?
     *
     * @var string
     */
    private $objectMidSeparatorGaia;

    public function __construct(array $options)
    {
        $this->players = $options['players'];

        $this->objectEndSeparator =
            pack('c*', 0xFF, 0xFF, 0xFF, 0xFF, 0x00, 0x00, 0x80, 0xBF, 0x00, 0x00, 0x80, 0xBF)
            . pack('c*', 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF)
            . pack('c*', 0x00, 0x00, 0x00, 0x00, 0x00, 0x00);
        $this->aokObjectEndSeparator =
            pack('c*', 0xFF, 0xFF, 0xFF, 0xFF, 0x00, 0x00, 0x80, 0xBF, 0x00)
            . pack('c*', 0x00, 0x80, 0xBF, 0x00, 0x00, 0x00, 0x00, 0x00);
        $this->playerInfoEndSeparator =
            pack('c*', 0x00, 0x0B, 0x00, 0x02, 0x00, 0x00, 0x00, 0x02, 0x00, 0x00, 0x00, 0x0B);
        $this->objectsMidSeparatorGaia =
            pack('c*', 0x00, 0x0B, 0x00, 0x40, 0x00, 0x00, 0x00, 0x20, 0x00, 0x00);
    }

    protected function run()
    {
        $this->version = $this->get(VersionAnalyzer::class);
        $this->pack = $this->rec->getResourcePack();

        $done = false;
        while (!$done) {
            $this->objectType = ord($this->header[$this->position]);

            $this->owner = null;
            $this->ownerId = null;
            if ($this->objectType !== 0) {
                $this->ownerId = ord($this->header[$this->position + 1]);
                $this->owner = $this->players[$this->ownerId];
            }

            $this->position += 2;
            $this->unitId = $this->readHeader('v', 2);

            switch ($this->objectType) {
                case self::UT_EYECANDY:
                    $this->readEyeCandy();
                    break;
                // Flags, Map Revealers, ???, only tends to appear in
                // scenario games.
                case self::UT_FLAG:
                    $this->readFlag();
                    break;
                case self::UT_DEAD_FISH:
                    $this->readDeadOrFish();
                    break;
                // Should there be an objectType = 40 and objectType = 50 here?
                case self::UT_PROJECTILE:
                    $this->readProjectile();
                    break;
                case self::UT_CREATABLE:
                    $this->readCreatableUnit();
                    break;
                case self::UT_BUILDING:
                    $this->readBuilding();
                    break;
                case 00:
                    $this->position -= 4;
                    $buff = $this->readHeaderRaw(strlen($this->playerInfoEndSeparator));

                    if ($buff === $this->playerInfoEndSeparator) {
                        $done = true;
                        break;
                    }
                    $this->position -= strlen($this->playerInfoEndSeparator);

                    if ($buff[0] === $this->objectsMidSeparatorGaia[0]
                        && $buff[1] === $this->objectsMidSeparatorGaia[1]
                    ) {
                        $this->position += strlen($this->objectsMidSeparatorGaia);
                    } else {
                        throw new \Exception('Could not find GAIA object separator');
                    }
                    break;
                default:
                    throw new \Exception(sprintf('Unknown object type %d', $this->objectType));
            }
        }

        $analysis = new \StdClass;
        // TODO these probably don't need to be separate.
        $analysis->gaiaObjects = $this->gaiaObjects;
        $analysis->playerObjects = $this->playerObjects;
        return $analysis;
    }

    private function readEyeCandy()
    {
        // Track GAIA objects for the map image generator.
        if ($this->pack->isGaiaObject($this->unitId)) {
            $restore = $this->position;
            $this->position += 19;
            $posX = $this->readHeader('f', 4);
            $posY = $this->readHeader('f', 4);
            $this->position = $restore;
            $go = new Unit($this->rec, $this->unitId, [round($posX), round($posY)]);
            $this->gaiaObjects[] = $go;
        }
        $this->position += 63 - 4;

        // TODO what's this?
        if ($this->version->isHDEdition) {
            $this->position += 3;
        }
        if ($this->version->isMgl) {
            $this->position += 1;
        }
    }

    private function readFlag()
    {
        if ($this->version->isHDEdition) {
            $this->position += 3;
        }
        if ($this->version->isMgx) {
            $this->position += 59;
            $isExtended = ord($this->header[$this->position]);
            $this->position += 1; // $isExtended
            $this->position += 4;
            if ($isExtended == 2) {
                $this->position += 34;
            }
        } else {
            $this->position += 103 - 4;
        }
    }

    private function readDeadOrFish()
    {
        // TODO what's this?
        if ($this->version->isHDEdition) {
            $this->position += 3;
        }
        if (!$this->version->isMgx) {
            $this->position += 1;
        }

        $isExtended = ord($this->header[$this->position + 59]);
        if ($isExtended === 2) {
            $this->position += 17;
        }

        $this->position += 204 - 4;

        if ($this->version->isHDPatch4) {
            $this->position += 1;
        }
    }

    private function readBird()
    {
        $b = ord($this->header[$this->position + 204]);
        $this->position += 233 - 4;

        if ($b) {
            $this->position += 67;
        }
    }

    private function readCreatableUnit()
    {
        if ($this->pack->isGaiaUnit($this->unitId)) {
            $this->position += 19;
            $posX = $this->readHeader('f', 4);
            $posY = $this->readHeader('f', 4);
            $go = new Unit($this->rec, $this->unitId, [round($posX), round($posY)]);
            $this->gaiaObjects[] = $go;
        } else if ($this->owner) {
            // These units belong to someone!
            $this->position += 19;
            $posX = $this->readHeader('f', 4);
            $posY = $this->readHeader('f', 4);
            $uo = new Unit($this->rec, $this->unitId, [round($posX), round($posY)]);
            $uo->owner = $this->owner;
            $this->playerObjects[] = $uo;
        }
        if ($this->version->isMgx) {
            $separatorPos = strpos($this->header, $this->objectEndSeparator, $this->position);
            $this->position = $separatorPos + strlen($this->objectEndSeparator);
        } else {
            $separatorPos = strpos($this->header, $this->aokObjectEndSeparator, $this->position);
            $this->position = $separatorPos + strlen($this->aokObjectEndSeparator);
        }
        if ($separatorPos == -1) {
            throw new \Exception('Could not find object end separator');
        }
    }

    private function readBuilding()
    {
        if ($this->owner) {
            $this->position += 19;
            $posX = $this->readHeader('f', 4);
            $posY = $this->readHeader('f', 4);
            $uo = new Unit($this->rec, $this->unitId, [round($posX), round($posY)]);
            $uo->owner = $this->owner;
            $this->playerObjects[] = $uo;
        }

        if ($this->version->isMgx) {
            $separatorPos = strpos($this->header, $this->objectEndSeparator, $this->position);
            $this->position = $separatorPos + strlen($this->objectEndSeparator);
        } else {
            $separatorPos = strpos($this->header, $this->aokObjectEndSeparator, $this->position);
            $this->position = $separatorPos + strlen($this->aokObjectEndSeparator);
        }
        if ($separatorPos == -1) {
            throw new \Exception('Could not find object end separator');
        }

        $this->position += 126;
        if ($this->version->isMgx) {
            $this->position += 1;
        }

        if ($this->version->isHDPatch4) {
            $this->position -= 4;
        }
    }
}
