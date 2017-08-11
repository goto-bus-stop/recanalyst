<?php

namespace RecAnalyst\Analyzers;

use RecAnalyst\Model\Version;

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
        Version::VERSION_AOKTRIAL,
        Version::VERSION_AOCTRIAL,
    ];

    /**
     * UserPatch game version IDs.
     *
     * @var int[]
     */
    public $userpatchVersions = [
        Version::VERSION_USERPATCH11,
        Version::VERSION_USERPATCH12,
        Version::VERSION_USERPATCH13,
        Version::VERSION_USERPATCH14,
        Version::VERSION_AOFE21,
    ];

    /**
     * Age of Kings game version IDs.
     *
     * @var int[]
     */
    public $aokVersions = [
        Version::VERSION_AOK,
        Version::VERSION_AOKTRIAL,
        Version::VERSION_AOK20,
        Version::VERSION_AOK20A,
    ];

    /**
     * Age of Conquerors expansion game version IDs.
     *
     * @var int[]
     */
    public $aocVersions = [
        Version::VERSION_AOC,
        Version::VERSION_AOCTRIAL,
        Version::VERSION_AOC10,
        Version::VERSION_AOC10C,
    ];

    /**
     * HD Edition game version IDs.
     *
     * @var int[]
     */
    public $hdVersions = [
        Version::VERSION_HD,
        Version::VERSION_HD43,
        Version::VERSION_HD46,
        // Currently unused: HD 4.6 and 4.7 use the same file format, so we can't
        // easily detect which one it is.
        Version::VERSION_HD47,
        Version::VERSION_HD48,
        Version::VERSION_HD50
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

        $analysis = new Version($this->rec, $versionString, $subVersion);
        $analysis->version = $version;

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
                return $this->isMgx ? Version::VERSION_AOCTRIAL : Version::VERSION_AOKTRIAL;
            case 'VER 9.3':
                return Version::VERSION_AOK;
            case 'VER 9.4':
                if ($subVersion >= 12.50) {
                    return Version::VERSION_HD50;
                }
                if ($subVersion >= 12.49) {
                    return Version::VERSION_HD48;
                }
                if ($subVersion >= 12.36) {
                    // Patch versions 4.6 and 4.7.
                    return Version::VERSION_HD46;
                }
                if ($subVersion >= 12.34) {
                    // Probably versions 4.3 through 4.5?
                    return Version::VERSION_HD43;
                }
                if ($subVersion > 11.76) {
                    return Version::VERSION_HD;
                }
                return Version::VERSION_AOC;
            case 'VER 9.5':
                return Version::VERSION_AOFE21;
            case 'VER 9.8':
                return Version::VERSION_USERPATCH12;
            case 'VER 9.9':
                return Version::VERSION_USERPATCH13;
            // UserPatch 1.4 RC 1
            case 'VER 9.A':
            // UserPatch 1.4 RC 2
            case 'VER 9.B':
            case 'VER 9.C':
            case 'VER 9.D':
                return Version::VERSION_USERPATCH14;
            default:
                return $version;
        }
    }
}
