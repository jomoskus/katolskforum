# ADR-0009: Nightwatch, ikke Sentry

**Status:** Vedtatt (2026-08-16)

## Kontekst

Grunnmuren hadde `sentry/sentry-laravel` for feilsporing. Begrunnelsen
var kjenthet og et gratisnivå. Laravel har et eget produkt,
Nightwatch, med gratisnivå (300k hendelser/mnd), EU-lagring, og
førsteparts støtte i Boost. Prinsippet «unngå avvik fra rammeverket»
taler for Nightwatch. Sentry-valget var vane, ikke et reelt
kostnadshinder.

## Beslutning

`laravel/nightwatch` er observabiliteten i produksjon. Sentry brukes
ikke.

- Avslått lokalt og i test (`NIGHTWATCH_ENABLED=false`, allerede i
  `phpunit.xml`).
- I produksjon: `NIGHTWATCH_TOKEN` pluss agenten
  (`php artisan nightwatch:agent`) som systemd/supervisor-tjeneste.
- Datregion **EU** når appen opprettes i Nightwatch.
- Ikke slå på `NIGHTWATCH_CAPTURE_REQUEST_PAYLOAD`. Unntak samples
  med rate 1; forespørsler kan samples ned hvis kvoten kniper.
- `AddRequestContext` (`X-Request-Id`) beholdes — det er vår
  korrelasjons-ID mot brukere og logger, uavhengig av Nightwatch.
- Ekstern oppetids-ping (f.eks. UptimeRobot mot `/up`) er ikke
  Nightwatch; den blir.

## Konsekvenser

- Én ekstra prosess i produksjon, på linje med køarbeideren.
- Agenter skal ikke legge Sentry tilbake «fordi alle kan det».
- Token og agent er menneskets jobb før lansering, se
  `docs/OBSERVABILITET.md`.
