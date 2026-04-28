<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( esc_html__( 'Geen toegang.', TMP_TEXT_DOMAIN ) );

$table_id = intval( $_GET['id'] ?? 0 );
$table    = $table_id ? TableMaster_DB::get_table( $table_id ) : null;

if ( ! $table ) {
    wp_die( esc_html__( 'Tabel niet gevonden.', TMP_TEXT_DOMAIN ) );
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html( $table->name ); ?> — Preview</title>
    <?php
    wp_enqueue_style( 'tablemaster-frontend', TMP_PLUGIN_URL . 'assets/css/frontend.css', array(), TMP_VERSION );
    wp_enqueue_script( 'tablemaster-frontend-js', TMP_PLUGIN_URL . 'assets/js/frontend.js', array(), TMP_VERSION, true );
    wp_localize_script( 'tablemaster-frontend-js', 'tableMasterFrontend', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
    ) );
    wp_print_styles();
    wp_print_scripts();
    ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            /* Standalone preview: gebruik de neutrale system-font van het OS van de bezoeker.
               De live frontend erft van de WP theme — deze preview heeft geen theme context. */
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 15px;
            line-height: 1.6;
            color: #333;
            background: #fff;
            -webkit-font-smoothing: antialiased;
        }
        .tmp-preview-toolbar {
            position: sticky;
            top: 0;
            z-index: 9999;
            background: #1d2327;
            color: #c3c4c7;
            padding: 0 20px;
            height: 40px;
            display: flex;
            align-items: center;
            gap: 16px;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 13px;
        }
        .tmp-preview-toolbar strong { color: #fff; }
        .tmp-preview-toolbar a {
            color: #72aee6;
            text-decoration: none;
            font-size: 12px;
        }
        .tmp-preview-toolbar a:hover { text-decoration: underline; }
        .tmp-preview-toolbar .tmp-tb-sep { color: #555; }
        .tmp-preview-page {
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px 24px;
        }
        @media (max-width: 600px) {
            .tmp-preview-page { padding: 16px 12px; }
            .tmp-preview-toolbar { padding: 0 12px; font-size: 12px; gap: 8px; }
        }

        /* THEME-BLEED SIMULATIE
           Veel WordPress themes (Twenty-Twenty, Astra, OceanWP, Storefront, etc.)
           geven globale regels mee via `.entry-content table td/th` voor:
             - borders (typisch 1px solid #ddd)
             - padding (typisch 8–12px)
             - achtergrond- en tekstkleur (zebra of accent)
             - font-size / font-family (kleinere/serif-stack)
           We simuleren die worst-case hier (zonder de extra `.entry-content`
           class — `.tmp-preview-page table` heeft dezelfde specificity 0,0,1,1
           als wat een theme typisch toepast). Renders de plugin-tabel hier
           visueel correct, dan ook op live. */
        .tmp-preview-page table,
        .tmp-preview-page tr,
        .tmp-preview-page td,
        .tmp-preview-page th {
            border: 1px solid #ddd;
            padding: 12px 10px;
            background: #f4f4f4;
            color: #111;
            font-family: Georgia, "Times New Roman", serif;
            font-size: 13px;
            text-align: right;
        }
        .tmp-preview-page th {
            background: #e2e2e2;
            font-weight: 400;
        }
        .tmp-preview-page tr:nth-child(odd) td {
            background: #f4f4f4;
        }
        .tmp-preview-page tr:nth-child(even) td {
            background: #ececec;
        }
    </style>
</head>
<body>
    <div class="tmp-preview-toolbar">
        <span>&#128065; <strong><?php echo esc_html( $table->name ); ?></strong></span>
        <span class="tmp-tb-sep">|</span>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=tablemaster-edit&id=' . $table_id ) ); ?>">
            &larr; <?php esc_html_e( 'Terug naar editor', TMP_TEXT_DOMAIN ); ?>
        </a>
    </div>
    <div class="tmp-preview-page">
        <?php
        $settings = json_decode( $table->settings, true );
        $lang     = TableMaster_WPML::get_current_language();
        $data     = TableMaster_DB::get_table_data( $table_id, $lang );
        include TMP_PLUGIN_DIR . 'templates/table-frontend.php';
        ?>
    </div>
</body>
</html>
<?php
exit;
