<?php

namespace TitasGailius\Calendar\Providers;

use Carbon\Carbon;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use Microsoft\Graph\Model\Attendee as MicrosoftAttendee;
use Microsoft\Graph\Model\Calendar as MicrosoftCalendar;
use Microsoft\Graph\Model\DateTimeTimeZone as MicrosoftDateTimeTimeZone;
use Microsoft\Graph\Model\EmailAddress as MicrosoftEmailAddress;
use Microsoft\Graph\Model\Event as MicrosoftEvent;
use Microsoft\Graph\Model\Recipient as MicrosoftRecipient;
use Microsoft\Graph\Model\SingleValueLegacyExtendedProperty;
use TitasGailius\Calendar\Resources\Attendee;
use TitasGailius\Calendar\Resources\Calendar;
use TitasGailius\Calendar\Resources\CalendarCollection;
use TitasGailius\Calendar\Resources\Event;
use TitasGailius\Calendar\Resources\EventCollection;
use TitasGailius\Calendar\Resources\Filters;
use TitasGailius\Calendar\Resources\Organiser;
use TitasGailius\Calendar\Resources\Rsvp;

class MicrosoftFactory
{
    /**
     * Guid used as a namsepace for event metadata.
     */
    public static string $guid;

    /**
     * Metadata that should be expanded.
     *
     * @var array<int, string>
     */
    public static array $metadata = [];

    /**
     * Make a URL that points to the given event.
     */
    public static function toEventUrl(string $id, string $calendar, array $options): string
    {
        $options = static::optionsForMetadata($options);

        return $calendar === 'primary'
            ? '/me/events/'.$id.'?'.http_build_query($options)
            : '/me/calendars/'.$calendar.'/events/'.$id.'?'.http_build_query($options);
    }

    /**
     * Parse included metadata options.
     *
     * @param  array<int, string>  $include
     * @return array<string, string>
     */
    protected function optionsForMetadata(array $options): array
    {
        if (empty(static::$metadata)) {
            return $options;
        }

        if (! isset(static::$guid)) {
            throw new Exception('You need to set GUID in order to retrieve custom event data.');
        }

        $guid = static::$guid;

        $filters = array_map(function (string $name) use ($guid) {
            return "(id eq 'String {{$guid}} Name {$name}')";
        }, static::$metadata);

        $options['$expand'] = implode(',', array_filter([
            $options['$expand'] ?? null,
            ! empty($filters)
                ? 'singleValueExtendedProperties('.implode(' or ', $filters).')'
                : null,
        ]));

        return $options;
    }

    /**
     * Convert to calendar array.
     *
     * @param  \Microsoft\Graph\Model\Calendar[]  $calendars
     */
    public static function toCalendarCollection(array $calendars): CalendarCollection
    {
        return new CalendarCollection(array_map([static::class, 'toCalendar'], $calendars));
    }

    /**
     * Convert to calendar instance.
     *
     * @return \TitasGailius\Calendar\Resources\Calendar<\Microsoft\Graph\Model\Calendar>
     */
    public static function toCalendar(MicrosoftCalendar $calendar): Calendar
    {
        return new Calendar(
            provider: 'microsoft',
            id: $calendar->getId(),
            name: $calendar->getName(),
            timeZone: 'Europe/Parins',
            raw: $calendar,
        );
    }

    /**
     * Convert to event collection.
     *
     * @param  \Microsoft\Graph\Model\Event[]  $events
     */
    public static function toEventCollection(array $events): EventCollection
    {
        return new EventCollection(array_map([static::class, 'toEvent'], $events));
    }

    /**
     * Convert to calendar instance.
     */
    public static function toEvent(MicrosoftEvent $event): Event
    {
        return (new Event(
            title: $event->getSubject(),
            attendees: array_map([static::class, 'toAttendee'], $event->getAttendees()),
            start: Carbon::parse($event->getStart()->getDateTime()),
            end: Carbon::parse($event->getEnd()->getDateTime()),
            organiser: new Organiser($event->getOrganizer()->getEmailAddress()->getAddress()),
            metadata: static::toMetadata($event->getSingleValueExtendedProperties() ?? []),
        ))->existing(
            id: $event->getId(),
            provider: 'microsoft',
            raw: $event,
        );
    }

    /**
     * Parse event's metadata.
     *
     * @param  array<int, array{id: string, value: string}>  $props
     * @return array<string, string>
     */
    public static function toMetadata(array $props): array
    {
        $result = [];

        foreach ($props as $prop) {
            $result[substr($prop['id'], strpos($prop['id'], 'Name ') + 5)] = $prop['value'];
        }

        return $result;
    }

    /**
     * Instantiate a new attendee instance.
     *
     * @param  mixed[]  $attendee
     */
    public static function toAttendee(array $attendee): Attendee
    {
        return new Attendee(
            email: $attendee['emailAddress']['address'],
            name: $attendee['emailAddress']['name'] ?? null,
            rsvp: match ($attendee['status']['response']) {
                'none' => Rsvp::PENDING,
                'organizer' => Rsvp::ACCEPTED,
                'tentativelyAccepted' => Rsvp::TENTATIVE,
                'accepted' => Rsvp::ACCEPTED,
                'declined' => Rsvp::DECLINED,
                'notResponded' => Rsvp::PENDING,
                default => Rsvp::PENDING,
            },
        );
    }

    /**
     * Make a new Microsoft event.
     */
    public static function fromEvent(Event $event): MicrosoftEvent
    {
        $new = new MicrosoftEvent;

        $new->setSubject($event->title);

        if ($event->organiser) {
            $new->setOrganizer(static::fromOrganiser($event->organiser));
        }

        if (isset($event->id)) {
            $new->setId($event->id);
        }

        if (! empty($event->metadata)) {
            $new->setSingleValueExtendedProperties(static::fromMetadata($event->metadata));
        }

        return $new
            ->setAttendees(static::fromAttendeesArray($event->attendees))
            ->setStart(static::fromDate($event->start))
            ->setEnd(static::fromDate($event->end));
    }

    /**
     * Format event's metadata.
     *
     * @param  array<string, string>  $metadata
     * @return array<int, \Microsoft\Graph\Model\SingleValueLegacyExtendedProperty>
     */
    public static function fromMetadata(array $metadata): array
    {
        if (! isset(static::$guid)) {
            throw new Exception('You need to set GUID in order to store custom event data.');
        }

        return array_map(fn (string $value, string $key) => new SingleValueLegacyExtendedProperty([
            'id' => sprintf('String {%s} Name %s', static::$guid, $key),
            'value' => $value,
        ]), $metadata, array_keys($metadata));
    }

    /**
     * Make a new Microsoft attendees array.
     *
     * @param  \TitasGailius\Calendar\Resources\Attendee[]  $attendees
     * @return \Microsoft\Graph\Model\Attendee[]
     */
    public static function fromAttendeesArray(array $attendees): array
    {
        return array_map([static::class, 'fromAttendee'], $attendees);
    }

    /**
     * Make a new Microsoft attendee instance.
     */
    public static function fromAttendee(Attendee $attendee): MicrosoftAttendee
    {
        $result = new MicrosoftAttendee;

        $result->setEmailAddress(
            static::fromEmail($attendee->email)
        );

        return $result;
    }

    /**
     * Make a new Microsoft email instance.
     */
    public static function fromEmail(string $email): MicrosoftEmailAddress
    {
        return (new MicrosoftEmailAddress)->setAddress($email);
    }

    /**
     * Make a new MicrosoftDateTimeTimeZone instance.
     */
    public static function fromDate(DateTimeInterface $date): MicrosoftDateTimeTimeZone
    {
        $date = Carbon::parse($date);

        return (new MicrosoftDateTimeTimeZone)
            ->setDateTime($date->toRfc3339String())
            ->setTimeZone($date->getTimeZone()->getName());
    }

    /**
     * Make a new recipient instance.
     */
    public static function fromOrganiser(Organiser $organiser): MicrosoftRecipient
    {
        return (new MicrosoftRecipient)->setEmailAddress(
            static::fromEmail($organiser->email)
        );
    }

    /**
     * Query string from event filters.
     *
     * @param  array<string, mixed>  $options
     */
    public static function buildQueryString(Filters $filters, array $options): string
    {
        $options = static::optionsForMetadata($options);
        $options = static::optionsForFilters($options, $filters);

        return http_build_query($options);
    }

    /**
     * Build query options for the given filters.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public static function optionsForFilters(array $options, Filters $filters): array
    {
        $options = $filters->expand === true
            ? static::optionsForExpandedEvents($options, $filters)
            : static::optionsForSingleEvents($options, $filters);

        if ($limit = $filters->limit) {
            $options['$top'] = $limit;
        }

        $options['$filter'] = array_merge($options['$filter'] ?? [], $filters->options([
            'search' => fn (string $search) => ["contains(subject, '{$search}')"],
        ]));

        if (empty($options['$filter'])) {
            unset($options['$filter']);
        } else {
            $options['$filter'] = implode(' and ', $options['$filter']);
        }

        return $options;
    }

    /**
     * Get query values for expanded events.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public static function optionsForExpandedEvents(array $options, Filters $filters): array
    {
        if ($filters->expand && (is_null($filters->start) || is_null($filters->end))) {
            throw new InvalidArgumentException('Start and end dates must be specified when expanding Microsoft events.');
        }

        $options['startDateTime'] = Carbon::parse($filters->start)->toRfc3339String();
        $options['endDateTime'] = Carbon::parse($filters->end)->toRfc3339String();

        return $options;
    }

    /**
     * Get query valeus for single events.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public static function optionsForSingleEvents(array $options, Filters $filters): array
    {
        $options['$filter'] = array_merge($options['$filter'] ?? [], $filters->options([
            'start' => fn (DateTimeInterface $start) => [
                'start/dateTime ge \''.Carbon::parse($start)->toRfc3339String().'\'',
            ],
            'end' => fn (DateTimeInterface $end) => [
                'end/dateTime le \''.Carbon::parse($end)->toRfc3339String().'\'',
            ],
        ]));

        return $options;
    }
}
