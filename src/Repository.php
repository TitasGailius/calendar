<?php

namespace TitasGailius\Calendar;

use DateTimeInterface;
use TitasGailius\Calendar\Contracts\Paginator;
use TitasGailius\Calendar\Contracts\Provider;
use TitasGailius\Calendar\Contracts\Repository as RepositoryContract;
use TitasGailius\Calendar\Resources\Event;
use TitasGailius\Calendar\Resources\Filters;
use TitasGailius\Calendar\Resources\Organiser;
use TitasGailius\Calendar\Resources\PartialEvent;
use TitasGailius\Calendar\Resources\Recurrence;

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
    public function getEvents(
        ?DateTimeInterface $start = null,
        ?DateTimeInterface $end = null,
        bool $expand = false,
        ?int $limit = null,
        ?string $search = null,
        ?array $metadata = null,
        ?string $calendar = 'primary',
    ): Paginator {
        return $this->provider->getEvents(new Filters(...func_get_args()), $this->options);
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
    public function getEvent(string|Event $id, string $calendar = 'primary'): ?Event
    {
        return $this->provider->getEvent(self::id($id), $calendar, $this->options);
    }

    /**
     * {@inheritdoc}
     */
    public function updateEvent(
        Event|string $id,
        string $calendar = 'primary',
        ?string $title = null,
        ?DateTimeInterface $start = null,
        ?DateTimeInterface $end = null,
        array $attendees = [],
        ?Recurrence $recurrence = null,
        ?Organiser $organiser = null,
        array $metadata = [],
    ): Event {
        return $this->provider->updateEvent(new PartialEvent(...func_get_args(), id: self::id($id)));
    }

    /**
     * {@inheritdoc}
     */
    public function deleteEvent(string|Event $id, string $calendar = 'primary'): void
    {
        $this->provider->deleteEvent(self::id($id), $calendar, $this->options);
    }

    /**
     * {@inheritdoc}
     */
    public function with(array $options = []): static
    {
        return new self($this->name, $this->provider, $this->options);
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

    /**
     * Get id.
     */
    protected static function id(Event|string $id): string
    {
        return $id instanceof Event ? $id->id : $id;
    }
}
