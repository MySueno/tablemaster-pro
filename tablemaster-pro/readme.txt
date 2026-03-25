=== TableMaster Pro ===
Contributors: tablemaster
Tags: table, tables, responsive table, sortable table, filterable table, wpml
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.3.25
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Maak krachtige, interactieve tabellen met groepering, sortering, filtering en paginering.

== Description ==

TableMaster Pro is een complete oplossing voor het aanmaken en beheren van interactieve tabellen in WordPress. 

Functies:
* Onbeperkt tabellen aanmaken
* 3-niveaus groepering (inklapbaar)
* Kleurthema's via kleurpicker (groen, rood, blauw, grijs, custom)
* Zoeken, sorteren en pagineren op de frontend
* Volledig responsief (scroll- of kaartmodus)
* WPML-compatibel
* Shortcode [tablemaster id="X"] en Gutenberg block
* Export naar CSV

== Installation ==

1. Upload de plugin-map naar /wp-content/plugins/
2. Activeer de plugin via het WordPress beheerderspaneel
3. Ga naar TableMaster > Alle Tabellen
4. Gebruik de shortcode [tablemaster id="X"] in uw berichten of pagina's

== Changelog ==

= 1.3.25 =
* Fix: Kolomlabel bewerken zonder wijziging maakt geen undo-punt meer aan
* Fix: Vet/cursief/link-formatting maakt nu één undo-punt per sessie in plaats van één per actie
* Fix: Kolom-merge toolbar en cel-merge toolbar gebruiken aparte CSS-klassen — sluiten niet meer elkaars toolbar

= 1.3.24 =
* Nieuw: Ongedaan maken/opnieuw — Ctrl+Z (undo) en Ctrl+Shift+Z / Ctrl+Y (redo) in de tabeleditor
* Nieuw: Datacellen samenvoegen — Ctrl/Cmd+klik om meerdere cellen in een rij te selecteren en samen te voegen
* Nieuw: Toolbar (vet, cursief, link, lijst) werkt nu ook bij het bewerken van kolomtitels en groepsnamen
* Fix: Samenvoeg-popup verschijnt nu onder de kolomkop in plaats van erboven — toolbar is niet meer geblokkeerd

= 1.3.21 =
* Fix: Samengevoegde kolomkoppen (g1 zonder g2-subgroepen) tonen nu als één brede cel — geen extra sub-rijen meer op de frontend

= 1.3.20 =
* Nieuw: WYSIWYG celeditor — vet, cursief en links worden visueel weergegeven in de admin (geen ruwe HTML-tags meer)
* Nieuw: Plak-handler schoont automatisch rommel-HTML op bij plakken vanuit Word/Excel
* Nieuw: Link invoegen modal met curseurpositie-behoud — links worden altijd op de juiste plek ingevoegd
* Fix: Sorteren + paginering — na sorteren toonde paginering rijen in de verkeerde volgorde
* Fix: Groepsvolgorde bleef nu behouden bij sorteren (was eerder onvoorspelbaar)
* Fix: Ingeklapte groepskoppen verschijnen niet meer op pagina's zonder bijbehorende kinderrijen
* Fix: Lege groeps- en afsluitrijen worden correct samengevoegd — br-tags en &amp;nbsp; tellen niet meer als gevulde cellen

= 1.3.19 =
* Nieuw: Kolom-headers samenvoegen (Hoofdgroep/Subgroep) — selecteer kolommen en groepeer ze met één klik
* Groepsnaam wordt automatisch overgenomen van de eerste geselecteerde kolom (geen popup meer)
* Groepsbadges zijn klikbaar voor inline hernoemen — wijziging geldt voor alle kolommen in dezelfde groep
* Mac Ctrl+klik selectie werkt nu correct (geen dubbel-toggle meer)
* Verbeterde visuele scheiding tussen groep- en kolomheaders op de frontend
* WPML: groepsnamen worden correct vertaald in alle talen
* Kolomselectie: zodra 1+ kolom geselecteerd is, schakelen gewone klikken ook over naar selectiemodus

= 1.3.18 =
* Nieuw: Per-cel uitlijning (links, midden, rechts) via toolbar-knoppen in de admin
* Uitlijning wordt opgeslagen per cel en werkt voor datarijen, groepsrijen, afsluitrijen en samengevoegde cellen
* Standaard uitlijning is nu links voor alle celtypen (voorheen was afsluitrij gecentreerd)
* DB-upgrade wordt automatisch uitgevoerd bij plugin-update

= 1.3.17 =
* Fix: CSV-export op de frontend exporteert nu alle gefilterde rijen, niet alleen de huidige pagina
* Fix: In-/uitklappen van groepen werkt nu correct samen met paginering — zebra-striping en paginanummers worden bijgewerkt

= 1.3.16 =
* Nieuw: Maximale tabelbreedte instelling — stel een max-width in (bijv. 800px, 90%) die altijd wordt toegepast, ook bij vertalingen (WPML)
* Fix: Vertaalde tabellen krijgen nu dezelfde breedte als de oorspronkelijke tabel, onafhankelijk van de paginalay-out

= 1.3.15 =
* Nieuw: Excel-achtige toolbar boven de tabel met Vet, Cursief, Link, Opsommingslijst, Rij verwijderen, Kolom verwijderen
* Toolbar toont actieve cel-referentie (kolom · rij) en is context-afhankelijk
* Actieve cel krijgt visuele highlight (blauwe rand)
* Nieuw: Opsommingslijst (bullet points) — selecteer tekst en klik op het lijst-icoon om automatisch een `<ul><li>` lijst te genereren
* Nieuw: CSV-import — importeer data vanuit CSV, TSV of puntkomma-gescheiden bestanden
* CSV-parser met automatische delimiter-detectie (komma, puntkomma, tab)
* Ondersteuning voor quoted velden, escaped aanhalingstekens, en newlines binnen velden
* BOM-stripping voor Excel-exports (UTF-8 met BOM)
* Bevestigingsdialoog bij vervangen van bestaande tabeldata

= 1.3.14 =
* Fix: Caption (onderschrift) wordt nu correct opgeslagen — was hardcoded als lege string in saveAll()
* Fix: Paginering toont geen lege groepskoppen meer op pagina's waar geen bijbehorende datarijen staan

= 1.3.13 =
* Fix: Sortering — lege cellen sorteren altijd naar onderaan, NaN-guards voor nummer- en datumkolommen
* Fix: Europese nummernotatie (1.250,50) wordt correct geparsed bij sortering
* Fix: Frontend CSV-export gebruikt nu puntkomma (;) als scheidingsteken, consistent met admin-export
* Fix: Zoekbalk focus-schaduw gebruikt nu de accentkleur in plaats van hardcoded groen
* Opgeschoond: Ongebruikte getVisibleDataRows methode verwijderd

= 1.3.12 =
* Fix: Zoeken en kolomfilters verbergen nu daadwerkelijk gefilterde rijen (ontbrekende CSS regel)
* Fix: CSV-export matcht kolomkoppen correct met celdata bij samengevoegde headers
* Fix: Sticky header checkbox triggert nu "onopgeslagen wijzigingen" waarschuwing
* Nieuw: Paginering toont nu "X–Y van Z resultaten" tekst onder de tabel

= 1.3.11 =
* Fix: Sortering werkt nu correct bij tabellen met samengevoegde kolomkoppen (data-col-id lookup)
* Fix: Klik buiten tabel deselecteert kolommen en verbergt samenvoeg-toolbar
* Fix: Escape bij kolomnaam bewerken herstelt nu correct de originele naam
* Fix: Merge toolbar wordt netjes opgeruimd bij tabel-rebuild
* Nieuw: WPML-vertaling voor kolomgroepsnamen (niveau 1 en 2) — registratie, vertaalweergave en frontend

= 1.3.10 =
* Fix: Versie bump om browser-caching van admin JS/CSS te doorbreken
* Nieuw: Update Server URL standaard ingevuld (https://table-importer-tool.replit.app/)

= 1.3.9 =
* Nieuw: Volledig vernieuwde kolom-popover met alle instellingen (naam, breedte, uitlijning, sorteerbaar, filterbaar, groepering)
* Nieuw: Header groepering — kolommen samenvoegen met Groep niveau 1 en 2 voor multi-level tabelkoppen
* Nieuw: Visuele indicator op kolomkoppen met actieve groepering in de admin
* Nieuw: Hulptekst en conditionele weergave van groep-velden (niveau 2 verschijnt pas bij ingevuld niveau 1)
* Nieuw: Bevestigingsdialoog bij kolom verwijderen
* Fix: Gegroepeerde kolomkoppen worden nu gecentreerd weergegeven
* Fix: Per-kolom uitlijning werkt nu correct in gegroepeerde header-modus
* Fix: Subtiele randen tussen gegroepeerde kolomkoppen voor betere visuele scheiding

= 1.3.8 =
* Fix: Sticky first column — groep-rijen (G1/G2/G3) en footer-rijen blijven nu zichtbaar bij horizontaal scrollen
* Fix: Groep-rij cel wordt gesplitst in een sticky label-cel en een lege rest-cel bij sticky first column

= 1.3.7 =
* Fix: Kritieke PHP parse error opgelost — geneste <?php tag in gegroepeerde header veroorzaakte site crash
* Fix: Collapsible groups instelling werd niet opgeslagen — checkbox toegevoegd aan Display tab en saveAll() gecorrigeerd
* Fix: Frontend CSV export bevatte groep- en footer-rijen — alleen data-rijen worden nu geëxporteerd
* Fix: CSV export pakte ook groepsheader-cellen mee als kolommen — nu alleen headers met data-col-id
* Fix: Ongegroepeerde kolommen in gegroepeerde tabellen misten data-col-id en sorteerattributen — sortering en export werken nu correct voor alle kolomtypes
* GPL-2.0 licentie volledig toegevoegd (LICENSE bestand + header)

= 1.3.2 =
* Fix: Vertaalde tabel werd niet getoond op de frontend — WPML-detectie werkte alleen in admin, nu ook op publieke pagina's
* Fix: Taalcode wordt nu expliciet doorgegeven door de hele vertaalchain — betrouwbaar voor alle talen
* Fix: Directe database-fallback toegevoegd wanneer WPML's vertaalfilter de vertaling niet vindt
* Verbeterd: Hover-effect kleurt nu de volledige rij (inclusief alle cellen)
* Verbeterd: Standaard font ingesteld op Calibri Regular (400) met semi-bold (600) voor kolomkoppen
* Fix: Standaard sorteer-pijltjes verborgen — alleen zichtbaar bij actief sorteren

= 1.3.1 =
* Fix: Vertaalvoortgang telt nu alle ingevulde velden mee (inclusief automatisch ingevulde)
* Fix: Na opslaan worden prefilled vertalingen correct als definitief gemarkeerd
* Fix: Globale vertaal-lookup werkte niet — context-patroon gecorrigeerd zodat vertalingen uit andere tabellen correct worden gevonden
* Fix: Groen thema toonde rode stip in tabeloverzicht — kleurcode gecorrigeerd
* Fix: Groen thema kon niet worden opgeslagen — ontbrak in de toegestane thema-lijst
* Fix: Groen kleurenpreset toegevoegd (was niet gedefinieerd)
* Fix: Groen thema toegevoegd aan instellingen-dropdown
* Fix: Gedupliceerde tabellen krijgen nu correct een aanmaakdatum

= 1.3.0 =
* Nieuw: Slimme vertaal-prefill — bestaande vertalingen uit andere tabellen worden automatisch ingevuld
* Nieuw: Globale vertaalgeheugen — zoekt in alle TableMaster tabellen naar eerder vertaalde teksten
* Verbeterd: Auto-fill werkt nu ook voor tabelnaam, onderschrift en kolomnamen (niet alleen celinhoud)
* Verbeterd: Update-mechanisme robuuster — automatische retry bij verbindingsproblemen (tot 3 pogingen)
* Verbeterd: Langere timeout (30s) en Connection: close header voor betere compatibiliteit met wisselende servers
* Verbeterd: Slimme backoff — bij onbereikbare server 30 minuten wachten voordat opnieuw wordt geprobeerd
* Verbeterd: Admin-melding bij verbindingsproblemen met de update server
* Fix: Download URL wordt nu lokaal opgebouwd vanuit de bekende server-URL — voorkomt fouten bij wisselende domeinen
* Fix: Versienummer constant (TMP_VERSION) gesynchroniseerd met plugin header
* Fix: Inklapbare groepen werkten niet op de frontend — collapsible_groups instelling werd genegeerd
* Beveiliging: API server beschermd tegen host header injection
* Beveiliging: Rate limiting, Helmet headers, streaming ZIP downloads
* Beveiliging: Footer rijtype toegevoegd aan whitelist, strict type-vergelijking bij validatie

= 1.2.8 =
* Verbeterd: Alle tekst in tabellen consistent links uitgelijnd — kolomtitels, groepsrijen en datacellen staan nu exact op dezelfde positie
* Verbeterd: Groepsrijen met samengevoegde cellen gebruiken links-uitlijning in plaats van gecentreerd
* Nieuw: Databescherming — tabeldata blijft bewaard bij verwijderen en herinstalleren van de plugin (standaard ingeschakeld)
* Nieuw: Optionele instelling om data te verwijderen bij deïnstallatie (standaard uitgeschakeld)
* Nieuw: Vertaalstatus per taal zichtbaar in tabeloverzicht (groen = compleet, geel = bezig, rood = niet gestart)
* Verbeterd: Onvolledige vertalingen vallen terug op de standaardtaal — bezoekers zien altijd een complete tabel
* Verbeterd: Duidelijke melding in vertaaleditor of de vertaling compleet is

= 1.2.7 =
* Nieuw: Admin CSV-export — exporteer tabellen als CSV-bestand vanuit de bewerkpagina en het tabeloverzicht
* Nieuw: Slimme vertaal auto-fill — identieke celteksten krijgen automatisch dezelfde vertaling (geel gemarkeerd)
* Nieuw: Live auto-fill bij typen — wanneer je een vertaling invoert, worden lege velden met dezelfde originele tekst automatisch ingevuld
* Verbeterd: Vertaalteller telt alleen handmatig vertaalde velden (prefilled velden tellen pas mee na opslaan)
* Verbeterd: Prefilled vertalingen worden groen na handmatige bewerking
* Fix: Geen geel bolletje meer bij lege prefilled velden

= 1.2.6 =
* Nieuw: Eigen vertaaleditor — side-by-side layout met origineel en vertaling
* Nieuw: Voortgangsteller voor vertalingen per taal
* Nieuw: Kopieerknop per veld om originele tekst over te nemen
* Nieuw: Sticky opslaan-balk onderaan de vertaaleditor
* Nieuw: Waarschuwing bij niet-opgeslagen vertalingen
* Nieuw: Taalschakelaar met dirty-check bij meerdere WPML-talen
* Verbeterd: WPML niet actief of slechts één taal → duidelijke melding in plaats van foutmelding

= 1.2.5 =
* Nieuw: Standaard kolombreedte op tabelniveau — stel een breedte in (bijv. 150px) die geldt voor alle kolommen zonder eigen breedte
* Responsief: Dynamische min-width op tabellen — kolommen behouden leesbare breedte op mobiel/tablet
* Responsief: Horizontale scroll werkt nu correct voor tabellen met veel kolommen

= 1.2.4 =
* Beveiliging: Strikte hex-kleur validatie — voorkomt CSS injection via kleurinstellingen
* Beveiliging: Alle tabelinstellingen worden nu diep gesanitized (whitelist thema's, posities, modi)
* Beveiliging: Input lengtebeperkingen — tabelnaam max 200, kolom labels max 200, max 100 kolommen, max 10.000 rijen
* Beveiliging: Kolombreedte-validatie via regex — alleen geldige CSS waarden geaccepteerd
* Beveiliging: Alignment validatie met whitelist (left/center/right)
* Beveiliging: Delete en duplicate valideren nu of de tabel bestaat vóór actie
* Beveiliging: Ongebruikte frontend nonce verwijderd (geen onnodige server-side overhead)
* Beveiliging: Payload-grootte limieten op structuur-opslag (1MB kolommen, 10MB rijen)
* Performance: Cache flush gebruikt nu specifieke delete_transient() i.p.v. trage LIKE queries
* Performance: Per-page waarden begrensd (max 500) om memory-problemen te voorkomen
* Verbeterd: Uninstall ruimt nu alle transients en WPML-registratie op
* Verbeterd: Header group velden worden nu ook correct gesanitized bij opslaan

= 1.2.3 =
* Nieuw: Eigen Elementor widget — sleep "TableMaster Pro" vanuit het Elementor paneel direct op je pagina
* Nieuw: Tabelselectie via dropdown in Elementor — geen shortcode nodig
* Nieuw: Elementor stijlopties — maximale breedte, lettergrootte, uitlijning per breakpoint
* Nieuw: Placeholder in de Elementor editor wanneer geen tabel geselecteerd is
* Verbeterd: Alle TableMaster stijlen laden correct in Elementor preview en editor
* Verbeterd: CSS-overrides voorkomen stijlconflicten met Elementor's eigen styling
* Verbeterd: Directe "Tabellen beheren" link in het Elementor paneel

= 1.2.2 =
* Nieuw standaard kleurthema: rood (#D32637) met G1 wit-op-rood, G2 rood-op-roze (#F9E6E7), data op #F8F8F8
* Alle kolommen standaard even breed (table-layout: fixed) — samengevoegde cellen passen zich aan
* Nieuwe tabellen starten nu automatisch met het rode kleurthema
* Alle kleurpresets bijgewerkt naar verfijnde kleuren

= 1.2.1 =
* Nieuw: Klikbaar rijtype-label — klik op Data/G1/G2/G3 om het type direct te wijzigen
* Nieuw: Rij dupliceren knop — kopieer een bestaande rij met alle inhoud
* Nieuw: Slimme auto-merge — lege cellen in groepsrijen worden automatisch samengevoegd op de frontend
* Verbeterd: Placeholder-hints in groepsrij cellen ("Leeg = samenvoegen →")
* Verbeterd: Tooltips op alle rij-knoppen met uitleg
* Verbeterd: Groepsrijen met meerdere gevulde cellen renderen nu als aparte cellen met colspan waar nodig

= 1.2.0 =
* Nieuw: Multi-level kolomheaders (3 niveaus) — maak tabellen met groepskoppen zoals E. coli > Ambulant > 2024-2025
* Per kolom twee optionele velden: "Header groep 1" en "Header groep 2" voor automatische colspan/rowspan berekening
* Rood kleurthema verfijnd: exacte kleuren afgestemd op branding (zachtere randen, neutrale even-rijen)
* Standaard border-radius verhoogd naar 12px
* Verticale celranden verwijderd voor een schoner uiterlijk

= 1.1.2 =
* Fix: WPML strings worden nu automatisch geregistreerd bij plugin-update (geen handmatig opslaan meer nodig)
* Fix: "Vertalen" knop linkt nu correct naar de WPML String Translation pagina met juiste context-filter
* Tabelnaam wordt nu ook als vertaalbare string geregistreerd

= 1.1.1 =
* Fix: Elementor-compatibiliteit — kleuren en stijlen worden nu correct geladen in Elementor editor en frontend
* Fix: Shortcode-detectie werkt nu ook als de shortcode in Elementor widgets staat

= 1.1.0 =
* Kleuren worden nu direct toegepast op de admin rijtabel (aparte preview verwijderd)
* Nieuwe globale instelling: Tabel border-radius (px) — geldt voor alle tabellen
* WPML-integratie verbeterd: wpml-config.xml, betere string-context per tabel
* "Vertalen" knop in tabellenlijst en bewerkpagina (linkt naar WPML String Translation)
* Derde demotabel "Anatomopathologie (Kiemen)" toegevoegd
* CSS-fix: groepsrijen gebruiken nu inner wrapper div voor correcte layout

= 1.0.0 =
* Initiële release
