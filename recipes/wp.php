<?php

/**
 * Flushes all sites permalinks.
 */
task('wp:flushall', function () {
    run('[ -f /usr/local/bin/wp ] && cd {{deploy_path}}/current && for i in $(/usr/local/bin/wp site list --fields=url --format=csv); do if [ \"$i\" != \"url\" ]; then /bin/echo -n \"$i: \" && /usr/local/bin/wp rewrite flush --url=$i; fi; done || echo "wp-cli is not installed"');
})->desc('Flushes all sites permalinks using wp-cli');