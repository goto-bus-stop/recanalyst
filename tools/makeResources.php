<?php

function array_merge_preserving_keys()
{
    $arrays = func_get_args();
    $result = [];
    foreach ($arrays as $array) {
        foreach ($array as $key => $value) {
            $result[$key] = $value;
        }
    }
    return $result;
}

function downloadOpenage()
{
    $shell = 'git clone ' .
        '--depth 1 ' .
        'https://github.com/sfttech/openage ' .
        escapeshellarg(__DIR__ . '/openage');
    echo "> $shell\n";
    shell_exec($shell);
}

/**
 * Use Openage's convert script to extract language and SLP index data.
 */
function loadResearchAndUnitData($agedir)
{
    $shell = 'python3 ' .
        escapeshellarg(__DIR__ . '/parse_empiresdat.py') . ' ' .
        escapeshellarg($agedir . '/resources/_common/dat/empires2_x2_p1.dat');
    echo "> $shell\n";
    $json = shell_exec($shell);
    return json_decode($json, true);
}

/**
 * Build language files for all available languages.
 */
function generateLanguageFiles($agedir, $data)
{
    $stringIndices = [
        'ages' => range(4201, 4205),
        'game_types' => [
            9226,
            9228,
            9227,
            9230,
            9229,
            9762,
            9761,
            9763,
            9764,
        ],
        'map_styles' => [
            13561,
            13543,
            13562,
            'SPECIAL_MAPS_LABEL',
        ],
        'map_names' => array_merge_preserving_keys(
            // Standard maps
            array_combine(
                range(9, 32), // Map IDs
                range(10875, 10898) // string indices
            ),
            // Nomad
            [ 33 => 10901 ],
            // Real world maps
            array_combine(
                range(34, 43),
                range(13544, 13553)
            ),
            // Custom
            [ 44 => 13562 ],
            // Blind random
            [ 48 => 10902 ],
            // Forgotten Empires maps
            array_combine(
                range(49, 64),
                range(10914, 10929)
            ),
            // Forgotten Empires maps in African Kingdoms
            array_combine(
                range(66, 81),
                range(10914, 10929)
            ),
            // African Kingdoms maps
            [
                82 => 'RMS_KILIMANJARO',
                83 => 'RMS_MOUNTAINPASS',
                84 => 'RMS_NILEDELTA',
                85 => 'RMS_SERENGETI',
                86 => 'RMS_SOCOTRA',
            ],
            // African Kingdoms Real World maps
            [
                87 => 'RWM_AMAZON',
                88 => 'RWM_CHINA',
                89 => 'RWM_HORNOFAFRICA',
                90 => 'RWM_INDIA',
                91 => 'RWM_MADAGASCAR',
                92 => 'RWM_WESTAFRICA',
                93 => 'RWM_BOHEMIA',
                94 => 'RWM_EARTH',
            ],
            // African Kingdoms special maps
            [
                95 => 'SPECIALMAP_CANYONS',
                96 => 'SPECIALMAP_ENEMYARCHIPELAGO',
                97 => 'SPECIALMAP_ENEMYISLANDS',
                98 => 'SPECIALMAP_FAROUT',
                99 => 'SPECIALMAP_FRONTLINE',
                100 => 'SPECIALMAP_INNERCIRCLE',
                101 => 'SPECIALMAP_MOTHERLAND',
                102 => 'SPECIALMAP_OPENPLAINS',
                103 => 'SPECIALMAP_RINGOFWATER',
                104 => 'SPECIALMAP_SNAKEPIT',
                105 => 'SPECIALMAP_THEEYE',
            ]
        ),
        'resources' => range(4301, 4304),
        'game_speeds' => range(9432, 9434),
        'reveal_map' => range(9755, 9757),
        'civilizations' => range(10270, 10318),
        'map_sizes' => range(10611, 10617),
        'difficulties' => range(11216, 11220),
    ];

    printf("found %d units and %d researches\n", count($data['units']), count($data['researches']));
    $stringIndices['units'] = [];
    foreach ($data['units'] as $id => $unit) {
        $stringIndices['units'][$id] = $unit['name'];
    }
    $stringIndices['researches'] = [];
    foreach ($data['researches'] as $id => $research) {
        $stringIndices['researches'][$id] = $research['name'];
    }

    $langFile = '/strings/key-value/key-value-strings-utf8.txt';

    echo "building dictionary\n";
    $dictionary = [];
    foreach (glob($agedir . '/resources/*') as $dir) {
        if (file_exists($dir . $langFile)) {
            $language = basename($dir);
            $dictionary[$language] = [];
            foreach (file($dir . $langFile) as $line) {
                $line = trim($line);
                if (!$line) {
                    continue;
                }
                if ($line[0] === '/' && $line[1] === '/') {
                    continue;
                }
                list ($key, $value) = preg_split('/\s+/', $line, 2);
                $dictionary[$language][$key] = substr($value, 1, -1);
            }
        }
    }

    $output = [];
    foreach ($dictionary as $lang => $strings) {
        $output[$lang] = [
            'ageofempires' => [],
        ];
        foreach ($stringIndices as $file => $indices) {
            $output[$lang]['ageofempires'][$file] =
                array_filter(array_map(function ($index) use (&$strings) {
                    return isset($strings[$index]) ? str_replace('\n', ' ', $strings[$index]) : false;
                }, $indices));
        }
    }

    $outputPath = __DIR__ . '/../resources/lang';
    foreach ($output as $lang => $files) {
        if (!is_dir($outputPath . '/' . $lang)) {
            mkdir($outputPath . '/' . $lang, 0777, true);
        }
        foreach ($files as $filename => $strings) {
            $outfile = $outputPath . '/' . $lang . '/' . $filename . '.php';
            echo "writing $outfile ...\n";
            file_put_contents($outfile,
                '<?php ' . "\n" .
                '// This file is automatically generated. DO NOT EDIT.' . "\n" .
                'return ' . var_export($strings, true) . ';'
            );
        }
    }
}

function slpRender($file, $outdir, $color = 1)
{
    $slpRender = __DIR__ . '/../node_modules/.bin/slp-render';
    $shell =
        escapeshellarg($slpRender) . ' ' .
        escapeshellarg($file) . ' ' .
        escapeshellarg($outdir) . ' ' .
        '-p ' . $color;
    echo "> $shell\n";
    shell_exec($shell);
}

function generateImages($agedir, $data)
{
    $uniqueUnits = [
        'Britons' => 41,
        'Franks' => 46,
        'Goths' => 50,
        'Teutons' => 45,
        'Japanese' => 44,
        'Chinese' => 36,
        'Byzantines' => 35,
        'Persians' => 43,
        'Saracens' => 37,
        'Turks' => 39,
        'Vikings' => 38,
        'Mongols' => 42,
        'Celts' => 47,
        'Spanish' => 106,
        'Aztecs' => 110,
        'Mayans' => 108,
        'Huns' => 105,
        'Koreans' => 117,
        'Italians' => 133,
        'Indians' => 93,
        'Incas' => 97,
        'Magyars' => 99,
        'Slavs' => 114,
        'Portuguese' => 190,
        'Ethiopians' => 195,
        'Malians' => 197,
        'Berbers' => 191,
        'Burmese' => 230,
        'Khmer' => 231,
        'Vietnamese' => 232,
        'Malay' => 233,
    ];
    $strings = require(__DIR__ . '/../resources/lang/en/ageofempires.php');
    $civNames = $strings['civilizations'];
    unset($civNames[0]);

    $researchesDir = __DIR__ . '/../resources/images/researches';
    $civsDir = __DIR__ . '/../resources/images/civs';
    $tempDir = __DIR__ . '/tmp';

    if (!is_dir($researchesDir)) {
        mkdir($researchesDir, 0777, true);
    }

    echo "rendering research icons ...\n";
    slpRender($agedir . '/resources/_common/drs/gamedata_x2/50729.slp', $tempDir);

    echo "renaming research icons ... ";
    foreach ($data['researches'] as $id => $research) {
        $in = $tempDir . '/' . $research['graphic'] . '.png';
        $out = $researchesDir . '/' . $id . '.png';

        echo "$id, ";
        if (file_exists($in)) {
            copy($in, $out);
        }
    }
    echo " done \n";

    for ($color = 0; $color < 8; $color++) {
        printf("rendering civ images %d/8\n", $color + 1);
        slpRender(
            $agedir . '/resources/_common/drs/gamedata_x2/50730.slp',
            $tempDir,
            $color + 1
        );

        if (!is_dir($civsDir . '/' . $color)) {
            mkdir($civsDir . '/' . $color, 0777, true);
        }
        foreach ($civNames as $id => $civName) {
            if (!isset($uniqueUnits[$civName])) {
                continue;
            }
            $unitIconId = $uniqueUnits[$civName];
            $in = $tempDir . '/' . $unitIconId . '.png';
            $out = $civsDir . '/' . $color . '/' . $id . '.png';
            if (file_exists($in)) {
                rename($in, $out);
            }
        }
    }
}

function generateColors($agedir, $data)
{
    $dataDir = __DIR__ . '/../resources/data/ageofempires';
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0777, true);
    }

    $palettePath = $agedir . '/resources/_common/drs/interface/50500.bina';
    $palette = [];
    // Skip the first three lines of metadata
    $lines = array_slice(file($palettePath), 3);
    foreach ($lines as $i => $line) {
        $parts = explode(' ', $line);
        if (count($parts) === 3) {
            $palette[$i] = vsprintf('#%02x%02x%02x', $parts);
        }
    }

    $colors = [
        'terrain' => [],
        'players' => [],
    ];
    foreach ($data['terrain_colors'] as $id => $terrain) {
        $minimapColors = array_slice($terrain['minimap'], 0, 3);
        $colors['terrain'][$id] = array_map(function ($index) use (&$palette) {
            return $palette[$index];
        }, $minimapColors);
    }
    $colors['players'] = array_map(function ($index) use (&$palette) {
        return $palette[$index];
    }, $data['player_colors']);

    file_put_contents($dataDir . '/colors.php',
        '<?php ' . "\n" .
        '// This file is automatically generated. DO NOT EDIT.' . "\n" .
        'return ' . var_export($colors, true) . ';'
    );
}

if (count($argv) < 2) {
    echo '
Please provide an Age of Empires 2 HD installation directory. e.g.:

    $ composer run-script make-resources ~/.wine/drive_c/Age2HD/

';
    die(1);
}

$agedir = rtrim($argv[1], '/');

if (!is_file($agedir . '/AoK HD.exe')) {
    echo '
That does not look like an Age of Empires 2 HD installation directory.

';
    die(1);
}

if (!is_file(__DIR__ . '/../node_modules/.bin/slp-render')) {
    shell_exec('npm install');
}

if (is_file(__DIR__ . '/cache.php')) {
    $data = require(__DIR__ . '/cache.php');
} else {
    if (!is_dir(__DIR__ . '/openage')) {
        downloadOpenage();
    }

    echo "loading research and unit data from empires2.dat ...\n";
    $data = loadResearchAndUnitData($agedir);
    if (!$data) {
        die(1);
    }
    echo "done \n";

    file_put_contents(__DIR__ . '/cache.php',
        '<?php return ' . var_export($data, true) . ';'
    );
}

generateLanguageFiles($agedir, $data);
generateImages($agedir, $data);
generateColors($agedir, $data);
