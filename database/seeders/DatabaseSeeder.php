<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ThreadKind;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the database with demo content for local development.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            return;
        }

        $admin = User::factory()->admin()->create([
            'name' => 'Administrator',
            'email' => 'admin@katolskforum.test',
        ]);

        $kari = User::factory()->create([
            'name' => 'Kari Nordmann',
            'email' => 'kari@example.com',
        ]);

        $ola = User::factory()->create([
            'name' => 'Ola Nordmann',
            'email' => 'ola@example.com',
        ]);

        $welcome = Thread::factory()->for($admin, 'author')->create([
            'title' => 'Velkommen til Katolsk forum',
            'kind' => ThreadKind::Article,
            'body' => <<<'MARKDOWN'
                Velkommen!

                Dette er et åpent forum for katolske temaer. Alle kan lese, og alle med
                en registrert konto kan delta i samtalen.

                Slik starter du en tråd:

                - **Artikkel** – skriv en tekst du har forfattet selv.
                - **Lenke** – del noe fra nettet, sammen med din egen kommentar.

                Vi ber om at samtalen føres med respekt og nestekjærlighet.
                MARKDOWN,
        ]);

        Post::factory()->for($welcome)->for($kari, 'author')->create([
            'body' => 'Takk for et flott initiativ! Jeg gleder meg til gode samtaler her.',
        ]);

        $articleThread = Thread::factory()->for($kari, 'author')->create([
            'title' => 'Tanker om kirkeårets rytme',
            'kind' => ThreadKind::Article,
            'body' => <<<'MARKDOWN'
                Kirkeåret gir livet en egen rytme: advent, jul, faste, påske og pinse.

                Jeg har skrevet ned noen tanker om hvordan denne rytmen kan prege
                hverdagen, også utenfor messen. Hva tenker dere?
                MARKDOWN,
        ]);

        Post::factory()->for($articleThread)->for($ola, 'author')->create([
            'body' => 'Fin refleksjon! Særlig adventstiden har mye å lære oss om å vente.',
        ]);

        Post::factory()->for($articleThread)->for($admin, 'author')->create([
            'body' => 'Takk for teksten. Dette kunne blitt en fin serie med innlegg.',
        ]);

        Thread::factory()->for($ola, 'author')->create([
            'title' => 'Katolsk.no om valfart til Nidaros',
            'kind' => ThreadKind::Link,
            'url' => 'https://www.katolsk.no/',
            'body' => 'Interessant artikkel om pilegrimstradisjonen til Nidaros. '
                .'Har noen her gått hele eller deler av leden?',
        ]);

        Thread::factory()
            ->fromDeletedUser()
            ->has(Post::factory()->count(2), 'posts')
            ->create([
                'title' => 'Arkivtråd fra et tidligere medlem',
                'kind' => ThreadKind::Article,
                'body' => 'Denne tråden ble startet av en bruker som senere slettet kontoen sin. '
                    .'Innholdet står igjen, slik forumregler tilsier.',
            ]);
    }
}
