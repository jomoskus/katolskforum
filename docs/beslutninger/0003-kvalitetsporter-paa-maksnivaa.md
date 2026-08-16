# ADR-0003: Kvalitetsporter på maksnivå

**Status:** Vedtatt (2026-08-16)

## Kontekst

Mesteparten av koden skrives av KI-agenter. Deterministiske, maskinelle
porter er den billigste og mest pålitelige måten å fange feil på — og de
gir agenter umiddelbar, presis tilbakemelding.

## Beslutning

Alle porter kjøres samlet med `composer test` og håndheves i CI:

| Port | Krav |
| --- | --- |
| Laravel Pint | Ingen stilavvik |
| Rector (m. Laravel-regelsett, strict_types, typede closures) | `--dry-run` ren |
| PHPStan/Larastan | Nivå **max**, ingen baseline, ingen ignores |
| Pest linjedekning | ≥ 95 % |
| Pest typedekning | 100 % |
| Pest mutasjonsscore | ≥ 85 % (`covers()` i alle testfiler) |
| Arkitekturtester | Pest-presets php/laravel/security + lagregler |
| `composer audit` | Ingen kjente sårbarheter |

Autofiks skjer med `composer fix` (Rector + Pint). Terskler endres kun via
egen PR med oppdatering av denne ADR-en.

## Konsekvenser

- Agenter kan aldri «bli ferdige» med rød port; feil fanges maskinelt før review.
- Mutasjonstesting tvinger fram grensetester (redigerte i praksis registeret
  i `docs/KANTTILFELLER.md` allerede første dag).
- Noe lengre CI-tid; akseptert kostnad.
