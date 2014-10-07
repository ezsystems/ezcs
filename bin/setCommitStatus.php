#!/usr/bin/php
<?php

$login = 'ezrobot';

$repo = $argv[1];
$sha1 = $argv[2];
$state = $argv[3];
$url = $argv[4];
$description = isset( $argv[5] ) ? $argv[5] : "";
$context = isset( $argv[6] ) ? $argv[6] : $login;

$ch = curl_init( "https://api.github.com/repos/ezsystems/$repo/statuses/$sha1" );

curl_setopt( $ch, CURLOPT_SSLVERSION, 3 );
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

curl_exec( $ch );
curl_close( $ch );
