<?php
namespace RecAnalyst;

/**
 * Represents a map tile.
 */
class Tile
{
    /**
     * X-coordinate.
     *
     * @var int
     */
    public $x;

    /**
     * Y-coordinate.
     *
     * @var int
     */
    public $y;

    /**
     * Terrain type ID.
     *
     * @var int
     */
    public $terrain;

    /**
     * Elevation level (0-7).
     *
     * @var int
     */
    public $elevation;

    /**
     * Create a terrain tile.
     *
     * @param int  $x  X-coordinate.
     * @param int  $y  y-coordinate.
     * @param int  $terrain  Terrain type ID.
     * @param int  $elevation  Elevation level.
     */
    public function __construct($x, $y, $terrain, $elevation)
    {
        $this->terrain = $terrain;
        $this->elevation = $elevation;
    }
}
