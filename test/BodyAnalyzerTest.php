<?php

use PHPUnit\Framework\TestCase;
use Webmozart\PathUtil\Path;
use RecAnalyst\RecordedGame;
use RecAnalyst\Analyzers\BodyAnalyzer;

class BodyAnalyzerTest extends TestCase
{
    /**
     * @dataProvider hdForgottenProvider
     */
    public function testHDForgotten($file)
    {
        $rec = new RecordedGame($file);
        // Just make sure it doesn't crash!
        $analysis = $rec->runAnalyzer(new BodyAnalyzer);

        $this->assertNotNull($analysis);
    }

    /**
     * @dataProvider recordsProvider
     */
    public function testParse($file)
    {
        $rec = new RecordedGame(Path::join(__DIR__, $file));
        $analysis = $rec->runAnalyzer(new BodyAnalyzer);
        $this->assertTrue($analysis->duration > 0);
    }

    /**
     * Check that voobly injected messages are trimmed correctly.
     */
    public function testVooblyInjectedMessages()
    {
        $rec = new RecordedGame(Path::join(__DIR__, 'recs/versions/up1.4.mgz'));
        $messages = $rec->runAnalyzer(new BodyAnalyzer)->chatMessages;
        // Rating messages should belong to a player.
        $this->assertEquals($messages[0]->group, 'Rating');
        $this->assertEquals($messages[0]->msg, '2212');
        $this->assertEquals($messages[0]->player->name, 'Zuppi');
    }

    public function recordsProvider()
    {
        return [
            ['./recs/versions/aok.mgl'],
            ['./recs/versions/HD_test.mgx'],
            ['./recs/versions/HD-FE.mgx2'],
            ['./recs/versions/up1.4.mgz'],
        ];
    }

    public function hdForgottenProvider()
    {
        return array_map(function ($path) {
            return [Path::makeRelative($path, getcwd())];
        }, glob(Path::join(__DIR__, './recs/forgotten/*')));
    }
}
