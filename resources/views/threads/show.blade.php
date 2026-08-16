@use('App\Enums\ThreadKind')

<x-layouts::forum :title="$thread->title">
    <article>
        <header>
            <div class="flex flex-wrap items-center gap-2">
                <x-thread-kind-badge :kind="$thread->kind" />
                <flux:heading size="xl" level="1">{{ $thread->title }}</flux:heading>
            </div>
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                Startet av {{ $thread->authorName() }}
                @if ($thread->created_at)
                    · <x-local-time :time="$thread->created_at" />
                @endif
            </p>
        </header>

        @if ($thread->kind === ThreadKind::Link && $thread->url !== null)
            <a
                href="{{ $thread->url }}"
                rel="nofollow ugc noopener noreferrer"
                target="_blank"
                class="mt-4 flex items-center gap-2 rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-600"
            >
                <flux:icon.arrow-up-right class="size-4 shrink-0 text-zinc-500" />
                <span class="truncate font-medium text-zinc-900 dark:text-white">{{ $thread->url }}</span>
                @if ($thread->linkHost())
                    <span class="ms-auto shrink-0 text-zinc-500 dark:text-zinc-400">{{ $thread->linkHost() }}</span>
                @endif
            </a>
        @endif

        <x-markdown :text="$thread->body" class="mt-5" />

        @auth
            <div class="mt-4 flex items-center gap-3 text-sm">
                @can('update', $thread)
                    <flux:button :href="route('threads.edit', $thread)" variant="ghost" size="sm" wire:navigate>
                        Rediger
                    </flux:button>
                @endcan
                @can('delete', $thread)
                    <form
                        method="POST"
                        action="{{ route('threads.destroy', $thread) }}"
                        onsubmit="return confirm('Er du sikker på at du vil slette tråden?');"
                    >
                        @csrf
                        @method('DELETE')
                        <flux:button type="submit" variant="danger" size="sm">Slett tråd</flux:button>
                    </form>
                @endcan
            </div>
        @endauth
    </article>

    <section class="mt-10" id="svar" aria-label="Svar">
        <flux:heading size="lg" level="2">{{ $thread->posts_count }} svar</flux:heading>

        <div class="mt-4 space-y-4">
            @forelse ($posts as $post)
                <article
                    id="post-{{ $post->id }}"
                    class="rounded-xl border border-zinc-200 bg-white p-4 target:border-accent dark:border-zinc-700 dark:bg-zinc-900"
                >
                    <header class="flex flex-wrap items-center justify-between gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                        <div class="flex items-center gap-2">
                            <flux:avatar size="xs" :name="$post->authorName()" />
                            <span class="font-medium text-zinc-900 dark:text-white">{{ $post->authorName() }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            @if ($post->created_at)
                                <a href="#post-{{ $post->id }}" class="hover:underline">
                                    <x-local-time :time="$post->created_at" />
                                </a>
                            @endif
                            @if ($post->isEdited())
                                <span class="italic">(redigert)</span>
                            @endif
                            @can('update', $post)
                                <a href="{{ route('posts.edit', $post) }}" class="hover:underline" wire:navigate>
                                    Rediger
                                </a>
                            @endcan
                            @can('delete', $post)
                                <form
                                    method="POST"
                                    action="{{ route('posts.destroy', $post) }}"
                                    onsubmit="return confirm('Er du sikker på at du vil slette innlegget?');"
                                    class="inline"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="cursor-pointer text-red-600 hover:underline dark:text-red-400">
                                        Slett
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </header>

                    <x-markdown :text="$post->body" class="mt-3" />
                </article>
            @empty
                <p class="text-zinc-500 dark:text-zinc-400">Ingen svar ennå.</p>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $posts->links() }}
        </div>

        <div class="mt-8">
            @auth
                @if (auth()->user()->hasVerifiedEmail())
                    <form method="POST" action="{{ route('threads.posts.store', $thread) }}">
                        @csrf
                        <flux:textarea
                            name="body"
                            rows="5"
                            label="Skriv et svar"
                            description="Markdown støttes."
                            required
                        >{{ old('body') }}</flux:textarea>
                        <flux:button type="submit" variant="primary" class="mt-3">Publiser svar</flux:button>
                    </form>
                @else
                    <flux:text>
                        Du må <a href="{{ route('verification.notice') }}" class="underline">bekrefte e-postadressen din</a>
                        før du kan skrive innlegg.
                    </flux:text>
                @endif
            @else
                <flux:text>
                    <a href="{{ route('login') }}" class="underline" wire:navigate>Logg inn</a>
                    eller
                    <a href="{{ route('register') }}" class="underline" wire:navigate>registrer deg</a>
                    for å delta i samtalen.
                </flux:text>
            @endauth
        </div>
    </section>
</x-layouts::forum>
