# Observabilitet

Siste lag i kanttilfelle-strategien: fange det som slipper gjennom til
produksjon, raskt nok til at det kan bli regresjonstester.

## Feilsporing og innsikt: Nightwatch

Laravel Nightwatch er førsteparts overvåking (unntak, forespørsler,
spørringer, køjobber). Pakken `laravel/nightwatch` er installert og
styres av miljøvariabler, jf. ADR-0009:

```
NIGHTWATCH_ENABLED=false   # lokalt og i tester; true i produksjon
NIGHTWATCH_TOKEN=          # fra nightwatch.laravel.com, per miljø
```

`phpunit.xml` tvinger `NIGHTWATCH_ENABLED=false`, så testene sender
ingenting. Gratisnivået (300k hendelser/mnd) holder for et lite forum;
unntak skal ha sample-rate 1, mens vanlige forespørsler kan samples
ned hvis kvoten kniper. Ikke slå på innsamling av request-payload.

Agenten må kjøre i produksjon (`php artisan nightwatch:agent`) via
systemd eller Supervisor, ellers samles dataene ikke inn. Velg
**EU-region** når appen opprettes.

## Strukturert loggkontekst

Middlewaren `AddRequestContext` legger på hver forespørsel:

- `request_id` (UUID) — returneres også som `X-Request-Id`-header til
  klienten, slik at en bruker kan oppgi ID-en ved feilmeldinger
- `user_id` (null for gjester)

Laravel `Context` sørger for at feltene følger med i alle loggmeldinger og
i køjobber. Let alltid etter `request_id` først når du feilsøker.

## Rutiner

1. **Ved alarm:** Finn `request_id` i Nightwatch og i loggene →
   gjenskap som feilende test → fiks via Fabrikken → oppfør
   kanttilfellet i `docs/KANTTILFELLER.md`.
2. **Ukentlig triage:** Gå gjennom nye unntaksgrupper og 4xx/5xx-mønstre.
   Alt reelt blir issues; gjentakende agentfeil blir regler i
   `AGENTS.md`.
3. **Oppetid:** ekstern ping (f.eks. UptimeRobot, gratis) mot forsiden og
   `/up` (Laravels helsesjekk-rute). Nightwatch erstatter ikke denne.

## Produksjonsnotater

- Opprett Nightwatch-app med EU-lagring, sett `NIGHTWATCH_TOKEN` og
  `NIGHTWATCH_ENABLED=true`, og kjør agenten som tjeneste.
- `MAIL_MAILER=log` må byttes til ekte leverandør før lansering
  (e-postverifisering avhenger av det). Resend/Postmark har rimelige nivåer.
- `APP_DEBUG=false` i produksjon; feilsider viser aldri intern informasjon.
- Backup: SQLite-filen kopieres enkelt; vurder Litestream for kontinuerlig
  replikering når forumet er i drift.
