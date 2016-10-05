<?php
namespace RecAnalyst\Processors;

use Intervention\Image\ImageManager;
use Intervention\Image\ImageManagerStatic;
use RecAnalyst\RecordedGame;
use RecAnalyst\Unit;
use RecAnalyst\Analyzers\HeaderAnalyzer;

class MapImage
{
    /**
     * @var RecAnalyst\RecordedGame
     */
    private $rec;

    /**
     * @var Intervention\Image\ImageManager
     */
    private $imageManager;

    /**
     * @var array
     */
    private $terrainColors;

    /**
     * Default terrain colors, indexed by ID.
     * @var array
     */
    public static $TERRAIN_COLORS = [
        '#339727',
        '#305db6',
        '#e8b478',
        '#e4a252',
        '#5492b0',
        '#339727',
        '#e4a252',
        '#82884d',
        '#82884d',
        '#339727',
        '#157615',
        '#e4a252',
        '#339727',
        '#157615',
        '#e8b478',
        '#305db6',
        '#339727',
        '#157615',
        '#157615',
        '#157615',
        '#157615',
        '#157615',
        '#004aa1',
        '#004abb',
        '#e4a252',
        '#e4a252',
        '#ffec49',
        '#e4a252',
        '#305db6',
        '#82884d',
        '#82884d',
        '#82884d',
        '#c8d8ff',
        '#c8d8ff',
        '#c8d8ff',
        '#98c0f0',
        '#c8d8ff',
        '#98c0f0',
        '#c8d8ff',
        '#c8d8ff',
        '#e4a252',
    ];

    /**
     * Default colors of GAIA-owned objects, indexed by unit ID.
     * @var array
     */
    public static $GAIA_COLORS = [
        Unit::GOLDMINE   => '#ffc700',
        Unit::STONEMINE  => '#919191',
        Unit::CLIFF1     => '#714b33',
        Unit::CLIFF2     => '#714b33',
        Unit::CLIFF3     => '#714b33',
        Unit::CLIFF4     => '#714b33',
        Unit::CLIFF5     => '#714b33',
        Unit::CLIFF6     => '#714b33',
        Unit::CLIFF7     => '#714b33',
        Unit::CLIFF8     => '#714b33',
        Unit::CLIFF9     => '#714b33',
        Unit::CLIFF10    => '#714b33',
        Unit::RELIC      => '#ffffff',
        Unit::TURKEY     => '#a5c46c',
        Unit::SHEEP      => '#a5c46c',
        Unit::DEER       => '#a5c46c',
        Unit::BOAR       => '#a5c46c',
        Unit::JAVELINA   => '#a5c46c',
        Unit::FORAGEBUSH => '#a5c46c',
    ];

    /**
     * Default player colors.
     *
     * @var array
     */
    public static $PLAYER_COLORS = [
        0 => '#0000ff',
        1 => '#ff0000',
        2 => '#00ff00',
        3 => '#ffff00',
        4 => '#00ffff',
        5 => '#ff00ff',
        6 => '#b9b9b9',
        7 => '#ff8201',
    ];

    /**
     * Configure the Intervention image manager. See:
     *
     *   http://image.intervention.io/getting_started/configuration
     *
     * @param \Intervention\ImageManager|array  $manager  Image manager or array of image
     *    manager options to use.
     *
     * @return \Intervention\ImageManager The current default image manager.
     */
    public static function defaultManager($manager = null)
    {
        static $defaultManager;
        if (is_array($manager)) {
            $defaultManager = new ImageManager($manager);
        } else if ($manager instanceof ImageManager) {
            $defaultManager = $manager;
        } else if (!isset($defaultManager)) {
            // Use global manager.
            $defaultManager = ImageManagerStatic::getManager();
        }
        return $defaultManager;
    }

    /**
     * Create a Map Image generator.
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param array  $options  Options to use.
     *     - `$options['manager']` - Use an ImageManager that's not the default.
     *     - `$options['showPositions']` - Set to false to hide player starting
     *       positions. Defaults to true.
     *     - `$options['showPlayerUnits']` - Set to false to hide positions of
     *       starting units (e.g. walls on Arena). Defaults to true.
     */
    public function __construct(RecordedGame $rec, array $options = [])
    {
        $this->rec = $rec;

        $options = array_merge([
            'manager' => null,
            'showPositions' => true,
            'showPlayerUnits' => true,
        ], $options);

        $this->imageManager = $options['manager'] ?: static::defaultManager();
        $this->showPositions = $options['showPositions'];
        $this->showPlayerUnits = $options['showPlayerUnits'];
        $this->terrainColors = static::$TERRAIN_COLORS;
        $this->gaiaColors = static::$GAIA_COLORS;
        $this->playerColors = static::$PLAYER_COLORS;
    }

    /**
     * Generate a map!
     *
     * @return \Intervention\Image
     */
    public function run()
    {
        $header = $this->rec->runAnalyzer(new HeaderAnalyzer);
        $mapData = $header->mapData;
        $mapSize = count($mapData);
        $image = $this->imageManager->canvas($mapSize, $mapSize);

        foreach ($mapData as $x => $row) {
            foreach ($row as $y => $tile) {
                if (array_key_exists($tile->terrain, $this->terrainColors)) {
                    $image->pixel($this->terrainColors[$tile->terrain], $x, $y);
                } else {
                    throw new \Exception(sprintf('Unknown terrain ID \'%d\'', $tile->terrain));
                }
            }
        }

        foreach ($header->playerInfo->gaiaObjects as $obj) {
            $color = $this->gaiaColors[$obj->id];
            list ($x, $y) = $obj->position;
            $image->rectangle($x - 1, $y - 1, $x + 1, $y + 1, function ($shape) use ($color) {
                $shape->background($color);
            });
        }

        if ($this->showPositions) {
            foreach ($header->players as $player) {
                if ($player->isCooping || $player->isSpectator()) {
                    continue;
                }

                $color = $this->playerColors[$player->colorId];
                list ($x, $y) = $player->initialState->position;
                $image->circle(18, $x, $y, function ($shape) use ($color) {
                    $shape->border(1, $color);
                });
                $image->circle(8, $x, $y, function ($shape) use ($color) {
                    $shape->background($color);
                });
            }
        }

        if ($this->showPlayerUnits) {
            foreach ($header->playerInfo->playerObjects as $object) {
                $color = $this->playerColors[$object->owner->colorId];
                list ($x, $y) = $object->position;
                $image->rectangle($x - 1, $y - 1, $x + 1, $y + 1, function ($shape) use ($color) {
                    $shape->background($color);
                });
            }
        }

        return $image->rotate(45, [0, 0, 0, 0]);
    }
}
