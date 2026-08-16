<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreThreadRequest;
use App\Http\Requests\UpdateThreadRequest;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ThreadController extends Controller
{
    public const int THREADS_PER_PAGE = 25;

    /**
     * The forum front page: all threads, most recently active first.
     */
    public function index(): View
    {
        $threads = Thread::query()
            ->with('author')
            ->withCount('posts')
            ->latest('updated_at')
            ->paginate(self::THREADS_PER_PAGE);

        return view('threads.index', [
            'threads' => $threads,
        ]);
    }

    public function create(): View
    {
        return view('threads.create');
    }

    public function store(StoreThreadRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $thread = $user->threads()->create($request->validated());

        return to_route('threads.show', $thread)
            ->with('status', 'Tråden er publisert.');
    }

    public function show(Thread $thread): View
    {
        $thread->loadCount('posts');

        $posts = $thread->posts()
            ->with('author')
            ->oldest()
            ->paginate(Thread::POSTS_PER_PAGE);

        return view('threads.show', [
            'thread' => $thread,
            'posts' => $posts,
        ]);
    }

    public function edit(Thread $thread): View
    {
        Gate::authorize('update', $thread);

        return view('threads.edit', [
            'thread' => $thread,
        ]);
    }

    public function update(UpdateThreadRequest $request, Thread $thread): RedirectResponse
    {
        Gate::authorize('update', $thread);

        $thread->update($request->validated());

        return to_route('threads.show', $thread)
            ->with('status', 'Tråden er oppdatert.');
    }

    public function destroy(Thread $thread): RedirectResponse
    {
        Gate::authorize('delete', $thread);

        $thread->delete();

        return to_route('home')
            ->with('status', 'Tråden er slettet.');
    }
}
