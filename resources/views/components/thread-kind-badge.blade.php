@props(['kind'])

<flux:badge size="sm" :color="$kind === \App\Enums\ThreadKind::Link ? 'sky' : 'emerald'">
    {{ $kind->label() }}
</flux:badge>
