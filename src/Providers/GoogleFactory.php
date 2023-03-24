<?php

namespace TitasGailius\Calendar\Providers;

use Exception;
use Carbon\Carbon;
use Google\Model;
use Google\Service\Calendar\CalendarList as GoogleCalendarList;
use Google\Service\Calendar\CalendarListEntry as GoogleCalendarListEntry;
use Google\Service\Calendar\Event as GoogleEvent;
use Google\Service\Calendar\EventAttendee as GoogleEventAttendee;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\Events as GoogleEvents;
use TitasGailius\Calendar\Contracts\Paginator;
use TitasGailius\Calendar\Resources\Attendee;
use TitasGailius\Calendar\Resources\Calendar;
use TitasGailius\Calendar\Resources\Event;
use TitasGailius\Calendar\Resources\Organiser;
use TitasGailius\Calendar\Resources\Paginator as GeneralPaginator;
use TitasGailius\Calendar\Resources\Rsvp;

class GoogleFactory
{
    /**
     * Map calendar list to calendar array.
     *
     * @return \TitasGailius\Calendar\Resources\Calendar[]
     */
    public static function toCalendarArray(GoogleCalendarList $list): array
    {
        return array_map([static::class, 'toCalendar'], $list->getItems());
    }

    /**
     * Instantiate a new calendar instance.
     */
    public static function toCalendar(GoogleCalendarListEntry $calendar): Calendar
    {
        return (new Calendar(
            provider: 'google',
            id: $calendar->getId(),
            name: $calendar->getSummary(),
        ))->setRaw(static::toArray($calendar));
    }

    /**
     * Map google events to events array.
     *
     * @return \TitasGailius\Calendar\Resources\Event[]
     */
    public static function toEventArray(GoogleEvents $events): array
    {
        return array_map([static::class, 'toEvent'], $events->getItems());
    }

    /**
     * Instantiate a new event instance.
     */
    public static function toEvent(GoogleEvent $event): Event
    {
        return (new Event(
            title: $event->getSummary(),
            attendees: array_map([static::class, 'toAttendee'], $event->getAttendees()),
            start: Carbon::parse($event->getStart()->getDateTime()),
            end: Carbon::parse($event->getEnd()->getDateTime()),
            organiser: new Organiser($event->getOrganizer()->getEmail()),
            id: $event->getId(),
        ))->setRaw(static::toArray($event));
    }

    /**
     * Instantiate a new attendee instance.
     */
    public static function toAttendee(GoogleEventAttendee $attendee): Attendee
    {
        return (new Attendee(
            email: $attendee->getEmail(),
            rsvp: match ($attendee->getResponseStatus()) {
                'needsAction' => Rsvp::PENDING,
                'declined' => Rsvp::DECLINED,
                'tentative' => Rsvp::TENTATIVE,
                'accepted' => Rsvp::ACCEPTED,
                default => Rsvp::PENDING,
            },
        ))->setRaw(static::toArray($attendee));
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
     * Create a new paginator instance.
     *
     * @template TValue
     * @param  class-string<TValue>  $mapper
     * @return \TitasGailius\Calendar\Contracts\Paginator<TValue>
     */
    public static function paginator(string $mapper, callable $getter): Paginator
    {
        $method = match($mapper) {
            Calendar::class => 'toCalendarArray',
            Event::class => 'toEventArray',
            default => throw new Exception('Unsupported mapper class.'),
        };

        return new GeneralPaginator(
            next: fn ($page) => match (true) {
                is_null($page) => $getter([]),
                is_null($page->getNextPageToken()) => null,
                default => $getter($page->getNextPageToken()),
            },
            mapper: fn (mixed $value) => static::{$method}($value),
        );
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
