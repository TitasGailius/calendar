<?php

namespace TitasGailius\Calendar\Providers;

use Exception;
use Microsoft\Graph\Http\GraphCollectionRequest;
use Microsoft\Graph\Model\Calendar as MicrosoftCalendar;
use TitasGailius\Calendar\Contracts\Paginator;
use TitasGailius\Calendar\Resources\Calendar;
use TitasGailius\Calendar\Resources\Paginator as GeneralPaginator;

class MicrosoftFactory
{
    /**
     * Convert to calendar array.
     *
     * @param  \Microsoft\Graph\Model\Calendar  $calendars
     */
    public static function toCalendarArray(array $calendars)
    {
        return array_map([static::class, 'toCalendar'], $calendars);
    }

    /**
     * Convert to calendar instance.
     */
    public static function toCalendar(MicrosoftCalendar $calendar): Calendar
    {
        return (new Calendar(
            provider: 'microsoft',
            id: $calendar->getId(),
            name: $calendar->getName(),
        ))->setRaw($calendar->getProperties());
    }

    /**
     * Create a new paginator instance.
     *
     * @template TValue
     * @param  class-string<TValue>  $mapper
     * @return \TitasGailius\Calendar\Contracts\Paginator<TValue>
     */
    public static function paginator(string $mapper, GraphCollectionRequest $getter): Paginator
    {
        $method = match($mapper) {
            Calendar::class => 'toCalendarArray',
            Event::class => 'toEventArray',
            default => throw new Exception('Unsupported mapper class.'),
        };

        return new GeneralPaginator(
            next: fn () => $getter->isEnd()? null: $getter->getPage(),
            mapper: fn (mixed $value) => static::{$method}($value),
        );
    }
}
