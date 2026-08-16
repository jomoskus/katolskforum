<?php

use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use App\Providers\FortifyServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;

covers(ResetUserPassword::class, FortifyServiceProvider::class);

beforeEach(function (): void {
    $this->skipUnlessFortifyHas(Features::resetPasswords());
});

test('reset password link screen can be rendered', function (): void {
    $response = $this->get(route('password.request'));

    $response->assertOk();
});

test('reset password link can be requested', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.request'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password screen can be rendered', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.request'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification): true {
        $response = $this->get(route('password.reset', $notification->token));

        $response->assertOk();

        return true;
    });
});

test('password can be reset with valid token', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.request'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user): true {
        $response = $this->post(route('password.update'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'new-Sikker-passphrase',
            'password_confirmation' => 'new-Sikker-passphrase',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login', absolute: false));

        return true;
    });

    $user->refresh();

    expect(Hash::check('new-Sikker-passphrase', $user->password))->toBeTrue()
        ->and(Hash::check('password', $user->password))->toBeFalse();
});

test('password reset fails with invalid input', function (array $input, string $errorField): void {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.request'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user, $input, $errorField): true {
        $response = $this->post(route('password.update'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'new-Sikker-passphrase',
            'password_confirmation' => 'new-Sikker-passphrase',
            ...$input,
        ]);

        $response->assertSessionHasErrors($errorField);

        return true;
    });

    expect(Hash::check('password', $user->refresh()->password))->toBeTrue();
})->with([
    'missing password' => [['password' => '', 'password_confirmation' => ''], 'password'],
    'password confirmation mismatch' => [['password_confirmation' => 'different'], 'password'],
    'password too short' => [['password' => 'short', 'password_confirmation' => 'short'], 'password'],
]);
