<?php

namespace TitasGailius\Calendar\Contracts;

use TitasGailius\Calendar\Resources\Event;

interface Provider
{
    /**
     * List calendars.
     *
     * @return array<int, \TitasGailius\Calendar\Calendar>
     */
    public function getCalendars(): array;

    /**
     * Create an event.
     */
    public function createEvent(Event $event): Event;

    /**
     * Get event.
     */
    public function getEvent(string $id): ?Event;

    /**
     * Save a new event.
     */
    public function updateEvent(Event $event): Event;

    /**
     * Delete a given event.
     */
    public function deleteEvent(string|Event $event);
}
