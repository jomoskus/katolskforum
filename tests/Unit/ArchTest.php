<?php

arch()->preset()->php();

arch()->preset()->laravel();

arch()->preset()->security();

arch('controllers do not leak business logic into views')
    ->expect('App\Http\Controllers')
    ->not->toUse(['Illuminate\Support\Facades\DB']);

arch('models are only touched from expected layers')
    ->expect('App\Models')
    ->toOnlyBeUsedIn([
        'App\Actions',
        'App\Concerns',
        'App\Http',
        'App\Livewire',
        'App\Models',
        'App\Policies',
        'App\Providers',
        'App\View',
        'Database\Factories',
        'Database\Seeders',
    ]);
