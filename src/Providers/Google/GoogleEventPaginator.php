<?php

namespace TitasGailius\Calendar\Providers\Google;

use Google\Service\Calendar as CalendarService;
use TitasGailius\Calendar\Providers\GoogleFactory;
use TitasGailius\Calendar\Resources\CollectionPaginator;
use TitasGailius\Calendar\Resources\EventFilters;
use TitasGailius\Calendar\Resources\Page;

/**
 * @extends \TitasGailius\Calendar\Resources\CollectionPaginator<\TitasGailius\Calendar\Resources\EventCollection>
 */
final class GoogleEventPaginator extends CollectionPaginator
{
    /**
     * Instantiate a new paginator instance.
     */
    final public function __construct(
        protected readonly CalendarService $service,
        protected readonly EventFilters $filters,
        protected readonly array $options,
    ) {}

    /**
     * {@inheritdoc}
     */
    protected function nextPage(array $options): Page
    {
        return new Page(
            raw: $page = $this->service->events->listEvents($this->filters->calendar, $this->options($options)),
            items: GoogleFactory::toEventCollection($page),
            hasNextPage: ! is_null($pageToken = $page->getNextPageToken()),
            nextPageOptions: compact('pageToken'),
        );
    }

    /**
     * Resolve request options.
     *
     * @param  mixed[]  $options
     * @return mixed[]
     */
    protected function options(array $options): array
    {
        return array_merge($this->options, $options, GoogleFactory::fromFilters($this->filters));
    }

    /**
     * {@inheritdoc}
     */
    protected function reset(): static
    {
        return new static($this->service, $this->filters, $this->options);
    }
}
