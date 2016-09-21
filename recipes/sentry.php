<?php

/**
 * Notify Sentry about deployment.
 */
task('sentry:notify_deployment', function () {
    $org = get('sentry_org');
    $project = get('sentry_project');
    $key = get('sentry_api_key');

    $data = ['version' => '{{release_name}}', 'ref' => '{{release_name}}', 'dateReleased' => date('Y-m-d H:i:s')];
    $json = json_encode($data);
    $json = addcslashes($json, '"\\/');

    runLocally("curl --user $key:\"\" -s -d \"$json\" -H \"Content-Type: application/json\" https://app.getsentry.com/api/0/projects/$org/$project/releases/");
})->desc('Notify Sentry about deployment');