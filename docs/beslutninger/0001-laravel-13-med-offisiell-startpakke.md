# ADR-0001: Laravel 13 med offisiell Livewire-startpakke

**Status:** Vedtatt (2026-08-16)

## Kontekst

Forumet skal bygges på Laravel med standardløsninger, lav kostnad og størst
mulig gjenkjennelighet for KI-agenter (rikelig treningsdata, offisielle
konvensjoner).

## Beslutning

- Laravel 13 (nyeste hovedversjon) med den offisielle Livewire-startpakken:
  Fortify-basert autentisering (innlogging, registrering, e-postverifisering,
  2FA, passkeys), Livewire 4, Flux UI (gratiskomponenter) og Tailwind 4.
- Selve forumet bygges som klassisk MVC: controllere, Form Requests,
  policies og Blade-visninger. Livewire brukes bare der startpakken alt
  bruker det (innstillinger). Enkel interaktivitet løses med Alpine.
- PHP-krav følger startpakken (^8.3). Produksjon bør kjøre nyeste stabile PHP.

## Konsekvenser

- Autentisering, 2FA og passkeys vedlikeholdes oppstrøms; vi skriver det ikke selv.
- Klassisk MVC er det mønsteret agenter kan best; mindre magi, lettere review.
- Flux Pro (betalt) unngås; ved behov bygges komponenter med Tailwind.
