<?php

/*
|--------------------------------------------------------------------------
| Google
|--------------------------------------------------------------------------
|
*/

$client = new Client([
    // ...
]);

$client->setAccessToken([
    // ...
]);

$provider = new GoogleProvider($client);

/*
|--------------------------------------------------------------------------
| Microsoft
|--------------------------------------------------------------------------
|
*/

$graph = new Graph;
$graph->setAccessToken('...');
$graph->setRefreshToken('...');

$provider = new MicrosoftProvider($graph);

/*
|--------------------------------------------------------------------------
| Factory
|--------------------------------------------------------------------------
|
*/

$google = Calendar::google([
    'applicationName' => '',
    'access' => 'offline',
    'access_token' => '',
    'refresh_token' => '',
    'client_id' => '',
    'client_secret' => '',
]);

Factory::google(


);

Factory::microsoft(
    accessToken: '',
    refreshToken: '',
);
