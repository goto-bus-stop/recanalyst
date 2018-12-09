<?php

require __DIR__.'/vendor/classpreloader/classpreloader/src/Config.php';
require __DIR__.'/vendor/classpreloader/classpreloader/src/ClassNode.php';
require __DIR__.'/vendor/classpreloader/classpreloader/src/ClassList.php';
require __DIR__.'/vendor/classpreloader/classpreloader/src/ClassLoader.php';

use ClassPreloader\ClassLoader;

$config = ClassLoader::getIncludes(function (ClassLoader $loader) {
    require __DIR__.'/vendor/autoload.php';
    $loader->register();
    $rec = new RecAnalyst\RecordedGame('./test/recs/versions/up1.4.mgz');
	$rec->header();
	$rec->body();
	$rec->mapImage();
	$rec->achievements();
});

return $config;
