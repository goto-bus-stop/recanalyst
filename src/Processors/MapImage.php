<?php
namespace RecAnalyst\Processors;

use Intervention\Image\ImageManager;
use Intervention\Image\ImageManagerStatic;
use RecAnalyst\RecordedGame;
use RecAnalyst\Analyzers\HeaderAnalyzer;
use RecAnalyst\ResourcePacks\AgeOfEmpires\Unit;

/**
 * Generate a top-down map image that shows the starting state of the game.
 */
class MapImage
{
    /**
     * Recorded game file to use.
     *
     * @var \RecAnalyst\RecordedGame
     */
    private $rec;

    /**
     * Image manager to use.
     *
     * @var \Intervention\Image\ImageManager
     */
    private $imageManager;

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
    }

    /**
     * Generate a map!
     *
     * @return \Intervention\Image
     */
    public function run()
    {
        $header = $this->rec->getAnalysis(HeaderAnalyzer::class)->analysis;
        $mapData = $header->mapData;
        $mapSize = count($mapData);
        $image = $this->imageManager->canvas($mapSize, $mapSize);
        $p = $this->rec->getResourcePack();

        foreach ($mapData as $x => $row) {
            foreach ($row as $y => $tile) {
                $color = $p->getTerrainColor($tile->terrain);
                if (!is_null($color)) {
                    $image->pixel($color, $x, $y);
                } else {
                    throw new \Exception(sprintf('Unknown terrain ID \'%d\'', $tile->terrain));
                }
            }
        }

        $gaiaObjects = $this->sortObjects($header->playerInfo->gaiaObjects);

        foreach ($gaiaObjects as $obj) {
            $color = $p->getUnitColor($obj->id);
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

                $color = $p->getPlayerColor($player->colorId);
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
                $color = $p->getPlayerColor($object->owner->colorId);
                list ($x, $y) = $object->position;
                $image->rectangle($x - 1, $y - 1, $x + 1, $y + 1, function ($shape) use ($color) {
                    $shape->background($color);
                });
            }
        }

        return $image->rotate(45, [0, 0, 0, 0]);
    }

    /**
     * Sort GAIA objects for a good draw order. Relics are important, and show
     * on top of everything else; cliffs are lines (so interruptions are OK) and
     * show below everything else.
     *
     * @param array  $objects  Unsorted GAIA objects.
     * @return array Sorted GAIA objects.
     */
    private function sortObjects($objects)
    {
        $p = $this->rec->getResourcePack();

        usort($objects, function ($item1, $item2) use (&$p) {
            // relics show on top of everything else
            if ($item1->id === Unit::RELIC && $item2->id !== Unit::RELIC) {
                return 1;
            }
            // cliffs show below everything else
            if ($p->isCliffUnit($item1->id) && !$p->isCliffUnit($item2->id)) {
                return -1;
            }
            if ($item2->id === Unit::RELIC && $item1->id !== Unit::RELIC) {
                return -1;
            }
            if ($p->isCliffUnit($item2->id) && !$p->isCliffUnit($item1->id)) {
                return 1;
            }
            return 0;
        });

        return $objects;
    }
}
