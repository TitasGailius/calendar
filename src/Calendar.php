<?php

namespace TitasGailius\Calendar;

use TitasGailius\Calendar\Providers\GoogleProvider;

class Calendar
{
    /**
     * Instantiate a new Google proviedr instance.
     *
     * @param array{
     *             'application_name'?: string,
     *             'base_path'?: string,
     *             'client_id': string,
     *             'client_secret': string,
     *             'scopes'?: array<int, string>|string,
     *             'quota_project'?: string,
     *             'redirect_uri': string,
     *             'credentials': string|array|\Google\Auth\CredentialsLoader
     *         }  $config
     */
    public static function google(array $config): GoogleProvider
    {
        $token = [
            'access_token' => $config['access_token'],
            'refresh_token' => $config['refresh_token'],
        ];

        unset($config['access_token']);
        unset($config['refresh_token']);

        $client = new Client($config);

        $client->setAccessToken($token);

        return new GoogleProvider($client);
    }

    /**
     * Instantiate a new Microsoft provider instance.
     */
    public static function microsoft(array $config): MicrosoftProvider
    {
        //
    }
}
