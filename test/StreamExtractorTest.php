<?php

use Webmozart\PathUtil\Path;
use PHPUnit\Framework\TestCase;

use RecAnalyst\StreamExtractor;

class StreamExtractorTest extends TestCase
{
    private function load($path)
    {
        $fp = fopen(Path::join(__DIR__, $path), 'r');
        return new StreamExtractor($fp);
    }

    /**
     * @dataProvider filesProvider
     */
    public function testStreamExtractor($file)
    {
        $extractor = $this->load($file);
        $this->assertNotNull($extractor->getHeader());
        $this->assertNotNull($extractor->getBody());
    }

    public function filesProvider()
    {
        return [
            ['recs/versions/aok.mgl'],
            ['recs/versions/up1.4.mgz'],
            ['recs/versions/mgx2_simple.mgx2'],
            ['recs/versions/MP Replay v4.3 @2015.09.11 221142 (2).msx'],
            ['recs/versions/MP_Replay_v4.msx2'],
            ['recs/versions/HD Tourney r1 robo_boro vs Dutch Class g1.aoe2record'],
            // A multiplayer autosave with front matter.
            // ['recs/auto save -  28-jan-2014 16`00`07.msx'],
        ];
    }
}
