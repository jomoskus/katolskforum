<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    public function store(StorePostRequest $request, Thread $thread): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $post = new Post($request->validated());
        $post->thread()->associate($thread);
        $post->author()->associate($user);
        $post->save();

        $lastPage = intdiv($thread->posts()->count() - 1, Thread::POSTS_PER_PAGE) + 1;

        return to_route('threads.show', [
            'thread' => $thread,
            'page' => $lastPage > 1 ? $lastPage : null,
        ])
            ->withFragment('post-'.$post->id)
            ->with('status', 'Innlegget er publisert.');
    }

    public function edit(Post $post): View
    {
        Gate::authorize('update', $post);

        return view('posts.edit', [
            'post' => $post,
        ]);
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        Gate::authorize('update', $post);

        $post->update($request->validated());

        return to_route('threads.show', $post->thread)
            ->withFragment('post-'.$post->id)
            ->with('status', 'Innlegget er oppdatert.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        Gate::authorize('delete', $post);

        $thread = $post->thread;

        $post->delete();

        return to_route('threads.show', $thread)
            ->with('status', 'Innlegget er slettet.');
    }
}
