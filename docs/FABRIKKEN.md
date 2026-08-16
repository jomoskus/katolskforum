# Fabrikken

Fabrikken er prosessen for alle kodeendringer i dette repoet. Målet er at
resultatet blir så likt det mennesket så for seg som mulig — eller bedre —
og med så få bugs som mulig. Vi bruker heller mye tid og tokens enn å slippe
gjennom feil. Tokens er billigere enn bugs.

Prosessen er bygget rundt tre uavhengige modellleverandører, slik at den som
skriver kode aldri er den eneste som vurderer den:

| Kapasitet | Verktøy | Typisk rolle |
| --- | --- | --- |
| Cursor (abonnement) | Cursor-agenter, skyagenter, **Bugbot** på PR-er | Implementasjon, review |
| Codex (abonnement) | Codex CLI/cloud, **Codex-review** på PR-er | Spesifikasjon, implementasjon, review |
| Synthetic.new | **GLM** og **Kimi** via Factory Droid (CLI/CI) | Uavhengig kritikk og review |

Mennesket er produkteier og eneste som fletter til `main`.

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

1. **Bugbot** (Cursor) reviewer PR-en automatisk.
2. **Codex-review** kjøres på PR-en (cloud-review eller `codex review` lokalt).
3. **Uavhengig tredjepart:** Factory Droid med GLM eller Kimi
   (Synthetic.new) reviewer diffen — automatisk via workflowen
   `droid-review` hvis `FACTORY_API_KEY` er satt, ellers manuelt med
   `droid exec` lokalt.

Hvert funn behandles skriftlig i PR-en: **fiks** eller **avvis med
begrunnelse**. Ingen funn kan stå ubesvart ved fletting.

**Uenighet mellom modeller:** be en tredje modellfamilie (den som ikke er
part i uenigheten) oppsummere begge syn og gi en anbefaling i PR-en.
Mennesket avgjør. Uenigheten og utfallet noteres — gjentatte mønstre blir
til regler i `AGENTS.md` eller nye porter.

### Steg 6 – Fletting (menneske)

Bare mennesket fletter. Squash-merge for ryddig historikk. Grenen slettes.

### Steg 7 – Etterpå: overvåking og læring

- Produksjonsfeil fanges av overvåkingen (se `docs/OBSERVABILITET.md`).
- Hver reell feil får: issue → regresjonstest → oppføring i
  `docs/KANTTILFELLER.md` → eventuelt ny regel i `AGENTS.md`.
- Når en agent gjør samme feil to ganger, er det prosessen som skal
  oppdateres, ikke bare koden.

## Engangsoppsett

Gjøres én gang av mennesket (agenter kan ikke gjøre dette):

1. **Bugbot:** slå på for repoet i Cursor Dashboard → Bugbot.
2. **Codex-review:** slå på automatisk kodegjennomgang for repoet i
   Codex-innstillingene (chatgpt.com/codex).
3. **Droid-review:** legg inn GitHub-secrets `FACTORY_API_KEY` (app.factory.ai)
   og `SYNTHETIC_API_KEY` (synthetic.new) under Settings → Secrets → Actions.
   Modell kan overstyres med variabelen `DROID_MODEL_ID`
   (f.eks. `hf:moonshotai/Kimi-K2-Instruct`).
4. **Grenvern på `main`:** Settings → Branches → krev at `ci / Kvalitetsporter`
   er grønn før fletting, krev én godkjenning, og slå på squash-merge.
5. **Cursor skyagenter:** repoet har `.cursor/environment.json`; første
   skyagent-VM bygges automatisk med `.cursor/install.sh`.

## Hvorfor PR-er når jeg er alene i repoet?

Fordi hele flermodell-maskineriet er festet til PR-er: CI-portene kjører på
PR-er, Bugbot/Codex/Droid leser PR-er, og funn/avvisninger arkiveres der.
PR-en er også kontrakten som gjør at en hvilken som helst agent kan ta over
saken senere. Grenvern på `main` (krev grønn CI + én godkjenning) anbefales
konfigurert i GitHub-innstillingene.
