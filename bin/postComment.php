#!/usr/bin/php
<?php

$login = 'ezrobot';

$ch = curl_init( "https://api.github.com/repos/ezsystems/ezpublish-kernel/issues/$argv[1]/comments" );

curl_setopt( $ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
curl_setopt( $ch, CURLOPT_USERPWD, $login . ':' . $_ENV[$login] );
curl_setopt( $ch, CURLOPT_SSLVERSION, 3 );
curl_setopt( $ch, CURLOPT_HEADER, true );
curl_setopt( $ch, CURLOPT_POST, true );
curl_setopt( $ch, CURLOPT_USERAGENT, "ezrobot PR CodeSniffer" );
curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json' ) );
curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( array( "body" => "This Pull Request does not respect our [Coding Standards](https://github.com/ezsystems/ezcs), please, see the report below:\n\n```\n" . file_get_contents( $argv[2] ) . "\n```" ) ) );

curl_exec( $ch );
curl_close( $ch );
