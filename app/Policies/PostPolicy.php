<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Only the author may edit a post.
     */
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    /**
     * The author may delete their own post; administrators may delete
     * any post (moderation).
     */
    public function delete(User $user, Post $post): bool
    {
        return $user->is_admin || $user->id === $post->user_id;
    }
}
