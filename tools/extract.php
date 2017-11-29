<?php

/**
 * Extract the header and body streams from a file.
 */

require __DIR__ . '/../vendor/autoload.php';

if (empty($argv[1]) || empty($argv[2])) {
  die(
<<<CLI
Usage: php tools/extract.php <file> <output>

CLI
  );
}

$file = $argv[1];
$outputDir = rtrim($argv[2], '/');

@mkdir($outputDir, 0777, true);

$rec = new RecAnalyst\RecordedGame($file);

file_put_contents($outputDir . '/header.dat', $rec->getHeaderContents());
file_put_contents($outputDir . '/body.dat', $rec->getBodyContents());
