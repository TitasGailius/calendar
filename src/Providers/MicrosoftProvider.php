<?php

namespace TitasGailius\Calendar\Providers;

use GuzzleHttp\Exception\ClientException;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Http\GraphCollectionRequest;
use Microsoft\Graph\Model\Calendar as MicrosoftCalendar;
use Microsoft\Graph\Model\Event as MicrosoftEvent;
use TitasGailius\Calendar\Contracts\Paginator;
use TitasGailius\Calendar\Contracts\Provider;
use TitasGailius\Calendar\Resources\Calendar;
use TitasGailius\Calendar\Resources\Event;
use TitasGailius\Calendar\Resources\Filters;
use TitasGailius\Calendar\Resources\GeneralCollectionPaginator;
use TitasGailius\Calendar\Resources\Page;
use TitasGailius\Calendar\Resources\Selector;

class MicrosoftProvider implements Provider
{
    /**
     * Instantiate a new provider instance.
     */
    public function __construct(
        protected readonly Graph $graph,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getCalendars(array $options = []): Paginator
    {
        $request = $this->graph
            ->createCollectionRequest('GET', '/me/calendars')
            ->setReturnType(MicrosoftCalendar::class);

        return new GeneralCollectionPaginator(
            next: fn () => new Page(
                raw: $page = $request->getPage(),
                items: MicrosoftFactory::toCalendarCollection($page),
                hasNextPage: ! $request->isEnd(),
                nextPageOptions: [],
            ),
            reset: fn () => $this->getCalendars($options),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents(Filters $filters, array $options = []): Paginator
    {
        $endpoint = match (true) {
            $filters->expand === true => '/me/calendar/calendarView',
            $filters->calendar === 'primary' => '/me/events',
            default => '/me/calendars/'.$filters->calendar.'/events',
        };

        $url = $endpoint.'?'.MicrosoftFactory::queryStringFromFilters($filters);

        $request = $this->graph
                ->createCollectionRequest('GET', $url)
                ->setReturnType(MicrosoftEvent::class);

        return new GeneralCollectionPaginator(
            next: fn () => new Page(
                raw: $page = $request->getPage(),
                items: MicrosoftFactory::toEventCollection($page),
                hasNextPage: ! $request->isEnd(),
                nextPageOptions: [],
            ),
            reset: fn () => $this->getEvents($filters, $options),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getEvent(Selector $selector, array $options = []): ?Event
    {
        return $this->handleNotFound(fn () => MicrosoftFactory::toEvent(
            $this->graph
                ->createRequest('GET', MicrosoftFactory::toEventUrl($selector->id, $selector->calendar))
                ->setReturnType(MicrosoftEvent::class)
                ->execute()
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function createEvent(Event $event, array $options = []): Event
    {
        return MicrosoftFactory::toEvent(
            $this->graph
                ->createRequest('POST', '/me/events')
                ->attachBody(MicrosoftFactory::fromEvent($event))
                ->setReturnType(MicrosoftEvent::class)
                ->execute()
        );
    }


    /**
     * {@inheritdoc}
     */
    public function updateEvent(Event $event, array $options = []): Event
    {
        $updated = MicrosoftFactory::toEvent(
            $this->graph
                ->createRequest('PATCH', MicrosoftFactory::toEventUrl($event->id, $event->calendar))
                ->attachBody(MicrosoftFactory::fromEvent($event))
                ->setReturnType(MicrosoftEvent::class)
                ->execute()
        );

        return $event->update($updated);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteEvent(Selector $selector, array $options = []): void
    {
        $this->graph
            ->createRequest('DELETE', MicrosoftFactory::toEventUrl($selector->id, $selector->calendar))
            ->execute();
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
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                return null;
            }

            throw $e;
        }
    }
}
