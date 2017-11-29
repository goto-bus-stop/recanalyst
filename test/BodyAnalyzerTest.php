<?php

use PHPUnit\Framework\TestCase;
use Webmozart\PathUtil\Path;
use RecAnalyst\RecordedGame;
use RecAnalyst\Model\Tribute;
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

    /**
     * Check that tributes have the correct properties and associated players.
     */
    public function testTributes()
    {
        $rec = new RecordedGame(Path::join(__DIR__, 'recs/versions/MP Replay v4.3 @2015.09.11 221142 (2).msx'));
        $tributes = $rec->runAnalyzer(new BodyAnalyzer)->tributes;
        $this->assertAttributeEquals(10000, 'amount', $tributes[0]);
        $this->assertAttributeEquals(Tribute::WOOD, 'resourceId', $tributes[0]);
        $this->assertAttributeEquals('Ruga the Hun (Original AI)', 'name', $tributes[0]->playerFrom);
        $this->assertAttributeEquals('Mu Gui-ying (Original AI)', 'name', $tributes[0]->playerTo);
    }

    public function testAchievements()
    {
        $rec = new RecordedGame(Path::join(__DIR__, 'recs/versions/up1.4.mgz'));
        $rec->body(); // Populate achievements on player objects.
        $this->assertAttributeEquals(7411, 'score', $rec->getPlayer(1)->achievements());
        $this->assertAttributeEquals(9484, 'score', $rec->getPlayer(2)->achievements());
    }

    public function testCoopChat()
    {
        $rec = new RecordedGame(Path::join(__DIR__, 'recs/FluffyFur+yousifr+TheBlackWinds+Mobius_One[Chinese]=VS=MOD3000+Chrazini+ClosedLoop+ [AGM]Wineup[Britons]_1v1_8PlayerCo-op_01222015.mgx2'));
        $messages = $rec->body()->chatMessages;

        // All these players are co-oping, so they share a player index.
        // They can all chat separately, though.
        $this->assertAttributeEquals('yousifr', 'name', $messages[0]->player);
        $this->assertAttributeEquals('TheBlackWinds', 'name', $messages[3]->player);
        $this->assertAttributeEquals('Mobius One', 'name', $messages[4]->player);
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
