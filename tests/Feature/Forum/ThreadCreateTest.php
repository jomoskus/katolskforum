<?php

declare(strict_types=1);

use App\Enums\ThreadKind;
use App\Http\Controllers\ThreadController;
use App\Http\Requests\StoreThreadRequest;
use App\Models\Thread;
use App\Models\User;

covers(ThreadController::class, StoreThreadRequest::class, Thread::class);

function validThreadInput(array $overrides = []): array
{
    return [
        'title' => 'En helt ny tråd',
        'kind' => ThreadKind::Article->value,
        'body' => 'Innholdet i tråden.',
        'url' => null,
        ...$overrides,
    ];
}

test('guests cannot open the thread form or store threads', function (): void {
    $this->get(route('threads.create'))->assertRedirect(route('login'));

    $this->post(route('threads.store'), validThreadInput())
        ->assertRedirect(route('login'));

    expect(Thread::query()->count())->toBe(0);
});

test('unverified members are sent to email verification', function (): void {
    $this->actingAs(User::factory()->unverified()->create());

    $this->get(route('threads.create'))
        ->assertRedirect(route('verification.notice'));

    $this->post(route('threads.store'), validThreadInput())
        ->assertRedirect(route('verification.notice'));

    expect(Thread::query()->count())->toBe(0);
});

test('verified members can open the thread form', function (): void {
    $this->actingAs(User::factory()->create());

    $this->get(route('threads.create'))
        ->assertOk()
        ->assertSee('Start en ny tråd')
        ->assertSee('Artikkel')
        ->assertSee('Lenke');
});

test('a member can publish an article thread', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->post(route('threads.store'), validThreadInput([
        'title' => 'Tanker om pilegrimsvandring',
        'body' => 'Min tekst om vandringen.',
    ]));

    $thread = Thread::query()->sole();

    $response->assertRedirect(route('threads.show', $thread));

    expect($thread->title)->toBe('Tanker om pilegrimsvandring')
        ->and($thread->kind)->toBe(ThreadKind::Article)
        ->and($thread->body)->toBe('Min tekst om vandringen.')
        ->and($thread->url)->toBeNull()
        ->and($thread->user_id)->toBe($user->id)
        ->and($thread->slug)->toBe('tanker-om-pilegrimsvandring');
});

test('a member can publish a link thread with commentary', function (): void {
    $this->actingAs(User::factory()->create());

    $this->post(route('threads.store'), validThreadInput([
        'kind' => ThreadKind::Link->value,
        'url' => 'https://www.katolsk.no/nyheter/sak',
        'body' => 'Dette synes jeg var interessant.',
    ]));

    $thread = Thread::query()->sole();

    expect($thread->kind)->toBe(ThreadKind::Link)
        ->and($thread->url)->toBe('https://www.katolsk.no/nyheter/sak')
        ->and($thread->body)->toBe('Dette synes jeg var interessant.');
});

test('invalid thread input is rejected', function (array $overrides, string $errorField): void {
    $this->actingAs(User::factory()->create());

    $this->from(route('threads.create'))
        ->post(route('threads.store'), validThreadInput($overrides))
        ->assertRedirect(route('threads.create'))
        ->assertSessionHasErrors($errorField);

    expect(Thread::query()->count())->toBe(0);
})->with([
    'missing title' => [['title' => ''], 'title'],
    'title too long' => [['title' => str_repeat('a', 151)], 'title'],
    'missing body' => [['body' => ''], 'body'],
    'body too long' => [['body' => str_repeat('a', 40001)], 'body'],
    'missing kind' => [['kind' => ''], 'kind'],
    'unknown kind' => [['kind' => 'video'], 'kind'],
    'link without url' => [['kind' => 'link', 'url' => ''], 'url'],
    'link with invalid url' => [['kind' => 'link', 'url' => 'ikke-en-lenke'], 'url'],
    'link with javascript url' => [['kind' => 'link', 'url' => 'javascript:alert(1)'], 'url'],
    'link with ftp url' => [['kind' => 'link', 'url' => 'ftp://example.com/fil'], 'url'],
    'link with too long url' => [['kind' => 'link', 'url' => 'https://example.com/'.str_repeat('a', 2050)], 'url'],
    'title as array' => [['title' => ['snedig', 'array']], 'title'],
    'body as array' => [['body' => ['snedig' => 'array']], 'body'],
]);

test('validation errors use norwegian field names and messages', function (): void {
    $this->actingAs(User::factory()->create());

    $response = $this->post(route('threads.store'), [
        'title' => '',
        'kind' => ThreadKind::Link->value,
        'body' => '',
        'url' => '',
    ]);

    $response->assertInvalid([
        'title' => 'Tittel må fylles ut.',
        'body' => 'Innhold må fylles ut.',
        'url' => 'Du må oppgi en lenke når tråden er av typen «Lenke».',
    ]);

    $this->post(route('threads.store'), validThreadInput(['kind' => '']))
        ->assertInvalid(['kind' => 'Type må fylles ut.']);

    $this->post(route('threads.store'), validThreadInput([
        'kind' => ThreadKind::Link->value,
        'url' => 'ugyldig',
    ]))->assertInvalid([
        'url' => 'Lenken må være en gyldig nettadresse som starter med http:// eller https://.',
    ]);

    $this->post(route('threads.store'), validThreadInput([
        'kind' => ThreadKind::Link->value,
        'url' => 'https://example.com/'.str_repeat('a', 2050),
    ]))->assertInvalid([
        'url' => 'Lenke må ikke være større enn',
    ]);
});

test('a url sent with an article thread is discarded', function (): void {
    $this->actingAs(User::factory()->create());

    $this->post(route('threads.store'), validThreadInput([
        'kind' => ThreadKind::Article->value,
        'url' => 'https://smugleforsok.example',
    ]));

    expect(Thread::query()->sole()->url)->toBeNull();
});

test('a title without sluggable characters falls back to a readable slug', function (): void {
    $this->actingAs(User::factory()->create());

    $this->post(route('threads.store'), validThreadInput(['title' => '???']));

    expect(Thread::query()->sole()->slug)->toBe('trad');
});

test('a thread titled create does not shadow the create route', function (): void {
    $this->actingAs(User::factory()->create());

    $this->post(route('threads.store'), validThreadInput(['title' => 'Create']));

    $thread = Thread::query()->sole();

    expect($thread->getRouteKey())->toBe('create-'.$thread->id);

    $this->get(route('threads.show', $thread))->assertOk();
    $this->get(route('threads.create'))->assertOk();
});

test('two threads can share the same title', function (): void {
    $this->actingAs(User::factory()->create());

    $this->post(route('threads.store'), validThreadInput(['title' => 'Samme tittel']));
    $this->post(route('threads.store'), validThreadInput(['title' => 'Samme tittel']));

    $threads = Thread::query()->get();

    expect($threads)->toHaveCount(2)
        ->and($threads[0]->getRouteKey())->not->toBe($threads[1]->getRouteKey());

    $this->get(route('threads.show', $threads[1]))->assertOk();
});

test('emoji and unicode titles survive the round trip', function (): void {
    $this->actingAs(User::factory()->create());

    $this->post(route('threads.store'), validThreadInput(['title' => 'Bønn og faste ✝️']));

    $thread = Thread::query()->sole();

    $this->get(route('threads.show', $thread))
        ->assertOk()
        ->assertSee('Bønn og faste ✝️');
});
