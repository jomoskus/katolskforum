# ADR-0002: SQLite som standard database

**Status:** Vedtatt (2026-08-16)

## Kontekst

Laravel bruker SQLite som standard. Forumet starter med beskjeden trafikk,
og kostnadene skal holdes lave.

## Beslutning

SQLite i utvikling, test og første produksjonsfase (én rimelig VPS).
All skjemadefinisjon går gjennom migrasjoner og holder seg til portable
kolonnetyper, slik at bytte til MySQL/PostgreSQL er en driftsbeslutning,
ikke en omskriving. `kind` lagres som streng med enum-cast i modellen
(ikke databasenes enum-type) av samme grunn.

## Konsekvenser

- Null databasekostnad og -drift i starten; enkel backup (én fil + Litestream
  el.l. ved behov).
- Bytte senere krever i praksis bare ny `DB_CONNECTION` og datamigrering.
- Skrivetrafikken i et tekstforum er lav; SQLite med WAL holder lenge.
