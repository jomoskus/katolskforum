# Forståelse

KI-en skriver mesteparten av koden. Da er den største risikoen på sikt ikke
bugs — portene fanger mange av dem — men at mennesket gradvis mister evnen
til å styre: å oppdage at noe er galt, stille de riktige spørsmålene og ta
gode beslutninger. Dette dokumentet definerer *hvor mye* mennesket skal
forstå før noe flettes (forståelsesporten i Fabrikken steg 6), og *hva*
mennesket bør kunne over tid (pensum).

Prinsippet: **din godkjenning av en PR er en forståelseserklæring.** Den er
først gyldig når kravene for endringens nivå er oppfylt.

## Forståelsesnivåene

| Nivå | Navn | Du kan, uten hjelp... |
| --- | --- | --- |
| 1 | **Bruker** | forklare med egne ord hva endringen gjør for forumet og hvorfor den er ønsket |
| 2 | **Kartleser** | i tillegg peke på hvor i koden det skjer, følge flyten (rute → controller → policy → modell → visning) og si hvilke tester som beviser at det virker |
| 3 | **Navigatør** | i tillegg forklare hvorfor det ble løst slik, hva som ble valgt bort, hvilke kanttilfeller som gjelder — og svare riktig på kontrollspørsmål |
| 4 | **Mester** | endre eller reversere endringen selv, uten agent |

Nivå 4 er et mål å vokse mot (pensumet peker dit), men aldri et krav for
fletting — det er det vi har agenter til.

## Krav per endringstype

| Endringstype | Minstenivå |
| --- | --- |
| Tekst, dokumentasjon, avhengighetsoppdateringer uten API-endring | 1 |
| Vanlig funksjonalitet (visninger, felter, validering, UI) | 2 |
| Datamodell og migrasjoner | 3 |
| Autorisasjon, autentisering, sikkerhet | 3 |
| Personvern og sletting av data | 3 |
| Kvalitetsporter, CI og Fabrikken selv | 3 |
| Arkitekturvalg (alt som får ADR) | 3 |

Er du i tvil om typen: velg det høyeste aktuelle nivået.

## Slik oppfylles nivåene

Agenten forbereder («Til mennesket»-seksjonen i PR-en), mennesket beviser:

**Nivå 1:**
- Les agentens forklaring.
- Skriv én setning med egne ord i PR-en (kommentar eller i
  forståelseserklæringen): «Denne endringen …». Ikke kopier agentens tekst.

**Nivå 2 (i tillegg):**
- Følg omvisningen: åpne filene agenten lister opp, i rekkefølge.
- Skriv 3–5 setninger teach-back i PR-en: hva skjer, hvor, og hvilken test
  ville feilet om det ble ødelagt.
- Be gjerne en agent verifisere teach-backen mot diffen («verifiser
  forståelseserklæringen min»); ved UI-endringer: prøv funksjonen selv
  (`composer dev`).

**Nivå 3 (i tillegg):**
- Svar på agentens tre kontrollspørsmål i en PR-kommentar, uten å slå opp
  først.
- En modell fra en **annen familie** enn implementasjonsagenten verifiserer
  svarene mot diffen og påpeker hull. Hull = les mer, svar på nytt.
- Har endringen ADR: les ADR-en og nevn i svaret ditt minst ett alternativ
  som ble valgt bort, og hvorfor.

Tidsbruken er bevisst: nivå 1 tar under ett minutt, nivå 2 noen minutter,
nivå 3 et kvarter. Det er prisen for å forbli sjef i eget repo.

## Pensum: hva mennesket bør vite og kunne

### Nå (for å styre trygt fra dag én)

- Kjøre forumet lokalt: `composer setup`, `composer dev`,
  `php artisan migrate:fresh --seed`.
- Lese resultatet av `composer test` og vite hva hver port sjekker
  (stil, refaktorering, typer, tester, dekning, mutasjon) — og hvorfor
  ingen port kan svekkes for å bli grønn.
- PR-flyten: gren → PR → porter → reviews → forståelsesport →
  squash-merge → grenen slettes (`main` er den eneste langlivede grenen).
- Domenereglene i `docs/DOMENE.md`, særlig autorisasjonsmatrisen.
- Hva som aldri committes (`.env`, hemmeligheter) og hvor secrets bor
  (GitHub Actions-secrets).
- Hvordan be en agent om review, verifisering av teach-back, eller en
  forklaring på et hvilket som helst nivå («forklar som om jeg er ny»).

### Snart (for å nå nivå 2 rutinemessig)

- Laravel-flyten i dette repoet: `routes/web.php` → Form Request →
  controller → policy → Eloquent-modell → Blade-visning.
- Migrasjoner: hvorfor de er append-only, og hva `php artisan migrate` gjør.
- Eloquent-relasjonene vi bruker: `BelongsTo`, `HasMany`, `$touches`.
- Blade-komponenter (`<x-markdown>`, layouts) og hvor tekstene bor.
- Middleware, med `AddRequestContext` som konkret eksempel.
- Øvelse i repoet: «følg et svar» — start i `routes/web.php` på
  `threads.posts.store`, les `PostController::store`, `StorePostRequest`,
  `PostPolicy` og testen `PostTest::'a verified member can reply to a thread'`.

### Etter hvert (for nivå 3 på alt)

- Validering i dybden (regler, `prepareForValidation`, norske meldinger).
- Policies og Gates i dybden; hvorfor admin kan slette men ikke redigere.
- Lese en overlevende mutant fra `composer test:mutation` og skrive testen
  som dreper den.
- Tolke PHPStan-feil på maksnivå (mixed, generics på relasjoner).
- Fortify-flytene: registrering, verifisering, 2FA, passkeys.

### Ressurser

- [Laravel Bootcamp](https://bootcamp.laravel.com) — offisiell, gratis,
  bygg en app fra null; nærmest pensum for nivå 2.
- [Laravel-dokumentasjonen](https://laravel.com/docs) — oppslagsverk;
  les «the basics»-kapitlene i rekkefølge.
- Dette repoet er selv pensum: `docs/ARKITEKTUR.md` → les koden den peker på.

## Læringsrutiner

- **Dagens konsept:** hver PR forklarer ett konsept fra diffen (agenten
  velger). Over tid dekker konseptene pensumet uten egne studieøkter.
- **Prod-feil skrives av mennesket:** når en produksjonsfeil føres inn i
  `docs/KANTTILFELLER.md`, er det mennesket som formulerer oppføringen —
  å beskrive feilen er å forstå den.
- **Månedlig stikkprøve (anbefalt):** velg en tilfeldig flettet PR og
  forklar den høyt/for deg selv på nivået den krevde. Klarer du ikke,
  har forståelsesgjelden begynt å vokse — juster tempoet.
