<?php

namespace TitasGailius\Calendar\Contracts;

use TitasGailius\Calendar\Resources\Event;
use TitasGailius\Calendar\Resources\Filters;

interface Provider
{
    /**
     * List calendars.
     *
     * @param  mixed[]  $options
     * @return \TitasGailius\Calendar\Contracts\Paginator<\TitasGailius\Calendar\Resources\CalendarCollection>
     */
    public function getCalendars(array $options = []): Paginator;

    /**
     * List events.
     *
     * @param  mixed[]  $options
     * @return \TitasGailius\Calendar\Contracts\Paginator<\TitasGailius\Calendar\Resources\EventCollection>
     */
    public function getEvents(Filters $filters, array $options = []): Paginator;

    /**
     * Get event.
     *
     * @param  mixed[]  $options
     */
    public function getEvent(Filters $filters, array $options = []): ?Event;

    /**
     * Create an event.
     *
     * @param  mixed[]  $options
     */
    public function createEvent(Event $event, array $options = []): Event;

    /**
     * Save a new event.
     *
     * @param  mixed[]  $options
     */
    public function updateEvent(Event $event, array $options = []): Event;

    /**
     * Delete a given event.
     *
     * @param  mixed[]  $options
     */
    public function deleteEvent(Filters $filters, array $options = []): void;
}
