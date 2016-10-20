<?php

/**
 * Configuration for the Sami documentation generator.
 */

use Sami\Sami;
use Sami\Version\GitVersionCollection;
use Sami\RemoteRepository\GitHubRemoteRepository;

$base = __DIR__ . '/..';
$dir = $base . '/src';

return new Sami($dir, [
    'title' => 'RecAnalyst API',
    'build_dir' => $base . '/doc',
    'cache_dir' => __DIR__ . '/tmp',
    'remote_repository' => new GitHubRemoteRepository('goto-bus-stop/recanalyst', $base),
    'default_opened_level' => 2,
]);
