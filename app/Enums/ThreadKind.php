<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The two ways a thread can be started: an article written by the author,
 * or a link to something elsewhere with the author's own commentary.
 */
enum ThreadKind: string
{
    case Article = 'article';
    case Link = 'link';

    public function label(): string
    {
        return match ($this) {
            self::Article => 'Artikkel',
            self::Link => 'Lenke',
        };
    }
}
