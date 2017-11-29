<?php

/**
 * Configuration for the Sami documentation generator.
 */

use Sami\Sami;
use Sami\Version\GitVersionCollection;
use Sami\RemoteRepository\GitHubRemoteRepository;

$base = __DIR__ . '/..';
$dir = $base . '/src';

$versions = GitVersionCollection::create($base)
  ->addFromTags('v4.*')
  ->add('master', 'master branch');

return new Sami($dir, [
    'title' => 'RecAnalyst API',
    'versions' => $versions,
    'build_dir' => $base . '/doc/%version%',
    'cache_dir' => __DIR__ . '/tmp/%version%',
    'remote_repository' => new GitHubRemoteRepository('goto-bus-stop/recanalyst', $base),
    'default_opened_level' => 2,
]);
