<?php

/**
 * This example reads chat messages from a recorded game.
 *
 * Usage:
 *    # Use a test file with lots of chat.
 *    php examples/chat.php
 *
 *    # Use a recorded game of your choice.
 *    php examples/chat.php /path/to/your/own/file.mgx
 */

require __DIR__ . '/../vendor/autoload.php';

use function RecAnalyst\gametime_format;
use RecAnalyst\RecordedGame;

// Read a recorded game filename from the command line.
// Default to a test team game.
$filename = __DIR__ . '/../test/recs/FluffyFur+yousifr+TheBlackWinds+Mobius_One[Chinese]=VS=MOD3000+Chrazini+ClosedLoop+ [AGM]Wineup[Britons]_1v1_8PlayerCo-op_01222015.mgx2';
if ($argc > 1) {
    $filename = $argv[1];
}

// Read a recorded game from a file path.
$rec = new RecordedGame($filename);

// There are two types of chat in a recorded game: pre-game multiplayer lobby
// chat, and in-game chat.

// Read the pre-game chat from the file header. Pre-game messages don't have a
// timestamp.
foreach ($rec->header()->pregameChat as $chat) {
        printf("<%s> %s\n", $chat->player->name, $chat->msg);
}

// Read the in-game chat from the file body.
foreach ($rec->body()->chatMessages as $chat) {
    // Format the millisecond time as HH:MM:SS.
    $time = gametime_format($chat->time);

    if ($chat->player) {
        printf("[%s] <%s> %s\n", $time, $chat->player->name, $chat->msg);
    } else {
        printf("[%s] * %s\n", $time, $chat->msg);
    }
}
