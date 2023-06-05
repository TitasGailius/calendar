<?php

namespace TitasGailius\Calendar\Providers\Google;

use Google\Service\Calendar as CalendarService;
use TitasGailius\Calendar\Providers\GoogleFactory;
use TitasGailius\Calendar\Resources\CollectionPaginator;
use TitasGailius\Calendar\Resources\Page;

/**
 * @extends \TitasGailius\Calendar\Resources\CollectionPaginator<\TitasGailius\Calendar\Resources\CalendarCollection>
 */
class GoogleCalendarPaginator extends CollectionPaginator
{
    /**
     * Instantiate a new paginator instance.
     */
    final public function __construct(
        protected readonly CalendarService $service,
        protected readonly array $options,
    ) {}

    /**
     * {@inheritdoc}
     */
    protected function nextPage(array $options): Page
    {
        return new Page(
            raw: $page = $this->service->calendarList->listCalendarList(array_merge($this->options, $options)),
            items: GoogleFactory::toCalendarCollection($page),
            hasNextPage: ! is_null($pageToken = $page->getNextPageToken()),
            nextPageOptions: compact('pageToken'),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function reset(): static
    {
        return new static($this->service, $this->options);
    }
}
