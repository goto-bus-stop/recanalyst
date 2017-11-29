<?php

/**
 * Outputs a bunch of information about the recorded game, in a specified
 * locale. By default, this script uses French, but a command-line parameter
 * can be passed to use a different language.
 *
 * Usage:
 *    php examples/simple-localized.php # Default language (French).
 *    php examples/simple-localized.php br # Use Brazilian Portuguese
 *    php examples/simple-localized.php fake # Nonexistent language, falls back
 *                                           # to RecAnalyst's default (English)
 */

require __DIR__ . '/../vendor/autoload.php';

use RecAnalyst\RecordedGame;
use RecAnalyst\BasicTranslator;

$filename = __DIR__ . '/../test/recs/versions/up1.4.mgz';

// Read a command-line argument specifying the language to use.
$locale = 'fr';
if ($argc > 1) {
    $locale = $argv[1];
}

// Read a recorded game from a file path.
$rec = new RecordedGame($filename, [
    'translator' => new BasicTranslator($locale)
]);

// Display some metadata.
echo 'Game Type: ' . $rec->gameSettings()->gameTypeName() . "\n";
echo 'Starting Age: ' . $rec->pov()->startingAge() . "\n";
echo 'Map Name: ' . $rec->gameSettings()->mapName() . "\n";

// Display players and their civilizations.
echo 'Players: ' . "\n";
foreach ($rec->players() as $player) {
    echo ' * ' . $player->name . ' (' . $player->civName(). ')' . "\n";
}
