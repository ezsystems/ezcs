#!/usr/bin/php
<?php

$login = 'ezrobot';

$repo = $argv[1];// repo name
$sha1 = $argv[2];// commit sha1
$state = $argv[3];// pending, success, error, or failure.
$url = isset( $argv[4] ) ? $argv[4] : "";// url to link to for info on the failure (should be public)
$description = isset( $argv[5] ) ? $argv[5] : "";// "must have" description for what the status is about
$context = isset( $argv[6] ) ? $argv[6] : $login;// identifier, i.e. "ci/ezcs", fallback to  "ezrobot"

$ch = curl_init( "https://api.github.com/repos/ezsystems/$repo/statuses/$sha1" );

curl_setopt( $ch, CURLOPT_HEADER, true );
curl_setopt( $ch, CURLOPT_POST, true );
curl_setopt( $ch, CURLOPT_USERAGENT, "ezrobot" );
curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'Authorization: token '. getenv( $login ) ) );
curl_setopt(
    $ch, CURLOPT_POSTFIELDS,
    json_encode(
        array(
            'state' => $state,
            'target_url' => $url,
            'description' => $description,
            'context' => $context
        )
    )
);

if ( curl_exec( $ch ) === false )
{
    echo "\n\n# setCommit.php curl error:\n" . curl_error( $ch ) . "\n\n";
    curl_close( $ch );
    exit( 1 );
}

curl_close( $ch );
