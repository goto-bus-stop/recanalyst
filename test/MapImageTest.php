<?php

use PHPUnit\Framework\TestCase;
use Webmozart\PathUtil\Path;
use RecAnalyst\RecordedGame;
use RecAnalyst\Processors\MapImage;

class MapImageTest extends TestCase
{
    public function setUp(): void
    {
        MapImage::defaultManager(['driver' => 'imagick']);
    }

    public function testImage()
    {
        $testPath = Path::join(__DIR__, uniqid('HD_test') . '.png');

        $rec = new RecordedGame(Path::join(__DIR__, './recs/versions/HD_test.mgx'));
        $processor = new MapImage($rec);

        $image = $processor->run();

        // Not much else to do. I don't really know how to test this ¯\_(ツ)_/¯
        $image->save($testPath);

        $this->assertFileExists($testPath);

        unlink($testPath);
    }

    /**
     * Draw a map image for a campaign game, with lots and lots of units
     * existing from the start.
     */
    public function testImageCampaign()
    {
        $testPath = Path::join(__DIR__, uniqid('SP Replay v4.6 @2015.12.29 001221') . '.png');

        $rec = new RecordedGame(Path::join(__DIR__, './recs/versions/SP Replay v4.6 @2015.12.29 001221.aoe2record'));
        $processor = new MapImage($rec);

        $image = $processor->run();

        // Not much else to do. I don't really know how to test this ¯\_(ツ)_/¯
        $image->save($testPath);

        $this->assertFileExists($testPath);

        unlink($testPath);
    }
}
