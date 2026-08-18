<?php

declare(strict_types=1);

use App\Enums\ThreadKind;
use App\Http\Controllers\ThreadController;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;
use App\Policies\ThreadPolicy;

covers(ThreadController::class, ThreadPolicy::class);

test('the author can open the edit form', function (): void {
    $author = User::factory()->create();
    $thread = Thread::factory()->for($author, 'author')->create();

    $this->actingAs($author);

    $this->get(route('threads.edit', $thread))
        ->assertOk()
        ->assertSee('Rediger tråd')
        ->assertSee($thread->title);
});

test('other members cannot open the edit form', function (): void {
    $thread = Thread::factory()->create();

    $this->actingAs(User::factory()->create());

    $this->get(route('threads.edit', $thread))->assertForbidden();
});

test('guests are sent to login when editing', function (): void {
    $thread = Thread::factory()->create();

    $this->get(route('threads.edit', $thread))->assertRedirect(route('login'));
});

test('the author can update their thread', function (): void {
    $author = User::factory()->create();
    $thread = Thread::factory()->for($author, 'author')->create();

    $this->actingAs($author);

    $response = $this->put(route('threads.update', $thread), [
        'title' => 'Oppdatert tittel',
        'kind' => ThreadKind::Article->value,
        'body' => 'Oppdatert innhold.',
    ]);

    $thread->refresh();

    $response->assertRedirect(route('threads.show', $thread));

    expect($thread->title)->toBe('Oppdatert tittel')
        ->and($thread->body)->toBe('Oppdatert innhold.');
});

test('other members cannot update the thread', function (): void {
    $thread = Thread::factory()->create(['title' => 'Original tittel']);

    $this->actingAs(User::factory()->create());

    $this->put(route('threads.update', $thread), [
        'title' => 'Kuppet tittel',
        'kind' => ThreadKind::Article->value,
        'body' => 'Kuppet innhold.',
    ])->assertForbidden();

    expect($thread->refresh()->title)->toBe('Original tittel');
});

test('admins cannot edit other peoples threads', function (): void {
    $thread = Thread::factory()->create();

    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('threads.edit', $thread))->assertForbidden();
});

test('switching a link thread to an article clears the url', function (): void {
    $author = User::factory()->create();
    $thread = Thread::factory()->link()->for($author, 'author')->create();

    $this->actingAs($author);

    $this->put(route('threads.update', $thread), [
        'title' => $thread->title,
        'kind' => ThreadKind::Article->value,
        'body' => $thread->body,
        'url' => $thread->url,
    ]);

    $thread->refresh();

    expect($thread->kind)->toBe(ThreadKind::Article)
        ->and($thread->url)->toBeNull();
});

test('switching an article to a link requires a url', function (): void {
    $author = User::factory()->create();
    $thread = Thread::factory()->for($author, 'author')->create();

    $this->actingAs($author);

    $this->put(route('threads.update', $thread), [
        'title' => $thread->title,
        'kind' => ThreadKind::Link->value,
        'body' => $thread->body,
        'url' => '',
    ])->assertSessionHasErrors('url');

    expect($thread->refresh()->kind)->toBe(ThreadKind::Article);
});

test('changing the title changes the url and redirects the old one', function (): void {
    $author = User::factory()->create();
    $thread = Thread::factory()->for($author, 'author')->create(['title' => 'Første utkast']);
    $staleKey = $thread->getRouteKey();

    $this->actingAs($author);

    $this->put(route('threads.update', $thread), [
        'title' => 'Endelig tittel',
        'kind' => ThreadKind::Article->value,
        'body' => $thread->body,
    ]);

    $thread->refresh();

    expect($thread->slug)->toBe('endelig-tittel');

    $this->get('/threads/'.$staleKey)
        ->assertPermanentRedirect(route('threads.show', $thread));
});

test('the author can delete a thread without replies', function (): void {
    $author = User::factory()->create();
    $thread = Thread::factory()->for($author, 'author')->create();

    $this->actingAs($author);

    $this->delete(route('threads.destroy', $thread))
        ->assertRedirect(route('home'));

    expect(Thread::query()->count())->toBe(0);
});

test('the author cannot delete a thread that has replies', function (): void {
    $author = User::factory()->create();
    $thread = Thread::factory()->for($author, 'author')->create();
    Post::factory()->for($thread)->create();

    $this->actingAs($author);

    $this->delete(route('threads.destroy', $thread))->assertForbidden();

    expect(Thread::query()->count())->toBe(1);
});

test('admins can delete any thread and replies cascade', function (): void {
    $thread = Thread::factory()->create();
    Post::factory()->count(2)->for($thread)->create();

    $this->actingAs(User::factory()->admin()->create());

    $this->delete(route('threads.destroy', $thread))
        ->assertRedirect(route('home'));

    expect(Thread::query()->count())->toBe(0)
        ->and(Post::query()->count())->toBe(0);
});

test('other members cannot delete the thread', function (): void {
    $thread = Thread::factory()->create();

    $this->actingAs(User::factory()->create());

    $this->delete(route('threads.destroy', $thread))->assertForbidden();

    expect(Thread::query()->count())->toBe(1);
});
