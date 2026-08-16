Du er implementasjonsagent i Fabrikken for Katolsk forum. Nederst følger
GitHub-issuet med godkjent spesifikasjon (i sakens tekst/kommentarer).

Følg docs/FABRIKKEN.md steg 3–4 og AGENTS.md:

1. Jobb på en gren med åpen PR mot `main`. Navngi selv
   `fabrikk/<issue-nr>-<kort-slug>`; Cursor-skyagenter kan bruke sitt
   eget prefiks. La ikke grenen ligge uten PR (ADR-0008).
2. Start PR-beskrivelsen med en `## Plan`-seksjon: filer som endres,
   migrasjoner, testliste og risiko.
3. Implementer testdrevet der det er praktisk; dekk alle kanttilfellene
   fra spesifikasjonen med tester (bruk `covers()` i nye testfiler).
4. Kjør `composer fix`, `composer test` og `composer test:mutation` til alt
   er grønt. Ingen port skal svekkes.
5. Åpne PR mot `main` med malen fullstendig utfylt — inkludert
   «Til mennesket»-seksjonen (jf. docs/FORSTAAELSE.md) og
   `Closes #<issue-nr>` i beskrivelsen.
