# ADR-0005: Selvhelbredende tråd-URL-er

**Status:** Vedtatt (2026-08-16)

## Kontekst

Tråder trenger lesbare URL-er. Rene slug-oppslag gir en hale av
kanttilfeller: dupliserte titler, reserverte ord («create» skygger
`/threads/create`), titler uten sluggbare tegn, og tittelendringer som
knekker gamle lenker.

## Beslutning

Spatie laravel-sluggable med **selvhelbredende URL-er**: rutenøkkelen er
`{slug}-{id}`, oppslag skjer på den avsluttende id-en, og en utdatert slug
gir 308-redirect til kanonisk URL. Dupliserte slugs tillates (id skiller).
Titler uten sluggbare tegn faller tilbake til sluggen `trad`. Slug
regenereres ved tittelendring — gamle lenker fortsetter å virke.

## Konsekvenser

- Hele klassen av slug-kanttilfeller forsvinner strukturelt i stedet for å
  håndteres én og én; mindre kode og færre tester å vedlikeholde.
- URL-ene får et id-suffiks (som Discourse/GitHub Issues); akseptert.
- Slug-kolonnen trenger verken unik indeks eller oppslagsindeks.
