<?php

namespace RecAnalyst\Analyzers;

use RecAnalyst\GameInfo;

/**
 * Determine the game version that created the recorded game file.
 */
class VersionAnalyzer extends Analyzer
{
    /**
     * Trial game version IDs.
     *
     * @var int[]
     */
    public $trialVersions = [
        GameInfo::VERSION_AOKTRIAL,
        GameInfo::VERSION_AOCTRIAL,
    ];

    /**
     * UserPatch game version IDs.
     *
     * @var int[]
     */
    public $userpatchVersions = [
        GameInfo::VERSION_USERPATCH11,
        GameInfo::VERSION_USERPATCH12,
        GameInfo::VERSION_USERPATCH13,
        GameInfo::VERSION_USERPATCH14,
        GameInfo::VERSION_AOFE21,
    ];

    /**
     * Age of Kings game version IDs.
     *
     * @var int[]
     */
    public $aokVersions = [
        GameInfo::VERSION_AOK,
        GameInfo::VERSION_AOKTRIAL,
        GameInfo::VERSION_AOK20,
        GameInfo::VERSION_AOK20A,
    ];

    /**
     * Age of Conquerors expansion game version IDs.
     *
     * @var int[]
     */
    public $aocVersions = [
        GameInfo::VERSION_AOC,
        GameInfo::VERSION_AOCTRIAL,
        GameInfo::VERSION_AOC10,
        GameInfo::VERSION_AOC10C,
    ];

    /**
     * HD Edition game version IDs.
     *
     * @var int[]
     */
    public $hdVersions = [
        GameInfo::VERSION_HD
    ];

    /**
     * Game version names.
     *
     * @var array
     */
    public $versionNames = [
        'Unknown',
        'AOK',
        'AOK Trial',
        'AOK 2.0',
        'AOK 2.0a',
        'AOC',
        'AOC Trial',
        'AOC 1.0',
        'AOC 1.0c',
        'AOC 1.1',
        'AOFE 2.1',
        'AOC (UP 1.4)',
        'Unknown',
        'Unknown',
        'HD'
    ];


    /**
     * Read recorded game version metadata.
     *
     * Recorded game version information is encoded as:
     *     char version[8]; // something like "VER 9.C\0"
     *     float subVersion;
     *
     * @return object
     */
    protected function run()
    {
        $versionString = rtrim($this->readHeaderRaw(8));
        $subVersion = round($this->readHeader('f', 4), 2);

        $version = $this->getVersion($versionString, $subVersion);

        $analysis = new \StdClass;
        $analysis->versionString = $versionString;
        $analysis->version = $version;
        $analysis->subVersion = $subVersion;

        $analysis->isTrial = in_array($version, $this->trialVersions);
        $analysis->isAoK = in_array($version, $this->aokVersions);
        $analysis->isUserPatch = in_array($version, $this->userpatchVersions);
        $analysis->isHDEdition = in_array($version, $this->hdVersions);
        $analysis->isHDPatch4 = $analysis->isHDEdition && $subVersion >= 12.00;
        $analysis->isAoC = $analysis->isUserPatch || $analysis->isHDEdition ||
            in_array($version, $this->aocVersions);

        $analysis->isMgl = $analysis->isAoK;
        $analysis->isMgx = $analysis->isAoC;
        $analysis->isMgz = $analysis->isUserPatch;
        // FIXME Not sure if this is the correct cutoff point.
        //
        // test/recs/versions/mgx2_simple.mgx2
        //    has subVersion == 12.31
        // test/recs/versions/MP Replay v4.3 @2015.09.11 221142 (2).msx
        //    has subVersion == 12.34
        // So it's somewhere between those two.
        $analysis->isMsx = $subVersion >= 12.32;
        $analysis->isAoe2Record = $subVersion >= 12.36;

        $analysis->name = $this->versionNames[$version];

        return $analysis;
    }

    /**
     * Get the version ID from a version string and sub-version number.
     *
     * @param string  $version  Version string, found at the start of the file
     *     header.
     * @param float  $subVersion  Sub-version number.
     * @return int Game version ID.
     */
    private function getVersion($version, $subVersion)
    {
        switch ($version) {
            case 'TRL 9.3':
                return $this->isMgx ? GameInfo::VERSION_AOCTRIAL : GameInfo::VERSION_AOKTRIAL;
            case 'VER 9.3':
                return GameInfo::VERSION_AOK;
            case 'VER 9.4':
                if ($subVersion > 11.76) {
                    return GameInfo::VERSION_HD;
                }
                return GameInfo::VERSION_AOC;
            case 'VER 9.5':
                return GameInfo::VERSION_AOFE21;
            case 'VER 9.8':
                return GameInfo::VERSION_USERPATCH12;
            case 'VER 9.9':
                return GameInfo::VERSION_USERPATCH13;
            // UserPatch 1.4 RC 1
            case 'VER 9.A':
            // UserPatch 1.4 RC 2
            case 'VER 9.B':
            case 'VER 9.C':
            case 'VER 9.D':
                return GameInfo::VERSION_USERPATCH14;
            default:
                return $version;
        }
    }
}
