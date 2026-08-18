<?php

declare(strict_types=1);

use App\Http\Controllers\ThreadController;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;

covers(ThreadController::class);

test('the front page renders for guests', function (): void {
    $thread = Thread::factory()->create(['title' => 'En synlig tråd']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('En synlig tråd')
        ->assertSee($thread->authorName());
});

test('the front page shows an empty state without threads', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Ingen tråder ennå');
});

test('threads are ordered by last activity', function (): void {
    $old = Thread::factory()->create(['title' => 'Gammel tråd']);
    $fresh = Thread::factory()->create(['title' => 'Fersk tråd']);

    $this->travel(1)->hours();

    // A reply bumps the old thread to the top.
    Post::factory()->for($old)->create();

    $response = $this->get(route('home'))->assertOk();

    $content = $response->getContent();

    expect($content)->toBeString()
        ->and(strpos((string) $content, 'Gammel tråd'))
        ->toBeLessThan(strpos((string) $content, 'Fersk tråd'));
});

test('the front page shows the reply count', function (): void {
    $thread = Thread::factory()->create();
    Post::factory()->count(3)->for($thread)->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('3 svar');
});

test('the front page paginates threads', function (): void {
    Thread::factory()->count(ThreadController::THREADS_PER_PAGE + 1)->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('page=2');

    $this->get(route('home', ['page' => 2]))->assertOk();
});

test('threads from deleted users show a placeholder author', function (): void {
    Thread::factory()->fromDeletedUser()->create(['title' => 'Foreldreløs tråd']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Foreldreløs tråd')
        ->assertSee('Slettet bruker');
});

test('link threads show the linked domain', function (): void {
    Thread::factory()->link()->create(['url' => 'https://www.katolsk.no/nyheter/sak']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('katolsk.no');
});

test('guests see login and register, members see new thread button', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Logg inn')
        ->assertSee('Registrer deg')
        ->assertDontSee('Ny tråd');

    $this->actingAs(User::factory()->create());

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Ny tråd');
});
