<?php

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
        escapeshellarg($agedir . '/resources/_common/dat/empires2_x1_p1.dat');
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
        'resources' => range(4301, 4304),
        'game_speeds' => range(9432, 9434),
        'reveal_map' => range(9755, 9757),
        'civilizations' => range(10221, 10247),
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
                '<?php return ' . var_export($strings, true) . ';'
            );
        }
    }
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

if (is_file(__DIR__ . '/cache.php')) {
    $data = require(__DIR__ . '/cache.php');
} else {
    if (!is_dir(__DIR__ . '/openage')) {
        downloadOpenage();
    }

    echo "loading research and unit data from empires2.dat ...\n";
    $data = loadResearchAndUnitData($agedir);
    echo "done \n";

    file_put_contents(__DIR__ . '/cache.php',
        '<?php return ' . var_export($data, true) . ';'
    );
}

generateLanguageFiles($agedir, $data);
