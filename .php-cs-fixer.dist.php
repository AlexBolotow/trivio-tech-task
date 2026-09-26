<?php

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__.'/app',
        __DIR__.'/bootstrap',
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ]);
$finder->exclude('cache');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__.'/storage/framework/cache/data/.php-cs-fixer.cache');
