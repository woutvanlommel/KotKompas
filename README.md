# Scope & MoSCoW — Laravel/Filament rebuild

Volledige herbouw van KotCompass met Laravel + Filament. Deze scope mapt alle 65 backlog-items (#1–#75) naar modules en MoSCoW-prioriteiten, onderbouwd vanuit het [[School/KotCompass/KotCompass-Marktonderzoek.docx|marktonderzoek]].

## Scope statement

**In scope:** de end-to-end loop die niemand in de markt heeft: zoeken → contact → contract → opvolging, plus het Filament-beheerdashboard, chat en GDPR-compliance. Dit is de wig uit het marktonderzoek (§4.5): geen enkele BE/NL-speler combineert zoekplatform + volledig beheer.

**Out of scope:** monetisatie-uitvoering (gefaseerd, zie onder), itsme/MyRent/bankkoppelingen (fase 3, Kotmaster-niveau), eigen betaalafhandeling van huur, native app.

**Leidende principes uit het marktonderzoek:**

1. Vertrouwen is de positionering, security, GDPR en transparantie zijn must, niet nice-to-have.
2. Monetisatie is fase 2 (gratis instap eerst, zie marktonderzoek §8.4), Stripe/Cashier bouwen mag, maar mag de launch niet blokkeren.
3. De Kotscore heeft data nodig, een leeg scoresysteem is waardeloos op dag één (cold start), dus Should i.p.v. Must.

## Prioriteiten-overzicht

| Prioriteit | Aantal | Wat het betekent                              |
| ---------- | ------ | --------------------------------------------- |
| Must       | 41     | Zonder dit geen werkende end-to-end loop      |
| Should     | 20     | Differentiators & fase 2 — direct na de Musts |
| Could      | 4      | Pas waardevol met schaal/data                 |
| Won't      | —      | Expliciet uitgesteld, zie onderaan            |

---

## 1. Fundament & infra

| #   | Item                      | MoSCoW   | Motivatie                                                                         |
| --- | ------------------------- | -------- | --------------------------------------------------------------------------------- |
| #8  | Laravel Sanctum           | **Must** | Auth-basis van alles                                                              |
| #75 | Rollensysteem             | **Must** | Huurder / verhuurder / admin — kern van het domeinmodel; in Filament via policies |
| #42 | Laravel Pint installatie  | **Must** | Goedkoop, vanaf dag één — voorkomt stijldiscussies                                |
| #43 | Security checks (CI/CD)   | **Must** | Vertrouwen = positionering; security niet achteraf                                |
| #74 | Security checks           | **Must** | Idem — audit vóór release                                                         |
| #44 | Backend & frontend checks | **Must** | CI-poort op elke PR                                                               |
| #45 | Deploy.yml                | **Must** | Reproduceerbare deploys vanaf het begin                                           |
| #73 | Toast component           | **Must** | Klein, maar basis van alle UI-feedback                                            |
| #68 | Webp conversie functie    | Should   | Performance + lost de oude Base64-fotoschuld op; koppel aan #53                   |

## 2. Authenticatie & profiel

| #   | Item                           | MoSCoW   | Motivatie                                                |
| --- | ------------------------------ | -------- | -------------------------------------------------------- |
| #5  | Custom login/registration page | **Must** | Eigen huisstijl, geen Filament-default voor publiek      |
| #6  | Registrate form                | **Must** | Instappunt beide doelgroepen                             |
| #7  | Login form                     | **Must** | —                                                        |
| #2  | Password change                | **Must** | Basis accountbeheer                                      |
| #3  | Profilepage form               | **Must** | Profieldata voedt contractgeneratie (#66)                |
| #1  | Profile picture                | Should   | Niet blokkerend, triviaal met Filament/media library     |
| #4  | 2FA authentication             | Should   | Vertrouwen en security stijgen, maar geen launch-blocker |

## 3. Zoekplatform (publiek — kern)

| #   | Item                 | MoSCoW   | Motivatie                                                          |
| --- | -------------------- | -------- | ------------------------------------------------------------------ |
| #26 | Search function      | **Must** | Hart van de zoekkant                                               |
| #27 | Filters              | **Must** | Was Must in originele scope; verwacht door markt                   |
| #21 | Lijst + grid view    | **Must** | Basis-UX overzichtspagina                                          |
| #22 | Leaflet kaart        | **Must** | Kaart is standaard bij alle concurrenten (iKot, Kamernet)          |
| #15 | Afbeelding gallerij  | **Must** | Duidelijke foto's = Must in originele scope                        |
| #18 | Faciliteitenlijst    | **Must** | Kerninfo per kot                                                   |
| #19 | Prijs overzicht      | **Must** | All-in prijstransparantie, verplicht in Vlaanderen + marktpijnpunt |
| #16 | Verhuurder info card | **Must** | Vertrouwen: wie is de verhuurder                                   |
| #69 | Favorieten           | **Must** | Was Must in originele scope                                        |
| #70 | Contact              | **Must** | Contact opnemen = de conversie van de zoekkant                     |
| #20 | Close-by kaart       | Should   | POI's (school, station, winkels), was Should in originele scope    |
| #25 | Uitgelichte koten    | Should   | Spotlight = fase 2-monetisatie; UI kan eerder                      |
| #9  | Interactive FAQ form | Should   | Wetgeving/info-sectie; verhoogt vertrouwen                         |

## 4. Beheerdashboard (Filament — de wig)

| #   | Item                                          | MoSCoW   | Motivatie                                                                              |
| --- | --------------------------------------------- | -------- | -------------------------------------------------------------------------------------- |
| #47 | Gebouwen aanmaken                             | **Must** | Kern beheermodel: gebouw → kot → huurder                                               |
| #49 | Koten aanmaken in het gebouw                  | **Must** | —                                                                                      |
| #51 | Detailpagina kot aanmaken/bewerken            | **Must** | —                                                                                      |
| #52 | Faciliteiten toevoegen en bewerken            | **Must** | Voedt zoekfilters                                                                      |
| #53 | Foto's uploaden per kot                       | **Must** | Combineer met webp (#68) — geen Base64 meer                                            |
| #50 | Link huurder–kot + document                   | **Must** | Dé kern van de loop: contract koppelt kamer aan huurder                                |
| #46 | Overzicht studenten per kot                   | **Must** | Portfolio-overzicht verhuurder                                                         |
| #55 | Huurder upload document, auto-gelinkt aan kot | **Must** | Documenten centraliseren = kernmotivatie van het project                               |
| #66 | Contractgeneratie                             | **Must** | Grootste differentiator t.o.v. alle zoeksites (alleen MyKot heeft iets vergelijkbaars) |
| #67 | Algemene CSV export modal                     | Should   | Handig voor verhuurders, niet blokkerend                                               |
| #62 | Statspage                                     | Should   | Bezettingsgraad/overzicht — basis kan in Filament-widgets                              |
| #54 | Kaart beheer met plaatsen in de buurt         | Could    | Admin-POI-beheer; pas nuttig met meerdere steden                                       |

## 5. Communicatie

| #   | Item                  | MoSCoW   | Motivatie                                                     |
| --- | --------------------- | -------- | ------------------------------------------------------------- |
| #56 | Chatpage              | **Must** | Centraliseren van communicatie = bestaansreden van KotCompass |
| #28 | Template mail         | **Must** | Transactionele mails (registratie, koppeling, melding)        |
| #29 | Mailpit               | **Must** | Mail lokaal testen vanaf dag één                              |
| #32 | Mailscore controleren | **Must** | Deliverability-tuning; pas relevant bij volume                |

## 6. Kotscore-systeem (nieuw — differentiator)

> Vertrouwen is de positionering en de score ondersteunt dat, maar zonder reviews is het systeem leeg (cold start). Bouw de basis als Should; release pas zichtbaar mét enquête-data.

| #   | Item                                             | MoSCoW | Motivatie                                                                                                   |
| --- | ------------------------------------------------ | ------ | ----------------------------------------------------------------------------------------------------------- |
| #17 | Kotscore (overkoepelend)                         | Should | —                                                                                                           |
| #35 | Algemene scores berekenen                        | Should | Rekenkern eerst                                                                                             |
| #36 | Aparte score verhuurder + gebouw                 | Should | Granulariteit verhuurder/gebouw                                                                             |
| #39 | Enquête na stopzetten huurperiode (anoniem)      | Should | Dé databron — zonder dit geen scores                                                                        |
| #38 | Badge voor de score                              | Should | Zichtbaarheid in zoekresultaten                                                                             |
| #40 | Verhuurder kan totale scores bekijken            | Should | Feedback-loop verhuurder                                                                                    |
| #41 | Huurders zien geen score-details op detailpagina | Should | Privacy-regel, hoort bij score-release, verhuurder ziet enkel de opbouw van de score in verband met het kot |
| #23 | Filtering kotscore + uitgelicht                  | Could  | Filteren op score pas zinvol met genoeg data                                                                |
| #37 | Verwerking score in uitgelichte koten            | Could  | Idem                                                                                                        |

## 7. Monetisatie (fase 2 — zie marktonderzoek §8.4)

> Gratis instap eerst (cold start oplossen), daarna pas betaald. Alles Should: bouwen mag, launch-blocker is het niet.

| #   | Item                             | MoSCoW | Motivatie                                             |
| --- | -------------------------------- | ------ | ----------------------------------------------------- |
| #10 | Stripe koppeling                 | Should | Herbruikbare kennis uit MVP                           |
| #11 | Laravel Cashier                  | Should | Abonnementen netjes via Cashier i.p.v. handwerk       |
| #12 | Abonnement koppeling             | Should | Beheer-abonnement = fase 3-omzetmodel                 |
| #13 | Overzicht abonnementen / credits | Should | —                                                     |
| #14 | Checkout page                    | Should | Transparante facturatie (anti-Rentola, zie onderzoek) |

## 8. Design & branding

| #   | Item                             | MoSCoW   | Motivatie                                       |
| --- | -------------------------------- | -------- | ----------------------------------------------- |
| #33 | Responsive / mobile-first design | **Must** | Studenten zoeken mobiel                         |
| #48 | Kleurenpalette                   | **Must** | Huisstijl bestaat al — design tokens overzetten |
| #34 | Fonts                            | **Must** | Area via Typekit, zelfde als MVP                |

## 9. Legal & compliance

| #   | Item           | MoSCoW   | Motivatie                                                          |
| --- | -------------- | -------- | ------------------------------------------------------------------ |
| #63 | Privacy policy | **Must** | GDPR: platform = verwerkingsverantwoordelijke (zie onderzoek §5.3) |
| #64 | Cookie policy  | **Must** | —                                                                  |
| #65 | General policy | **Must** | Algemene voorwaarden vóór eerste echte gebruiker                   |

## Won't haves (v1)

- Eigen betaalafhandeling van huurgeld (was ook Won't in originele scope)
- itsme-verificatie, MyRent-registratie, bankkoppelingen — fase 3, pas als we Kotmaster frontaal aanvallen met een volledig geïntegreerd platform + monetisatie
- Kotmatcher / Kotswiper
- Native app
- Plattegronden van koten

## Aanbevolen bouwvolgorde

1. **Sprint 1** — fundament: #8, #75, #42, #43, #44, #45, #48, #34, #73 - beheer (Filament):\*\* module 4 volledig — dit is ons datamodel en onze wig
2. **Sprint 2** — zoekplatform:** module 3 + auth/profiel (module 2)
   — communicatie + legal:** #56, #28, #29, #63–65, responsive pass (#33)
3. **Sprint 3 (finalisatie)** kotscore-basis + enquête, monetisatie, spotlight, 2FA

> Logica: beheer eerst, want het datamodel (gebouw → kot → contract → huurder) bepaalt alles wat de zoekkant toont. Zo bouwen we nooit twee keer.
