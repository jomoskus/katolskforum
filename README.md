# Katolsk forum

Et åpent, norskspråklig forum for katolske temaer — [katolskforum.no](https://katolskforum.no).
Alle kan lese; registrerte medlemmer med bekreftet e-post kan starte tråder
og svare. En tråd er enten en **artikkel** man har skrevet selv, eller en
**lenke** til noe annet med ens egen kommentar.

Bygget på Laravel 13 med den offisielle Livewire-startpakken (Fortify-auth
med 2FA og passkeys), Tailwind og SQLite. Utviklingen er KI-først: all
endring går gjennom prosessen i [`docs/FABRIKKEN.md`](docs/FABRIKKEN.md)
med flermodell-review, og kvalitetsportene er satt på maksnivå.

## Kom i gang

Krav: PHP 8.3+, Composer, Node 22+.

```bash
composer setup       # avhengigheter, .env, nøkkel, migrasjoner, frontend
php artisan db:seed  # norsk demoinnhold (valgfritt)
composer dev         # server + kø + logger + vite
```

## Kvalitet

```bash
composer fix            # autofiks (Rector + Pint)
composer test           # hele porten: stil, statisk analyse (max),
                        # tester, dekning >= 95 %, typedekning 100 %
composer test:mutation  # mutasjonstesting (score >= 85 %)
```

Alle porter håndheves i CI og er beskrevet i
[`docs/beslutninger/0003`](docs/beslutninger/0003-kvalitetsporter-paa-maksnivaa.md).

## Dokumentasjon

| Dokument | Innhold |
| --- | --- |
| [`AGENTS.md`](AGENTS.md) | Instruks for KI-agenter (kommandoer, regler, grenser) |
| [`docs/FABRIKKEN.md`](docs/FABRIKKEN.md) | Utviklingsprosessen: spesifikasjon → kritikk → implementasjon → flermodell-review → forståelsesport |
| [`docs/FORSTAAELSE.md`](docs/FORSTAAELSE.md) | Forståelsesporten: nivåene, kravene og menneskets pensum |
| [`docs/KANTTILFELLER.md`](docs/KANTTILFELLER.md) | Kanttilfelle-strategien og registeret |
| [`docs/DOMENE.md`](docs/DOMENE.md) | Domenespråk og autorisasjonsmatrise |
| [`docs/ARKITEKTUR.md`](docs/ARKITEKTUR.md) | Struktur og viktige mekanismer |
| [`docs/OBSERVABILITET.md`](docs/OBSERVABILITET.md) | Feilsporing, logger og rutiner i produksjon |
| [`docs/beslutninger/`](docs/beslutninger/) | Arkitekturbeslutninger (ADR) |
