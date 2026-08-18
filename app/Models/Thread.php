<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ThreadKind;
use App\Models\Concerns\HasAuthor;
use Database\Factories\ThreadFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * A forum thread: either a self-written article or a link with the
 * author's own commentary. Replies live in {@see Post}.
 *
 * `updated_at` doubles as "last activity" because posts touch their
 * thread; the front page sorts on it.
 *
 * URLs are self-healing: the route key is "{slug}-{id}", lookups resolve
 * by the trailing id, and stale slugs redirect to the canonical URL.
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $title
 * @property string $slug
 * @property ThreadKind $kind
 * @property string $body
 * @property string|null $url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $author
 * @property-read Collection<int, Post> $posts
 * @property-read int|null $posts_count
 */
#[Fillable(['title', 'kind', 'body', 'url'])]
class Thread extends Model
{
    /** @use HasFactory<ThreadFactory> */
    use HasAuthor, HasFactory, HasSlug;

    public const int POSTS_PER_PAGE = 25;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => ThreadKind::class,
        ];
    }

    /** @return HasMany<Post, $this> */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * The host of the linked page (without "www."), for display next to
     * link threads. Returns null for articles and unparsable URLs.
     */
    public function linkHost(): ?string
    {
        if ($this->url === null) {
            return null;
        }

        $host = parse_url($this->url, PHP_URL_HOST);

        // parse_url() returns false or null (never '') when the host is
        // missing or the URL is seriously malformed.
        if (! is_string($host)) {
            return null;
        }

        return Str::chopStart(Str::lower($host), 'www.');
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(fn (Thread $thread): string => Str::slug($thread->title) === '' ? 'trad' : $thread->title)
            ->saveSlugsTo('slug')
            ->allowDuplicateSlugs()
            ->selfHealing();
    }
}
