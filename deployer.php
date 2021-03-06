<?php

// Require custom functions.
require_once __DIR__ . '/functions.php';

// Require recipes.
$recipes = [
    'apache.php',
    'deploy.php',
    'ghostinspector.php',
    'redis.php',
    'sentry.php',
    'wp.php',
];

array_walk( $recipes, function ( $file ) {
    $path = __DIR__ . '/recipes/' . $file;

    if ( file_exists( $path ) ) {
        require_once $path;
    }
} );
