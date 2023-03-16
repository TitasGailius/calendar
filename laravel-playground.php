<?php

/*
|--------------------------------------------------------------------------
| Configuration
|--------------------------------------------------------------------------
|
*/

return [
    'providers' => [
        'google' => [
            'driver' => 'google',
            'application_name' => env('GOOGLE_APPLICATION_NAME'),
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'developer_key' => env('GOOGLE_DEVELOPER_KEY'),
        ],

        'microsoft' => [
            'driver' => 'microsoft',
        ],
    ],
];

/*
|--------------------------------------------------------------------------
| Usage
|--------------------------------------------------------------------------
|
*/

Calendar::provider('google', [
    'access_token' => $user->access_token,
    'refresh_token' => $user->refresh_token,
])->save(new Event(
    title: 'Meeting with John Doe',
    start: '2023-02-28 12:00',
    end: '2023-02-28 12:30',
    attendees: ['john.doe@example.com'],
));

$user->calendar()->list();

$user->calendar()->save(new Event(
    title: 'Meeting with John Doe',
    start: '2023-02-28 12:00',
    end: '2023-02-28 12:30',
    attendees: ['john.doe@example.com'],
));

/*
|--------------------------------------------------------------------------
| Trait Draft
|--------------------------------------------------------------------------
|
*/

trait HasCalendar
{
    /**
     * Get calendar provider name.
     */
    abstract public function getDefaultCalendarProvider(): string;

    /**
     * Authenticate a given calendar provider.
     */
    abstract public function getCalendarCredentials(string $provider): array;

    /**
     * Resolve calendar provider.
     */
    public function calendar(?string $provider = null): Provider
    {
        $provider ??= $this->getDefaultCalendarProvider();

        return Calendar::provider($provider, $this->getCalendarCredentials($provider));
    }
}

/*
|--------------------------------------------------------------------------
| Multiple Calendars
|--------------------------------------------------------------------------
|
*/

$user->calendar()
    ->primary()
    ->save(new Event(
        title: 'Meeting with John Doe',
        start: '2023-02-28 12:00',
        end: '2023-02-28 12:30',
        attendees: ['john.doe@example.com'],
    ));

$calendars = $user->calendar()
    ->all()
    ->save(new Event(
        title: 'Meeting with John Doe',
        start: '2023-02-28 12:00',
        end: '2023-02-28 12:30',
        attendees: ['john.doe@example.com'],
    ));
