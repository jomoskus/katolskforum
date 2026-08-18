<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasAuthor;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Touches;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A reply in a {@see Thread}. Saving a post touches the thread's
 * `updated_at`, which is what the front page sorts on ("last activity").
 *
 * @property int $id
 * @property int $thread_id
 * @property int|null $user_id
 * @property string $body
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Thread $thread
 * @property-read User|null $author
 */
#[Fillable(['body'])]
#[Touches(['thread'])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasAuthor, HasFactory;

    /** @return BelongsTo<Thread, $this> */
    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    /**
     * Whether the post has been edited after it was published.
     */
    public function isEdited(): bool
    {
        return $this->created_at !== null
            && $this->updated_at !== null
            && $this->updated_at->gt($this->created_at);
    }
}
