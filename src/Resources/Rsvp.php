<?php

namespace TitasGailius\Calendar\Resources;

use JsonSerializable;

enum Rsvp implements JsonSerializable
{
    case ACCEPTED;
    case DECLINED;
    case PENDING;
    case TENTATIVE;

    /**
     * JSON serialize.
     */
    public function jsonSerialize(): string
    {
        return match($this) {
            Rsvp::ACCEPTED => 'accepted',
            Rsvp::DECLINED => 'declined',
            Rsvp::PENDING => 'pending',
            Rsvp::TENTATIVE => 'tentative',
        };
    }
}
