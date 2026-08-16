<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Shared behaviour for content written by a user. The author reference is
 * nullable because users can delete their account while their contributions
 * remain (displayed as "Slettet bruker").
 */
trait HasAuthor
{
    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function authorName(): string
    {
        return $this->author->name ?? 'Slettet bruker';
    }
}
