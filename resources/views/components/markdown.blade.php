@props(['text'])

<div {{ $attributes->merge(['class' => 'prose prose-zinc max-w-none dark:prose-invert prose-a:break-words']) }}>
    {!! \App\Support\Markdown::toHtml($text) !!}
</div>
