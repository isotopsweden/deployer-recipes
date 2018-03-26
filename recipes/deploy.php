<?php

namespace Deployer;

require_once 'recipe/common.php';

/**
 * Environment variables.
 */
set( 'composer_options', '{{composer_action}} --no-dev --verbose --prefer-dist --optimize-autoloader --no-progress --no-interaction --no-scripts' );

/**
 * Common parameters.
 */
set( 'user', 'deploy' );
set( 'group', 'www-data' );

set( 'release_name', function () {
    // Set the deployment timezone
    if ( ! date_default_timezone_set( get( 'timezone', 'UTC' ) ) ) {
        date_default_timezone_set( 'UTC' );
    }

    return date( 'YmdHis' );
} );

/**
 * Set right user and group on root files.
 */
task( 'deploy:groupify_root', function () {
    cd( '/' );
    run( '( test -d {{deploy_path}}/ && sudo chown -R {{user}}:{{group}} {{deploy_path}}/ ) || echo "New deploy path, chown not needed"' );
    run( '( test -d {{deploy_path}}/ && sudo chmod -R g=u {{deploy_path}}/ ) || echo "New deploy path, chmod not needed"' );
} )->desc( 'Set right permissions on root directory' );

/**
 * Set right user and group on releases files.
 */
task( 'deploy:groupify_releases', function () {
    cd( '/' );
    run( '( test -d {{deploy_path}}/releases/ && sudo chown -R {{user}}:{{group}} {{deploy_path}}/releases/ ) || echo "Release directory missing, chown not needed"' );
} )->desc( 'Set right permissions on releases directory' );

/**
 * Set right user and group on shared files.
 */
task( 'deploy:groupify_shared', function () {
    cd( '/' );
    run( '(test -d {{deploy_path}}/shared/ && sudo chown -R {{user}}:{{group}} {{deploy_path}}/shared/ ) || echo "Shared directory missing, chown not needed"' );
} )->desc( 'Set right permissions on shared files' );

/**
 * Update code.
 */
task( 'deploy:update_code', function () {
    $branch  = get( 'branch' ) ? get( 'branch' ) : 'master';
    $folder  = ''; // get( 'folder' ) ? get( 'folder' ) : '';
    $ci      = getenv( 'CI_COMMIT_SHA' ) ?: '';
    $verbose = '';

    // Remove invalid characters in filename
    $branch_filename = mb_ereg_replace( "([^\w\s\d\-_])", '', $branch );

    $tarballPath = '/tmp/{{release_name}}-' . $branch_filename . '.gz';

    if ( isVerbose() ) {
        $verbose = '-v';
    }

    // Extract from git to tarball.
    if ( ! empty( $ci ) ) {
        if ( empty( $folder ) ) {
            runLocally( "git archive --format=tar $verbose HEAD | bzip2 > $tarballPath" );
        } else {
            runLocally( "git archive --format=tar $verbose HEAD $folder | bzip2 > $tarballPath" );
        }
    } else {
        runLocally( "git fetch --all" );
        $local_commit  = runLocally( "git rev-parse $branch" );
        $remote_commit = runLocally( "git rev-parse origin/$branch" );

        if ( $local_commit !== $remote_commit ) {
            writeln( "<fg=red>></fg=red> Branch $branch not in sync with origin/$branch" );
            exit( 1 );
        }

        if ( empty( $folder ) ) {
            runLocally( "git archive --format=tar $verbose $branch | bzip2 > $tarballPath" );
        } else {
            runLocally( "git archive --format=tar $verbose $branch $folder | bzip2 > $tarballPath" );
        }
    }

    if ( isVerbose() ) {
        writeln( "<fg=green>></fg=green> Successfully archived to $tarballPath" );
    }

    // Upload tarball.
    upload( $tarballPath, $tarballPath );

    // Extract tarball.
    run( "sudo mkdir -p {{deploy_path}}/tar/$branch" );
    run( "sudo chown -R {{user}}:{{group}} {{deploy_path}}/tar" );
    run( "tar -xf $tarballPath -C {{deploy_path}}/tar/$branch" );
    run( "find {{deploy_path}}/tar/$branch/ -mindepth 1 -maxdepth 1 -exec mv -t {{release_path}}/ -- {} +" );

    // Delete folder in folder.
    if ( ! empty( $folder ) ) {
        run( "mv {{release_path}}/$folder {{release_path}}/.dep" );
        run( "cp -r {{release_path}}/.dep/. {{release_path}}/" );
        run( "rm -rf {{release_path}}/.dep" );
    }

    // Cleanup.
    run( "sudo rm -rf {{deploy_path}}/tar" );
    run( "rm $tarballPath" );
} )->desc( 'Updating code' );

/**
 * Installing vendors tasks.
 */
task( 'deploy:vendors', function () {
    $composer    = get( 'bin/composer' );
    $envVars     = get( 'env' ) ? 'export ' . get( 'env' ) . ' &&' : '';
    $githubToken = has( 'github_token' ) ? get( 'github_token' ) : '';

    if ( ! empty( $githubToken ) ) {
        run( "cd {{release_path}} && $envVars $composer config -g github-oauth.github.com $githubToken" );
    }

    run( "cd {{release_path}} && $envVars $composer {{composer_options}}" );
} )->desc( 'Installing vendors' );

/**
 * Add before and after hooks.
 */
before( 'deploy:prepare', 'deploy:groupify_root' );
before( 'deploy:update_code', 'deploy:groupify_root' );
before( 'rollback', 'deploy:groupify_releases' );
after( 'deploy:update_code', 'deploy:groupify_releases' );
after( 'success', 'deploy:groupify_root' );
