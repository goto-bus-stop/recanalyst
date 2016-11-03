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
                // TODO Is this ordering new in HD Edition? Here, the "main" players
                // are at the end, and the coop partners at the start of the players
                // array; in older AoC versions, the "main" players were at the start,
                // and coop partners at the end.
                ['team' => 1, 'isCooping' => false, 'name' => 'FluffyFur'],
                ['team' => 2, 'isCooping' => false, 'name' => '[AGM]Wineup'],
            ], 2],
            ['./recs/versions/HD Tourney r1 robo_boro vs Dutch Class g1.aoe2record', [
                ['name' => 'Dutch Class', 'colorId' => 0],
                ['name' => 'robo_boro', 'colorId' => 4],
            ], 2],
            ['./recs/versions/Kingdoms Britons v Britons - SP Replay v4.6 @2016.05.05 130519.aoe2record', [
                ['civId' => 1, 'name' => 'Idle Beaver'],
                ['civId' => 1, 'name' => 'Duke of Normandy (AI)'],
            ], 2],
        ];
    }
}
