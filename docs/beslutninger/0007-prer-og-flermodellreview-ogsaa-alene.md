# ADR-0007: PR-er og flermodell-review — også med én utvikler

**Status:** Vedtatt (2026-08-16)

## Kontekst

Repoet har i dag ett menneske. Spørsmålet var om PR-er er nødvendige nå,
eller om man kan committe rett til `main`.

## Beslutning

All endring går via PR fra dag én, jf. `docs/FABRIKKEN.md`:

1. CI-portene kjører på PR-er og blokkerer fletting.
2. Flermodell-reviewene (Codex-review, Droid med GLM og Kimi, manuelle
   Cursor-agent-reviews) er festet til PR-er — uten PR finnes det ingen
   flate der uavhengige modeller kan kommentere, og ingen arkivert
   begrunnelse for valgene.
3. PR-en er overleveringsformatet mellom agenter: neste agent skal kunne
   lese historikken og ta over.

Anbefalt grenvern på `main` (settes i GitHub-innstillingene): krev grønn
CI og minst én godkjenning; squash-merge. Grenenes livsløp etter PR-en
står i ADR-0008.

## Konsekvenser

- Litt mer seremoni for småfiks; oppveies av at maskineriet fanger feil før
  de når `main`, og av komplett sporbarhet.
