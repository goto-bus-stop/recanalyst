<?php

copy_dir(
    __DIR__ . '/vendor/recanalyst/recanalyst/resources/images',
    __DIR__ . '/public/vendor/recanalyst');

function copy_dir($source, $target)
{
    $files = glob($source . '/*');
    if (!is_dir($target)) {
        mkdir($target, 0777, true);
    }
    foreach ($files as $file) {
        $name = basename($file);
        if (is_dir($file)) {
            copy_dir($file, "{$target}/{$name}");
        } else {
            copy($file, "{$target}/{$name}");
        }
    }
}
