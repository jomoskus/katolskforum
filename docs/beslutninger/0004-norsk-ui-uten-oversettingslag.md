# ADR-0004: Norsk UI uten oversettingslag for egne visninger

**Status:** Vedtatt (2026-08-16)

## Kontekst

Forumet er norskspråklig og skal forbli det. Startpakkens auth- og
innstillingssider bruker `__()`-kall med engelske nøkler.

## Beslutning

- Applikasjonsspråket er `nb`. Rammeverks- og startpakketekster oversettes
  av `laravel-lang` (lang/nb), som holdes oppdatert med `php artisan lang:update`.
- Egne forumvisninger skriver norsk bokmål direkte i Blade — uten `__()`.
  Ett språk gir null gevinst av nøkkelindireksjon, og direkte tekst er
  lettere å lese og reviewe.
- Kode, kommentarer, commit-meldinger og AGENTS.md er engelsk (mest
  treningsdata, best verktøystøtte). Prosessdokumentene i `docs/` er norske.
- Feltnavn i valideringsmeldinger settes på norsk i Form Requests
  (`attributes()`); merk at laravel-lang oversetter `body` til «kropp»,
  som alltid må overstyres til «innhold».

## Konsekvenser

- Skulle forumet en dag bli flerspråklig, må egne visninger refaktoreres
  til `__()`; det er en mekanisk jobb agenter gjør billig.
