<?php

namespace TitasGailius\Calendar\Providers;

use Google\Client;
use Google\Service\Calendar as CalendarService;
use Google\Service\Calendar\CalendarListEntry;
use TitasGailius\Calendar\Contracts\Provider;
use TitasGailius\Calendar\Resources\Calendar;
use TitasGailius\Calendar\Resources\Event;

class GoogleProvider implements Provider
{
    /**
     * Instantiate a new provider instance.
     */
    public function __construct(protected CalendarService $service)
    {
        //
    }

    /**
     * List calendars.
     *
     * @return array<int, \TitasGailius\Calendar\Calendar>
     */
    public function getCalendars(): array
    {
        $result = [];

        do {
            $list = $this->service->calendarList->listCalendarList(
                isset($pageToken) ? compact('pageToken') : []
            );

            foreach ($list->getItems() as $item) {
                $result[] = $this->newCalendarInstance($item);
            }
        } while ($pageToken = $calendarList->getNextPageToken());

        return $result;
    }

    /**
     * Create an event.
     */
    public function createEvent(Event $event): Event
    {
        //
    }

    /**
     * Get event.
     */
    public function getEvent(string $id): ?Event
    {
        //
    }

    /**
     * Save a new event.
     */
    public function updateEvent(Event $event): Event
    {
        //
    }

    /**
     * Delete a given event.
     */
    public function deleteEvent(string|Event $event)
    {
        //
    }

    /**
     * Instantiate a new calendar instance.
     */
    protected function newCalendarInstance(CalendarListEntry $calendar): Calendar
    {
        return new Calendar(
            id: $calendar->getId(),
            name: $calendar->getSummary(),
        );
    }
}
