<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Validation\UncompromisedVerifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

covers(AppServiceProvider::class, FortifyServiceProvider::class);

function passwordPassesInProduction(string $password): bool
{
    $validator = Validator::make(
        ['password' => $password],
        ['password' => Password::default()],
    );

    return $validator->passes();
}

beforeEach(function (): void {
    $this->app['env'] = 'testing';
});

test('dates resolve as immutable instances', function (): void {
    expect(now())->toBeInstanceOf(CarbonImmutable::class);
});

test('destructive database commands are prohibited in production', function (): void {
    $this->app['env'] = 'production';

    (new AppServiceProvider($this->app))->boot();

    try {
        $this->artisan('db:wipe')->assertFailed();
    } finally {
        DB::prohibitDestructiveCommands(false);
    }
});

describe('production password defaults', function (): void {
    beforeEach(function (): void {
        $this->app['env'] = 'production';
        $this->mock(UncompromisedVerifier::class)
            ->shouldReceive('verify')
            ->andReturnTrue();
    });

    test('a strong twelve character password passes', function (): void {
        expect(passwordPassesInProduction('Ab3$efghijkl'))->toBeTrue();
    });

    test('an eleven character password fails the minimum length', function (): void {
        expect(passwordPassesInProduction('Ab3$efghijk'))->toBeFalse();
    });

    test('a password without mixed case fails', function (): void {
        expect(passwordPassesInProduction('ab3$efghijkl'))->toBeFalse();
    });

    test('a password without numbers fails', function (): void {
        expect(passwordPassesInProduction('Abc$efghijkl'))->toBeFalse();
    });

    test('a password without symbols fails', function (): void {
        expect(passwordPassesInProduction('Ab3defghijkl'))->toBeFalse();
    });
});

test('compromised passwords are rejected in production', function (): void {
    $this->app['env'] = 'production';
    $this->mock(UncompromisedVerifier::class)
        ->shouldReceive('verify')
        ->andReturnFalse();

    expect(passwordPassesInProduction('Ab3$efghijkl'))->toBeFalse();
});

test('local environment uses relaxed password defaults', function (): void {
    $validator = Validator::make(
        ['password' => 'password'],
        ['password' => Password::default()],
    );

    expect($validator->passes())->toBeTrue();
});

describe('rate limiters', function (): void {
    test('two factor attempts are keyed by login session id', function (): void {
        $limiter = RateLimiter::limiter('two-factor');

        $request = Request::create('/two-factor-challenge', 'POST');
        $request->setLaravelSession($this->app['session']->driver());
        $request->session()->put('login.id', 42);

        $limit = $limiter($request);

        expect($limit)->toBeInstanceOf(Limit::class)
            ->and($limit->maxAttempts)->toBe(5)
            ->and($limit->key)->toBe(42);
    });

    test('login attempts are keyed by lowercased username and ip', function (): void {
        $limiter = RateLimiter::limiter('login');

        $request = Request::create('/login', 'POST', ['email' => 'Test@Example.com']);

        $limit = $limiter($request);

        expect($limit->maxAttempts)->toBe(5)
            ->and($limit->key)->toBe('test@example.com|127.0.0.1');
    });

    test('passkey attempts are keyed by credential id and ip', function (): void {
        $limiter = RateLimiter::limiter('passkeys');

        $request = Request::create('/passkeys', 'POST', ['credential' => ['id' => 'credential-123']]);

        $limit = $limiter($request);

        expect($limit->maxAttempts)->toBe(10)
            ->and($limit->key)->toBe('credential-123|127.0.0.1');
    });

    test('passkey attempts fall back to the session id without a credential', function (): void {
        $limiter = RateLimiter::limiter('passkeys');

        $request = Request::create('/passkeys', 'POST');
        $request->setLaravelSession($this->app['session']->driver());
        $sessionId = $request->session()->getId();

        $limit = $limiter($request);

        expect($limit->maxAttempts)->toBe(10)
            ->and($limit->key)->toBe($sessionId.'|127.0.0.1');
    });
});
