<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( esc_html__( 'Geen toegang.', TMP_TEXT_DOMAIN ) );

$table_id  = intval( $_GET['id'] ?? 0 );
$is_new    = ! $table_id;
$table     = $table_id ? TableMaster_DB::get_table( $table_id ) : null;
$settings  = $table ? json_decode( $table->settings, true ) : array();
$table_name= $table ? $table->name : '';

$colors        = $settings['colors'] ?? array();
$presets       = TableMaster_Settings::get_color_presets();
$active_theme  = $settings['theme'] ?? 'green';
$default_colors= $presets['green'];

$c = array_merge( $default_colors, $colors );

$page_title = $is_new
    ? __( 'Nieuwe Tabel aanmaken', TMP_TEXT_DOMAIN )
    : __( 'Tabel bewerken', TMP_TEXT_DOMAIN );
?>
<div class="wrap tmp-wrap">
    <h1>
        <?php echo esc_html( $page_title ); ?>
        <?php if ( ! $is_new ) : ?>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=tablemaster' ) ); ?>" class="page-title-action">
                &larr; <?php esc_html_e( 'Alle Tabellen', TMP_TEXT_DOMAIN ); ?>
            </a>
        <?php endif; ?>
    </h1>

    <?php if ( ! $is_new ) : ?>
        <div class="tmp-shortcode-bar">
            <span><?php esc_html_e( 'Shortcode:', TMP_TEXT_DOMAIN ); ?></span>
            <code id="tmp-shortcode-value">[tablemaster id="<?php echo esc_attr( $table_id ); ?>"]</code>
            <button class="button button-small tmp-copy-btn" data-shortcode='[tablemaster id="<?php echo esc_attr( $table_id ); ?>"]'>
                <?php esc_html_e( 'Kopiëren', TMP_TEXT_DOMAIN ); ?>
            </button>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=tablemaster-preview&id=' . $table_id ) ); ?>"
               class="button button-small" target="_blank" style="margin-left:8px;">
                <span class="dashicons dashicons-visibility" style="margin-top:3px;margin-right:3px;"></span>
                <?php esc_html_e( 'Bekijk op website', TMP_TEXT_DOMAIN ); ?>
            </a>
        </div>
    <?php endif; ?>

    <div class="tmp-editor-layout">

        <!-- LEFT PANEL -->
        <div class="tmp-panel tmp-panel-left">
            <div class="tmp-panel-header">
                <h2><?php esc_html_e( 'Tabelstructuur', TMP_TEXT_DOMAIN ); ?></h2>
                <div class="tmp-table-name-field">
                    <label for="tmp-table-name"><?php esc_html_e( 'Tabelnaam (intern):', TMP_TEXT_DOMAIN ); ?></label>
                    <input type="text" id="tmp-table-name" value="<?php echo esc_attr( $table_name ); ?>" placeholder="<?php esc_attr_e( 'Bijv. Productenlijst', TMP_TEXT_DOMAIN ); ?>" class="regular-text">
                </div>
            </div>

            <!-- COLUMNS -->
            <div class="tmp-section">
                <div class="tmp-section-header">
                    <h3><?php esc_html_e( 'Kolommen', TMP_TEXT_DOMAIN ); ?></h3>
                    <button id="tmp-add-column" class="button button-secondary button-small">
                        + <?php esc_html_e( 'Kolom toevoegen', TMP_TEXT_DOMAIN ); ?>
                    </button>
                </div>
                <div id="tmp-columns-container" class="tmp-columns-sortable">
                    <div class="tmp-columns-empty tmp-hint"><?php esc_html_e( 'Nog geen kolommen. Klik op "+ Kolom toevoegen"', TMP_TEXT_DOMAIN ); ?></div>
                </div>
            </div>

            <!-- ROWS -->
            <div class="tmp-section">
                <div class="tmp-section-header">
                    <h3><?php esc_html_e( 'Rijen & Gegevens', TMP_TEXT_DOMAIN ); ?></h3>
                    <div class="tmp-row-buttons">
                        <button id="tmp-add-row"    class="button button-secondary button-small">+ <?php esc_html_e( 'Rij', TMP_TEXT_DOMAIN ); ?></button>
                        <button id="tmp-add-group1" class="button button-secondary button-small tmp-group1-btn">+ <?php esc_html_e( 'Groep 1', TMP_TEXT_DOMAIN ); ?></button>
                        <button id="tmp-add-group2" class="button button-secondary button-small tmp-group2-btn">+ <?php esc_html_e( 'Groep 2', TMP_TEXT_DOMAIN ); ?></button>
                        <button id="tmp-add-group3" class="button button-secondary button-small tmp-group3-btn">+ <?php esc_html_e( 'Groep 3', TMP_TEXT_DOMAIN ); ?></button>
                    </div>
                </div>
                <div class="tmp-rows-hint tmp-hint"><?php esc_html_e( 'Klik op een cel om te bewerken. Sleep rijen om te herordenen.', TMP_TEXT_DOMAIN ); ?></div>
                <div id="tmp-rows-wrapper" class="tmp-rows-wrapper">
                    <div class="tmp-rows-empty tmp-hint"><?php esc_html_e( 'Nog geen rijen. Voeg kolommen toe en klik op "+ Rij toevoegen".', TMP_TEXT_DOMAIN ); ?></div>
                </div>
            </div>

            <div class="tmp-save-bar">
                <button id="tmp-save-all" class="button button-primary button-large">
                    <span class="dashicons dashicons-saved"></span>
                    <?php esc_html_e( 'Alles opslaan', TMP_TEXT_DOMAIN ); ?>
                </button>
                <span id="tmp-save-status" class="tmp-save-status"></span>
            </div>
        </div>

        <!-- RIGHT PANEL -->
        <div class="tmp-panel tmp-panel-right">
            <div class="tmp-tabs">
                <button class="tmp-tab active" data-tab="colors"><?php esc_html_e( 'Kleuren', TMP_TEXT_DOMAIN ); ?></button>
                <button class="tmp-tab" data-tab="display"><?php esc_html_e( 'Weergave', TMP_TEXT_DOMAIN ); ?></button>
                <button class="tmp-tab" data-tab="filters"><?php esc_html_e( 'Filters', TMP_TEXT_DOMAIN ); ?></button>
                <button class="tmp-tab" data-tab="advanced"><?php esc_html_e( 'Geavanceerd', TMP_TEXT_DOMAIN ); ?></button>
            </div>

            <!-- COLORS TAB -->
            <div class="tmp-tab-content active" id="tmp-tab-colors">
                <div class="tmp-presets">
                    <label><?php esc_html_e( 'Kleurthema:', TMP_TEXT_DOMAIN ); ?></label>
                    <div class="tmp-preset-buttons">
                        <?php foreach ( $presets as $key => $preset ) : ?>
                            <button class="tmp-preset-btn <?php echo $active_theme === $key ? 'active' : ''; ?>"
                                    data-preset="<?php echo esc_attr( $key ); ?>"
                                    style="background:<?php echo esc_attr( $preset['header_bg'] ); ?>"
                                    title="<?php echo esc_attr( ucfirst( $key ) ); ?>">
                                <?php echo esc_html( ucfirst( $key ) ); ?>
                            </button>
                        <?php endforeach; ?>
                        <button class="tmp-preset-btn <?php echo $active_theme === 'custom' ? 'active' : ''; ?>" data-preset="custom" style="background:linear-gradient(135deg,#ff6b6b,#4ecdc4,#45b7d1)">
                            <?php esc_html_e( 'Custom', TMP_TEXT_DOMAIN ); ?>
                        </button>
                    </div>
                </div>

                <div class="tmp-color-grid">
                    <?php
                    $color_fields = array(
                        'header_bg'    => __( 'Koptekst achtergrond', TMP_TEXT_DOMAIN ),
                        'header_text'  => __( 'Koptekst tekstkleur', TMP_TEXT_DOMAIN ),
                        'group1_bg'    => __( 'Groep 1 achtergrond', TMP_TEXT_DOMAIN ),
                        'group1_text'  => __( 'Groep 1 tekstkleur', TMP_TEXT_DOMAIN ),
                        'group2_bg'    => __( 'Groep 2 achtergrond', TMP_TEXT_DOMAIN ),
                        'group2_text'  => __( 'Groep 2 tekstkleur', TMP_TEXT_DOMAIN ),
                        'group3_bg'    => __( 'Groep 3 achtergrond', TMP_TEXT_DOMAIN ),
                        'group3_text'  => __( 'Groep 3 tekstkleur', TMP_TEXT_DOMAIN ),
                        'odd_bg'       => __( 'Oneven rijen achtergrond', TMP_TEXT_DOMAIN ),
                        'even_bg'      => __( 'Even rijen achtergrond', TMP_TEXT_DOMAIN ),
                        'hover_bg'     => __( 'Hover kleur rij', TMP_TEXT_DOMAIN ),
                        'border_color' => __( 'Randkleur', TMP_TEXT_DOMAIN ),
                        'accent_color' => __( 'Accentkleur', TMP_TEXT_DOMAIN ),
                    );
                    foreach ( $color_fields as $key => $label ) : ?>
                        <div class="tmp-color-field">
                            <label><?php echo esc_html( $label ); ?></label>
                            <input type="text"
                                   class="tmp-color-picker"
                                   data-color-key="<?php echo esc_attr( $key ); ?>"
                                   value="<?php echo esc_attr( $c[$key] ?? '#ffffff' ); ?>"
                                   data-default-color="<?php echo esc_attr( $c[$key] ?? '#ffffff' ); ?>">
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- MINI LIVE PREVIEW -->
                <div class="tmp-color-preview-section">
                    <h4><?php esc_html_e( 'Live Kleurenpreview', TMP_TEXT_DOMAIN ); ?></h4>
                    <div id="tmp-color-preview" class="tmp-color-preview-table">
                        <table>
                            <thead>
                                <tr class="tmp-prev-header">
                                    <th><?php esc_html_e( 'Kolom A', TMP_TEXT_DOMAIN ); ?></th>
                                    <th><?php esc_html_e( 'Kolom B', TMP_TEXT_DOMAIN ); ?></th>
                                    <th><?php esc_html_e( 'Kolom C', TMP_TEXT_DOMAIN ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="tmp-prev-group1"><td colspan="3"><?php esc_html_e( '▼ Groep 1', TMP_TEXT_DOMAIN ); ?></td></tr>
                                <tr class="tmp-prev-group2"><td colspan="3">&nbsp;&nbsp;<?php esc_html_e( '▼ Subgroep 1.1', TMP_TEXT_DOMAIN ); ?></td></tr>
                                <tr class="tmp-prev-odd"><td><?php esc_html_e( 'Waarde A', TMP_TEXT_DOMAIN ); ?></td><td><?php esc_html_e( 'Waarde B', TMP_TEXT_DOMAIN ); ?></td><td><?php esc_html_e( 'Waarde C', TMP_TEXT_DOMAIN ); ?></td></tr>
                                <tr class="tmp-prev-even"><td><?php esc_html_e( 'Waarde A', TMP_TEXT_DOMAIN ); ?></td><td><?php esc_html_e( 'Waarde B', TMP_TEXT_DOMAIN ); ?></td><td><?php esc_html_e( 'Waarde C', TMP_TEXT_DOMAIN ); ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- DISPLAY TAB -->
            <div class="tmp-tab-content" id="tmp-tab-display">
                <div class="tmp-form-group">
                    <label for="tmp-caption"><?php esc_html_e( 'Tabel caption/titel (optioneel):', TMP_TEXT_DOMAIN ); ?></label>
                    <input type="text" id="tmp-caption" value="<?php echo esc_attr( $settings['caption'] ?? '' ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Bijv. Productenlijst 2026', TMP_TEXT_DOMAIN ); ?>">
                </div>
                <div class="tmp-form-group">
                    <label>
                        <input type="checkbox" id="tmp-search" <?php checked( $settings['search'] ?? true ); ?>>
                        <?php esc_html_e( 'Zoekbalk tonen', TMP_TEXT_DOMAIN ); ?>
                    </label>
                </div>
                <div class="tmp-form-group tmp-indent" id="tmp-search-position-group">
                    <label for="tmp-search-position"><?php esc_html_e( 'Positie zoekbalk:', TMP_TEXT_DOMAIN ); ?></label>
                    <select id="tmp-search-position">
                        <?php
                        $sp = $settings['search_position'] ?? 'right';
                        foreach ( array( 'left' => __( 'Links', TMP_TEXT_DOMAIN ), 'right' => __( 'Rechts', TMP_TEXT_DOMAIN ) ) as $val => $lbl ) {
                            echo '<option value="' . esc_attr( $val ) . '"' . selected( $sp, $val, false ) . '>' . esc_html( $lbl ) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="tmp-form-group">
                    <label>
                        <input type="checkbox" id="tmp-pagination" <?php checked( $settings['pagination'] ?? true ); ?>>
                        <?php esc_html_e( 'Paginering inschakelen', TMP_TEXT_DOMAIN ); ?>
                    </label>
                </div>
                <div class="tmp-form-group tmp-indent" id="tmp-per-page-group">
                    <label for="tmp-per-page"><?php esc_html_e( 'Items per pagina:', TMP_TEXT_DOMAIN ); ?></label>
                    <select id="tmp-per-page">
                        <?php
                        $pp = intval( $settings['per_page'] ?? 10 );
                        foreach ( array( 5, 10, 25, 50, 100, -1 ) as $val ) {
                            $lbl = $val === -1 ? __( 'Alle', TMP_TEXT_DOMAIN ) : $val;
                            echo '<option value="' . esc_attr( $val ) . '"' . selected( $pp, $val, false ) . '>' . esc_html( $lbl ) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="tmp-form-group tmp-indent">
                    <label>
                        <input type="checkbox" id="tmp-per-page-selector" <?php checked( $settings['per_page_selector'] ?? true ); ?>>
                        <?php esc_html_e( 'Items-per-pagina selector tonen', TMP_TEXT_DOMAIN ); ?>
                    </label>
                </div>
                <div class="tmp-form-group">
                    <label>
                        <input type="checkbox" id="tmp-collapsible" <?php checked( $settings['collapsible_groups'] ?? true ); ?>>
                        <?php esc_html_e( 'Inklapbare groepen', TMP_TEXT_DOMAIN ); ?>
                    </label>
                </div>
                <div class="tmp-form-group">
                    <label for="tmp-mobile-mode"><?php esc_html_e( 'Mobiel gedrag:', TMP_TEXT_DOMAIN ); ?></label>
                    <select id="tmp-mobile-mode">
                        <?php
                        $mm = $settings['mobile_mode'] ?? 'scroll';
                        foreach ( array( 'scroll' => __( 'Horizontaal scrollen', TMP_TEXT_DOMAIN ), 'card' => __( 'Kaartweergave', TMP_TEXT_DOMAIN ) ) as $val => $lbl ) {
                            echo '<option value="' . esc_attr( $val ) . '"' . selected( $mm, $val, false ) . '>' . esc_html( $lbl ) . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>

            <!-- FILTERS TAB -->
            <div class="tmp-tab-content" id="tmp-tab-filters">
                <p class="description"><?php esc_html_e( 'Filters worden per kolom ingesteld (zie kolominstellingen in het linker paneel). Hieronder zijn de globale filteropties:', TMP_TEXT_DOMAIN ); ?></p>
                <div class="tmp-form-group">
                    <label>
                        <input type="checkbox" id="tmp-column-filters" <?php checked( $settings['column_filters'] ?? false ); ?>>
                        <?php esc_html_e( 'Kolomfilters tonen (dropdown per kolom)', TMP_TEXT_DOMAIN ); ?>
                    </label>
                </div>
            </div>

            <!-- ADVANCED TAB -->
            <div class="tmp-tab-content" id="tmp-tab-advanced">
                <div class="tmp-form-group">
                    <label>
                        <input type="checkbox" id="tmp-inline-html" <?php checked( $settings['inline_html'] ?? false ); ?>>
                        <?php esc_html_e( 'Inline HTML toestaan in cellen', TMP_TEXT_DOMAIN ); ?>
                    </label>
                    <p class="description"><?php esc_html_e( 'Laat HTML-opmaak toe in celinhoud. Gebruik alleen als u de inhoud vertrouwt.', TMP_TEXT_DOMAIN ); ?></p>
                </div>
                <div class="tmp-form-group">
                    <label>
                        <input type="checkbox" id="tmp-sticky-first-col" <?php checked( $settings['sticky_first_col'] ?? false ); ?>>
                        <?php esc_html_e( 'Eerste kolom vastzetten bij horizontaal scrollen', TMP_TEXT_DOMAIN ); ?>
                    </label>
                    <p class="description"><?php esc_html_e( 'De eerste kolom blijft zichtbaar als de tabel horizontaal gescrolld wordt (scroll-modus).', TMP_TEXT_DOMAIN ); ?></p>
                </div>
                <div class="tmp-form-group">
                    <label for="tmp-default-sort-col"><?php esc_html_e( 'Standaard sorteerkolom (index, 0 = eerste):', TMP_TEXT_DOMAIN ); ?></label>
                    <input type="number" id="tmp-default-sort-col" value="<?php echo esc_attr( $settings['default_sort_col'] ?? '' ); ?>" min="0" class="small-text">
                </div>
                <div class="tmp-form-group">
                    <label for="tmp-default-sort-dir"><?php esc_html_e( 'Standaard sorteerrichting:', TMP_TEXT_DOMAIN ); ?></label>
                    <select id="tmp-default-sort-dir">
                        <?php
                        $sd = $settings['default_sort_dir'] ?? 'asc';
                        echo '<option value="asc"'  . selected( $sd, 'asc', false )  . '>' . esc_html__( 'Oplopend', TMP_TEXT_DOMAIN ) . '</option>';
                        echo '<option value="desc"' . selected( $sd, 'desc', false ) . '>' . esc_html__( 'Aflopend', TMP_TEXT_DOMAIN ) . '</option>';
                        ?>
                    </select>
                </div>
                <?php if ( ! $is_new ) : ?>
                <div class="tmp-form-group">
                    <label><?php esc_html_e( 'Tabel ID (voor debuggen):', TMP_TEXT_DOMAIN ); ?></label>
                    <code><?php echo esc_html( $table_id ); ?></code>
                </div>
                <?php endif; ?>
            </div>

        </div><!-- end right panel -->
    </div><!-- end editor layout -->
</div>
