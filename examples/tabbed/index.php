<?php

require __DIR__ . '/vendor/autoload.php';

use Intervention\Image\ImageManagerStatic;
use RecAnalyst\RecAnalystConst;
use RecAnalyst\RecordedGame;
use Twig_Environment;
use Twig_Loader_Array;

// Read a recorded game from a file path.
$rec = new RecordedGame(__DIR__ . '/../../test/recs/versions/HD_test.mgx');

// Configure Twig.
$loader = new Twig_Loader_Array([
    // In a real app, the Twig filesystem loader should probably be used,
    // but for demonstration purposes here we load the template manually.
    'results' => file_get_contents(__DIR__ . '/template.html'),
]);
$twig = new Twig_Environment($loader);

// Add some Twig filters.
$twig->addFilter(new Twig_SimpleFilter('formatGameTime', '\RecAnalyst\Utils::formatGameTime'));

$twig->addFilter(new Twig_SimpleFilter('getResearchImage', function ($research) {
    $path = __DIR__ . '/../../resources/images/researches/' . $research->id . '.png';
    if (is_file($path)) {
        return ImageManagerStatic::make($path)->encode('data-url');
    }
    return '';
}));

echo $twig->render('results', [
    'mapImage' => $rec->mapImage()->resize(360, 180)->encode('data-url'),
    'rec' => $rec,
]);
