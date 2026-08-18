# Domenespråk

Felles ordbok for mennesker og agenter. Bruk disse begrepene konsekvent i
kode (engelsk), UI (norsk) og samtaler. Endringer i domenespråket skjer via
PR som oppdaterer dette dokumentet.

## Begreper

| Norsk (UI) | Engelsk (kode) | Definisjon |
| --- | --- | --- |
| Tråd | `Thread` | En samtale startet av et medlem. Har alltid tittel og innhold, og er enten **artikkel** eller **lenke**. |
| Artikkel | `ThreadKind::Article` | Tråd der innholdet er en tekst forfatteren har skrevet selv. |
| Lenke | `ThreadKind::Link` | Tråd som peker på noe utenfor forumet (`url`), der innholdet er forfatterens egen kommentar til det lenkede. |
| Innlegg / svar | `Post` | Et svar i en tråd. Vises kronologisk (eldst først). |
| Medlem | `User` | Registrert bruker. Kan lese alt; kan poste når e-posten er bekreftet. |
| Administrator | `User::$is_admin` | Medlem med moderasjonsrett: kan slette alt innhold, men aldri redigere andres tekst. Settes manuelt (aldri masse-tilordnes). |
| Slettet bruker | `authorName()`-fallback | Visningsnavn for innhold der forfatteren har slettet kontoen (`user_id = null`). Innholdet består. |
| Sist aktivitet | `threads.updated_at` | Tidspunkt for siste svar eller redigering i tråden. Innlegg `$touches` tråden. Forsiden sorteres på dette, synkende. |
| Forsiden | `home` / `threads.index` | Lista over alle tråder, sortert på sist aktivitet. Åpen for alle. |
| Redigert | `Post::isEdited()` | Et innlegg som er endret etter publisering (sekundpresisjon). Vises som «(redigert)». |

## Regler (autorisasjonsmatrise)

| Handling | Gjest | Medlem (ubekreftet e-post) | Medlem (bekreftet) | Forfatter | Administrator |
| --- | --- | --- | --- | --- | --- |
| Lese alt | ✅ | ✅ | ✅ | ✅ | ✅ |
| Starte tråd | ❌ | ❌ | ✅ | – | ✅ |
| Svare i tråd | ❌ | ❌ | ✅ | – | ✅ |
| Redigere tråd/innlegg | ❌ | ❌ | ❌ | ✅ (egne) | ❌ (aldri andres tekst) |
| Slette innlegg | ❌ | ❌ | ❌ | ✅ (egne) | ✅ (alle) |
| Slette tråd uten svar | ❌ | ❌ | ❌ | ✅ (egen) | ✅ |
| Slette tråd med svar | ❌ | ❌ | ❌ | ❌ | ✅ |

Reglene er implementert i `app/Policies/` og verifisert i
`tests/Feature/Forum/` (autorisasjonsmatrisen er testdekket).

## URL-er

- Tråder: `/threads/{slug}-{id}` — selvhelbredende: oppslag skjer på `id`,
  og utdatert slug gir 308-redirect til kanonisk URL.
- Statisk segment `/threads/create` er registrert før trådvisningen og kan
  aldri skygges av en tråd (rutenøkkelen slutter alltid på `-{id}`).

## Invariansregler

1. Innhold overlever kontosletting; forfatterreferanser er alltid nullbare.
2. All markdown fra brukere rendres gjennom `App\Support\Markdown` (XSS-trygt).
3. En lenketråd har alltid `url`; en artikkel har aldri `url`.
4. `is_admin` kan ikke masse-tilordnes.
5. UI-tekst er norsk bokmål; kode, kommentarer og commit-meldinger er engelsk.
