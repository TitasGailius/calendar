<?php

namespace TitasGailius\Calendar\Resources;

class Recurrence extends Resource
{
    /**
     * Instantiate a new recurrence instance.
     *
     * @param  string[]  $rules
     */
    public function __construct(public array $rules)
    {
        //
    }
}
