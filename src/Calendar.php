<?php

namespace TitasGailius\Calendar;

use Carbon\Carbon;
use Closure;
use Google\Client;
use Google\Service\Calendar as CalendarService;
use GuzzleHttp\Client as Guzzle;
use Microsoft\Graph\Graph;
use TitasGailius\Calendar\Contracts\Repository as RepositoryContract;
use TitasGailius\Calendar\Providers\GoogleProvider;
use TitasGailius\Calendar\Providers\MicrosoftFactory;
use TitasGailius\Calendar\Providers\MicrosoftProvider;
use TitasGailius\Calendar\Repository;

class Calendar
{
    /**
     * Instantiate a new Google proviedr instance.
     *
     * @param  array{client_id: string, client_secret: string}  $client
     * @param  array{access_token: string, refresh_token: string, created: int, expires_in: int}  $token
     */
    public static function google(array $client, array|string $token, Closure $onTokenRefresh): RepositoryContract
    {
        $client = new Client($client);
        $client->setAccessToken($token);

        if ($client->isAccessTokenExpired()) {
            $onTokenRefresh($client->fetchAccessTokenWithRefreshToken(
                $client->getRefreshToken()
            ));
        }

        return new Repository('google', new GoogleProvider(new CalendarService($client)));
    }

    /**
     * Instantiate a new Microsoft provider instance.
     *
     * @param  array{client_id: string, client_secret: string, guid: ?string}  $client
     * @param  array{refresh_token: string, access_token: string, created: int, expires_in: int}  $token
    *  @param  Closure(array{refresh_token: string, access_token: string, created: int, expires_in: int}): void  $onTokenRefresh
     */
    public static function microsoft(array $client, array $token, Closure $onTokenRefresh): RepositoryContract
    {
        $graph = new Graph;

        $expirationDate = Carbon::parse($token['created'])->addSeconds($token['expires_in']);

        if (Carbon::now()->isAfter($expirationDate)) {
            $token = static::refreshMicrosoftToken($client, $token['refresh_token']);

            $onTokenRefresh($token);
        }

        $graph->setAccessToken($token['access_token']);

        if (isset($client['guid'])) {
            MicrosoftFactory::$guid = $client['guid'];
        }

        if (isset($client['metadata'])) {
            MicrosoftFactory::$metadata = $client['metadata'];
        }

        return new Repository('microsoft', new MicrosoftProvider($graph));
    }

    /**
     * Refresh microsoft token.
     *
     * @param  mixed[]  $client
     * @return mixed[]
     */
    protected static function refreshMicrosoftToken(array $client, string $refreshToken): array
    {
        $response = (new Guzzle)->post('https://login.microsoftonline.com/common/oauth2/v2.0/token', [
            'form_params' => [
                'client_id' => $client['client_id'],
                'client_secret' => $client['client_secret'],
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            ],
        ]);

        $payload = json_decode((string) $response->getBody(), true);

        return [
            'refresh_token' => $payload['refresh_token'],
            'access_token' => $payload['access_token'],
            'created' => time(),
            'expires_in' => $payload['expires_in'],
        ];
    }
}
