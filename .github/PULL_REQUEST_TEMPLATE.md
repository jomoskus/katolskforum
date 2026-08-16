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

## Til mennesket

<!-- Fylles av agenten, på norsk, jf. docs/FORSTAAELSE.md:
     1) Jargongfri forklaring: hva, hvor, hvorfor
     2) Foreslått forståelsesnivå (1–3) med én linjes begrunnelse
     3) Nivå >= 2: omvisning — filene å lese, i rekkefølge, én linje per fil
     4) Nivå 3: tre kontrollspørsmål (uten svar)
     5) Dagens konsept: ett konsept fra diffen, forklart på 3–5 setninger -->

## Forståelseserklæring (fylles av mennesket før fletting)

<!-- Jf. docs/FORSTAAELSE.md. Godkjenningen din er først gyldig når dette er sant. -->

Nivå for denne endringen: 1 / 2 / 3

- [ ] Jeg har lest forklaringen og skrevet med egne ord i PR-en hva endringen gjør
- [ ] (Nivå 2+) Jeg har fulgt omvisningen og skrevet teach-back (3–5 setninger)
- [ ] (Nivå 3) Jeg har besvart kontrollspørsmålene, og en annen modellfamilie har verifisert svarene
- [ ] (UI-endringer) Jeg har prøvd funksjonen selv
