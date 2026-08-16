<?php

use App\Models\User;
use App\Providers\FortifyServiceProvider;

covers(FortifyServiceProvider::class);

test('confirm password screen can be rendered', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('password.confirm'));

    $response->assertOk();
});
