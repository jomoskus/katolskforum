# ADR-0008: Grener er midlertidige

**Status:** Vedtatt (2026-08-16)

## Kontekst

Etter squash-merge (ADR-0007) er arbeidsgreenen ikke lenger en presis
kopi av det som ligger på `main`. Uten en slette-regel hoper agenter og
skisser opp grener som ser levende ut, men som ingen eier. Spørsmålet
var om vi skulle bygge egen opprydding (slett etter N dager) eller
følge GitHubs innebygde praksis.

## Beslutning

`main` er den eneste langlivede grenen. Alle andre grener er
arbeidslinjer og skal avsluttes:

1. Hver gren hører til en åpen pull request. En gren uten PR er ikke
   arkiv — åpne en PR, eller slett.
2. Når PR-en squash-merges, slettes grenen av GitHub
   («Automatically delete head branches»). Ingen egen workflow.
3. Når arbeidet forkastes, lukkes PR-en uten fletting, og grenen
   slettes der og da i GitHub-UI-et. GitHub sletter ikke automatisk
   ved lukking uten merge; det er bevisst, og vi kompenserer manuelt
   i det øyeblikket vi forkaster — ikke med en tidsstyrt bot.
4. Work flettes ikke inn «for å rydde». Forkastet arbeid skal ikke på
   `main`.

Vi innfører ikke en jobb som sletter urørte grener etter N dager, og
vi tvinger ikke et grennavn som kjemper mot det Cursor lager
(`cursor/…`). Implementasjonsagenter som navngir selv, bruker
`fabrikk/<issue-nr>-<slug>` (jf. `docs/FABRIKKEN.md`).

## Konsekvenser

- Ryddig branchoversikt uten eget vedlikehold, så lenge
  GitHub-innstillingen er på og forkastede PR-er slettes i UI-et.
- GitHub kan gjenopprette en slettet gren fra PR-en i en periode;
  sletting er ikke irreversibelt.
- Agenter skal åpne PR, ikke la en gren ligge «til senere» uten PR.
- Innstillingen settes av mennesket (engangsoppsett i
  `docs/FABRIKKEN.md`); agenter kan ikke slå den på.
