<?php

/**
 * Restart apache graceful.
 */
task('apache:restart', function () {
    run("apachectl -t && sudo apachectl -k graceful || echo \"apachectl cannot be found\"");
})->desc('Restart apache graceful');
