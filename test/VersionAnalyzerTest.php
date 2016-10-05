<?php

use PHPUnit\Framework\TestCase;
use Webmozart\PathUtil\Path;
use RecAnalyst\RecordedGame;
use RecAnalyst\GameInfo;
use RecAnalyst\Analyzers\VersionAnalyzer;

class VersionAnalyzerTest extends TestCase
{
    /**
     * @dataProvider versionsProvider
     */
    public function testVersion($file, $expectedVersion, $expectedProps)
    {
        $rec = new RecordedGame(Path::join(__DIR__, $file));
        $version = $rec->runAnalyzer(new VersionAnalyzer);
        $this->assertAttributeEquals(
            $expectedVersion, 'version', $version,
            sprintf('Expected \'%s\' to have version \'%d\'.', $file, $expectedVersion)
        );
        foreach ($expectedProps as $prop => $value) {
            $this->assertAttributeEquals(
                $value, $prop, $version,
                sprintf(
                    'Expected \'%s\' version to %s property \'%s\'.',
                    $file,
                    $value ? 'have' : 'not have',
                    $prop
                )
            );
        }
    }

    public function versionsProvider()
    {
        return [
            ['./recs/versions/aok.mgl', GameInfo::VERSION_AOK, [
                'isAoK' => true,
                'isAoC' => false,
                'isMgl' => true,
            ]],
            ['./recs/versions/HD_test.mgx', GameInfo::VERSION_HD, [
                'isAoC' => true,
                'isHDEdition' => true,
            ]],
            ['./recs/versions/HD-FE.mgx2', GameInfo::VERSION_HD, [
                'isHDEdition' => true,
            ]],
        ];
    }
}
