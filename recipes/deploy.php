<?php

/**
 * Environment variables.
 */
env( 'composer_options', 'install --no-dev --verbose --prefer-dist --optimize-autoloader --no-progress --no-interaction --no-scripts' );

/**
 * Common parameters.
 */
set( 'user', 'deploy' );
set( 'group', 'www-data' );

/**
 * Set right user and group on root files.
 */
task( 'deploy:groupify_root', function () {
	$user  = get( 'user' );
	$group = get( 'group' );

	cd( '/' );
	run( "( test -d {{deploy_path}}/ && sudo chown -R $user:$group {{deploy_path}}/ ) || echo 'New deploy path, chown not needed'" );
} )->desc( 'Set right permissions on root directory' );

/**
 * Set right user and group on releases files.
 */
task( 'deploy:groupify_releases', function () {
	$user  = get( 'user' );
	$group = get( 'group' );

	cd( '/' );
	run( "( test -d {{deploy_path}}/releases/ && sudo chown -R $user:$group {{deploy_path}}/releases/ ) || echo 'Release directory missing, chown not needed'" );
} )->desc( 'Set right permissions on releases directory' );

/**
 * Set right user and group on shared files.
 */
task( 'deploy:groupify_shared', function () {
	$user  = get( 'user' );
	$group = get( 'group' );

	cd( '/' );
	run( "(test -d {{deploy_path}}/shared/ && sudo chown -R $user:$group {{deploy_path}}/shared/ ) || echo 'Shared directory missing, chown not needed'" );
} )->desc( 'Set right permissions on shared files' );

/**
 * Update code.
 */
task( 'deploy:update_code', function () {
	$repository  = trim( get( 'repository' ) );
	$branch      = env( 'branch' ) ?: 'master';
	$git         = env( 'bin/git' );
	$ci          = getenv( 'CI_BUILD_REF' ) ?: '';
	$verbose     = '';
	$tarballPath = '/tmp/{{release_name}}.gz';

	if ( isVerbose() ) {
		$verbose = '-v';
	}

	// Extract from git to tarball.
	if ( ! empty( $ci ) ) {
		runLocally( "git archive --format=tar $verbose HEAD | bzip2 > $tarballPath" );
	} else {
		runLocally( "git archive --remote=$repository --format=tar $verbose $branch | bzip2 > $tarballPath" );
	}

	// Upload tarball.
	upload( $tarballPath, $tarballPath );

	// Extract tarball.
	run( "mkdir -p {{deploy_path}}/tar/$branch" );
	run( "tar -xf $tarballPath -C {{deploy_path}}/tar/$branch" );
	run( "find {{deploy_path}}/tar/$branch/ -mindepth 1 -maxdepth 1 -exec mv -t {{release_path}}/ -- {} +" );

	// Cleanup.
	run( "rm -rf {{deploy_path}}/tar" );
	run( "rm $tarballPath" );
} )->desc( 'Updating code' );

/**
 * Installing vendors tasks.
 */
task( 'deploy:vendors', function () {
	$composer    = env( 'bin/composer' );
	$envVars     = env( 'env_vars' ) ? 'export ' . env( 'env_vars' ) . ' &&' : '';
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
