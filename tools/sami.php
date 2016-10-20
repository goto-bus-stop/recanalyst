<?php

/**
 * Configuration for the Sami documentation generator.
 */

use Sami\Sami;
use Sami\Version\GitVersionCollection;
use Sami\RemoteRepository\GitHubRemoteRepository;

$dir = __DIR__ . '/../src/';

return new Sami($dir, [
    'title' => 'RecAnalyst API',
    'build_dir' => __DIR__ . '/doc',
    'cache_dir' => __DIR__ . '/tmp',
    'remote_repository' => new GitHubRemoteRepository('goto-bus-stop/recanalyst', '/src/'),
    'default_opened_level' => 2,
]);
