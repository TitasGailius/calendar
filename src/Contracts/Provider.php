<?php

namespace TitasGailius\Calendar\Contracts;

use TitasGailius\Calendar\Resources\Event;

interface Provider
{
    /**
     * List calendars.
     *
     * @return \TitasGailius\Calendar\Contracts\Paginator<\TitasGailius\Calendar\Resources\Calendar>
     */
    public function getCalendars(): Paginator;

    /**
     * List events.
     *
     * @return \TitasGailius\Calendar\Contracts\Paginator<\TitasGailius\Calendar\Resources\Event>
     */
    public function getEvents(): Paginator;

    /**
     * Create an event.
     */
    public function createEvent(Event $event): Event;

    /**
     * Get event.
     */
    public function getEvent(string|Event $id): ?Event;

    /**
     * Save a new event.
     */
    public function updateEvent(Event $event): Event;

    /**
     * Delete a given event.
     */
    public function deleteEvent(string|Event $event): void;
}
