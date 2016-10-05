<?php

use PHPUnit\Framework\TestCase;
use Webmozart\PathUtil\Path;
use RecAnalyst\RecordedGame;
use RecAnalyst\GameInfo;
use RecAnalyst\Analyzers\BodyAnalyzer;

class BodyAnalyzerTest extends TestCase
{
    /**
     * @dataProvider recordsProvider
     */
    public function testParse($file)
    {
        $rec = new RecordedGame(Path::join(__DIR__, $file));
        $analysis = $rec->runAnalyzer(new BodyAnalyzer);
        $this->assertTrue($analysis->duration > 0);
    }

    public function recordsProvider()
    {
        return [
            ['./recs/versions/aok.mgl'],
            ['./recs/versions/HD_test.mgx'],
            ['./recs/versions/HD-FE.mgx2'],
            ['./recs/up1.4.mgz'],
        ];
    }
}
