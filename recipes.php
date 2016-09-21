<?php

$recipes = [
    'apache.php',
    'deploy.php',
    'redis.php',
    'sentry.php',
    'wp.php'
];

array_walk($recipes, function ($file) {
    $path = __DIR__ . '/recipes/' . $file;

    if (file_exists($path)) {
        require_once $path;
    }
});