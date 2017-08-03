<?php

namespace Deployer;
/**
 * Trigger Ghost Inspector tests
 */

/*
// EXAMPLE CONFIG
set( 'ghostinspector', [
	'apikey' => 'APIKEY',
	'tests'  => [
		'TESTID' => [
			'https://www.example.org',
			'https://www.example.com',
		],
	],
    'settings' => [
        'disableNotifications' => 0
    ]
] );
*/

task( 'ghostinspector:run', function () {
    $settings = get( 'ghostinspector', [] );
    if ( empty( $settings ) ) {
        writeln( '<error>Missing Ghost Inspector config</error>' );
    } else if ( empty( $settings['apikey'] ) ) {
        writeln( '<error>Missing Ghost Inspector api key</error>' );
    } else if ( empty( $settings['tests'] ) || ! is_array( $settings['tests'] ) ) {
        writeln( '<error>No Ghost Inspector tests defined</error>' );
    } else {
        $api_settings = empty( $settings['settings'] ) ? [] : $settings['settings'];
        $api_settings = array_merge(
            [
                'immediate'            => 1,
                'apiKey'               => $settings['apikey'],
                'disableNotifications' => 1,
            ],
            $api_settings
        );

        $api_settings = array_filter( $api_settings );

        foreach ( $settings['tests'] as $test_id => $test_urls ) {
            $test_urls = (array) $test_urls;

            foreach ( $test_urls as $test_url ) {
                $query = http_build_query(
                    array_merge(
                        $api_settings,
                        [
                            'startUrl' => $test_url,
                        ]
                    )
                );

                file_get_contents( sprintf(
                    'https://api.ghostinspector.com/v1/tests/%s/execute/?%s',
                    $test_id,
                    $query
                ) );
            }
        }
    }

} )->desc( 'Trigger Ghost Inspector tests' );
