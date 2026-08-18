<?php

declare(strict_types=1);

use App\Enums\ThreadKind;

covers(ThreadKind::class);

test('thread kinds have norwegian labels', function (): void {
    expect(ThreadKind::Article->label())->toBe('Artikkel')
        ->and(ThreadKind::Link->label())->toBe('Lenke');
});

test('thread kinds are backed by stable values', function (): void {
    expect(ThreadKind::Article->value)->toBe('article')
        ->and(ThreadKind::Link->value)->toBe('link');
});
