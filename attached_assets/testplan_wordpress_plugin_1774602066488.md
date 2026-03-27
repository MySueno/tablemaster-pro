# WordPress Tabel Plugin — QA Testplan
**Versie:** 1.0  
**Gemaakt:** 2026-03-27  
**Doel:** Volledig visueel en functioneel testen van de WordPress tabel plugin (shortcode-gebaseerd)

---

## Instructies voor Replit

Dit testplan bevat 63 testcases verdeeld over 9 categorieën.  
Elke test heeft een unieke ID, prioriteitslabel en verwacht resultaat.  
Vink tests af door `[ ]` te vervangen door `[x]`.

**Prioriteitslabels:**
- `[KRITISCH]` — Showstopper, plugin onbruikbaar of onveilig indien mislukt
- `[RAND]` — Edge case, vaak vergeten maar breekt in productie
- `[VISUEEL]` — Front-end weergave, geen foutmelding maar ziet er fout uit

---

## Categorie 1 — Tabel aanmaken & basisfunctionaliteit

| ID | Prioriteit | Testgeval | Verwacht resultaat | Status |
|----|-----------|-----------|-------------------|--------|
| T01 | — | Nieuwe lege tabel aanmaken via plugin interface | Tabel aangemaakt, shortcode beschikbaar | [ ] |
| T02 | `[RAND]` | Tabel aanmaken met minimale grootte (1×1 cel) | Tabel opgeslagen zonder fout | [ ] |
| T03 | `[RAND]` | Tabel aanmaken met grote grootte (20 rijen × 10 kolommen) | Tabel opgeslagen, shortcode werkt | [ ] |
| T04 | — | Tabel opslaan en shortcode kopiëren | Shortcode correct gekopieerd | [ ] |
| T05 | `[VISUEEL]` | Shortcode plaatsen op pagina/post — tabel verschijnt | Tabel volledig zichtbaar op front-end | [ ] |
| T06 | `[VISUEEL]` | Meerdere tabellen op dezelfde pagina via aparte shortcodes | Beide tabellen zichtbaar, geen conflict | [ ] |

---

## Categorie 2 — Visuele weergave & responsiviteit

| ID | Prioriteit | Testgeval | Verwacht resultaat | Status |
|----|-----------|-----------|-------------------|--------|
| T07 | `[VISUEEL]` | Tabel op desktopbreedte (1280px+) | Correct gerenderd, geen overflow | [ ] |
| T08 | `[VISUEEL]` | Tabel op tablet (768px) | Leesbaar, geen afgeknipte cellen | [ ] |
| T09 | `[KRITISCH]` | Tabel op mobiel (375px) | Geen overflow, horizontale scroll indien nodig | [ ] |
| T10 | `[VISUEEL]` | Tabel breder dan scherm — horizontale scroll actief | Scrollbar zichtbaar, geen content verloren | [ ] |
| T11 | `[VISUEEL]` | Kolombreedtes passen proportioneel aan | Geen kolom die samenkrimpt tot onleesbaar | [ ] |
| T12 | `[VISUEEL]` | Lange tekst in cel breekt correct af | Geen overflow buiten celgrens | [ ] |
| T13 | `[RAND]` | Lege cellen tonen geen layout-fouten | Cel zichtbaar maar leeg, geen collapse | [ ] |

---

## Categorie 3 — Celinhoud & opmaak

| ID | Prioriteit | Testgeval | Verwacht resultaat | Status |
|----|-----------|-----------|-------------------|--------|
| T14 | `[VISUEEL]` | Vetgedrukte tekst (bold) in cel | Bold correct weergegeven op front-end | [ ] |
| T15 | `[VISUEEL]` | Cursieve tekst (italic) in cel | Italic correct weergegeven op front-end | [ ] |
| T16 | — | Link in cel — klikbaar op front-end | Link opent correct, geen plain tekst | [ ] |
| T17 | `[VISUEEL]` | Afbeelding in cel — laadt en schaalt correct | Afbeelding zichtbaar, geen overflow | [ ] |
| T18 | — | Nummers en valuta correct weergegeven | Geen codering-fouten (bijv. &euro; ipv €) | [ ] |
| T19 | `[RAND]` | Speciale tekens (€, ©, é, ç, î) in cel | Tekens correct weergegeven | [ ] |
| T20 | `[KRITISCH]` | HTML-tekens in celinhoud (bijv. `<script>alert(1)</script>`) | Tekst getoond als plain tekst, geen uitvoering | [ ] |

---

## Categorie 4 — Cellen samenvoegen & splitsen

| ID | Prioriteit | Testgeval | Verwacht resultaat | Status |
|----|-----------|-----------|-------------------|--------|
| T21 | `[VISUEEL]` | Twee naast elkaar liggende cellen samenvoegen (colspan) | Samengevoegde cel correct breed | [ ] |
| T22 | `[VISUEEL]` | Twee boven elkaar liggende cellen samenvoegen (rowspan) | Samengevoegde cel correct hoog | [ ] |
| T23 | `[VISUEEL]` | Samengevoegde cellen splitsen — layout herstelt | Rijen/kolommen terug in originele staat | [ ] |
| T24 | `[RAND]` | Samengevoegde cel met inhoud splitsen | Inhoud in eerste cel, rest leeg | [ ] |
| T25 | `[VISUEEL]` | Meerdere aaneengesloten samenvoegingen in één tabel | Alle samenvoegingen correct op front-end | [ ] |
| T26 | — | Exporteer tabel met samengevoegde cellen | Structuur behouden of correct afgevlakt in export | [ ] |

---

## Categorie 5 — Kleuren & styling

| ID | Prioriteit | Testgeval | Verwacht resultaat | Status |
|----|-----------|-----------|-------------------|--------|
| T27 | `[VISUEEL]` | Achtergrondkleur van een rij instellen | Kleur zichtbaar op front-end | [ ] |
| T28 | `[VISUEEL]` | Achtergrondkleur van een cel instellen | Kleur zichtbaar op front-end, alleen die cel | [ ] |
| T29 | `[VISUEEL]` | Tekstkleur aanpassen in een cel | Tekstkleur gewijzigd op front-end | [ ] |
| T30 | `[VISUEEL]` | Koptekstrij (header row) heeft andere stijl | Header visueel onderscheidbaar van data-rijen | [ ] |
| T31 | `[VISUEEL]` | Zebra-striping — afwisselende rijkleuren | Patroon correct afwisselend | [ ] |
| T32 | — | Aangepaste stijl bewaard na opslaan en herladen | Stijl persistent na page refresh | [ ] |
| T33 | `[KRITISCH]` | Plugin CSS conflicteert niet met WordPress thema | Geen stijlen overschreven buiten de tabel-container | [ ] |

---

## Categorie 6 — Vertalingen & meertaligheid

| ID | Prioriteit | Testgeval | Verwacht resultaat | Status |
|----|-----------|-----------|-------------------|--------|
| T34 | — | Tabelinhoud in het Nederlands | Geen codering-fouten | [ ] |
| T35 | `[VISUEEL]` | Tabelinhoud in het Frans (é, è, ç, î, ô) | Accenten correct weergegeven | [ ] |
| T36 | `[RAND]` | Rechts-naar-links tekst (Arabisch/Hebreeuws) | Cel-uitlijning correct (RTL) | [ ] |
| T37 | — | Plugin-interface-labels vertaald (.po/.mo aanwezig) | Labels in juiste taal weergegeven | [ ] |
| T38 | `[KRITISCH]` | Exportbestand bevat correcte tekens (UTF-8 encoding) | Geen vraagtekens of zwarte blokken in export | [ ] |

---

## Categorie 7 — Export

| ID | Prioriteit | Testgeval | Verwacht resultaat | Status |
|----|-----------|-----------|-------------------|--------|
| T39 | — | Exporteer als CSV — bestand downloadt | Bestand gedownload zonder fout | [ ] |
| T40 | `[VISUEEL]` | CSV openen in Excel — kolommen correct gescheiden | Structuur correct, geen vervorming | [ ] |
| T41 | `[VISUEEL]` | CSV openen in Google Sheets — correcte structuur | Kolommen en rijen correct | [ ] |
| T42 | — | Exporteer als Excel (.xlsx) indien ondersteund | Bestand downloadt, opent correct | [ ] |
| T43 | `[VISUEEL]` | Exporteer als PDF — lay-out correct | Inhoud leesbaar, geen afgeknipte cellen | [ ] |
| T44 | `[KRITISCH]` | Export van grote tabel (500+ rijen) | Geen timeout, geen lege download | [ ] |
| T45 | `[RAND]` | Samengevoegde cellen in export bewaard | Structuur correct of netjes afgevlakt | [ ] |
| T46 | `[RAND]` | Speciale tekens correct in alle exportformaten | Geen codering-fouten in CSV, XLSX, PDF | [ ] |

---

## Categorie 8 — Import

| ID | Prioriteit | Testgeval | Verwacht resultaat | Status |
|----|-----------|-----------|-------------------|--------|
| T47 | — | CSV importeren — tabel aangemaakt met juiste structuur | Kolommen en rijen correct ingelezen | [ ] |
| T48 | `[RAND]` | CSV met komma's in celinhoud (geciteerd veld) | Geciteerde komma's niet als scheiding behandeld | [ ] |
| T49 | `[RAND]` | CSV met puntkomma als scheidingsteken | Ondersteund of duidelijke foutmelding | [ ] |
| T50 | — | Excel-bestand (.xlsx) importeren | Kolommen en rijen correct vertaald | [ ] |
| T51 | `[RAND]` | Import van bestand met speciale tekens (UTF-8) | Geen codering-fouten na import | [ ] |
| T52 | `[KRITISCH]` | Import van leeg bestand | Duidelijke foutmelding, geen crash of fatal error | [ ] |
| T53 | `[KRITISCH]` | Import van bestand met 1000+ rijen | Geen timeout of geheugenfout | [ ] |
| T54 | — | Na import: shortcode werkt op front-end | Tabel zichtbaar na plaatsen shortcode | [ ] |

---

## Categorie 9 — Randgevallen & robuustheid

| ID | Prioriteit | Testgeval | Verwacht resultaat | Status |
|----|-----------|-----------|-------------------|--------|
| T55 | `[KRITISCH]` | Tabel verwijderen — shortcode op front-end | Geen fatal error, stille of nette fallback | [ ] |
| T56 | `[KRITISCH]` | Shortcode met onbestaand tabel-ID | Geen PHP-warning zichtbaar op front-end | [ ] |
| T57 | `[RAND]` | Tabel met 0 rijen of 0 kolommen | Geen lege container of broken layout | [ ] |
| T58 | `[RAND]` | Twee gebruikers bewerken dezelfde tabel tegelijk | Geen datastuurverlies, laatste opslag wint | [ ] |
| T59 | `[KRITISCH]` | Plugin deactiveren en heractiveren | Alle tabellen nog aanwezig, geen dataverlies | [ ] |
| T60 | — | WordPress-update uitvoeren | Plugin blijft functioneren na update | [ ] |
| T61 | `[KRITISCH]` | Shortcode geplaatst in widget-gebied | Tabel correct gerenderd buiten post-context | [ ] |
| T62 | `[RAND]` | Shortcode in een excerpt of RSS-feed | Geen rauwe shortcode-tekst zichtbaar | [ ] |
| T63 | `[KRITISCH]` | Tabel bekijken als niet-ingelogde bezoeker | Tabel zichtbaar als public, geen auth-vereiste | [ ] |

---

## Samenvatting

| Categorie | Totaal | Kritisch | Geslaagd | Mislukt |
|-----------|--------|----------|----------|---------|
| 1 — Aanmaken & basis | 6 | 0 | | |
| 2 — Visueel & responsief | 7 | 1 | | |
| 3 — Celinhoud & opmaak | 7 | 1 | | |
| 4 — Samenvoegen & splitsen | 6 | 0 | | |
| 5 — Kleuren & styling | 7 | 1 | | |
| 6 — Vertalingen | 5 | 1 | | |
| 7 — Export | 8 | 1 | | |
| 8 — Import | 8 | 2 | | |
| 9 — Randgevallen | 9 | 5 | | |
| **Totaal** | **63** | **12** | | |

---

*Testplan gegenereerd voor MySueno QA — WordPress Tabel Plugin*
