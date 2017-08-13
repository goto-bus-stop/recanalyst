<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>RecAnalyst Demo</title>
  <link rel="stylesheet" href="app.css">
</head>
<body class="nojs">
<?php

require __DIR__ . '/../vendor/autoload.php';

use Intervention\Image\ImageManagerStatic;
use RecAnalyst\RecAnalystConst;
use RecAnalyst\RecordedGame;
use Twig_Environment;
use Twig_Loader_Filesystem;

$loader = new Twig_Loader_Filesystem(__DIR__ . '/../');
$twig = new Twig_Environment($loader);

$twig->addFilter(new Twig_SimpleFilter('formatGameTime', '\RecAnalyst\Utils::formatGameTime'));

function buildResearchesTable($players) {
    $researches = [];
    foreach ($players as $player) {
        $researches[$player->index] = [];
    }
    $researchesByMinute = [];
    foreach ($players as $player) {
        foreach ($player->researches() as $research) {
            $minute = floor($research->time / 1000 / 60);
            $researchesByMinute[$minute][$player->index][] = $research;
        }
    }
    foreach ($researchesByMinute as $minute => $researchesByPlayer) {
        foreach ($players as $player) {
            $researches[$player->index][$minute] =
                $researchesByPlayer[$player->index] ?? [];
        }
    }
    foreach ($researches as &$timeline) {
        ksort($timeline, SORT_NUMERIC);
    }
    return $researches;
}

if (isset($_FILES['recorded_game'])) {
    $rec = new RecordedGame($_FILES['recorded_game']['tmp_name']);

    echo $twig->render('result.twig.html', [
        'rec' => $rec,
        'mapImage' => $rec->mapImage()->resize(360, 180)->encode('data-url'),
        'pov' => $rec->pov(),
        'achievements' => !!$rec->achievements(),
        'table' => buildResearchesTable($rec->players()),
    ]);
} else {
?>
  <div class="section">
    <form action=""
          method="POST"
          enctype="multipart/form-data"
          class="container"
          id="upload-form">
      <label class="label">Recorded Game File</label>
      <p class="control">
        <input class="input" type="file" name="recorded_game" id="upload-file">
      </p>
      <p class="control">
        <button class="button is-primary" type="submit" id="upload-button">Analyze</button>
      </p>
    </form>
  </div>
<?php
}
?>

  <footer class="footer" style="padding: 20px; margin-top: 20px">
    <div class="container">
      <p class="has-text-centered">
        <a href="https://github.com/goto-bus-stop/recanalyst">RecAnalyst on Github</a>
      </p>
    </div>
  </footer>

  <script src="css.escape.js"></script>
  <script src="app.js"></script>
</body>
</html>
