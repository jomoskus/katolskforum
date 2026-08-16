# Kanttilfeller

Vi vil fange opp så mange kanttilfeller som mulig, så tidlig som mulig, i
flere lag. Dette dokumentet beskriver strategien og fører register over
kjente kanttilfeller og hvordan de er håndtert.

## Strategien: seks lag

1. **Spesifikasjonstid.** Hver spesifikasjon har en obligatorisk
   kanttilfelle-seksjon. Minst to modellfamilier genererer kanttilfeller
   hver for seg (adversarielt) før de konsolideres — forskjellige modeller
   overser forskjellige ting. Bruk sjekklisten nederst som utgangspunkt.
2. **Testtid.** Kanttilfellene fra spesifikasjonen blir tester (gjerne
   Pest-datasets/matriser) før eller under implementasjonen. Grenseverdier
   testes eksplisitt (n-1, n, n+1).
3. **Mutasjonstesting.** `composer test:mutation` avslører logikk som ikke
   er reelt verifisert, og tvinger fram grensetester vi ikke tenkte på.
   Score-kravet gjør dette til en port, ikke en anbefaling.
4. **Statisk analyse og strenge typer.** PHPStan på maksnivå og
   `declare(strict_types=1)` fanger null-/typekanttilfeller før kjøring.
5. **Kjøretid i utvikling.** Seederen inneholder kanttilfelle-data
   (slettede brukere, lenketråder, tomme tilstander) slik at man ser dem
   hver dag, ikke bare i tester.
6. **Produksjon.** Overvåking fanger det som slapp gjennom: feilsporing,
   strukturerte logger med forespørsels-ID, ukentlig triage. Hver reell
   feil blir regresjonstest + oppføring her. Se `docs/OBSERVABILITET.md`.

## Register

Status: ✅ håndtert og testet · 📋 bevisst valg uten kode (dokumentert) · ⏳ åpent

### Innhold og inndata

| Kanttilfelle | Status | Håndtering |
| --- | --- | --- |
| Tittel uten sluggbare tegn (`???`, emoji) | ✅ | Slug faller tilbake til `trad`; test i `ThreadCreateTest` |
| Tråd med tittelen «Create» skygger `/threads/create` | ✅ | Selvhelbredende URL-er `{slug}-{id}`; test i `ThreadCreateTest` |
| To tråder med identisk tittel | ✅ | Duplikate slugs tillatt, ID skiller; test i `ThreadCreateTest` |
| Tittelendring knekker gamle lenker | ✅ | Utdaterte slugs gir 308 til kanonisk URL; test i `ThreadShowTest`/`ThreadEditTest` |
| XSS via markdown (`<script>`, `javascript:`-lenker, `onerror`) | ✅ | `html_input=strip`, `allow_unsafe_links=false`; tester i `MarkdownTest`/`ThreadShowTest` |
| Patologisk dyp markdown-nesting (DoS) | ✅ | `max_nesting_level=20`; eksakt test i `MarkdownTest` |
| Enkle linjeskift forsvinner i CommonMark | ✅ | `soft_break` rendres som `<br>`; test i `MarkdownTest` |
| Tom/for lang tittel, innhold, URL | ✅ | Valideringsmatrise i `ThreadCreateTest`/`PostTest` (maks 150/40 000/2 048) |
| Inndata som array i stedet for streng | ✅ | `string`-regel; datasets i valideringsmatrisene |
| `javascript:`- og `ftp:`-URL-er i lenketråder | ✅ | `url:http,https`-regel; test i `ThreadCreateTest` |
| URL smugles inn på artikkeltråd | ✅ | `prepareForValidation` nuller url når kind ≠ link; test |
| Bytte lenketråd → artikkel etterlater gammel URL | ✅ | Samme mekanisme ved oppdatering; test i `ThreadEditTest` |
| Norske tegn (æøå) i titler og slugs | ✅ | Translitterasjon (`kirkeårets` → `kirkearets`); test med emoji/unicode |
| Vertsløse/rare URL-er i `linkHost()` | ✅ | `parse_url`-fallback til null; datasets i `ThreadShowTest` |

### Brukere og tilgang

| Kanttilfelle | Status | Håndtering |
| --- | --- | --- |
| Bruker sletter kontoen — hva med innholdet? | ✅ | Innhold består med «Slettet bruker»; `nullOnDelete` + tester |
| Uverifisert e-post prøver å poste | ✅ | `verified`-middleware + skjult skjema med forklaring; tester |
| Gjest prøver å poste/redigere | ✅ | Redirect til innlogging; tester |
| Andre redigerer/sletter ens innhold | ✅ | Policies + 403-tester (autorisasjonsmatrise) |
| Admin redigerer andres tekst | 📋 | Bevisst nei (moderasjon = sletting, ikke omskriving); test |
| Forfatter sletter tråd med svar | 📋 | Bevisst nei (svarene tilhører fellesskapet); admin kan; tester |
| Brute force på innlogging/2FA/passkeys | ✅ | Rate limiting fra startpakken; nøkkel-tester i `SecurityDefaultsTest` |
| Svake/kompromitterte passord i produksjon | ✅ | Grensetester 11/12 tegn + HIBP-sjekk; `SecurityDefaultsTest` |

### Tid, rekkefølge og paginering

| Kanttilfelle | Status | Håndtering |
| --- | --- | --- |
| Svar nr. 26 havner på side 2 — hvor lander brukeren? | ✅ | Redirect til riktig side + `#post-{id}`; grensetester (24→25 og 25→26) |
| Redigering i samme sekund som opprettelse | ✅ | Sekundpresisjon dokumentert; `isEdited()`-tester inkl. klokkeskjevhet |
| Sortering «sist aktivitet» når svar kommer i gammel tråd | ✅ | `$touches` + sorteringstest med tidsreise |
| Tom forside (ingen tråder) | ✅ | Tom-tilstand med oppfordring; test |

### Åpne / fremtidige

| Kanttilfelle | Status | Notat |
| --- | --- | --- |
| Samtidige svar (dobbeltklikk på «Publiser svar») | ⏳ | Vurder idempotens-nøkkel eller debounce; lav risiko nå |
| Spam-registreringer tross e-postverifisering | ⏳ | Vurder honeypot/registrerings-throttling når forumet åpner |
| Svært lange ord/URL-er som sprenger layout | ✅ | `prose-a:break-words` i markdown-komponenten |
| Sletting av bruker midt i visning av side (race) | ⏳ | `authorName()` tåler null; full transaksjonssikkerhet uvurdert |

## Sjekkliste for nye spesifikasjoner

Gå gjennom kategoriene og noter relevante tilfeller i spesifikasjonen:

- **Tom / manglende:** tom streng, null, manglende relasjon, tom liste, side 0/utenfor rekkevidde
- **Størrelse:** makslengder (n-1, n, n+1), enorme inndata, dyp nesting
- **Tegn:** unicode, emoji, æøå, RTL, kontrolltegn, HTML/markdown-injeksjon
- **Type:** array der streng ventes, tall som streng, boolsk som streng
- **Tid:** samme sekund, klokkeskjevhet, tidssoner, sommertid, gamle datoer
- **Tilgang:** gjest, uverifisert, feil bruker, admin, slettet bruker
- **Samtidighet:** dobbeltklikk, to faner, sletting under lesing
- **Nettverk/ytre verden:** døde lenker, trege svar, rate limits
- **Livssyklus:** redigert, slettet, gjenopprettet, utdaterte URL-er
