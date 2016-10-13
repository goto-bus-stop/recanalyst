<?php

/**
 * A very barebones example. It outputs a map image to a PNG file and
 * outputs the players in the game to the command line.
 */

require __DIR__ . '/../vendor/autoload.php';

use RecAnalyst\RecordedGame;

// Read a recorded game from a file path.
$rec = new RecordedGame('recorded_game.mgx2');

// Render a map image. Map images are instances of the \Intervention\Image
// library, so you can easily manipulate them.
$rec->mapImage()
    ->resize(240, 120)
    ->save('minimap.png');

// Display players and their civilizations.
foreach ($rec->players() as $player) {
    echo $player->name . ' (' . $player->civName(). ')' . "\n";
}
