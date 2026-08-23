<?php

namespace App\Enums;

/**
 * Which Attendees a Poll may be cast for.
 *
 * The type is the eligibility rule: the rest of a Poll — its name, window and
 * limit — is data an Organiser sets.
 */
enum PollType: string
{
    case Painting = 'painting';
    case FavouriteOpponent = 'favourite_opponent';

    public function label(): string
    {
        return match ($this) {
            self::Painting => 'Painting Competition',
            self::FavouriteOpponent => 'Favourite Opponent',
        };
    }
}
