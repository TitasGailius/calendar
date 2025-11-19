<?php

namespace TitasGailius\Calendar;

use Carbon\Carbon;
use Closure;
use Exception;
use Google\Client;
use Google\Service\Calendar as CalendarService;
use GuzzleHttp\Client as Guzzle;
use Microsoft\Graph\Graph;
use TitasGailius\Calendar\Contracts\Repository as RepositoryContract;
use TitasGailius\Calendar\Exceptions\RefreshTokenExpiredException;
use TitasGailius\Calendar\Providers\GoogleProvider;
use TitasGailius\Calendar\Providers\MicrosoftFactory;
use TitasGailius\Calendar\Providers\MicrosoftProvider;

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
            $onTokenRefresh(static::refreshGoogleToken($client));
        }

        return new Repository('google', new GoogleProvider(new CalendarService($client)));
    }

    /**
     * Refresh Google token.
     */
    protected static function refreshGoogleToken(Client $client)
    {
        $response = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

        if (isset($response['error'])) {
            throw new RefreshTokenExpiredException('google');
        }

        return $response;
    }

    /**
     * Instantiate a new Microsoft provider instance.
     *
     * @param  array{client_id: string, client_secret: string, guid?: string, metadata?: array<string>}  $client
     * @param  array{refresh_token: string, access_token: string, created: int, expires_in: int}  $token
     * @param  Closure(array{refresh_token: string, access_token: string, created: int, expires_in: int}): void  $onTokenRefresh
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

        MicrosoftFactory::$guid = $client['guid'] ?? $client['client_id'];

        if (isset($client['metadata'])) {
            MicrosoftFactory::$metadata = $client['metadata'];
        }

        return new Repository('microsoft', new MicrosoftProvider($graph));
    }

    /**
     * Refresh microsoft token.
     *
     * @param  mixed[]  $client
     * @return array{refresh_token: string, access_token: string, created: int, expires_in: int}
     */
    protected static function refreshMicrosoftToken(array $client, string $refreshToken): array
    {
        try {
            $response = (new Guzzle)->post('https://login.microsoftonline.com/common/oauth2/v2.0/token', [
                'form_params' => [
                    'client_id' => $client['client_id'],
                    'client_secret' => $client['client_secret'],
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken,
                ],
            ]);
        } catch (Exception $e) {
            throw new RefreshTokenExpiredException('microsoft');
        }

        $payload = json_decode((string) $response->getBody(), true);

        return [
            'refresh_token' => $payload['refresh_token'],
            'access_token' => $payload['access_token'],
            'created' => time(),
            'expires_in' => $payload['expires_in'],
        ];
    }
}
