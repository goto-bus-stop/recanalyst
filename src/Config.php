<?php
/**
 * Defines Config class.
 *
 * @package RecAnalyst
 */

namespace RecAnalyst;

/**
 * Class Config.
 *
 * Configuration class.
 * Config implements configuration constants used for RecAnalyst.
 *
 * @package RecAnalyst
 */
class Config
{

    /**
     * Defines a path (absolute or relative) to directory where we store
     * resources required for generating research timelines.
     *
     * @var string
     */
    public $resourcesDir;

    /**
     * Defines a width of the map image we wish to generate.
     * @var int
     */
    public $mapWidth;

    /**
     * Defines a height of the map image we wish to generate.
     * @var int
     */
    public $mapHeight;

    /**
     * Defines width and height of one research tile in research timelines image.
     * @var int
     */
    public $researchTileSize;

    /**
     * Defines vertical spacing between players in research timelines image.
     * @var int
     */
    public $researchVSpacing;

    /**
     * Defines background image for research timelines image.
     * @var string
     */
    public $researchBackgroundImage;

    /**
     * Defines color for Dark Age in the research timelines image.
     * Array consist of red, green, blue color and alpha.
     * @var array
     */
    public $researchDAColor;

    /**
     * Defines color for Feudal Age in the research timelines image.
     * @var array
     * @see $researchDAColor
     */
    public $researchFAColor;

    /**
     * Defines color for Castle Age in the research timelines image.
     * @var array
     * @see $researchDAColor
     */
    public $researchCAColor;

    /**
     * Defines color for Imperial Age in the research timelines image.
     * @var array
     * @see $researchDAColor
     */
    public $researchIAColor;

    /**
     * Determines if to show players positions on the map.
     * @var bool
     */
    public $showPositions;

    /**
     * Class constructor. Tries to do some default stuff.
     *
     * @return void
     */
    public function __construct()
    {
        $baseDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
        $this->resourcesDir = $baseDir . 'resources' . DIRECTORY_SEPARATOR;

        // map image generation
        $this->mapWidth = 204;
        $this->mapHeight = 102;
        $this->showPositions = true;
        // research image generation
        $this->researchTileSize = 19;
        $this->researchVSpacing = 8;
        $this->researchBackgroundImage = $this->resourcesDir . 'background.jpg';
        $this->researchDAColor = array(0xff, 0x00, 0x00, 0x50); // red
        $this->researchFAColor = array(0x00, 0xff, 0x00, 0x50); // green
        $this->researchCAColor = array(0x00, 0x00, 0xff, 0x50); // blue
        $this->researchIAColor = array(0x99, 0x66, 0x00, 0x50); // orangy
    }

}
