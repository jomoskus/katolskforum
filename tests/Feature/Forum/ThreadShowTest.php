<?php

declare(strict_types=1);

use App\Http\Controllers\ThreadController;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;

covers(ThreadController::class, Thread::class);

test('an article thread renders its body as formatted markdown', function (): void {
    $thread = Thread::factory()->create([
        'title' => 'En lesverdig artikkel',
        'body' => "**Viktig poeng** her.\n\nNytt avsnitt.",
    ]);

    $this->get(route('threads.show', $thread))
        ->assertOk()
        ->assertSee('En lesverdig artikkel')
        ->assertSee('<strong>Viktig poeng</strong>', escape: false)
        ->assertSee('Startet av '.$thread->authorName());
});

test('a link thread shows the link with safe rel attributes', function (): void {
    $thread = Thread::factory()->link()->create([
        'url' => 'https://www.katolsk.no/nyheter/sak',
        'body' => 'Min kommentar til saken.',
    ]);

    $response = $this->get(route('threads.show', $thread))->assertOk();

    $response->assertSee('https://www.katolsk.no/nyheter/sak')
        ->assertSee('rel="nofollow ugc noopener noreferrer"', escape: false)
        ->assertSee('katolsk.no')
        ->assertSee('Min kommentar til saken.');
});

test('malicious markdown in threads is neutralised', function (): void {
    $thread = Thread::factory()->create([
        'body' => "<script>alert('xss')</script>\n\n[lenke](javascript:alert(1))",
    ]);

    $response = $this->get(route('threads.show', $thread))->assertOk();

    $content = (string) $response->getContent();

    expect($content)->not->toContain("<script>alert('xss')</script>")
        ->and($content)->not->toContain('javascript:alert');
});

test('posts are listed oldest first', function (): void {
    $thread = Thread::factory()->create();

    $first = Post::factory()->for($thread)->create(['body' => 'Første svar i tråden']);
    $this->travel(10)->minutes();
    $second = Post::factory()->for($thread)->create(['body' => 'Andre svar i tråden']);

    $response = $this->get(route('threads.show', $thread))->assertOk();

    $content = (string) $response->getContent();

    expect(strpos($content, 'Første svar i tråden'))
        ->toBeLessThan(strpos($content, 'Andre svar i tråden'));
});

test('posts are paginated', function (): void {
    $thread = Thread::factory()->create();
    Post::factory()->count(Thread::POSTS_PER_PAGE + 1)->for($thread)->create();

    $this->get(route('threads.show', $thread))
        ->assertOk()
        ->assertSee('page=2');
});

test('the reply count is shown in the thread', function (): void {
    $thread = Thread::factory()->create();
    Post::factory()->count(2)->for($thread)->create();

    $this->get(route('threads.show', $thread))
        ->assertOk()
        ->assertSee('2 svar');
});

test('link host extraction handles odd urls', function (?string $url, ?string $expected): void {
    $thread = Thread::factory()->make(['url' => $url]);

    expect($thread->linkHost())->toBe($expected);
})->with([
    'article without url' => [null, null],
    'plain domain' => ['https://katolsk.no', 'katolsk.no'],
    'www is stripped' => ['https://www.katolsk.no/nyheter', 'katolsk.no'],
    'uppercase host is lowered' => ['HTTPS://WWW.KATOLSK.NO/SAK', 'katolsk.no'],
    'subdomain is kept' => ['https://blogg.katolsk.no/', 'blogg.katolsk.no'],
    'hostless url' => ['https:///bare-sti', null],
]);

test('posts from deleted users show a placeholder author', function (): void {
    $thread = Thread::factory()->create();
    Post::factory()->fromDeletedUser()->for($thread)->create(['body' => 'Etterlatt svar']);

    $this->get(route('threads.show', $thread))
        ->assertOk()
        ->assertSee('Etterlatt svar')
        ->assertSee('Slettet bruker');
});

test('edited posts are marked as edited', function (): void {
    $thread = Thread::factory()->create();
    $post = Post::factory()->for($thread)->create();

    $this->get(route('threads.show', $thread))->assertDontSee('(redigert)');

    $this->travel(5)->minutes();
    $post->update(['body' => 'Oppdatert innhold']);

    $this->get(route('threads.show', $thread))->assertSee('(redigert)');
});

test('guests are invited to log in to reply', function (): void {
    $thread = Thread::factory()->create();

    $this->get(route('threads.show', $thread))
        ->assertOk()
        ->assertSee('Logg inn')
        ->assertDontSee('Skriv et svar');
});

test('unverified members are asked to verify their email', function (): void {
    $thread = Thread::factory()->create();

    $this->actingAs(User::factory()->unverified()->create());

    $this->get(route('threads.show', $thread))
        ->assertOk()
        ->assertSee('bekrefte e-postadressen')
        ->assertDontSee('Skriv et svar');
});

test('verified members see the reply form', function (): void {
    $thread = Thread::factory()->create();

    $this->actingAs(User::factory()->create());

    $this->get(route('threads.show', $thread))
        ->assertOk()
        ->assertSee('Skriv et svar');
});

test('a stale thread url redirects permanently to the canonical url', function (): void {
    $thread = Thread::factory()->create(['title' => 'Opprinnelig tittel']);
    $staleKey = $thread->getRouteKey();

    $thread->update(['title' => 'Helt ny tittel']);

    $this->get('/threads/'.$staleKey)
        ->assertPermanentRedirect(route('threads.show', $thread->refresh()));
});

test('unknown thread urls give 404', function (string $path): void {
    $this->get($path)->assertNotFound();
})->with([
    'nonexistent id' => ['/threads/finnes-ikke-99999'],
    'no id suffix' => ['/threads/bare-tull'],
]);

test('only the author sees edit controls on a thread', function (): void {
    $author = User::factory()->create();
    $thread = Thread::factory()->for($author, 'author')->create();

    $this->actingAs($author);
    $this->get(route('threads.show', $thread))->assertSee('Rediger');

    $this->actingAs(User::factory()->create());
    $this->get(route('threads.show', $thread))->assertDontSee('Rediger');
});
