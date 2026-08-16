<?php

declare(strict_types=1);

use App\Http\Controllers\PostController;
use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;
use App\Policies\PostPolicy;

covers(PostController::class, PostPolicy::class, StorePostRequest::class, Post::class);

test('guests cannot reply', function (): void {
    $thread = Thread::factory()->create();

    $this->post(route('threads.posts.store', $thread), ['body' => 'Mitt svar'])
        ->assertRedirect(route('login'));

    expect(Post::query()->count())->toBe(0);
});

test('unverified members cannot reply', function (): void {
    $thread = Thread::factory()->create();

    $this->actingAs(User::factory()->unverified()->create());

    $this->post(route('threads.posts.store', $thread), ['body' => 'Mitt svar'])
        ->assertRedirect(route('verification.notice'));

    expect(Post::query()->count())->toBe(0);
});

test('a verified member can reply to a thread', function (): void {
    $user = User::factory()->create();
    $thread = Thread::factory()->create();

    $this->actingAs($user);

    $response = $this->post(route('threads.posts.store', $thread), [
        'body' => 'Et gjennomtenkt svar.',
    ]);

    $post = Post::query()->sole();

    $response->assertRedirect(route('threads.show', $thread).'#post-'.$post->id);

    expect($post->body)->toBe('Et gjennomtenkt svar.')
        ->and($post->user_id)->toBe($user->id)
        ->and($post->thread_id)->toBe($thread->id);
});

test('replying bumps the thread to the top of the front page', function (): void {
    $thread = Thread::factory()->create();
    $originalActivity = $thread->updated_at;

    $this->travel(2)->hours();

    $this->actingAs(User::factory()->create());
    $this->post(route('threads.posts.store', $thread), ['body' => 'Ny aktivitet']);

    expect($thread->refresh()->updated_at?->isAfter($originalActivity))->toBeTrue();
});

test('invalid replies are rejected', function (array $input, string $errorField): void {
    $thread = Thread::factory()->create();

    $this->actingAs(User::factory()->create());

    $this->post(route('threads.posts.store', $thread), $input)
        ->assertSessionHasErrors($errorField);

    expect(Post::query()->count())->toBe(0);
})->with([
    'empty body' => [['body' => ''], 'body'],
    'body too long' => [['body' => str_repeat('a', 40001)], 'body'],
    'body as array' => [['body' => ['snedig' => 'array']], 'body'],
]);

test('reply validation errors use norwegian field names', function (): void {
    $thread = Thread::factory()->create();

    $this->actingAs(User::factory()->create());

    $this->post(route('threads.posts.store', $thread), ['body' => ''])
        ->assertInvalid(['body' => 'Innhold må fylles ut.']);
});

test('edit detection handles missing and skewed timestamps', function (): void {
    $post = new Post;

    expect($post->isEdited())->toBeFalse();

    $post->updated_at = now();
    expect($post->isEdited())->toBeFalse();

    $post->created_at = now();
    expect($post->isEdited())->toBeFalse();

    $post->created_at = now()->addMinute();
    expect($post->isEdited())->toBeFalse();
});

test('a reply to a full page lands on the next page', function (): void {
    $thread = Thread::factory()->create();
    Post::factory()->count(Thread::POSTS_PER_PAGE)->for($thread)->create();

    $this->actingAs(User::factory()->create());

    $response = $this->post(route('threads.posts.store', $thread), [
        'body' => 'Svar nummer '.(Thread::POSTS_PER_PAGE + 1),
    ]);

    $post = Post::query()->latest('id')->firstOrFail();

    $response->assertRedirect(
        route('threads.show', ['thread' => $thread, 'page' => 2]).'#post-'.$post->id,
    );
});

test('a reply that exactly fills the first page stays on page one', function (): void {
    $thread = Thread::factory()->create();
    Post::factory()->count(Thread::POSTS_PER_PAGE - 1)->for($thread)->create();

    $this->actingAs(User::factory()->create());

    $response = $this->post(route('threads.posts.store', $thread), [
        'body' => 'Svar som fyller siden helt',
    ]);

    $post = Post::query()->latest('id')->firstOrFail();

    $response->assertRedirect(
        route('threads.show', $thread).'#post-'.$post->id,
    );
});

test('the author can edit their own post', function (): void {
    $author = User::factory()->create();
    $post = Post::factory()->for($author, 'author')->create();

    $this->actingAs($author);

    $this->get(route('posts.edit', $post))
        ->assertOk()
        ->assertSee('Rediger innlegg');

    // Timestamps have second precision; an edit in the same second as the
    // creation would not register as edited.
    $this->travel(5)->minutes();

    $this->put(route('posts.update', $post), ['body' => 'Rettet svar.'])
        ->assertRedirect(route('threads.show', $post->thread).'#post-'.$post->id);

    expect($post->refresh()->body)->toBe('Rettet svar.')
        ->and($post->isEdited())->toBeTrue();
});

test('other members cannot edit the post', function (): void {
    $post = Post::factory()->create(['body' => 'Opprinnelig svar']);

    $this->actingAs(User::factory()->create());

    $this->get(route('posts.edit', $post))->assertForbidden();

    $this->put(route('posts.update', $post), ['body' => 'Kuppet svar'])
        ->assertForbidden();

    expect($post->refresh()->body)->toBe('Opprinnelig svar');
});

test('admins cannot edit other peoples posts', function (): void {
    $post = Post::factory()->create();

    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('posts.edit', $post))->assertForbidden();
});

test('the author can delete their own post', function (): void {
    $author = User::factory()->create();
    $post = Post::factory()->for($author, 'author')->create();
    $thread = $post->thread;

    $this->actingAs($author);

    $this->delete(route('posts.destroy', $post))
        ->assertRedirect(route('threads.show', $thread));

    expect(Post::query()->count())->toBe(0);
});

test('admins can delete any post', function (): void {
    $post = Post::factory()->create();

    $this->actingAs(User::factory()->admin()->create());

    $this->delete(route('posts.destroy', $post));

    expect(Post::query()->count())->toBe(0);
});

test('other members cannot delete the post', function (): void {
    $post = Post::factory()->create();

    $this->actingAs(User::factory()->create());

    $this->delete(route('posts.destroy', $post))->assertForbidden();

    expect(Post::query()->count())->toBe(1);
});

test('deleting a user keeps their content as deleted user', function (): void {
    $user = User::factory()->create();
    $thread = Thread::factory()->for($user, 'author')->create();
    $post = Post::factory()->for($user, 'author')->for($thread)->create();

    $user->delete();

    expect($thread->refresh()->user_id)->toBeNull()
        ->and($post->refresh()->user_id)->toBeNull()
        ->and($thread->authorName())->toBe('Slettet bruker')
        ->and($post->authorName())->toBe('Slettet bruker');
});
