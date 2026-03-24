<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( esc_html__( 'Geen toegang.', TMP_TEXT_DOMAIN ) );

$table_id = intval( $_GET['id'] ?? 0 );
$table    = $table_id ? TableMaster_DB::get_table( $table_id ) : null;

if ( ! $table ) {
    wp_die( esc_html__( 'Tabel niet gevonden.', TMP_TEXT_DOMAIN ) );
}

TableMaster::enqueue_frontend_assets();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html( $table->name ); ?> — Preview</title>
    <?php wp_head(); ?>
    <style>
        body { margin: 0; padding: 24px; font-family: inherit; background: #f0f0f1; }
        .tmp-preview-bar {
            background: #1d2327;
            color: #fff;
            padding: 10px 20px;
            margin: -24px -24px 24px -24px;
            display: flex;
            align-items: center;
            gap: 16px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 13px;
        }
        .tmp-preview-bar a { color: #72aee6; text-decoration: none; }
        .tmp-preview-bar a:hover { text-decoration: underline; }
        .tmp-preview-container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            padding: 32px;
            border-radius: 4px;
            box-shadow: 0 1px 4px rgba(0,0,0,.15);
        }
    </style>
</head>
<body>
    <div class="tmp-preview-bar">
        <span>&#128065; TableMaster Preview: <strong><?php echo esc_html( $table->name ); ?></strong></span>
        <span>&mdash;</span>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=tablemaster-edit&id=' . $table_id ) ); ?>">
            &larr; Terug naar editor
        </a>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=tablemaster' ) ); ?>">
            Alle tabellen
        </a>
    </div>
    <div class="tmp-preview-container">
        <?php
        $settings = json_decode( $table->settings, true );
        $lang     = defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : '';
        $data     = TableMaster_DB::get_table_data( $table_id, $lang );
        include TMP_PLUGIN_DIR . 'templates/table-frontend.php';
        ?>
    </div>
    <?php wp_footer(); ?>
</body>
</html>
<?php
exit;
