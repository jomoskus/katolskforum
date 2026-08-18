# Arkitektur

Katolsk forum er en klassisk Laravel-monolitt. Strukturen følger rammeverkets
konvensjoner slavisk — det er et bevisst valg for at både mennesker og
KI-agenter skal kjenne seg igjen umiddelbart (se ADR-0001).

## Flyt

```
Rute (routes/web.php)
  → Form Request (validering, app/Http/Requests)
  → Controller (tynn orkestrering, app/Http/Controllers)
  → Policy via Gate (autorisasjon, app/Policies)
  → Eloquent-modell (app/Models)
  → Blade-visning (resources/views)
```

## Kart

| Del | Sti | Innhold |
| --- | --- | --- |
| Domenemodeller | `app/Models/` | `Thread`, `Post`, `User` + `Concerns/HasAuthor` |
| Enums | `app/Enums/` | `ThreadKind` (article/link) |
| HTTP | `app/Http/Controllers`, `app/Http/Requests` | Tynne controllere, all validering i requests |
| Autorisasjon | `app/Policies/` | `ThreadPolicy`, `PostPolicy` (auto-oppdages) |
| Støtte | `app/Support/` | `Markdown` (trygg rendering) |
| Auth | `app/Actions/Fortify`, `app/Providers/FortifyServiceProvider` | Fra startpakken |
| Visninger | `resources/views/threads`, `posts`, `layouts/forum.blade.php` | Norsk UI, Flux-komponenter |
| Tester | `tests/Feature/Forum`, `tests/Feature/Auth`, `tests/Unit` | Feature-tunge; `covers()` overalt |

## Viktige mekanismer

- **Sist aktivitet:** `Post` har `#[Touches(['thread'])]`; forsiden sorterer
  på `threads.updated_at`. Ingen egen kolonne, ingen observer.
- **URL-er:** selvhelbredende (`{slug}-{id}`), se ADR-0005.
- **Markdown:** all brukertekst rendres via `App\Support\Markdown` med
  HTML-stripping og lenkevern; visningskomponenten er `<x-markdown>`.
- **E-postverifisering:** `User` implementerer `MustVerifyEmail`; ruter som
  skaper innhold ligger bak `auth` + `verified`.
- **Svar-paginering:** nye svar redirecter til riktig side med
  `#post-{id}`-fragment.

## Bevisst utelatt (foreløpig)

Kategorier/underfora, søk, varsler, siteringer, reaksjoner, vedlegg,
moderasjonskø og API. Utvidelser skal gjennom Fabrikken med spesifikasjon
og kanttilfelleanalyse først.
