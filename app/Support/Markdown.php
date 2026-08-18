<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Renders user-submitted Markdown as safe HTML.
 *
 * Raw HTML is stripped and unsafe link schemes (javascript: etc.) are
 * refused, so the output can be echoed without further escaping.
 */
final class Markdown
{
    public static function toHtml(string $markdown): string
    {
        return Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 20,
            'renderer' => [
                'soft_break' => "<br />\n",
            ],
        ]);
    }
}
