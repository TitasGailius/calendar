<?php

namespace TitasGailius\Calendar;

use TitasGailius\Calendar\Contracts\Paginator;
use TitasGailius\Calendar\Contracts\Provider;
use TitasGailius\Calendar\Contracts\Repository as RepositoryContract;
use TitasGailius\Calendar\Resources\Event;
use TitasGailius\Calendar\Resources\Filters;
use TitasGailius\Calendar\Resources\Selector;

final class Repository implements RepositoryContract
{
    /**
     * Instantiate a new repository instance.
     *
     * @param  mixed[]  $options
     */
    public function __construct(
        protected readonly string $name,
        protected readonly Provider $provider,
        protected readonly array $options = [],
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getCalendars(): Paginator
    {
        return $this->provider->getCalendars($this->options);
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents(?Filters $filters = null): Paginator
    {
        return $this->provider->getEvents($filters ?? new Filters, $this->options);
    }

    /**
     * {@inheritdoc}
     */
    public function createEvent(Event $event): Event
    {
        return $this->provider->createEvent($event, $this->options);
    }

    /**
     * {@inheritdoc}
     */
    public function getEvent(string|Event|Selector|null $selector = null): ?Event
    {
        return $this->provider->getEvent(Selector::parse($selector), $this->options);
    }

    /**
     * {@inheritdoc}
     */
    public function updateEvent(Event $event): Event
    {
        return $this->provider->updateEvent($event, $this->options);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteEvent(string|Event|Selector|null $selector = null): void
    {
        $this->provider->deleteEvent(Selector::parse($selector), $this->options);
    }

    /**
     * {@inheritdoc}
     */
    public function with(array $options = []): static
    {
        return new static($this->name, $this->provider, $this->options);
    }

    /**
     * {@inheritdoc}
     */
    public function whenWith(string $provider, callable $callback): static
    {
        return $provider === $this->getName() ? $this->with($callback()) : $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProvider(): Provider
    {
        return $this->provider;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Merge options.
     *
     * @param  mixed[]  $options
     * @return mixed[]
     */
    protected function options(array $options = []): array
    {
        return array_merge($this->options, $options);
    }
}
