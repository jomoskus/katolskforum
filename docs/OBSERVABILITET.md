# Observabilitet

Siste lag i kanttilfelle-strategien: fange det som slipper gjennom til
produksjon, raskt nok til at det kan bli regresjonstester.

## Feilsporing: Sentry

`sentry/sentry-laravel` er installert og aktiveres av én miljøvariabel:

```
SENTRY_LARAVEL_DSN=  # tom = avslått (lokalt/test); sett i produksjon
```

Gratisnivået holder lenge for et forum. Alle uhåndterte unntak rapporteres
med forespørsels-ID (se under), rute og bruker-ID — aldri e-post eller navn
(`send_default_pii` er avslått).

## Strukturert loggkontekst

Middlewaren `AddRequestContext` legger på hver forespørsel:

- `request_id` (UUID) — returneres også som `X-Request-Id`-header til
  klienten, slik at en bruker kan oppgi ID-en ved feilmeldinger
- `user_id` (null for gjester)

Laravel `Context` sørger for at feltene følger med i alle loggmeldinger og
i køjobber. Let alltid etter `request_id` først når du feilsøker.

## Rutiner

1. **Ved alarm:** Finn `request_id` i Sentry → søk i logg → gjenskap som
   feilende test → fiks via Fabrikken → oppfør kanttilfellet i
   `docs/KANTTILFELLER.md`.
2. **Ukentlig triage:** Gå gjennom nye Sentry-grupper og 4xx/5xx-mønstre i
   loggene. Alt reelt blir issues; gjentakende agentfeil blir regler i
   `AGENTS.md`.
3. **Oppetid:** ekstern ping (f.eks. UptimeRobot, gratis) mot forsiden og
   `/up` (Laravels helsesjekk-rute).

## Produksjonsnotater

- `MAIL_MAILER=log` må byttes til ekte leverandør før lansering
  (e-postverifisering avhenger av det). Resend/Postmark har rimelige nivåer.
- `APP_DEBUG=false` i produksjon; feilsider viser aldri intern informasjon.
- Backup: SQLite-filen kopieres enkelt; vurder Litestream for kontinuerlig
  replikering når forumet er i drift.
