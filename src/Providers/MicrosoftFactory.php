<?php

namespace TitasGailius\Calendar\Providers;

use Carbon\Carbon;
use DateTimeInterface;
use Exception;
use Microsoft\Graph\Http\GraphCollectionRequest;
use Microsoft\Graph\Model\Attendee as MicrosoftAttendee;
use Microsoft\Graph\Model\Calendar as MicrosoftCalendar;
use Microsoft\Graph\Model\DateTimeTimeZone as MicrosoftDateTimeTimeZone;
use Microsoft\Graph\Model\EmailAddress as MicrosoftEmailAddress;
use Microsoft\Graph\Model\Event as MicrosoftEvent;
use Microsoft\Graph\Model\Recipient as MicrosoftRecipient;
use TitasGailius\Calendar\Contracts\Paginator;
use TitasGailius\Calendar\Resources\Attendee;
use TitasGailius\Calendar\Resources\Calendar;
use TitasGailius\Calendar\Resources\CalendarCollection;
use TitasGailius\Calendar\Resources\Event;
use TitasGailius\Calendar\Resources\EventCollection;
use TitasGailius\Calendar\Resources\Filters;
use TitasGailius\Calendar\Resources\GeneralPaginator;
use TitasGailius\Calendar\Resources\Organiser;
use TitasGailius\Calendar\Resources\Rsvp;

class MicrosoftFactory
{
    /**
     * Make a URL that points to the given event.
     */
    public static function toEventUrl(string $id, string $calendar): string
    {
        return $calendar === 'primary'
            ? '/me/events/'.$id
            : '/me/calendars/'.$calendar.'/events/'.$id;
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
     */
    public static function toCalendar(MicrosoftCalendar $calendar): Calendar
    {
        return new Calendar(
            provider: 'microsoft',
            id: $calendar->getId(),
            name: $calendar->getName(),
            raw: $calendar->getProperties(),
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
        return new Event(
            title: $event->getSubject(),
            attendees: array_map([static::class, 'toAttendee'], $event->getAttendees()),
            start: Carbon::parse($event->getStart()->getDateTime()),
            end: Carbon::parse($event->getEnd()->getDateTime()),
            organiser: new Organiser($event->getOrganizer()->getEmailAddress()->getAddress()),
            id: $event->getId(),
            raw: $event->getProperties(),
        );
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
        $new->setOrganizer(static::fromOrganiser($event->organiser));

        if ($event->id) {
            $new->setId($event->id);
        }

        return $new
            ->setAttendees(static::fromAttendeesArray($event->attendees))
            ->setStart(static::fromDate($event->start))
            ->setEnd(static::fromDate($event->end));
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
        return (new MicrosoftRecipient)->setEmailAddress(static::fromEmail($organiser->email));
    }

    /**
     * Query string from event filters.
     */
    public static function queryStringFromFilters(Filters $filters): string
    {
        $query = [];

        if ($limit = $filters->limit) {
            $query['$top'] = $limit;
        }

        $filterOptions = $filters->options([
            'start' => fn (Carbon $start) => ["start/dateTime ge '{$filters->start->toRfc3339String()}'"],
            'end' => fn (Carbon $end) => ["end/dateTime le '{$filters->start->toRfc3339String()}'"],
            'search' => fn (string $search) => ["contains(subject, '{$search}')"],
        ]);

        if (! empty($filterOptions)) {
            $query['$filter'] = implode(' and ', $filterOptions);
        }

        return http_build_query($query);
    }
}
