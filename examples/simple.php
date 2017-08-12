<?php

/**
 * A very barebones example. It outputs a map image to a PNG file and
 * outputs the players in the game to the command line.
 */

require __DIR__ . '/../vendor/autoload.php';

use RecAnalyst\RecordedGame;

$filename = $argv[1] ?? __DIR__ . '/../test/recs/forgotten/HD-FE.mgx2';

// Read a recorded game from a file path.
$rec = new RecordedGame($filename);

$version = $rec->version();
echo 'Version: ' . $version->versionString . ' (' . $version->subVersion . ')' . "\n";

// Display players and their civilizations.
echo 'Players: ' . "\n";
foreach ($rec->players() as $player) {
    printf(" %s %s (%s)\n",
        $player->owner ? '>' : '*',
        $player->name,
        $player->civName());
}

// Render a map image. Map images are instances of the \Intervention\Image
// library, so you can easily manipulate them.
$rec->mapImage()
    ->resize(240, 120)
    ->save('minimap.png');

echo 'Minimap saved in minimap.png.' . "\n";
