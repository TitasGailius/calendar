<?php

namespace TitasGailius\Calendar\Providers;

use Carbon\Carbon;
use Exception;
use Google\Model;
use Google\Service\Calendar\CalendarList as GoogleCalendarList;
use Google\Service\Calendar\CalendarListEntry as GoogleCalendarListEntry;
use Google\Service\Calendar\Event as GoogleEvent;
use Google\Service\Calendar\EventAttendee as GoogleEventAttendee;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\Events as GoogleEvents;
use TitasGailius\Calendar\Contracts\CalendarPaginator;
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

class GoogleFactory
{
    /**
     * Map calendar list to calendar array.
     */
    public static function toCalendarCollection(GoogleCalendarList $list): CalendarCollection
    {
        return new CalendarCollection(array_map([static::class, 'toCalendar'], $list->getItems()));
    }

    /**
     * Instantiate a new calendar instance.
     */
    public static function toCalendar(GoogleCalendarListEntry $calendar): Calendar
    {
        return new Calendar(
            provider: 'google',
            id: $calendar->getId(),
            name: $calendar->getSummary(),
            raw: static::toArray($calendar),
        );
    }

    /**
     * Map google events to events array.
     */
    public static function toEventCollection(GoogleEvents $events): EventCollection
    {
        return new EventCollection(array_map([static::class, 'toEvent'], $events->getItems()));
    }

    /**
     * Instantiate a new event instance.
     */
    public static function toEvent(GoogleEvent $event): Event
    {
        return new Event(
            title: $event->getSummary(),
            attendees: array_map([static::class, 'toAttendee'], $event->getAttendees()),
            start: Carbon::parse($event->getStart()->getDateTime()),
            end: Carbon::parse($event->getEnd()->getDateTime()),
            organiser: new Organiser($event->getOrganizer()->getEmail()),
            id: $event->getId(),
            raw: static::toArray($event),
        );
    }

    /**
     * Instantiate a new attendee instance.
     */
    public static function toAttendee(GoogleEventAttendee $attendee): Attendee
    {
        return new Attendee(
            email: $attendee->getEmail(),
            rsvp: match ($attendee->getResponseStatus()) {
                'needsAction' => Rsvp::PENDING,
                'declined' => Rsvp::DECLINED,
                'tentative' => Rsvp::TENTATIVE,
                'accepted' => Rsvp::ACCEPTED,
                default => Rsvp::PENDING,
            },
        );
    }

    /**
     * Instantiate a new google event instance.
     */
    public static function fromEvent(Event $event): GoogleEvent
    {
        return new GoogleEvent([
            'summary' => $event->title,
            'start' => ['dateTime' => Carbon::parse($event->start)->toRfc3339String()],
            'end' => ['dateTime' => Carbon::parse($event->end)->toRfc3339String()],
            'attendees' => array_map(fn (Attendee $attendee) => [
                'email' => $attendee->email,
                'responseStatus' => match ($attendee->rsvp) {
                    Rsvp::ACCEPTED => 'accepted',
                    Rsvp::DECLINED => 'declined',
                    Rsvp::TENTATIVE => 'tentative',
                    Rsvp::PENDING => 'needsAction',
                    default => 'needsAction',
                },
            ], $event->attendees),
        ]);
    }

    /**
     * Generate options from the given event filters.
     */
    public static function fromFilters(Filters $filters)
    {
        return $filters;
    }

    /**
     * Convert google model to an array.
     *
     * @return array<mixed>
     */
    public static function toArray(Model $model): array
    {
        $json = json_encode($model->toSimpleObject());

        if ($json === false) {
            return [];
        }

        return (array) json_decode($json, true);
    }
}
