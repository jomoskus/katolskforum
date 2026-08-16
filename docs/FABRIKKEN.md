# Fabrikken

Fabrikken er prosessen for alle kodeendringer i dette repoet. Målet er at
resultatet blir så likt det mennesket så for seg som mulig — eller bedre —
og med så få bugs som mulig. Vi bruker heller mye tid og tokens enn å slippe
gjennom feil. Tokens er billigere enn bugs.

Prosessen er bygget rundt tre uavhengige modellleverandører, slik at den som
skriver kode aldri er den eneste som vurderer den:

| Kapasitet | Verktøy | Typisk rolle |
| --- | --- | --- |
| Cursor (abonnement) | Cursor-agenter (editor, CLI og sky) | Implementasjon, manuell review av større endringer |
| Codex (abonnement) | Codex CLI/cloud, **Codex-review** på PR-er | Spesifikasjon, implementasjon, automatisk review |
| Synthetic.new (fast pris) | **GLM**, **Kimi** og **GPT-OSS** via Factory Droid (CLI/CI) | Spesifikasjonsutkast, kritikk og uavhengige reviews |

Mennesket er produkteier og eneste som fletter til `main`.

Alle lagene dekkes av faste abonnementer — ingen per-bruk-fakturering.
(Cursors Bugbot er bevisst utelatt: den prises per bruk.)

## Grunnprinsipper

1. **Uavhengighet.** Modellen som skriver koden skal ikke være samme
   modellfamilie som (eneste) reviewer. Minst to andre leverandører skal
   vurdere hver PR.
2. **Skriftlighet.** Alle funn, avvisninger og begrunnelser skjer i PR-en
   eller issuet, aldri i private samtaler. Sporbarhet er en forutsetning for
   at neste agent kan ta over.
3. **Deterministiske porter først.** Maskinelle sjekker (`composer test`,
   mutasjon, CI) avgjør alt de kan avgjøre. Modell-review brukes på det
   maskiner ikke fanger: intensjon, design, hull i spesifikasjonen.
4. **Små endringer.** PR-er bør holde seg under ~400 endrede linjer.
   Kvaliteten på både menneskelig og maskinell review faller bratt med
   størrelsen.
5. **Ingen port svekkes for å bli grønn.** Terskler og regler endres bare
   gjennom egen PR med ADR.
6. **Mennesket skal forstå det som flettes.** Ingen endring flettes før
   mennesket har oppfylt forståelsesnivået endringen krever, se
   `docs/FORSTAAELSE.md`. Fart er aldri en grunn til å hoppe over dette.

## Stegene

### Steg 0 – Idé (menneske)

Beskriv behovet i et GitHub-issue: hva, hvorfor, og eventuelle harde krav.
Én setning kan være nok; Fabrikken foredler det.

### Steg 1 – Spesifikasjon (én modell skriver)

En agent (typisk Codex eller en Cursor-agent) skriver utkast til
spesifikasjon i issuet:

- Brukerhistorie og mål
- Akseptansekriterier (testbare)
- **Kanttilfeller** – obligatorisk seksjon, se `docs/KANTTILFELLER.md`
- Testplan (hvilke tester skal finnes når dette er ferdig)
- Avgrensninger (hva som bevisst ikke gjøres)

### Steg 2 – Spesifikasjonskritikk (minst to andre modellfamilier)

Minst to modeller fra andre leverandører enn forfatteren leser
spesifikasjonen som djevelens advokat:

- Hvilke krav er tvetydige eller selvmotsigende?
- Hvilke kanttilfeller mangler?
- Hva kommer til å overraske brukeren?

Praktisk: `droid exec` med GLM/Kimi (Synthetic), og en Cursor- eller
Codex-agent. Funnene legges som kommentarer i issuet. Forfattermodellen
konsoliderer. **Mennesket godkjenner spesifikasjonen** før implementasjon.

### Steg 3 – Plan (implementasjonsagenten)

Agenten som skal implementere skriver kort plan i issuet: filer som endres,
migrasjoner, testliste, risiko. Ved vesentlig usikkerhet: be en annen
modellfamilie kritisere planen før start.

### Steg 4 – Implementasjon (én agent, gjerne skyagent)

- Jobb på egen gren (`fabrikk/<issue-nr>-<slug>`), aldri rett på `main`.
- Testdrevet der det er praktisk: skriv testene fra testplanen først.
- `composer fix && composer test && composer test:mutation` skal være grønt
  før PR åpnes.
- PR-malen fylles ut, inkludert kanttilfelle-seksjonen.

### Steg 5 – Portene

**Maskinelle porter (CI, blokkerende):** Pint, Rector, PHPStan (max),
Pest med dekning ≥ 95 %, typedekning 100 %, mutasjonsscore ≥ 85 %,
arkitekturtester, `composer audit`.

**Flermodell-review (blokkerende):**

1. **Codex-review** kjøres på PR-en (automatisk cloud-review, inkludert i
   Codex-abonnementet; alternativt `codex review` lokalt).
2. **To uavhengige tredjeparter:** workflowen `droid-review` kjører Factory
   Droid to ganger per PR — én gang med **GLM** og én gang med **Kimi**
   (Synthetic.new, fast pris) — og poster funnene som PR-kommentarer.
   Uten API-nøkler hopper den stille over; kjør da `droid exec` manuelt.
3. **Ved større eller risikable endringer:** manuell review med en
   Cursor-agent (editor eller CLI), som dekkes av Cursor-abonnementet.
   Be agenten eksplisitt om å granske diffen mot AGENTS.md og
   kanttilfelleregisteret.

Hvert funn behandles skriftlig i PR-en: **fiks** eller **avvis med
begrunnelse**. Ingen funn kan stå ubesvart ved fletting.

**Uenighet mellom modeller:** be en tredje modellfamilie (den som ikke er
part i uenigheten) oppsummere begge syn og gi en anbefaling i PR-en.
Mennesket avgjør. Uenigheten og utfallet noteres — gjentatte mønstre blir
til regler i `AGENTS.md` eller nye porter.

### Steg 6 – Forståelsesporten (menneske)

Før fletting skal mennesket forstå endringen på nivået den krever —
nivåene, kravene per endringstype og mekanismene (teach-back,
kontrollspørsmål, verifisering) er definert i `docs/FORSTAAELSE.md`.

Kort: agenten leverer en «Til mennesket»-seksjon i PR-en (forklaring,
foreslått nivå, omvisning i filene, kontrollspørsmål ved nivå 3, dagens
konsept). Mennesket beviser forståelsen skriftlig i PR-en; ved nivå 3
verifiserer en annen modellfamilie svarene. Godkjenningen i GitHub **er**
forståelseserklæringen og er først gyldig når kravene er oppfylt.

### Steg 7 – Fletting (menneske)

Bare mennesket fletter. Squash-merge for ryddig historikk.

**Grener er midlertidige** (ADR-0008). `main` er den eneste grenen som
får leve:

- Hver gren hører til en åpen PR. Ingen gren uten PR er arkiv.
- Flettes PR-en, slettes grenen. GitHub gjør det når «Automatically
  delete head branches» er slått på (se engangsoppsettet).
- Forkaster du arbeidet, lukker du PR-en og sletter grenen der og da
  i GitHub-UI-et. Work flettes ikke inn «for å rydde».

### Steg 8 – Etterpå: overvåking og læring

- Produksjonsfeil fanges av overvåkingen (se `docs/OBSERVABILITET.md`).
- Hver reell feil får: issue → regresjonstest → oppføring i
  `docs/KANTTILFELLER.md` → eventuelt ny regel i `AGENTS.md`.
- Når en agent gjør samme feil to ganger, er det prosessen som skal
  oppdateres, ikke bare koden.

## Automatisering av stegene

GitHub er tilstandsmaskinen: **etiketter på issues er knappene** som fyrer
av riktig agent for riktig steg. Workflowen `fabrikken-dispatch` lytter på
etikettene, poster resultatet som kommentar og fjerner etiketten etterpå
(sett den igjen for å kjøre steget på nytt).

| Trigger | Hva skjer automatisk | Kostnadsprofil |
| --- | --- | --- |
| Etikett `fabrikk:spesifikasjon` | GLM (Droid) skriver spesifikasjonsutkast som kommentar | Fast pris (Synthetic) |
| Etikett `fabrikk:kritikk` | Kimi og GPT-OSS kritiserer utkastet hver for seg | Fast pris (Synthetic) |
| Etikett `fabrikk:implementer` | Cursor-skyagent lanseres via API, planlegger og åpner PR | Inkludert bruk først; stoppes av on-demand-taket ditt |
| PR åpnes/oppdateres | `ci` (alle porter), `droid-review` (GLM + Kimi), Codex-review | Fast pris / inkludert |
| PR-beskrivelse | `fabrikk-lint` krever «Til mennesket» + forståelseserklæring | Gratis |

Promptene per steg ligger i `.github/fabrikken/` og kan forbedres som all
annen kode. Merk at spesifikasjonsforfatteren (GLM) bevisst er en annen
modellfamilie enn kritikerne (Kimi, GPT-OSS).

**Manuelle spor (dekket av abonnementene):** kommenter `@codex` eller
`@cursor` direkte på et issue eller en PR for å sette en skyagent på saken
— mentions må komme fra et menneske (agent-postede mentions trigges ikke,
det er derfor workflowen bruker CLI/API). Cursor har i tillegg native
**Automations** ([cursor.com/automations](https://cursor.com/automations))
for tidsstyrte og hendelsesstyrte agenter uten egen workflow-kode.

## Engangsoppsett

Gjøres én gang av mennesket (agenter kan ikke gjøre dette). Huk av
etter hvert:

- [ ] **Codex-review og `@codex`:** installer Codex-GitHub-appen og slå på
  automatisk kodegjennomgang for repoet i Codex-innstillingene
  (chatgpt.com/codex).
- [ ] **Droid-review og Fabrikken-stegene:** legg inn GitHub-secrets
  `FACTORY_API_KEY` (app.factory.ai) og `SYNTHETIC_API_KEY`
  (synthetic.new) under Settings → Secrets → Actions. Modellene byttes i
  `.github/workflows/droid-review.yml` og `fabrikken-dispatch.yml`.
- [ ] **Cursor-skyagenter og `@cursor`:** koble GitHub-kontoen på
  [cursor.com/agents](https://cursor.com/agents), lag en API-nøkkel
  (Dashboard → API Keys) og legg den inn som secret `CURSOR_API_KEY`.
  Slå på on-demand-bruk med et **lavt tak** (f.eks. 10–20 USD) — skyagenter
  trekker fra planens inkluderte bruk først, og taket er sikkerhetsnettet.
  Modell kan overstyres med variabelen `CURSOR_MODEL`.
- [ ] **Etikettene:** opprett dem med
  `gh label create 'fabrikk:spesifikasjon' -c '#1d76db' -d 'Fyrer av spesifikasjonsutkast (GLM)'`,
  `gh label create 'fabrikk:kritikk' -c '#d93f0b' -d 'Fyrer av kritikkrunde (Kimi + GPT-OSS)'` og
  `gh label create 'fabrikk:implementer' -c '#0e8a16' -d 'Lanserer Cursor-skyagent som åpner PR'`.
- [ ] **Grenvern på `main`:** Settings → Branches → krev at
  `ci / Kvalitetsporter` og `fabrikk-lint / PR følger Fabrikken` er grønne
  før fletting, krev én godkjenning, og slå på squash-merge.
- [ ] **Slett grener etter fletting:** Settings → General → Pull Requests →
  huk av **Automatically delete head branches**. Det er GitHubs egen
  sletting etter squash-merge, ikke en egen jobb. Lukker du en PR uten
  å flette, sletter du grenen manuelt i UI-et (GitHub gjør det ikke
  automatisk da).
- [ ] **Cursor skyagent-miljø:** repoet har `.cursor/environment.json`; første
  skyagent-VM bygges automatisk med `.cursor/install.sh`.

## Før lansering

Gjøres av mennesket når forumet skal ut, ikke som del av GitHub-oppsettet.
Detaljer i `docs/OBSERVABILITET.md`.

- [ ] Nightwatch-app på [nightwatch.laravel.com](https://nightwatch.laravel.com)
  med **EU-lagring**, `NIGHTWATCH_TOKEN` og `NIGHTWATCH_ENABLED=true` i
  produksjon, agenten som systemd/Supervisor-tjeneste.
- [ ] Ekte e-postleverandør (`MAIL_MAILER`, f.eks. Resend eller Postmark).
- [ ] `APP_DEBUG=false`, HTTPS og DNS mot katolskforum.no.
- [ ] Ekstern ping mot forsiden og `/up`.
- [ ] Backup av SQLite-filen (Litestream eller jevnlig kopi).

## Hvorfor PR-er når jeg er alene i repoet?

Fordi hele flermodell-maskineriet er festet til PR-er: CI-portene kjører på
PR-er, Codex og Droid-reviewene leser PR-er, og funn/avvisninger arkiveres der.
PR-en er også kontrakten som gjør at en hvilken som helst agent kan ta over
saken senere. Grenvern på `main` (krev grønn CI + én godkjenning) anbefales
konfigurert i GitHub-innstillingene.
