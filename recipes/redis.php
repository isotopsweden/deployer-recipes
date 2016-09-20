<?php

/**
 * Redis flushall.
 */
task('redis:flushall', function () {
    run('sudo redis-cli flushall || echo "Redis is not installed"');
})->desc('Redis flushall');

/**
 * Restart redis.
 */
task('redis:restart', function () {
    run('[ -f /etc/init.d/redis-server ] && sudo /etc/init.d/redis-server stop && sudo /etc/init.d/redis-server start || echo "Redis is not installed"');
})->desc('Restart redis');