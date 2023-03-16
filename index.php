<?php

require __DIR__.'/../vendor/autoload.php';

$google = TitasGailius\Calendar\Calendar::google([
    'client_id' => '',
    'client_secret' => '',
    'access_token' => '',
    'refresh_token' => '',
]);

$google->save(new Event(
    subject: 'This is First Event',
    attendees: ['john@example.com'],
    start: Carbon::parse('2023-03-15 10:00'),
    end: Carbon::parse('2023-03-15 11:00'),
));
