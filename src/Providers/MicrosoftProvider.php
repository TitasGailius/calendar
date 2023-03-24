<?php

namespace TitasGailius\Calendar\Providers;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Http\GraphCollectionRequest;
use Microsoft\Graph\Model\Calendar as MicrosoftCalendar;
use TitasGailius\Calendar\Contracts\Paginator;
use TitasGailius\Calendar\Contracts\Provider;
use TitasGailius\Calendar\Resources\Calendar;
use TitasGailius\Calendar\Resources\Event;

class MicrosoftProvider implements Provider
{
    /**
     * Instantiate a new provider instance.
     */
    public function __construct(
        protected readonly Graph $graph,
    ) {}

    /**
     * List calendars.
     *
     * @return \TitasGailius\Calendar\Contracts\Paginator<\TitasGailius\Calendar\Resources\Calendar>
     */
    public function getCalendars(): Paginator
    {
        return MicrosoftFactory::paginator(Calendar::class,
            $this->graph
                ->createCollectionRequest('GET', '/me/calendars')
                ->setReturnType(MicrosoftCalendar::class),
        );
    }

    /**
     * List events.
     *
     * @return \TitasGailius\Calendar\Contracts\Paginator<\TitasGailius\Calendar\Resources\Event>
     */
    public function getEvents(): Paginator
    {
        throw new \Exception('Method getEvents() is not implemented.');
    }

    /**
     * Create an event.
     */
    public function createEvent(Event $event): Event
    {
        throw new \Exception('Method createEvent() is not implemented.');
    }

    /**
     * Get event.
     */
    public function getEvent(string|Event $id): ?Event
    {
        //
    }

    /**
     * Save a new event.
     */
    public function updateEvent(Event $event): Event
    {
        throw new \Exception('Method getEvent() is not implemented.');
    }

    /**
     * Delete a given event.
     */
    public function deleteEvent(string|Event $event): void
    {
        throw new \Exception('Method deleteEvent() is not implemented.');
    }
}
