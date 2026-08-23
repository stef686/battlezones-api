<?php

namespace App\Models;

use Database\Factories\FeedbackResponseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One answer to one question, for one Event.
 *
 * There is deliberately no reference to the Player or their invitation: this
 * row cannot be traced back to who wrote it, which is what makes the answers
 * worth reading.
 *
 * @property int $id
 * @property int $event_id
 * @property int $feedback_question_id
 * @property int|null $rating
 * @property string|null $answer
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Event $event
 * @property-read FeedbackQuestion $question
 *
 * @method static \Database\Factories\FeedbackResponseFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedbackResponse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedbackResponse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedbackResponse query()
 *
 * @mixin \Eloquent
 */
class FeedbackResponse extends Model
{
    /** @use HasFactory<FeedbackResponseFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'feedback_question_id',
        'rating',
        'answer',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return BelongsTo<FeedbackQuestion, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(FeedbackQuestion::class, 'feedback_question_id');
    }
}
