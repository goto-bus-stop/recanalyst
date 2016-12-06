<?php

use Webmozart\PathUtil\Path;
use PHPUnit\Framework\TestCase;

use RecAnalyst\RecordedGame;

class MapNameTest extends TestCase
{
    private function load($path)
    {
        return new RecordedGame(Path::join(__DIR__, $path));
    }

    public function testCustomMapNameExtract()
    {
        $rec = $this->load('recs/game-settings/[AoFE-FF DE R1] RoOk_FaLCoN - [Pervert]Moneimon (pov) G1.mgz');
        $this->assertEquals($rec->gameSettings()->mapName(), 'Acropolis');
        $this->assertEquals($rec->gameSettings()->mapName([
            'extractRMSName' => false
        ]), 'Custom');
    }

    public function testNonEnglishMapNameExtract()
    {
        $rec = $this->load('recs/game-settings/rec.20140311-034826.mgz');
        $this->assertEquals($rec->gameSettings()->mapName(), 'Golden Pit');
        $this->assertEquals($rec->gameSettings()->mapName([
            'extractRMSName' => false
        ]), 'Custom');
    }
}
