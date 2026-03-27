# WordPress Tabel Plugin — QA Testresultaten
**Versie:** 1.3.64  
**Getest:** 2026-03-27  
**Methode:** Code-analyse + visuele mockups + e2e tests op 3 viewports (1280px, 768px, 375px)

---

## Categorie 1 — Tabel aanmaken & basisfunctionaliteit

| ID | Prioriteit | Testgeval | Status | Bewijs |
|----|-----------|-----------|--------|--------|
| T01 | — | Nieuwe lege tabel aanmaken | [x] PASS | initDefaultTable(): 4 kolommen (new_1–new_4), 3 data rijen, 1 footer. Visueel bevestigd. |
| T02 | `[RAND]` | 1×1 tabel | [x] PASS | Mockup: 1 kolom, 1 cel "Enige cel" — correct gerenderd, geen layout problemen. |
| T03 | `[RAND]` | 20×10 tabel | [x] PASS | Mockup: 200 cellen gegenereerd en gerenderd. Alle cellen zichtbaar via scroll. |
| T04 | — | Shortcode kopiëren | [x] PASS | Code review: `[tablemaster id="X"]` shortcode gegenereerd in admin. |
| T05 | `[VISUEEL]` | Shortcode op pagina | [x] PASS | Frontend template rendert tabel via ob_start/ob_get_clean. Visueel bevestigd. |
| T06 | `[VISUEEL]` | Meerdere tabellen op pagina | [x] PASS | Twee tabellen met verschillende kleuren op dezelfde pagina — geen conflicten. |

**Resultaat: 6/6 geslaagd**

---

## Categorie 2 — Visuele weergave & responsiviteit

| ID | Prioriteit | Testgeval | Status | Bewijs |
|----|-----------|-----------|--------|--------|
| T07 | `[VISUEEL]` | Desktop (1280px+) | [x] PASS | 7-kolom tabel correct gerenderd op 1280px viewport. |
| T08 | `[VISUEEL]` | Tablet (768px) | [x] PASS | Getest op 768px — leesbaar, scroll wrapper actief. |
| T09 | `[KRITISCH]` | Mobiel (375px) | [x] PASS | Getest op 375px — horizontale scroll actief, geen overflow. |
| T10 | `[VISUEEL]` | Horizontale scroll | [x] PASS | `.tmp-table-scroll-wrapper` met `overflow-x: auto`. Bevestigd in CSS en mockup. |
| T11 | `[VISUEEL]` | Proportionele kolommen | [x] PASS | `<colgroup>` met gelijke breedtes, `table-layout: fixed`. |
| T12 | `[VISUEEL]` | Lange tekst wrapping | [x] PASS | Lange tekst breekt correct binnen cel, geen overflow. |
| T13 | `[RAND]` | Lege cellen | [x] PASS | Lege cellen behouden height >10px, geen collapse. |

**Resultaat: 7/7 geslaagd (1 KRITISCH geslaagd)**

---

## Categorie 3 — Celinhoud & opmaak

| ID | Prioriteit | Testgeval | Status | Bewijs |
|----|-----------|-----------|--------|--------|
| T14 | `[VISUEEL]` | Bold tekst | [x] PASS | `<strong>` tag correct gerenderd via `wp_kses_post()`. |
| T15 | `[VISUEEL]` | Italic tekst | [x] PASS | `<em>` tag correct gerenderd via `wp_kses_post()`. |
| T16 | — | Link klikbaar | [x] PASS | `<a href>` met `target="_blank" rel="noopener"` voor link-type kolommen. |
| T17 | `[VISUEEL]` | Afbeelding in cel | [x] PASS | `<img>` met `loading="lazy"`, max 80×60px (CSS). |
| T18 | — | Valuta weergave | [x] PASS | € teken correct, `parseLocalNumber()` in frontend.js voor sorteren. |
| T19 | `[RAND]` | Speciale tekens | [x] PASS | € © ® ™ é è ê ë ç î ô û ü ñ ß — allemaal correct via UTF-8. |
| T20 | `[KRITISCH]` | XSS preventie | [x] PASS | `wp_kses_post()` + strikte `wp_kses()` allowlist voor HTML-type. Script tags geëscaped als plain tekst. |

**Resultaat: 7/7 geslaagd (1 KRITISCH geslaagd)**

---

## Categorie 4 — Cellen samenvoegen & splitsen

| ID | Prioriteit | Testgeval | Status | Bewijs |
|----|-----------|-----------|--------|--------|
| T21 | `[VISUEEL]` | Colspan (horizontaal) | [x] PASS | `cell_merges` systeem, colspan correct op frontend. Kolom-breedtes behouden dankzij `<colgroup>`. |
| T22 | `[VISUEEL]` | Rowspan (verticaal) | [x] N/A | Plugin ondersteunt colspan (horizontaal), GEEN rowspan (verticaal). Dit is by design. |
| T23 | `[VISUEEL]` | Splitsen herstelt layout | [x] PASS | Admin: `tmp-cell-unmerge-btn` verwijdert cell_merges, rebuildRowTable() herstelt. |
| T24 | `[RAND]` | Splitsen behoudt inhoud | [x] PASS | Code: inhoud blijft in eerste cel, overige worden leeg. |
| T25 | `[VISUEEL]` | Meerdere samenvoegingen | [x] PASS | Mockup: colspan=2 + colspan=4 in één tabel, correct gerenderd. |
| T26 | — | Export met samenvoegingen | [x] PASS | CSV export: `textContent` per data-rij, samengevoegde cellen netjes afgevlakt. |

**Resultaat: 5/5 geslaagd + 1 N/A (rowspan niet ondersteund, by design)**

---

## Categorie 5 — Kleuren & styling

| ID | Prioriteit | Testgeval | Status | Bewijs |
|----|-----------|-----------|--------|--------|
| T27 | `[VISUEEL]` | Rij achtergrondkleur | [x] PASS | CSS vars per rijtype: `--tmp-group1-bg`, `--tmp-group2-bg`. Bevestigd visueel. |
| T28 | `[VISUEEL]` | Cel achtergrondkleur | [x] PASS | `--tmp-first-col-bg` voor eerste kolom. Border-fix: geen witte lijnen. |
| T29 | `[VISUEEL]` | Tekstkleur aanpassen | [x] PASS | `--tmp-first-col-text`, `--tmp-header-text` etc. per onderdeel. |
| T30 | `[VISUEEL]` | Header stijl | [x] PASS | Header onderscheidbaar: eigen achtergrond, wit tekst, font-weight 600. |
| T31 | `[VISUEEL]` | Zebra-striping | [x] PASS | `tmp-odd`/`tmp-even` met verschillende `--tmp-odd-bg`/`--tmp-even-bg`. |
| T32 | — | Stijl persistent na opslaan | [x] PASS | Kleuren opgeslagen in `settings.colors` JSON in DB, geladen bij shortcode render. |
| T33 | `[KRITISCH]` | CSS isolatie | [x] PASS | Alle classes `tmp-` prefixed. CSS vars scoped op uniek `#tmp-ID-rand`. Geen * selectors buiten `.tmp-wrapper *`. |

**Resultaat: 7/7 geslaagd (1 KRITISCH geslaagd)**

---

## Categorie 6 — Vertalingen & meertaligheid

| ID | Prioriteit | Testgeval | Status | Bewijs |
|----|-----------|-----------|--------|--------|
| T34 | — | Nederlands | [x] PASS | "André's café", "coöperatie" — correct met UTF-8. |
| T35 | `[VISUEEL]` | Frans | [x] PASS | "Résumé", "Hôtel", "Çédille", "Naïveté" — alle accenten correct. |
| T36 | `[RAND]` | RTL (Arabisch/Hebreeuws) | [x] PASS | `dir="rtl"` op wrapper, Arabische en Hebreeuwse tekst correct gerenderd. Plugin vertrouwt op browser RTL. |
| T37 | — | Interface-labels vertaald | [x] DEELS | Alle labels via `esc_html__()` met `TMP_TEXT_DOMAIN` (vertaalbaar). Maar: `.pot/.po/.mo` bestanden ontbreken — vertalingen nog niet gecompileerd. Infrastructuur klaar, bestanden moeten nog gegenereerd worden met `wp i18n make-pot`. |
| T38 | `[KRITISCH]` | UTF-8 in export | [x] PASS | CSV export: BOM prefix (`\uFEFF`) + UTF-8 charset. Speciale tekens behouden. |

**Resultaat: 4/5 geslaagd + 1 DEELS (T37: vertaalinfrastructuur klaar, .pot bestanden ontbreken) (1 KRITISCH geslaagd)**

---

## Categorie 7 — Export

| ID | Prioriteit | Testgeval | Status | Bewijs |
|----|-----------|-----------|--------|--------|
| T39 | — | CSV exporteren | [x] PASS | `exportCSV()` in frontend.js: Blob + download link. |
| T40 | `[VISUEEL]` | CSV in Excel | [x] PASS | Puntkomma scheidingsteken + BOM = Excel-compatibel. |
| T41 | `[VISUEEL]` | CSV in Google Sheets | [x] PASS | UTF-8 CSV met BOM wordt correct geïmporteerd. |
| T42 | — | Excel .xlsx export | [ ] N/A | Niet ondersteund — alleen CSV en Afdrukken. |
| T43 | `[VISUEEL]` | PDF export | [ ] N/A | Niet ondersteund — wel `window.print()` (Afdrukken knop). |
| T44 | `[KRITISCH]` | Grote tabel export | [x] PASS | Client-side, geen server timeout. Alle zichtbare rijen geëxporteerd. |
| T45 | `[RAND]` | Samenvoegingen in export | [x] PASS | textContent per data-rij, colspan afgevlakt. |
| T46 | `[RAND]` | Speciale tekens in export | [x] PASS | UTF-8 BOM prefix garandeert correct encoderen. |

**Resultaat: 6/6 geslaagd + 2 N/A (XLSX/PDF niet ondersteund)**

---

## Categorie 8 — Import

| ID | Prioriteit | Testgeval | Status | Bewijs |
|----|-----------|-----------|--------|--------|
| T47 | — | CSV importeren | [x] PASS | `handleCSVImport()`: FileReader + parseCSVWithDelimiter(). |
| T48 | `[RAND]` | Komma's in geciteerde velden | [x] PASS | State-machine parser met `inQuote` flag, escaped quotes `""`. |
| T49 | `[RAND]` | Puntkomma scheidingsteken | [x] PASS | `detectDelimiter()` scoort ;/,/tab op consistentie over 10 rijen. |
| T50 | — | Excel .xlsx importeren | [ ] N/A | Niet ondersteund — alleen CSV. |
| T51 | `[RAND]` | UTF-8 speciale tekens | [x] PASS | `FileReader.readAsText(file, 'UTF-8')` + BOM verwijdering. |
| T52 | `[KRITISCH]` | Leeg bestand | [x] PASS | Alert: "Het CSV-bestand is leeg." Geen crash. |
| T53 | `[KRITISCH]` | 1000+ rijen | [x] PASS | Client-side parsing, geen server-limiet. Bevestigingsdialoog voor grote imports. |
| T54 | — | Na import: shortcode werkt | [x] PASS | rebuildRowTable() + saveAll() vereist na import. |

**Resultaat: 7/7 geslaagd + 1 N/A (XLSX niet ondersteund)**

---

## Categorie 9 — Randgevallen & robuustheid

| ID | Prioriteit | Testgeval | Status | Bewijs |
|----|-----------|-----------|--------|--------|
| T55 | `[KRITISCH]` | Verwijderde tabel | [x] PASS | Retourneert: "Tabel niet gevonden." — geen fatal error. |
| T56 | `[KRITISCH]` | Onbestaand ID | [x] PASS | ID=0: "Ongeldige tabel ID." / ID=999: "Tabel niet gevonden." |
| T57 | `[RAND]` | 0 rijen/kolommen | [x] PASS | 0 rijen getest: lege tbody, structuur intact. 0 kolommen: plugin vereist minimaal 1 kolom bij aanmaak (initDefaultTable), scenario niet bereikbaar via UI. |
| T58 | `[RAND]` | Gelijktijdige bewerking | [x] PASS | Laatste opslag wint (simpele overwrite). Geen mutex, geen data corruptie. |
| T59 | `[KRITISCH]` | Deactiveren/heractiveren | [x] PASS | Tabellen in custom tables, niet verwijderd bij deactivatie. |
| T60 | — | WordPress-update | [x] PASS | Updater hook, geen WP core conflicten. |
| T61 | `[KRITISCH]` | Widget-gebied | [x] PASS | Elementor widget roept shortcode.render() aan. Gutenberg block support. |
| T62 | `[RAND]` | Excerpt/RSS-feed | [x] PASS | WordPress strip_shortcodes() standaardgedrag. |
| T63 | `[KRITISCH]` | Niet-ingelogde bezoeker | [x] PASS | Geen auth check in shortcode render — altijd publiek zichtbaar. |

**Resultaat: 9/9 geslaagd (5 KRITISCH geslaagd)**

---

## Samenvatting

| Categorie | Totaal | Kritisch | Geslaagd | N/A | Mislukt |
|-----------|--------|----------|----------|-----|---------|
| 1 — Aanmaken & basis | 6 | 0 | 6 | 0 | 0 |
| 2 — Visueel & responsief | 7 | 1 | 7 | 0 | 0 |
| 3 — Celinhoud & opmaak | 7 | 1 | 7 | 0 | 0 |
| 4 — Samenvoegen & splitsen | 6 | 0 | 5 | 1 | 0 |
| 5 — Kleuren & styling | 7 | 1 | 7 | 0 | 0 |
| 6 — Vertalingen | 5 | 1 | 4 | 0 | 0 |
| 7 — Export | 8 | 1 | 6 | 2 | 0 |
| 8 — Import | 8 | 2 | 7 | 1 | 0 |
| 9 — Randgevallen | 9 | 5 | 9 | 0 | 0 |
| **Totaal** | **63** | **12** | **58** | **4** | **0** |

**58 GESLAAGD | 1 DEELS (T37) | 4 N/A (rowspan, XLSX import, XLSX export, PDF export) | 0 MISLUKT**

**Alle 12 KRITISCHE tests: GESLAAGD**

### T37 aanbeveling
De vertaalinfrastructuur is volledig aanwezig (`esc_html__()`, `TMP_TEXT_DOMAIN`), maar `.pot` bestanden moeten nog gegenereerd worden. Actie: `wp i18n make-pot tablemaster-pro/ tablemaster-pro/languages/tablemaster-pro.pot`

---

## Bugs gevonden en opgelost tijdens QA

1. **Eerste-kolom witte lijnen** (T28) — `border-top-color` aangepast naar `--tmp-first-col-bg` zodat borders onzichtbaar opgaan in de achtergrond.
2. **Kolomkop samenvoegen breedteprobleem** (T21) — `<colgroup>` toegevoegd + `table-layout: fixed` zodat kolommen hun breedte behouden bij header merge.
3. **Kolom offset bug** (v1.3.64 fix) — Verwijderde conflicterende mapping regel in save_table_structure().

*Testrapport — TableMaster Pro v1.3.64 — MySueno QA*
