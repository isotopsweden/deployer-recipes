<?php

namespace Deployer;

/**
 * Flushes all sites permalinks.
 */
task( 'wp:flushall', function () {
    run( '[ -f /usr/local/bin/wp ] && cd {{deploy_path}}/current && for i in $(/usr/local/bin/wp site list --fields=url --format=csv); do if [ \"$i\" != \"url\" ]; then /bin/echo -n \"$i: \" && /usr/local/bin/wp rewrite flush --url=$i; fi; done || echo "wp-cli is not installed"' );
} )->desc( 'Flushes all sites permalinks using wp-cli' );

/**
 * Upgrades all databases
 */
task( 'wp:dbupgrade', function () {
    run( '[ -f /usr/local/bin/wp ] && cd {{deploy_path}}/current && for i in $(/usr/local/bin/wp site list --fields=url --format=csv); do if [ \"$i\" != \"url\" ]; then /bin/echo -n \"$i: \" && /usr/local/bin/wp core update-db --url=$i; fi; done || echo "wp-cli is not installed"' );
} )->desc( 'Upgrades all databases using wp-cli' );

/**
 * Run data migrations
 */
task( 'wp:datamigrate', function () {
    run( '[ -f /usr/local/bin/wp ] && cd {{deploy_path}}/current && /usr/local/bin/wp data-migrate run || echo "wp-cli is not installed"' );
} )->desc( 'Run data migrations' );

