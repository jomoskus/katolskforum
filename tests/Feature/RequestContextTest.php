<?php

declare(strict_types=1);

use App\Http\Middleware\AddRequestContext;
use App\Models\User;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;

covers(AddRequestContext::class);

test('every response carries a request id header', function (): void {
    $response = $this->get(route('home'));

    $requestId = $response->headers->get('X-Request-Id');

    expect($requestId)->toBeString()
        ->and(Str::isUuid((string) $requestId))->toBeTrue()
        ->and(Context::get('request_id'))->toBe($requestId);
});

test('a well-formed incoming request id is reused', function (): void {
    $incoming = (string) Str::uuid();

    $this->withHeader('X-Request-Id', $incoming)
        ->get(route('home'))
        ->assertHeader('X-Request-Id', $incoming);
});

test('a malformed incoming request id is replaced', function (): void {
    $response = $this->withHeader('X-Request-Id', "junk\nX-Evil: 1")
        ->get(route('home'));

    $requestId = (string) $response->headers->get('X-Request-Id');

    expect(Str::isUuid($requestId))->toBeTrue();
});

test('the acting user is added to the log context', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('home'));

    expect(Context::get('user_id'))->toBe($user->id);
});

test('guests have a null user in the log context', function (): void {
    $this->get(route('home'));

    expect(Context::get('user_id'))->toBeNull();
});
