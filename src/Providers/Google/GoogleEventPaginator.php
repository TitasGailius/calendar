<?php

namespace TitasGailius\Calendar\Providers\Google;

use Carbon\Carbon;
use DateTimeInterface;
use Google\Service\Calendar as CalendarService;
use TitasGailius\Calendar\Providers\GoogleFactory;
use TitasGailius\Calendar\Resources\CollectionPaginator;
use TitasGailius\Calendar\Resources\Filters;
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
        protected readonly Filters $filters,
        protected readonly array $options,
    ) {}

    /**
     * {@inheritdoc}
     */
    protected function nextPage(array $options): Page
    {
        return new Page(
            raw: $page = $this->service->events->listEvents(
                $this->filters->calendar, $this->options($options)
            ),
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
        return array_merge($this->options, $options, $this->filters->options([
            'start' => fn (DateTimeInterface $start) => ['timeMin' => Carbon::parse($start)->toRfc3339String()],
            'end' => fn (DateTimeInterface $end) => ['timeMax' => Carbon::parse($end)->toRfc3339String()],
            'limit' => fn (int $limit) => ['maxResults' => $limit],
            'search' => fn (string $search) => ['q' => $search],
            'expand' => fn (bool $expand) => ['singleEvents' => $expand],
            'metadata' => $this->toMetadataFilterProperties(...),
        ]));
    }

    /**
     * Convert the given metadata to filter properties.
     *
     * @param  array<string, mixed>  $metadata
     * @return array{privateExtendedProperty: array<string>|string}
     */
    protected function toMetadataFilterProperties(array $metadata): array
    {
        $result = [];

        foreach ($metadata as $key => $value) {
            $result[] = $key.'='.$value;
        }

        return ['privateExtendedProperty' => count($result) === 1 ? $result[0] : $result];
    }

    /**
     * {@inheritdoc}
     */
    protected function reset(): static
    {
        return new static($this->service, $this->filters, $this->options);
    }
}
