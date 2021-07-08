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
    const UT_GENIE_STATIC = 10;

    /**
     * Unit type ID.
     *
     * @var int
     */
    const UT_GENIE_ANIMATED = 20;

    /**
     * Unit type ID.
     *
     * @var int
     */
    const UT_GENIE_DOPPLE = 25;

    /**
     * Unit type ID.
     *
     * @var int
     */
    const UT_GENIE_MOVING = 30;

    /**
     * Unit type ID.
     *
     * @var int
     */
    const UT_GENIE_ACTION = 40;

    /**
     * Unit type ID.
     *
     * @var int
     */
    const UT_GENIE_COMBAT = 50;

    /**
     * Unit type ID.
     *
     * @var int
     */
    const UT_GENIE_MISSILE = 60;

    /**
     * Unit type ID.
     *
     * @var int
     */
    const UT_TRIBE_TREE = 15;

    /**
     * Unit type ID.
     *
     * @var int
     */
    const UT_TRIBE_COMBAT = 70;

    /**
     * Unit type ID.
     *
     * @var int
     */
    const UT_TRIBE_BUILDING = 80;

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

        // a null byte is written to end iteration of linked list for unit-type
        while ($this->objectType = $this->readHeader('c', 1)) {

            switch ($this->objectType) {

                case self::UT_GENIE_STATIC:
                    $this->readGenieStaticObj();
                    break;

                case self::UT_GENIE_ANIMATED:
                    $this->readGenieAnimated();
                    break;

                case self::UT_GENIE_DOPPLE:
                    $this->readGenieDopple();
                    break;

                case self::UT_GENIE_MOVING:
                    $this->readGenieMoving();
                    break;

				case self::UT_GENIE_ACTION:
					$this->readGenieActionObj();
					break;

                case self::UT_GENIE_COMBAT:
                    $this->readGenieCombatObj();
                    break;

                case self::UT_GENIE_MISSILE:
                    $this->readGenieMissileObj();
                    break;

                case self::UT_TRIBE_TREE:
                    $this->readTribeTree();
                    break;

                case self::UT_TRIBE_COMBAT:
                    $this->readTribeCombat();
                    break;

                case self::UT_TRIBE_BUILDING:
                    $this->readTribeBuilding();
                    break;

				case 0:// todo: get rid of this stuff as it's not needed
                    $this->position -= 4;

                    $buff = $this->readHeaderRaw(strlen($this->playerInfoEndSeparator));
                    if ($buff === $this->playerInfoEndSeparator) {
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

	/**
	 *
	 */
    private function readObj()
	{
		$this->ownerId = $this->readHeader('c', 1);// PlayerId (unsigned)
		$this->owner = $this->players[$this->ownerId];
		$this->unitId = $this->readHeader('v', 2);// MasterId (signed)
	}

	/**
	 * AOC sub_4CE690
	 */
    private function readGenieStaticObj()
    {
    	$this->readObj();

		OLD_OFFSET_0:
    	$this->position += 2;// SpriteId
    	$this->position += 4;// InsideObjId
    	$this->position += 4;// HitPoints
    	$this->position += 1;// State
    	$this->position += 1;// SleepMode
		if ($this->version->subVersion >= 7.09){
    		$this->position += 1;// DoppleMode
		}
		$this->position += 1;// GoingToSleepMode
		$this->position += 4;// Identity
		$this->position += 1;// Facet
		OLD_OFFSET_19:
		$posX = $this->readHeader('f', 4);// PositionX
		$posY = $this->readHeader('f', 4);// PositionY
		if ($this->pack->isGaiaObject($this->unitId)) {
			// Track GAIA objects for the map image generator.
			$go = new Unit($this->rec, $this->unitId, [round($posX), round($posY)]);
			$this->gaiaObjects[] = $go;
		}elseif ($this->owner) {
			// These units belong to someone!
			$uo = new Unit($this->rec, $this->unitId, [round($posX), round($posY)]);
			$uo->owner = $this->owner;
			$this->playerObjects[] = $uo;
		}
		$this->position += 4;// PositionZ
		$this->position += 2;// ScreenOffset0X
		$this->position += 2;// ScreenOffset0Y
		$this->position += 2;// ScreenOffset1X
		$this->position += 2;// ScreenOffset1Y
		if ($this->version->subVersion < 11.58){
			$this->position += 1;// SelectedGroup
		}
		OLD_OFFSET_40:
		$this->position += 2;// HeldAttributeId
		$this->position += 4;// HeldAttributeAmount
		if ($this->version->isHDEdition) {// HD made no version control for this cast! boohh...
			$this->position += 4;// WorkerNum (32-bit)
		}else{
			$this->position += 1;// WorkerNum (8-bit)
		}
		$this->position += 1;// DamagePercent
		if ($this->version->subVersion >= 9.85){
			$this->position += 1;// ???
		}
		$this->position += 1;// UnderAttack
		OLD_OFFSET_50:
		if ($this->version->subVersion < 10.85){
			$this->position += 4;// GroupCommander
			$this->position += 4;// GroupRange
		}
		if ($this->version->subVersion == 6.99){
			$this->position += 4;// Unused1A (float)
			$this->position += 4;// Unused1B (float)
			$this->position += 4;// Unused1C (float)
			$this->position += 4;// Unused2
			$this->position += 4;// Unused3A
			$this->position += 4;// Unused3B
		}
		if ($this->version->subVersion < 10.85){
			$groupUnitsCount = $this->readHeader('l', 4);// CommandingGroupUnitsCount
			$this->position += $groupUnitsCount * 4;// CommandingGroupMembers
		}
		$groupUnitsCount = $this->readHeader('l', 4);// GroupUnitsCount
		$this->position += $groupUnitsCount * 4;// GroupMembers
		if ($this->version->subVersion >= 9.11 &&
			$this->version->subVersion <= 9.62){
			$this->position += 4;// ???
		}
		if ($this->version->subVersion >= 9.66){
			$this->position += 4;// ??? (float)
		}
		if ($this->version->subVersion >= 11.33){
			$this->position += 1;// ???
		}
		OLD_OFFSET_59:
		if ($this->version->subVersion >= 12.49) {// HD Edition
			$this->position += 4;// ???
		}
		if ($this->version->subVersion >= 11.11){
			$actSprUsed = $this->readHeader('c', 1);// ActiveSpritesUsed
		}else{
			$actSprUsed = 1;
		}
		if ($actSprUsed){
			$this->readActiveSprites();
		}
    }

	/**
	 *
	 */
	const ACTIVE_SPRITE_TYPE_NORMAL = 1;

	/**
	 *
	 */
	const ACTIVE_SPRITE_TYPE_ANIMATED = 2;

	/**
	 * AOC sub_5ECAC0
	 */
	private function readActiveSprites()
	{
		while ($activeSpriteType = $this->readHeader('c', 1)){

			switch ($activeSpriteType) {// there are only 2 types of active sprites

				case $this::ACTIVE_SPRITE_TYPE_NORMAL:
					$this->readActiveSprite();
					break;

				case $this::ACTIVE_SPRITE_TYPE_ANIMATED:
					$this->readActiveSpriteAnimated();
					break;
			}
			if ($this->version->subVersion >= 9.23) {
				$this->position += 1;// ActiveSpriteNodeOrder
				$this->position += 1;// ActiveSpriteNodeCount
				$this->position += 1;// ActiveSpriteNodeUnknown
			}
		}
	}

	/**
	 * AOC sub_5EC030
	 */
	private function readActiveSprite()
	{
		$this->position += 2;// SpriteId
		$this->position += 4;// OffsetX
		$this->position += 4;// OffsetY
		$this->position += 2;// Facet
		$this->position += 1;// Mode
	}

	/**
	 * AOC sub_5EC460
	 */
	private function readActiveSpriteAnimated()
	{
		$this->readActiveSprite();
		$this->position += 4;// Tempo (float)
		$this->position += 4;// Unknown1 (float)
		$this->position += 2;// Unknown2
		$this->position += 1;// FrameEnd
		$this->position += 1;// Looped
		$this->position += 1;// Animating
		$this->position += 4;// Unknown3 (float)
	}

	/**
	 * AOC sub_5ED420
	 */
    private function readGenieAnimated()
    {
    	$this->readGenieStaticObj();

    	$this->position += 4;// Speed (float)
		// yep, only speed is read, nothing else...
    }

	/**
	 * AOC sub_5A51A0
	 */
    private function readGenieDopple()
	{
		$this->readGenieStaticObj();

		$this->position += 4;// DoppledSpawnPtr
		$this->position += 1;// MapDrawLevel
		$this->position += 4;// MapColor
		$this->position += 4;// DoppledMasterPtr
		$this->position += 4;// DoppledPlayerId
		if ($this->version->subVersion >= 7.06) {
			$this->position += 4;// FogFlag
		}
		if ($this->version->subVersion >= 9.09) {
			$this->position += 1;// ???
		}
	}

	/**
	 *
	 */
    private function readGenieMoving()
    {
    	$this->readGenieAnimated();

    	$this->position += 4;// TrailRemainder
    	$this->position += 4;// VelocityX
    	$this->position += 4;// VelocityY
    	$this->position += 4;// VelocityZ
    	$this->position += 4;// Angle
    	$this->position += 4;// TurnTowardsTime
    	$this->position += 4;//
    	$this->position += 4;//
    	$this->position += 4;//
    	$this->position += 4;//
    	$this->position += 1;//
    	$this->position += 1;//
		if ($this->version->subVersion >= 9.58) {
    		$this->position += 1;//
		}
		$tempInt = $this->readHeader('l', 4);
		for ($i = 0; $i < $tempInt; $i ++) {
			$this->readUnkStruct();
		}
		if ($this->version->subVersion >= 10.10) {
			$tempInt = $this->readHeader('l', 4);
			if ($tempInt === 1) {
				$this->readUnkStruct();
			}
		}
		$tempInt = $this->readHeader('l', 4);
		if ($tempInt === 1) {
			$this->readWaypoint();//
			$this->readWaypoint();//
		}
		$this->readWaypoint();//
		$this->readWaypoint();//
		$this->readWaypoint();//
		$this->position += 4;// ???
		$tempInt = $this->readHeader('l', 4);
		for ($i = 0; $i < $tempInt; $i ++) {
			$this->readWaypoint();// Waypoints
		}
		if ($this->version->subVersion >= 10.05) {
			$this->position += 4;// ???
			$this->readWaypoint();
		}
		if ($this->version->subVersion >= 10.06) {
			$this->position += 4;// ???
		}
		if ($this->version->subVersion == 10.64) {
			$this->position += 4;// ???
		}
		if ($this->version->subVersion >= 12.10) {// HD Edition
			$this->position += 1;// ???
		}
    }

	/**
	 *
	 */
    private function readUnkStruct()
	{
		if ($this->version->subVersion >= 9.44) {
			$this->position += 4;//
		}
		$this->position += 4;//
		$this->position += 4;//
		$this->position += 4;//
		$this->position += 4;//
		if ($this->version->subVersion >= 10.20) {
			$this->position += 4;//
		}
		if ($this->version->subVersion >= 10.24) {
			$this->position += 4;//
		}
		$this->position += 4;//
		$this->position += 4;//
		$this->position += 4;//
		$this->position += 4;//
		$this->position += 4;//
		if ($this->version->subVersion >= 10.25) {
			$this->position += 4;//
		}
		if ($this->version->subVersion >= 12.10) {// HD Edition
			$this->position += 4;//
		}
	}

	/**
	 * AoK way-points are supposed to be 12 bytes in length.
	 * AoE way-points were 16 bytes in length as they had "NextFacet" property.
	 * @param bool $readNextFacet
	 */
    private function readWaypoint($readNextFacet = FALSE)
	{
		$this->position += 4;// X (float)
		$this->position += 4;// Y (float)
		$this->position += 4;// Z (float)
		$readNextFacet &&
			$this->position += 4;// NextFacet
	}

	/**
	 *
	 */
    private function readGenieActionObj()
    {
		$this->readGenieMoving();

		$this->position += 1;// Waiting
		if ($this->version->subVersion >= 6.50){
			$this->position += 1;// CommandMode
		}
		if ($this->version->subVersion >= 11.58){
			if ($this->version->subVersion >= 11.90){
				$this->position += 4;// ??? (32-bit)
			}else{
				$this->position += 2;// ??? (16-bit)
			}
		}
		$this->readActions();
    }

	/**
	 * AOC(00.07.26.0809) sub_5CDA10
	 */
    private function readGenieCombatObj()
    {
		$this->readGenieActionObj();

		if ($this->version->subVersion >= 9.05) {
			$this->position += 1;// ???
			$this->position += 1;// ???
			$this->position += 1;// ???
		}
		$this->position += 4;// AttackTimer (float)
		if ($this->version->subVersion >= 2.01) {
			$this->position += 1;// ???
		}
		if ($this->version->subVersion >= 9.09) {
			$this->position += 1;// ???
			$this->position += 1;// ???
		}
		if ($this->version->subVersion >= 10.02) {
			$this->position += 4;// ???
		}
    }

	/**
	 * AOC sub_57A5A0
	 */
    private function readGenieMissileObj()
    {
		$this->readGenieCombatObj();

		if ($this->version->subVersion > 7.09) {
			$this->position += 4;// RangeMax (float)
		}
		if ($this->version->subVersion >= 10.37) {
			$this->position += 4;// ??? maybe MissileMasterId...
			$masterParsed = $this->readHeader('l', 4);
		}else{
			$masterParsed = 0;
		}
		if ($masterParsed) {
			$masterType = $this->readHeader('c', 1);
			// todo: read master object for missile type
			0 && $this->readMasterMissileObject();
		}
    }

	/**
	 *
	 */
    private function readTribeTree()
    {
		$this->readGenieStaticObj();
		// yep, nothing else is read...
    }

	/**
	 * TODO: need to fix this function
	 */
    private function readTribeCombat()
    {
		$this->readObj();

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

	/**
	 * TODO: need to fix this function
	 */
    private function readTribeBuilding()
    {
    	$this->readObj();

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

	/**
	 * AOC sub_601510
	 */
	private function readActions()
	{
		while ($actionType = $this->readHeader('s', 2)) {
			$this->readActionsForTribe($actionType);
		}
	}

	const ACTION_TYPE_GENIE_MOVE = 1;
	const ACTION_TYPE_GENIE_ENTER = 3;
	const ACTION_TYPE_GENIE_EXPLORE = 4;
	const ACTION_TYPE_GENIE_GATHER = 5;
	const ACTION_TYPE_GENIE_GRAZE = 6;
	const ACTION_TYPE_GENIE_MISSILE = 8;
	const ACTION_TYPE_GENIE_ATTACK = 9;
	const ACTION_TYPE_GENIE_BIRD = 10;
	const ACTION_TYPE_GENIE_TRANSPORT = 12;
	const ACTION_TYPE_GENIE_GUARD = 13;
	const ACTION_TYPE_GENIE_MAKE = 21;

	const ACTION_TYPE_TRIBE_BUILD = 101;
	const ACTION_TYPE_TRIBE_SPAWN = 102;
	const ACTION_TYPE_TRIBE_RESEARCH = 103;
	const ACTION_TYPE_TRIBE_CONVERT = 104;
	const ACTION_TYPE_TRIBE_HEAL = 105;
	const ACTION_TYPE_TRIBE_REPAIR = 106;
	const ACTION_TYPE_TRIBE_ARTIFACT = 107;
	const ACTION_TYPE_TRIBE_DISCOVERY = 108;
	const ACTION_TYPE_TRIBE_EXPLORE = 109;
	const ACTION_TYPE_TRIBE_HUNT = 110;
	const ACTION_TYPE_TRIBE_TRADE = 111;

	const ACTION_TYPE_TRIBE_WONDER = 120;
	const ACTION_TYPE_TRIBE_FARM = 121;
	const ACTION_TYPE_TRIBE_GATHER = 122;
	const ACTION_TYPE_TRIBE_HOUSING = 123;
	const ACTION_TYPE_TRIBE_PACK = 124;
	const ACTION_TYPE_TRIBE_UNPACK = 125;

	const ACTION_TYPE_TRIBE_MERCHANT = 131;
	const ACTION_TYPE_TRIBE_PICKUP = 132;
	const ACTION_TYPE_TRIBE_CHARGE = 133;
	const ACTION_TYPE_TRIBE_TRANSFORM = 134;
	const ACTION_TYPE_TRIBE_CAPTURE = 135;
	const ACTION_TYPE_TRIBE_DELIVER = 136;
	const ACTION_TYPE_TRIBE_SHEPHERD = 149;

	/**
	 * AOC(00.07.26.0809) sub_601600
	 * @param int $actionType
	 */
	private function readActionsForGenie($actionType)
	{
		switch ($actionType) {

			case $this::ACTION_TYPE_GENIE_GRAZE:
				$this->readActionGenieGraze();
				break;

			case $this::ACTION_TYPE_GENIE_ATTACK:
				$this->readActionGenieAttack();
				break;

			case $this::ACTION_TYPE_GENIE_BIRD:
				$this->readActionGenieBird();
				break;

			case $this::ACTION_TYPE_GENIE_EXPLORE:
				$this->readActionGenieExplore();
				break;

			case $this::ACTION_TYPE_GENIE_GATHER:
				$this->readActionGenieGather();
				break;

			case $this::ACTION_TYPE_GENIE_MISSILE:
				$this->readActionGenieMissile();
				break;

			case $this::ACTION_TYPE_GENIE_MOVE:
				$this->readActionGenieMove();
				break;

			case $this::ACTION_TYPE_GENIE_MAKE:
				$this->readActionGenieMake();
				break;

			case $this::ACTION_TYPE_GENIE_GUARD:
				$this->readActionGenieGuard();
				break;

			default:// todo: normally this should be an error
				$this->readAction();
				break;
		}
	}

	/**
	 * AOC sub_4B1A20
	 * @param int $actionType
	 */
	private function readActionsForTribe($actionType)
	{
		switch ($actionType) {
			
			// The following Action instances are from Genie, yet read from Tribe:

			case $this::ACTION_TYPE_GENIE_TRANSPORT:
				$this->readActionGenieTransport();
				break;

			case $this::ACTION_TYPE_GENIE_ENTER:
				$this->readActionGenieEnter();
				break;

			// The following Action instances are from Tribe:

			case $this::ACTION_TYPE_TRIBE_BUILD:
				$this->readActionTribeBuild();
				break;

			case $this::ACTION_TYPE_TRIBE_SPAWN:
				$this->readActionTribeSpawn();
				break;

			case $this::ACTION_TYPE_TRIBE_RESEARCH:
				$this->readActionTribeResearch();
				break;

			case $this::ACTION_TYPE_TRIBE_CONVERT:
				$this->readActionTribeConvert();
				break;

			case $this::ACTION_TYPE_TRIBE_HEAL:
				$this->readActionTribeHeal();
				break;

			case $this::ACTION_TYPE_TRIBE_REPAIR:
				$this->readActionTribeRepair();
				break;

			case $this::ACTION_TYPE_TRIBE_ARTIFACT:
				$this->readActionTribeArtifact();
				break;

			case $this::ACTION_TYPE_TRIBE_DISCOVERY:
				$this->readActionTribeDiscovery();
				break;

			case $this::ACTION_TYPE_TRIBE_EXPLORE:
				$this->readActionTribeExplore();
				break;

			case $this::ACTION_TYPE_TRIBE_HUNT:
				$this->readActionTribeHunt();
				break;

			case $this::ACTION_TYPE_TRIBE_TRADE:
				$this->readActionTribeTrade();
				break;

			case $this::ACTION_TYPE_TRIBE_WONDER:
				$this->readActionTribeWonder();
				break;

			case $this::ACTION_TYPE_TRIBE_FARM:
				$this->readActionTribeFarm();
				break;

			case $this::ACTION_TYPE_TRIBE_GATHER:
				$this->readActionTribeGather();
				break;

			case $this::ACTION_TYPE_TRIBE_HOUSING:
				$this->readActionTribeHousing();
				break;

			case $this::ACTION_TYPE_TRIBE_PACK:
				$this->readActionTribePack();
				break;

			case $this::ACTION_TYPE_TRIBE_UNPACK:
				$this->readActionTribeUnpack();
				break;

			case $this::ACTION_TYPE_TRIBE_MERCHANT:
				$this->readActionTribeMerchant();
				break;

			case $this::ACTION_TYPE_TRIBE_PICKUP:
				$this->readActionTribePickup();
				break;

			case $this::ACTION_TYPE_TRIBE_CHARGE:
				$this->readActionTribeCharge();
				break;

			case $this::ACTION_TYPE_TRIBE_TRANSFORM:
				$this->readActionTribeTransform();
				break;

			case $this::ACTION_TYPE_TRIBE_CAPTURE:
				$this->readActionTribeCapture();
				break;

			case $this::ACTION_TYPE_TRIBE_DELIVER:
				$this->readActionTribeDeliver();
				break;

			case $this::ACTION_TYPE_TRIBE_SHEPHERD:
				$this->readActionTribeShepherd();
				break;

			default:
				$this->readActionsForGenie($actionType);
				break;
		}
	}

	/**
	 * AOC sub_5FDA30
	 */
	private function readAction()
	{
		$this->position += 1;// State
		$this->position += 4;// TargetSpawnPtr1
		$this->position += 4;// TargetSpawnPtr2
		$this->position += 4;// TargetSpawnId1
		$this->position += 4;// TargetSpawnId2
		$this->position += 4;// TargetSpawnAxisX
		$this->position += 4;// TargetSpawnAxisY
		$this->position += 4;// TargetSpawnAxisZ
		$this->position += 4;// Timer
		if ($this->version->subVersion >= 9.92){
			$this->position += 1;// ???
		}
		$this->position += 2;// TaskId
		$this->position += 1;// SubActionValue
		$this->readActions();// SubActions
		$this->position += 2;// SpriteId
	}

	/**
	 *
	 */
	private function readActionGenieGraze()
	{
		$this->readAction();
	}

	/**
	 * AOC sub_605370
	 */
	private function readActionGenieAttack()
	{
		$this->readAction();

		$this->position += 4;// RangeMax (float)
		$this->position += 4;// RangeMin (float)
		$this->position += 2;// MissileMasterId
		$this->position += 2;// MissileAtFrame
		$this->position += 1;// NeedToAttack
		$this->position += 1;// WasSameOwner
		$this->position += 1;// IndirectFireMode
		$this->position += 2;// MoveSpriteId
		$this->position += 2;// FightSpriteId
		$this->position += 2;// WaitSpriteId
		if ($this->version->subVersion >= 9.02) {
			$this->position += 4;// PositionX (float)
			$this->position += 4;// PositionY (float)
			$this->position += 4;// PositionZ (float)
		}
	}

	/**
	 *
	 */
	private function readActionGenieExplore()
	{
		$this->readAction();
	}

	/**
	 *
	 */
	private function readActionGenieGuard()
	{
		$this->readAction();
	}

	/**
	 * AOC sub_601280
	 */
	private function readActionGenieMake()
	{
		$this->readAction();

		if ($this->version->subVersion >= 9.41) {
			$this->position += 4;// ??? (float) (what is this? it's set to 120.0 by default)
		}
	}

	/**
	 * AOC sub_5FFC10
	 */
	private function readActionGenieMove()
	{
		$this->readAction();

		$this->position += 4;// Range (float)
	}

	/**
	 *
	 */
	private function readActionGenieBird()
	{
		$this->readAction();
	}

	/**
	 * AOC sub_602060
	 */
	private function readActionGenieGather()
	{
		$this->readAction();

		$this->position += 4;// Classifier
		if ($this->version->subVersion >= 9.01) {
			$this->position += 1;// ???
			$this->position += 1;// ???
			$this->position += 4;// AttributeFactor (float)
			$this->position += 4;// ???
			$this->position += 1;// ???
			$this->position += 1;// ???
		}
		if ($this->version->subVersion >= 11.57) {
			$this->position += 4;// DepositAxisX (float)
			$this->position += 4;// DepositAxisY (float)
		}
	}

	/**
	 * AOC sub_600350
	 */
	private function readActionGenieMissile()
	{
		$this->readAction();

		$this->position += 4;// VelocityX (float)
		$this->position += 4;// VelocityY (float)
		$this->position += 4;// VelocityZ (float)
		$this->position += 4;// BallisticVelocity (float)
		$this->position += 4;// BallisticAcceleration (float)
		if ($this->version->subVersion >= 10.29) {
			$this->position += 4;// ???
		}
	}

	/**
	 *
	 */
	private function readActionGenieTransport()
	{
		$this->readAction();
	}

	/**
	 *
	 */
	private function readActionGenieEnter()
	{
		$this->readAction();

		if ($this->version->subVersion >= 9.92){
			$this->position += 4;// ???
		}
	}

	/**
	 *
	 */
	private function readActionTribeBuild()
	{
		$this->readAction();

		if ($this->version->subVersion >= 10.47){
			$this->position += 4;// ???
		}
	}

	/**
	 * AOC sub_4B0A20
	 */
	private function readActionTribeSpawn()
	{
		$this->readAction();

		$this->position += 2;// MasterId
		$this->position += 4;// WorkDone
		$this->position += 4;// UniqueId
	}

	/**
	 * AOC sub_4AC920
	 */
	private function readActionTribeResearch()
	{
		$this->readAction();

		$this->position += 2;// ResearchId
		$this->position += 4;// UniqueId
	}

	/**
	 * AOC sub_4B76D0
	 */
	private function readActionTribeConvert()
	{
		$this->readAction();

		$this->position += 1;// WasSameOwner
		$this->position += 4;// RequiredRange (float)
		if ($this->version->subVersion >= 9.53) {
			$this->position += 4;// ??? (float)
		}
		if ($this->version->subVersion >= 9.54) {
			$this->position += 1;// ???
			$this->position += 1;// ???
			$this->position += 4;// ???
		}
		if ($this->version->subVersion >= 10.33) {
			$this->position += 4;// ???
			$this->position += 4;// ???
			$this->position += 4;// ??? (some offset to structure)
		}
	}

	/**
	 *
	 */
	private function readActionTribeHeal()
	{
		$this->readAction();
	}

	/**
	 * AOC sub_4AEAA0
	 */
	private function readActionTribeRepair()
	{
		$this->readAction();

		if ($this->version->subVersion >= 6.50) {
			$this->position += 1;// SaveTargetCommand
		}
		if ($this->version->subVersion >= 10.47) {
			$this->position += 4;// ???
		}
	}

	/**
	 *
	 */
	private function readActionTribeArtifact()
	{
		$this->readAction();
	}

	/**
	 *
	 */
	private function readActionTribeDiscovery()
	{
		$this->readAction();

		$numPlayers = 9;
		$this->position += 1 * $numPlayers;// DiscoverOwners
	}

	/**
	 *
	 */
	private function readActionTribeExplore()
	{
		$this->readAction();
	}

	/**
	 * AOC sub_4B2EE0
	 */
	private function readActionTribeHunt()
	{
		$this->readAction();

		$this->position += 4;// TargetMasterClass
		if ($this->version->subVersion >= 10.31) {
			$this->position += 4;// ???
		}
		if ($this->version->subVersion >= 10.53) {
			$this->position += 1;// ???
		}
		if ($this->version->subVersion >= 10.56) {
			$this->position += 4;// ??? (float)
		}
		if ($this->version->subVersion >= 10.53) {
			$this->position += 2;// ???
			$this->position += 4;// ??? (float)
			$this->position += 4;// ??? (float)
			$this->position += 4;// ??? (some offset to structure)
		}
	}

	/**
	 * AOC sub_4AB8C0
	 */
	private function readActionTribeTrade()
	{
		$this->readAction();

		if ($this->version->subVersion >= 10.04) {
			$this->position += 1;// ???
			$this->position += 4;// ???
			$this->position += 4;// ???
		}
		if ($this->version->subVersion >= 10.07) {
			$this->position += 4;// ??? (float)
			$this->position += 4;// ??? (float)
		}
	}

	/**
	 * AOC sub_4AA260
	 */
	private function readActionTribeWonder()
	{
		$this->readAction();

		if ($this->version->subVersion >= 9.33) {
			$this->position += 4;// ??? (float) (internally set to 2000.0, possibly time)
		}
		if ($this->version->subVersion >= 11.73) {
			$this->position += 1;// ???
		}
	}

	/**
	 *
	 */
	private function readActionTribeFarm()
	{
		$this->readAction();
	}

	/**
	 * AOC sub_4B53D0
	 */
	private function readActionTribeGather()
	{
		$this->readAction();

		$this->position += 4;// GatherMasterId
	}

	/**
	 * AOC sub_4B2B80
	 */
	private function readActionTribeHousing()
	{
		$this->readAction();

		$this->position += 4;// ???
	}

	/**
	 * AOC sub_4AF610
	 */
	private function readActionTribePack()
	{
		$this->readAction();

		$this->position += 4;// ???
		$this->position += 1;// ???
		if ($this->version->subVersion >= 10.27) {
			$this->position += 4;// ???
		}
	}

	/**
	 * AOC sub_4AB130
	 */
	private function readActionTribeUnpack()
	{
		$this->readAction();

		$this->position += 4;// ???
		$this->position += 1;// ???
		if ($this->version->subVersion >= 9.40) {
			$this->position += 4;// ???
		}
		if ($this->version->subVersion >= 10.27) {
			$this->position += 4;// ???
		}
	}

	/**
	 * AOC sub_4AFA80
	 */
	private function readActionTribeMerchant()
	{
		$this->readAction();

		$this->position += 4;// ???
		$this->position += 4;// ???
	}

	/**
	 * AOC sub_4B0380
	 */
	private function readActionTribePickup()
	{
		$this->readAction();
	}

	/**
	 * AOC sub_4AAB70
	 */
	private function readActionTribeTransform()
	{
		$this->readAction();

		$this->position += 4;// ???
	}

	/**
	 * AOC sub_4B7220
	 */
	private function readActionTribeCharge()
	{
		$this->readAction();
	}

	/**
	 * AOC sub_4B8B70
	 */
	private function readActionTribeCapture()
	{
		$this->readAction();
	}

	/**
	 * AOC sub_4B1440
	 */
	private function readActionTribeDeliver()
	{
		$this->readAction();
		
		$this->position += 1;// ???
	}

	/**
	 *
	 */
	private function readActionTribeShepherd()
	{
		$this->readAction();
	}
}
