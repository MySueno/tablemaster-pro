=== TableMaster Pro ===
Contributors: tablemaster
Tags: table, tables, responsive table, sortable table, filterable table, wpml
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.2.9
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

= 1.2.9 =
* Fix: Versienummer constant (TMP_VERSION) gesynchroniseerd met plugin header
* Fix: Inklapbare groepen werkten niet op de frontend — collapsible_groups instelling werd genegeerd
* Beveiliging: API server beschermd tegen host header injection — download URL nu afgeleid van serveromgeving
* Beveiliging: Rate limiting toegevoegd op API server (60 req/min algemeen, 10 req/min downloads)
* Beveiliging: Helmet security headers toegevoegd (CSP, HSTS, X-Content-Type-Options, etc.)
* Beveiliging: ZIP download gebruikt nu streaming i.p.v. geheugen-buffering
* Beveiliging: Footer rijtype toegevoegd aan whitelist bij opslaan
* Beveiliging: Strict type-vergelijking bij rijtype validatie

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
