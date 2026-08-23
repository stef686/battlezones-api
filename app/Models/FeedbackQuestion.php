<?php

namespace App\Models;

use App\Enums\FeedbackQuestionType;
use Database\Factories\FeedbackQuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * One question on the post-event feedback form.
 *
 * Seeded rows rather than a hardcoded array: rewording a question is then a
 * seeder run rather than a deploy. The set is fixed for every Event — a
 * per-Event editor is a different feature with nothing asking for it.
 *
 * @property int $id
 * @property string $key
 * @property string $prompt
 * @property FeedbackQuestionType $type
 * @property int $display_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Database\Factories\FeedbackQuestionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedbackQuestion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedbackQuestion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedbackQuestion query()
 *
 * @mixin \Eloquent
 */
class FeedbackQuestion extends Model
{
    /** @use HasFactory<FeedbackQuestionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'prompt',
        'type',
        'display_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => FeedbackQuestionType::class,
            'display_order' => 'integer',
        ];
    }
}
