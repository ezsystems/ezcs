#!/usr/bin/php
<?php

$login = 'ezrobot';

$ch = curl_init( "https://api.github.com/repos/ezsystems/$argv[1]/issues/$argv[2]/comments" );

curl_setopt( $ch, CURLOPT_SSLVERSION, 3 );
curl_setopt( $ch, CURLOPT_HEADER, true );
curl_setopt( $ch, CURLOPT_POST, true );
curl_setopt( $ch, CURLOPT_USERAGENT, "ezrobot PR CodeSniffer" );
curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'Authorization: token '. getenv( $login ) ) );
curl_setopt(
    $ch, CURLOPT_POSTFIELDS,
    json_encode( array( "body" => file_get_contents( $argv[3] ) ) )
);

curl_exec( $ch );
curl_close( $ch );
