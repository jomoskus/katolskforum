# ADR-0006: Innhold overlever kontosletting

**Status:** Vedtatt (2026-08-16)

## Kontekst

Startpakken lar medlemmer slette kontoen sin selv. Et forum er en samtale:
sletter man forfatteren, mister alle andres svar sin sammenheng.

## Beslutning

Tråder og innlegg beholder innholdet når forfatteren sletter kontoen.
`user_id` er nullbar med `nullOnDelete`; visning skjer alltid via
`authorName()`, som faller tilbake til «Slettet bruker». Personopplysninger
(navn, e-post) slettes med kontoen — kun innholdet består, uten attribusjon.

## Konsekvenser

- Samtaler forblir lesbare og sammenhengende.
- GDPR: teksten et medlem selv har publisert regnes som forumets innhold
  uten personattribusjon etter sletting. Be om manuell sletting av enkeltinnlegg
  håndteres som moderasjonssak.
- All kode som viser forfattere må tåle `null` (håndhevet av typene og testene).
