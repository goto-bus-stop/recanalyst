<?php

use PHPUnit\Framework\TestCase;
use Webmozart\PathUtil\Path;
use RecAnalyst\RecordedGame;
use RecAnalyst\Analyzers\HeaderAnalyzer;

class HeaderAnalyzerTest extends TestCase
{
    /**
     * @dataProvider gamesProvider
     */
    public function testBasic($file)
    {
        $rec = new RecordedGame($file);
        // Just make sure it doesn't crash!
        $analysis = $rec->runAnalyzer(new HeaderAnalyzer);

        $this->assertNotNull($analysis);
    }

    /**
     * @dataProvider playersProvider
     */
    public function testPlayers($file, $expected, $expectedCount)
    {
        $rec = new RecordedGame(Path::join(__DIR__, $file));
        $analysis = $rec->runAnalyzer(new HeaderAnalyzer);
        $this->assertAttributeEquals($expectedCount, 'numPlayers', $analysis);
        foreach ($expected as $index => $player) {
            foreach ($player as $prop => $value) {
                $this->assertAttributeEquals($value, $prop, $analysis->players[$index]);
            }
        }
    }

    public function gamesProvider()
    {
        $files = glob(Path::makeRelative(
            Path::join(__DIR__, './recs/{forgotten,versions,ai}/*'),
            getcwd()
        ), GLOB_BRACE);

        $provides = [];
        foreach ($files as $path) {
            $provides[$path] = [$path];
        }
        return $provides;
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
            ], 2]
        ];
    }
}
