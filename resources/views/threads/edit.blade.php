<x-layouts::forum title="Rediger tråd">
    <flux:heading size="xl" level="1">Rediger tråd</flux:heading>

    <form
        method="POST"
        action="{{ route('threads.update', $thread) }}"
        x-data="{ kind: @js(old('kind', $thread->kind->value)) }"
        class="mt-6 space-y-6"
    >
        @csrf
        @method('PUT')

        <fieldset>
            <legend class="mb-2 text-sm font-medium text-zinc-800 dark:text-white">Type</legend>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach (\App\Enums\ThreadKind::cases() as $kind)
                    <label
                        class="flex cursor-pointer items-start gap-3 rounded-lg border border-zinc-200 p-4 has-checked:border-accent has-checked:ring-1 has-checked:ring-accent dark:border-zinc-700"
                    >
                        <input
                            type="radio"
                            name="kind"
                            value="{{ $kind->value }}"
                            x-model="kind"
                            class="mt-1"
                            required
                        />
                        <span>
                            <span class="block font-medium text-zinc-900 dark:text-white">{{ $kind->label() }}</span>
                            <span class="block text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $kind === \App\Enums\ThreadKind::Article ? 'En tekst du har skrevet selv.' : 'Noe fra nettet, med din kommentar.' }}
                            </span>
                        </span>
                    </label>
                @endforeach
            </div>
            <flux:error name="kind" />
        </fieldset>

        <div x-show="kind === 'link'" x-cloak>
            <flux:input
                type="url"
                name="url"
                label="Lenke (URL)"
                :value="old('url', $thread->url)"
                placeholder="https://…"
                maxlength="2048"
            />
        </div>

        <flux:input
            name="title"
            label="Tittel"
            :value="old('title', $thread->title)"
            required
            maxlength="150"
        />

        <flux:textarea
            name="body"
            rows="12"
            label="Innhold"
            description="Artikkelteksten din, eller kommentaren til det du lenker til. Markdown støttes."
            required
        >{{ old('body', $thread->body) }}</flux:textarea>

        <div class="flex items-center gap-3">
            <flux:button type="submit" variant="primary">Lagre endringer</flux:button>
            <flux:button :href="route('threads.show', $thread)" variant="ghost" wire:navigate>Avbryt</flux:button>
        </div>
    </form>
</x-layouts::forum>
