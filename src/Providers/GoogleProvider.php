<?php

namespace TitasGailius\Calendar\Providers;

use Google\Service\Calendar as CalendarService;
use Google\Service\Exception;
use TitasGailius\Calendar\Contracts\Paginator;
use TitasGailius\Calendar\Contracts\Provider;
use TitasGailius\Calendar\Resources\Calendar;
use TitasGailius\Calendar\Resources\Event;

class GoogleProvider implements Provider
{
    /**
     * Instantiate a new provider instance.
     */
    public function __construct(
        protected readonly CalendarService $service,
    ) {}

    /**
     * List calendars.
     *
     * @return \TitasGailius\Calendar\Contracts\Paginator<\TitasGailius\Calendar\Resources\Calendar>
     */
    public function getCalendars(): Paginator
    {
        return GoogleFactory::paginator(Calendar::class, function (array $options) {
            return $this->service->calendarList->listCalendarList($options);
        });
    }

    /**
     * List events.
     *
     * @return \TitasGailius\Calendar\Contracts\Paginator<\TitasGailius\Calendar\Resources\Event>
     */
    public function getEvents(): Paginator
    {
        return GoogleFactory::paginator(Event::class, function (array $options) {
            return $this->service->events->listEvents('primary', $options);
        });
    }

    /**
     * Create an event.
     */
    public function createEvent(Event $event): Event
    {
        $created = GoogleFactory::toEvent($this->service->events->insert(
            $event->calendar, GoogleFactory::fromEvent($event)
        ));

        return $event->update($created);
    }

    /**
     * Get event.
     */
    public function getEvent(string|Event $event): ?Event
    {
        $event = Event::parse($event);

        return GoogleFactory::toEvent($this->handleNotFound(function () use ($event) {
            return $this->service->events->get($event->calendar, $event->id);
        }));
    }

    /**
     * Save a new event.
     */
    public function updateEvent(Event $event): Event
    {
        $updated = GoogleFactory::toEvent($this->service->events->patch(
            $event->calendar,
            $event->id,
            GoogleFactory::fromEvent($event),
        ));

        return $event->update($updated);
    }

    /**
     * Delete a given event.
     */
    public function deleteEvent(string|Event $event): void
    {
        $event = Event::parse($event);

        $this->service->events->delete($event->calendar, $event->id);
    }

    /**
     * Handle not found exception.
     *
     * @template TValue
     * @param  callable(): TValue  $callback
     * @return ?TValue
     */
    protected function handleNotFound(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (Exception $e) {
            if ($e->getCode() === 404) {
                return null;
            }

            throw $e;
        }
    }
}
