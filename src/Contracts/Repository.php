<?php

namespace TitasGailius\Calendar\Contracts;

use TitasGailius\Calendar\Contracts\Paginator;
use TitasGailius\Calendar\Resources\Event;
use TitasGailius\Calendar\Resources\Filters;
use TitasGailius\Calendar\Resources\Selector;

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
    public function getEvents(?Filters $filters = null): Paginator;

    /**
     * Get event.
     */
    public function getEvent(string|Event|Selector|null $selector = null): ?Event;

    /**
     * Create an event.
     */
    public function createEvent(Event $event): Event;

    /**
     * Save a new event.
     */
    public function updateEvent(Event $event): Event;

    /**
     * Delete a given event.
     */
    public function deleteEvent(string|Event|Selector|null $selector = null): void;

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
