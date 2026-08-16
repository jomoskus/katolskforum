<x-layouts::forum title="Rediger innlegg">
    <flux:heading size="xl" level="1">Rediger innlegg</flux:heading>
    <flux:text class="mt-1">
        Svar i tråden
        <a href="{{ route('threads.show', $post->thread) }}" class="underline" wire:navigate>{{ $post->thread->title }}</a>.
    </flux:text>

    <form method="POST" action="{{ route('posts.update', $post) }}" class="mt-6 space-y-6">
        @csrf
        @method('PUT')

        <flux:textarea
            name="body"
            rows="8"
            label="Innhold"
            description="Markdown støttes."
            required
        >{{ old('body', $post->body) }}</flux:textarea>

        <div class="flex items-center gap-3">
            <flux:button type="submit" variant="primary">Lagre endringer</flux:button>
            <flux:button :href="route('threads.show', $post->thread)" variant="ghost" wire:navigate>Avbryt</flux:button>
        </div>
    </form>
</x-layouts::forum>
