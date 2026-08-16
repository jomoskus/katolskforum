## Hva

<!-- Kort beskrivelse av endringen. Lenk til issue/spesifikasjon. -->

## Hvorfor

<!-- Hvilket behov eller problem løser dette? -->

## Kanttilfeller

<!-- Hvilke kanttilfeller er vurdert, og hvordan er de dekket av tester?
     Oppdater docs/KANTTILFELLER.md ved nye funn. -->

## Fabrikken-sjekkliste

- [ ] Spesifikasjonen/planen er fulgt (eller avvik er begrunnet under)
- [ ] `composer test` er grønn lokalt (Pint, Rector, PHPStan max, dekning >= 95 %, typedekning 100 %)
- [ ] `composer test:mutation` er grønn (score >= 85 %), og nye testfiler bruker `covers()`
- [ ] Nye kanttilfeller er dokumentert i `docs/KANTTILFELLER.md` og testet
- [ ] Domenespråket i `docs/DOMENE.md` er fulgt (og oppdatert ved behov)
- [ ] Eventuelle arkitekturvalg er nedfelt som ADR i `docs/beslutninger/`

## Flermodell-gjennomgang

<!-- Fylles ut/verifiseres før fletting, jf. docs/FABRIKKEN.md steg 5 -->

- [ ] Codex-review har vurdert PR-en
- [ ] Droid/GLM har vurdert PR-en (automatisk workflow)
- [ ] Droid/Kimi har vurdert PR-en (automatisk workflow)
- [ ] Ved større endringer: manuell Cursor-agent-review er gjennomført
- [ ] Alle funn er enten rettet eller eksplisitt avvist med begrunnelse i tråden
