<?php

use PHPUnit\Framework\TestCase;
use Webmozart\PathUtil\Path;
use RecAnalyst\RecordedGame;
use RecAnalyst\Model\Version;
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
        $this->assertEquals(
            $expectedVersion, $version->version,
            sprintf('Expected \'%s\' to have version \'%d\'.', $file, $expectedVersion)
        );
        foreach ($expectedProps as $prop => $value) {
            $this->assertEquals(
                $value, $version->$prop,
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
            ['./recs/versions/aok.mgl', Version::VERSION_AOK, [
                'isMgl' => true,
                'isMgx' => false,
                'isMgz' => false,
                'isMsx' => false,
                'isAoK' => true,
                'isAoC' => false,
                'isAoe2Record' => false,
                'isHDEdition' => false,
            ]],
            ['./recs/versions/HD_test.mgx', Version::VERSION_HD, [
                'isAoC' => true,
                'isHDEdition' => true,
                'isHDPatch4' => false,
                'isMsx' => false,
                'isAoe2Record' => false,
            ]],
            ['./recs/versions/HD-FE.mgx2', Version::VERSION_HD, [
                'isHDEdition' => true,
                'isHDPatch4' => false,
                'isMsx' => false,
                'isAoe2Record' => false,
            ]],
            ['./recs/versions/mgx2_simple.mgx2', Version::VERSION_HD, [
                'isHDEdition' => true,
                'isHDPatch4' => true,
                'isMsx' => false,
                'isAoe2Record' => false,
            ]],
            ['./recs/versions/MP Replay v4.3 @2015.09.11 221142 (2).msx', Version::VERSION_HD43, [
                'isHDEdition' => true,
                'isHDPatch4' => true,
                'isAoe2Record' => false,
                'isMsx' => true,
            ]],
            ['./recs/versions/MP_Replay_v4.msx2', Version::VERSION_HD43, [
                'isHDEdition' => true,
                'isHDPatch4' => true,
                'isAoe2Record' => false,
                'isMsx' => true,
            ]],
            ['./recs/versions/SP Replay v4.6 @2016.05.05 130050.aoe2record', Version::VERSION_HD46, [
                'isHDEdition' => true,
                'isHDPatch4' => true,
                'isAoe2Record' => true,
            ]]
        ];
    }
}
