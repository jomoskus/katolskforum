@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <a href="{{ route('home') }}" class="me-5 flex items-center gap-2" wire:navigate>
                <span class="flex size-8 items-center justify-center rounded-md bg-accent text-sm font-semibold text-accent-foreground">Kf</span>
                <span class="text-base font-semibold text-zinc-900 dark:text-white">Katolsk forum</span>
            </a>

            <flux:spacer />

            @auth
                <flux:navbar class="me-2">
                    <flux:button :href="route('threads.create')" variant="primary" size="sm" wire:navigate>
                        Ny tråd
                    </flux:button>
                </flux:navbar>
                <x-desktop-user-menu />
            @else
                <flux:navbar class="gap-2">
                    <flux:button :href="route('login')" variant="ghost" size="sm" wire:navigate>
                        Logg inn
                    </flux:button>
                    <flux:button :href="route('register')" variant="primary" size="sm" wire:navigate>
                        Registrer deg
                    </flux:button>
                </flux:navbar>
            @endauth
        </flux:header>

        <main class="mx-auto w-full max-w-3xl px-4 py-8">
            @if (session('status'))
                <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900 dark:bg-green-950 dark:text-green-200" role="status">
                    {{ session('status') }}
                </div>
            @endif

            {{ $slot }}
        </main>

        <footer class="mx-auto w-full max-w-3xl px-4 pb-10">
            <flux:separator class="mb-4" />
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                Katolsk forum – et åpent forum for katolske temaer.
            </p>
        </footer>

        @fluxScripts
    </body>
</html>
