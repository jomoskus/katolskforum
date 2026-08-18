<x-layouts::forum>
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Katolsk forum</flux:heading>
            <flux:text class="mt-1">Artikler, lenker og samtale om katolske temaer. Alle kan lese – logg inn for å delta.</flux:text>
        </div>

        @auth
            <flux:button :href="route('threads.create')" variant="primary" size="sm" wire:navigate>
                Ny tråd
            </flux:button>
        @endauth
    </div>

    <div class="mt-6 divide-y divide-zinc-200 rounded-xl border border-zinc-200 bg-white dark:divide-zinc-700 dark:border-zinc-700 dark:bg-zinc-900">
        @forelse ($threads as $thread)
            <article class="flex flex-col gap-2 p-4 sm:flex-row sm:items-center sm:justify-between sm:gap-4" @if ($loop->first) data-test="thread-row" @endif>
                <div class="min-w-0">
                    <h2 class="font-medium text-zinc-900 dark:text-white">
                        <a href="{{ route('threads.show', $thread) }}" class="hover:underline" wire:navigate>
                            {{ $thread->title }}
                        </a>
                    </h2>
                    <p class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-zinc-500 dark:text-zinc-400">
                        <x-thread-kind-badge :kind="$thread->kind" />
                        @if ($thread->linkHost())
                            <span class="truncate">{{ $thread->linkHost() }}</span>
                            <span aria-hidden="true">·</span>
                        @endif
                        <span>av {{ $thread->authorName() }}</span>
                    </p>
                </div>
                <div class="shrink-0 text-sm text-zinc-500 dark:text-zinc-400 sm:text-right">
                    <div>{{ $thread->posts_count }} svar</div>
                    @if ($thread->updated_at)
                        <div><x-local-time :time="$thread->updated_at" /></div>
                    @endif
                </div>
            </article>
        @empty
            <div class="p-8 text-center text-zinc-500 dark:text-zinc-400">
                Ingen tråder ennå. Bli den første til å starte en samtale!
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $threads->links() }}
    </div>
</x-layouts::forum>
