#!/usr/bin/php
<?php

$login = 'ezrobot';

$repo = $argv[1];// repo name
$sha1 = $argv[2];// commit sha1
$state = $argv[3];// pending, success, error, or failure.
$url = isset( $argv[4] ) ? $argv[4] : "";// url to link to for info on the failure (should be public)
$description = isset( $argv[5] ) ? $argv[5] : "";// "must have" description for what the status is about
$context = isset( $argv[6] ) ? $argv[6] : $login;// identifier, i.e. "ci/ezcs", fallback to  "ezrobot"

$maxRetry = 5;
$githubUrl = "https://api.github.com/repos/ezsystems/$repo/statuses/$sha1";
$ch = curl_init( $githubUrl );

// Enable CURLOPT_HEADER if you want to output debug stuff
//curl_setopt( $ch, CURLOPT_HEADER, true );
curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
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

echo( "Updating commit status on github, $githubUrl, state=$state, target_url=$url, $description\"$description\", context=\"$context\"\n" );

do
{
    $success = curl_exec( $ch );
    --$maxRetry;
    if ( $success === false )
    {
        if ( $maxRetry > 0 )
            $retrying = ". Retrying...\n";
        else
            $retrying = "\n";
        echo "# setCommit.php curl error:\n" . curl_error( $ch ) . "$retrying\n";
    }
} while ( ( $success === false ) and ( $maxRetry > 0 ) );

curl_close( $ch );
if ( $success === false )
    exit( 1 );
