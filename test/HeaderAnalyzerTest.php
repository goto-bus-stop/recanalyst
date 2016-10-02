<?php

use PHPUnit\Framework\TestCase;
use Webmozart\PathUtil\Path;
use RecAnalyst\RecordedGame;
use RecAnalyst\GameInfo;
use RecAnalyst\Analyzers\HeaderAnalyzer;

class HeaderAnalyzerTest extends TestCase
{
    /**
     * @dataProvider playersProvider
     */
    public function testPlayers($file, $expected)
    {
        $rec = new RecordedGame(Path::join(__DIR__, $file));
        $analysis = $rec->runAnalyzer(new HeaderAnalyzer);
        $this->assertAttributeEquals(count($expected), 'numPlayers', $analysis);
        foreach ($expected as $index => $player) {
            foreach ($player as $prop => $value) {
                $this->assertAttributeEquals($value, $prop, $analysis->players[$index]);
            }
        }
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
            ]],
        ];
    }
}
