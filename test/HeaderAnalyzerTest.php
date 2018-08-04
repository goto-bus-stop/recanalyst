<?php

use Webmozart\PathUtil\Path;
use PHPUnit\Framework\TestCase;

use RecAnalyst\RecordedGame;
use RecAnalyst\Model\GameSettings;
use RecAnalyst\Model\VictorySettings;
use RecAnalyst\Analyzers\HeaderAnalyzer;

class HeaderAnalyzerTest extends TestCase
{
    private function load($path)
    {
        return new RecordedGame(Path::join(__DIR__, $path));
    }

    /**
     * @dataProvider playersProvider
     */
    public function testPlayers($file, $expected, $expectedCount)
    {
        $rec = $this->load($file);
        $analysis = $rec->runAnalyzer(new HeaderAnalyzer);
        $this->assertAttributeEquals($expectedCount, 'numPlayers', $analysis);
        foreach ($expected as $index => $player) {
            foreach ($player as $prop => $value) {
                $this->assertAttributeEquals($value, $prop, $analysis->players[$index]);
            }
        }
    }

    public function testScenarioMessages()
    {
        $rec = $this->load('recs/scenario/scenario-with-messages.mgz');
        $analysis = $rec->runAnalyzer(new HeaderAnalyzer);
        $messageTypes = [
            // Identifiers embedded in the test game.
            'instructions' => 'RECANALYST:INSTRUCTIONS',
            'hints' => 'RECANALYST:HINTS',
            'loss' => 'RECANALYST:LOSS',
            'victory' => 'RECANALYST:VICTORY',
            'scouts' => 'RECANALYST:SCOUT',
            'history' => 'RECANALYST:HISTORY',
        ];
        foreach ($messageTypes as $type => $expected) {
            $this->assertAttributeContains($expected, $type, $analysis->messages);
        }
    }

    /**
     * Test a game with multiple AI players.
     */
    public function testSkippingAiInfo()
    {
        $rec = $this->load('recs/ai/20141214_blutze(mong)+ffraid(pers) vs bots(goth+chin).mgx2');
        $analysis = $rec->runAnalyzer(new HeaderAnalyzer);
        // Just check that we didn't crash.
        $this->assertNotNull($analysis);
    }

    /**
     * Test a scenario with complex trigger info. This Age of Heroes beta
     * version contains something like 700+ triggers.
     */
    public function testSkippingComplexTriggerInfo()
    {
        $rec = $this->load('recs/scenario/age-of-heroes.mgz');
        $analysis = $rec->runAnalyzer(new HeaderAnalyzer);
        // Just check that we didn't crash.
        $this->assertNotNull($analysis);
    }

    /**
     * Test a single-player campaign game in HD Edition Patch 4+.
     */
    public function testAoe2RecordWithTriggerInfo()
    {
        $rec = $this->load('recs/versions/SP Replay v4.6 @2015.12.29 001221.aoe2record');
        $analysis = $rec->runAnalyzer(new HeaderAnalyzer);
        // Just check that we didn't crash.
        $this->assertNotNull($analysis);
    }

    public function testAoe2Record()
    {
        $rec = $this->load('recs/versions/HD Tourney r1 robo_boro vs Dutch Class g1.aoe2record');
        $analysis = $rec->runAnalyzer(new HeaderAnalyzer);
        $this->assertAttributeEquals(1, 'lockDiplomacy', $analysis->gameSettings);
        $this->assertAttributeEquals(GameSettings::LEVEL_EASIEST, 'difficultyLevel', $analysis->gameSettings);
        $this->assertAttributeEquals(28, 'mapId', $analysis->gameSettings);

        $this->assertAttributeContains('Conquest Game', 'instructions', $analysis->messages);
    }

    public function testAoe2RecordVictorySettings()
    {
        $rec = $this->load('recs/versions/HD Tourney r1 robo_boro vs Dutch Class g1.aoe2record');
        $analysis = $rec->runAnalyzer(new HeaderAnalyzer);

        $this->assertAttributeEquals(VictorySettings::STANDARD, 'mode', $analysis->victory);
        $this->assertAttributeEquals(900, 'scoreLimit', $analysis->victory);
        $this->assertAttributeEquals(9000, 'timeLimit', $analysis->victory);
    }

    /**
     * Skipping AI in HD edition 5.0.
     */
    public function testAoe2RecordWithAi()
    {
        $rec = $this->load('recs/versions/SP Replay v5.0 @2016.12.21 111710.aoe2record');
        $analysis = $rec->runAnalyzer(new HeaderAnalyzer);
        // Just check that we didn't crash.
        $this->assertNotNull($analysis);
    }

    /**
     * @dataProvider chatCountsProvider
     */
    public function testChat($file, $expectedCount)
    {
        $rec = $this->load($file);
        $analysis = $rec->runAnalyzer(new HeaderAnalyzer);
        $this->assertCount($expectedCount, $analysis->pregameChat);
    }

    public function testCoopChats()
    {
        $rec = $this->load('recs/chat/TCC R5 Team Picon vs Combat Wombats G3.mgx2');
        $analysis = $rec->runAnalyzer(new HeaderAnalyzer);
        $l = count($analysis->pregameChat) - 1;

        // Three performance warning notifications from different players:
        for ($i = 2; $i < 5; $i += 1) {
            $this->assertStringMatchesFormat(
                'Performance warning: There is moderate latency between %s and %s. This will hinder the speed of the match.',
                $analysis->pregameChat[$l - $i]->msg
            );
        }

        $this->assertAttributeEquals('hf gl', 'msg', $analysis->pregameChat[$l - 1]);
        $this->assertAttributeEquals('gl hf', 'msg', $analysis->pregameChat[$l]);
    }

    public function testCoops()
    {
        $rec = $this->load('recs/coop/coop.mgx');
        $players = $rec->runAnalyzer(new HeaderAnalyzer)->players;

        $this->assertAttributeEquals(1, 'index', $players[0]);
        $this->assertAttributeEquals(2, 'index', $players[1]);
        $this->assertAttributeEquals(2, 'index', $players[2]);
        $this->assertAttributeEquals(1, 'index', $players[3]);

        $this->assertTrue($players[0]->isCooping());
        $this->assertTrue($players[1]->isCooping());
        $this->assertTrue($players[2]->isCooping());
        $this->assertTrue($players[3]->isCooping());

        // Check that coop main/partner are defined correctly.
        $this->assertTrue($players[0]->isCoopMain());
        $this->assertFalse($players[0]->isCoopPartner());
        $this->assertTrue($players[1]->isCoopMain());
        $this->assertFalse($players[1]->isCoopPartner());
        $this->assertTrue($players[2]->isCoopPartner());
        $this->assertFalse($players[2]->isCoopMain());
        $this->assertTrue($players[3]->isCoopPartner());
        $this->assertFalse($players[3]->isCoopMain());

        $rec = $this->load('recs/FluffyFur+yousifr+TheBlackWinds+Mobius_One[Chinese]=VS=MOD3000+Chrazini+ClosedLoop+ [AGM]Wineup[Britons]_1v1_8PlayerCo-op_01222015.mgx2');
        $players = $rec->runAnalyzer(new HeaderAnalyzer)->players;

        // Check that coop partners are collected correctly.
        $partners = $players[0]->getCoopPartners();
        $this->assertCount(3, $partners);
        $partners = $players[7]->getCoopPartners();
        $this->assertCount(3, $partners);

        // Check that coop mains are returned correctly.
        $this->assertEquals(
            $players[6]->getCoopMain(),
            $players[0]
        );
        $this->assertContains($players[6], $players[0]->getCoopPartners());
    }

    public function playersProvider()
    {
        return [
            ['./recs/versions/HD_test.mgx', [
                ['team' => 3, 'name' => 'ZeroEmpires'],
                ['team' => 2, 'name' => 'Befbeer'],
                ['team' => 2, 'name' => 'dark_knight1907'],
                ['team' => 3, 'name' => 'Idle Beaver'],
                ['team' => 3, 'name' => 'Hand Banana'],
                ['team' => 3, 'name' => 'Iso'],
                ['team' => 2, 'name' => 'JJEL'],
                ['team' => 2, 'name' => 'SudsNDeath'],
            ], 8],
            ['./recs/versions/aok.mgl', [
                ['team' => 1, 'name' => 'AoE2_K_Master'],
                ['team' => 2, 'name' => 'Elsakar'],
                ['team' => 1, 'name' => 'AOKH_Washizu'],
                ['team' => 2, 'name' => 'Baked_potato_'],
            ], 4],
            ['./recs/versions/up1.4.mgz', [
                ['name' => 'Zuppi'],
                ['name' => 'JorDan_23'],
            ], 2],
            ['./recs/versions/up1.5.mgz', [
                ['name' => 'Myth'],
                ['name' => 'Louis IX'],
                ['name' => 'Athelred the Unready'],
            ], 3],
            ['./recs/ai/Lobsth_15-pop-scouts_ai.mgx', [
                ['name' => 'Eternal Lobster'],
                ['name' => 'Ernak the Hun'],
            ], 2],
            ['./recs/ai/20141214_blutze(mong)+ffraid(pers) vs bots(goth+chin).mgx2', [
                ['name' => 'Purpleblutzicle'],
                ['name' => 'Ffraid'],
                ['name' => 'Li Shi-min'],
                ['name' => 'Theodoric the Goth'],
            ], 4],
            ['./recs/FluffyFur+yousifr+TheBlackWinds+Mobius_One[Chinese]=VS=MOD3000+Chrazini+ClosedLoop+ [AGM]Wineup[Britons]_1v1_8PlayerCo-op_01222015.mgx2', [
                ['team' => 1, 'isCooping' => true, 'name' => 'Mobius One'],
                ['team' => 2, 'isCooping' => true, 'name' => 'MOD3000'],
                ['team' => 1, 'isCooping' => true, 'name' => 'TheBlackWinds'],
                ['team' => 1, 'isCooping' => true, 'name' => 'yousifr'],
                ['team' => 2, 'isCooping' => true, 'name' => 'Chrazini'],
                ['team' => 2, 'isCooping' => true, 'name' => 'ClosedLoop'],
                ['team' => 1, 'isCooping' => true, 'name' => 'FluffyFur'],
                ['team' => 2, 'isCooping' => true, 'name' => '[AGM]Wineup'],
            ], 2],
            ['./recs/versions/HD Tourney r1 robo_boro vs Dutch Class g1.aoe2record', [
                ['name' => 'Dutch Class', 'colorId' => 0],
                ['name' => 'robo_boro', 'colorId' => 4],
            ], 2],
            ['./recs/versions/Kingdoms Britons v Britons - SP Replay v4.6 @2016.05.05 130519.aoe2record', [
                ['civId' => 1, 'name' => 'Idle Beaver'],
                ['civId' => 1, 'name' => 'Duke of Normandy (AI)'],
            ], 2],
            ['./recs/versions/MP_Replay_v4.8_2016.11.03_221821_2.aoe2record', [
                ['civId' => 6, 'name' => 'Nobody'],
                ['civId' => 25, 'name' => 'TWest'],
            ], 2],
            ['./recs/versions/HD Tourney Winner Final robo vs Klavskis g1.aoe2record', [
                ['civId' => 11, 'name' => 'robo_boro'],
                ['civId' => 11, 'name' => 'Klavskis'],
            ], 2],
            ['./recs/versions/HD Tourney Winner Final robo vs Klavskis g2.aoe2record', [
                ['civId' => 21, 'name' => 'robo_boro'],
                ['civId' => 21, 'name' => 'Klavskis'],
            ], 2],
            ['./recs/versions/HD Tourney Winner Final robo vs Klavskis g3.aoe2record', [
                ['civId' => 7, 'name' => 'robo_boro'],
                ['civId' => 7, 'name' => 'Klavskis'],
            ], 2],
            ['./recs/versions/HD Tourney Winner Final robo vs Klavskis g4.aoe2record', [
                ['civId' => 15, 'name' => 'robo_boro'],
                ['civId' => 15, 'name' => 'Klavskis'],
            ], 2],
            ['./recs/ECL EUE 1v1 GF reallydiao (PoV) vs TheViper g1.mgz', [
                ['name' => 'TheViper'],
                ['name' => 'ReallyDiao'],
            ], 2]
        ];
    }

    public function chatCountsProvider()
    {
        return [
            ['recs/versions/HD Tourney r1 robo_boro vs Dutch Class g1.aoe2record', 13],
            ['recs/versions/HD_test.mgx', 50],
        ];
    }
}
