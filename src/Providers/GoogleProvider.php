<?php

namespace TitasGailius\Calendar\Providers;

use Google\Service\Calendar as CalendarService;
use Google\Service\Exception;
use TitasGailius\Calendar\Contracts\Paginator;
use TitasGailius\Calendar\Contracts\Provider;
use TitasGailius\Calendar\Providers\GoogleFactory;
use TitasGailius\Calendar\Providers\Google\GoogleCalendarPaginator;
use TitasGailius\Calendar\Providers\Google\GoogleEventPaginator;
use TitasGailius\Calendar\Resources\Calendar;
use TitasGailius\Calendar\Resources\CalendarCollection;
use TitasGailius\Calendar\Resources\CollectionPaginator;
use TitasGailius\Calendar\Resources\Event;
use TitasGailius\Calendar\Resources\EventCollection;
use TitasGailius\Calendar\Resources\Filters;
use TitasGailius\Calendar\Resources\Selector;

class GoogleProvider implements Provider
{
    /**
     * Instantiate a new provider instance.
     */
    public function __construct(
        protected readonly CalendarService $service,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getCalendars(array $options = []): Paginator
    {
        return new GoogleCalendarPaginator($this->service, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents(Filters $filters, array $options = []): Paginator
    {
        return new GoogleEventPaginator($this->service, $filters, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function createEvent(Event $event, array $options = []): Event
    {
        $created = GoogleFactory::toEvent($this->service->events->insert(
            $event->calendar, GoogleFactory::fromEvent($event), $options
        ));

        return $event->update($created);
    }

    /**
     * {@inheritdoc}
     */
    public function getEvent(Selector $selector, array $options = []): ?Event
    {
        return GoogleFactory::toEvent($this->handleNotFound(function () use ($selector, $options) {
            return $this->service->events->get($selector->calendar, $selector->id, $options);
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function updateEvent(Event $event, array $options = []): Event
    {
        $updated = GoogleFactory::toEvent($this->service->events->patch(
            $event->calendar,
            $event->id,
            GoogleFactory::fromEvent($event),
            $options,
        ));

        return $event->update($updated);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteEvent(Selector $selector, array $options = []): void
    {
        $this->service->events->delete($selector->calendar, $selector->id, $options);
    }

    /**
     * Handle not found exception.
     *
     * @template TValue
     *
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
