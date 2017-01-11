<?php
namespace Deployer;

/**
 * Restart apache graceful.
 */
task('apache:restart', function () {
    run("sudo apachectl -t && sudo apachectl -k graceful || echo \"apachectl cannot be found\"");
})->desc('Restart apache graceful');
