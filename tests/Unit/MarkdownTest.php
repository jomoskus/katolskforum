<?php

declare(strict_types=1);

use App\Support\Markdown;

covers(Markdown::class);

test('markdown renders to html', function (): void {
    $html = Markdown::toHtml('**Fet tekst** og *kursiv*.');

    expect($html)->toContain('<strong>Fet tekst</strong>')
        ->and($html)->toContain('<em>kursiv</em>');
});

test('headings and lists are supported', function (): void {
    $html = Markdown::toHtml("# Overskrift\n\n- punkt en\n- punkt to");

    expect($html)->toContain('<h1>Overskrift</h1>')
        ->and($html)->toContain('<li>punkt en</li>');
});

test('raw html is stripped', function (): void {
    $html = Markdown::toHtml('Hei <script>alert("xss")</script> på deg');

    expect($html)->not->toContain('<script>')
        ->and($html)->not->toContain('alert("xss")</script>');
});

test('iframes and event handlers are stripped', function (): void {
    $html = Markdown::toHtml('<iframe src="https://evil.example"></iframe><img src="x" onerror="alert(1)">');

    expect($html)->not->toContain('<iframe')
        ->and($html)->not->toContain('onerror');
});

test('javascript links are refused', function (): void {
    $html = Markdown::toHtml('[klikk her](javascript:alert(1))');

    expect($html)->not->toContain('javascript:');
});

test('regular links are rendered', function (): void {
    $html = Markdown::toHtml('[katolsk.no](https://www.katolsk.no)');

    expect($html)->toContain('<a href="https://www.katolsk.no">katolsk.no</a>');
});

test('single line breaks become visible breaks', function (): void {
    $html = Markdown::toHtml("Første linje\nAndre linje");

    expect($html)->toContain('<br />');
});

test('pathologically nested input is capped at the nesting limit', function (): void {
    $html = Markdown::toHtml(str_repeat('> ', 100).'dypt sitat');

    expect(substr_count($html, '<blockquote>'))->toBe(20)
        ->and($html)->toContain('dypt sitat');
});

test('empty input renders to empty output', function (): void {
    expect(Markdown::toHtml(''))->toBe('');
});

test('norwegian characters survive rendering', function (): void {
    $html = Markdown::toHtml('Blåbærsyltetøy er godt.');

    expect($html)->toContain('Blåbærsyltetøy er godt.');
});
