<?php
namespace RecAnalyst;

class Tile
{
    public $terrain;
    public $elevation;
    public function __construct($terrain, $elevation)
    {
        $this->terrain = $terrain;
        $this->elevation = $elevation;
    }
}
