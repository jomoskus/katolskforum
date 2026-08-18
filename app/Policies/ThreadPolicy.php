<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Thread;
use App\Models\User;

class ThreadPolicy
{
    /**
     * Only the author may edit a thread.
     */
    public function update(User $user, Thread $thread): bool
    {
        return $user->id === $thread->user_id;
    }

    /**
     * The author may delete a thread as long as nobody has replied;
     * administrators may always delete (moderation).
     */
    public function delete(User $user, Thread $thread): bool
    {
        if ($user->is_admin) {
            return true;
        }

        return $user->id === $thread->user_id
            && $thread->posts()->doesntExist();
    }
}
