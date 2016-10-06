<?php
namespace RecAnalyst;

/**
 * Represents a map tile.
 */
class Tile
{
    /** @var int X-coordinate. */
    public $x;
    /** @var int Y-coordinate. */
    public $y;
    /** @var int Terrain type ID. */
    public $terrain;
    /** @var int Elevation level (0-7). */
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
