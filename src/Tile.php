<?php
namespace RecAnalyst;

class Tile
{
    public $x;
    public $y;
    public $terrain;
    public $elevation;
    public function __construct($x, $y, $terrain, $elevation)
    {
        $this->terrain = $terrain;
        $this->elevation = $elevation;
    }
}
