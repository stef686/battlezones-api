<?php

namespace App\Models;

use App\Models\Concerns\HasReactions;
use Database\Factories\PhotoFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $description
 * @property string $path
 * @property string|null $thumbnail_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $url
 * @property-read string|null $thumbnail_url
 * @property-read User $user
 * @property-read int|null $reactions_count
 *
 * @mixin \Eloquent
 */
class Photo extends Model
{
    /** @use HasFactory<PhotoFactory> */
    use HasFactory;

    use HasReactions;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'path',
        'thumbnail_path',
        'user_id',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn (): string => Storage::disk('public')->url($this->path));
    }

    protected function thumbnailUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->thumbnail_path
            ? Storage::disk('public')->url($this->thumbnail_path)
            : null);
    }
}
