<?php

namespace TitasGailius\Calendar\Contracts;

use DateTimeInterface;
use TitasGailius\Calendar\Resources\Event;
use TitasGailius\Calendar\Resources\Organiser;
use TitasGailius\Calendar\Resources\Recurrence;

interface Repository
{
    /**
     * List calendars.
     *
     * @return \TitasGailius\Calendar\Contracts\Paginator<\TitasGailius\Calendar\Resources\CalendarCollection>
     */
    public function getCalendars(): Paginator;

    /**
     * List events.
     *
     * @return \TitasGailius\Calendar\Contracts\Paginator<\TitasGailius\Calendar\Resources\EventCollection>
     */
    public function getEvents(
        ?DateTimeInterface $start = null,
        ?DateTimeInterface $end = null,
        bool $expand = false,
        ?int $limit = null,
        ?string $search = null,
        ?array $metadata = null,
        ?string $calendar = 'primary',
    ): Paginator;

    /**
     * Get event.
     */
    public function getEvent(string|Event $id, string $calendar = 'primary'): ?Event;

    /**
     * Create an event.
     */
    public function createEvent(Event $event): Event;

    /**
     * Save a new event.
     */
    public function updateEvent(
        Event|string $event,
        string $calendar = 'primary',
        ?string $title = null,
        ?DateTimeInterface $start = null,
        ?DateTimeInterface $end = null,
        array $attendees = [],
        ?Recurrence $recurrence = null,
        ?Organiser $organiser = null,
        array $metadata = [],
    ): Event;

    /**
     * Delete a given event.
     */
    public function deleteEvent(string|Event $selector): void;

    /**
     * Set custom options for the current request.
     *
     * @param  mixed[]  $options
     */
    public function with(array $options = []): static;

    /**
     * Apply custom options when the provider is named after the given name.
     */
    public function whenWith(string $provider, callable $callback): static;

    /**
     * Get the Provider instance.
     */
    public function getProvider(): Provider;

    /**
     * Get provider name.
     */
    public function getName(): string;
}
