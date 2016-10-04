<?php

use PHPUnit\Framework\TestCase;
use Webmozart\PathUtil\Path;
use RecAnalyst\RecordedGame;
use RecAnalyst\Processors\MapImage;

class MapImageTest extends TestCase
{
    public function testImage()
    {
        MapImage::defaultManager(['driver' => 'imagick']);
        $testPath = Path::join(__DIR__, uniqid('HD_test') . '.png');

        $rec = new RecordedGame(Path::join(__DIR__, './recs/versions/HD_test.mgx'));
        $processor = new MapImage($rec);

        $image = $processor->run();

        // Not much else to do. I don't really know how to test this ¯\_(ツ)_/¯
        $image->save($testPath);

        $this->assertFileExists($testPath);

        unlink($testPath);
    }
}
