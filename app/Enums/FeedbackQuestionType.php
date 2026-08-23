<?php

namespace App\Enums;

enum FeedbackQuestionType: string
{
    case Rating = 'rating';
    case Text = 'text';

    public function label(): string
    {
        return match ($this) {
            self::Rating => 'Rating',
            self::Text => 'Text',
        };
    }
}
