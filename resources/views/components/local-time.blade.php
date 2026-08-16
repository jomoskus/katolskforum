@props(['time'])

<time datetime="{{ $time->toIso8601String() }}" title="{{ $time->translatedFormat('j. F Y \k\l. H:i') }}">{{ $time->diffForHumans() }}</time>
