<?php

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use App\Providers\FortifyServiceProvider;
use Laravel\Fortify\Features;

covers(CreateNewUser::class, FortifyServiceProvider::class);

beforeEach(function (): void {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function (): void {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function (): void {
    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('home', absolute: false));

    $this->assertAuthenticated();
});

test('registration fails with invalid input', function (array $input, string $errorField): void {
    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        ...$input,
    ]);

    $response->assertSessionHasErrors($errorField);

    $this->assertGuest();
    expect(User::query()->count())->toBe(0);
})->with([
    'missing name' => [['name' => ''], 'name'],
    'invalid email' => [['email' => 'not-an-email'], 'email'],
    'missing password' => [['password' => '', 'password_confirmation' => ''], 'password'],
    'password confirmation mismatch' => [['password_confirmation' => 'different'], 'password'],
    'password too short' => [['password' => 'short', 'password_confirmation' => 'short'], 'password'],
]);

test('registration fails when the email address is already taken', function (): void {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'taken@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('email');

    $this->assertGuest();
    expect(User::query()->count())->toBe(1);
});
