<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class TableMaster_Shortcode {

    public function register() {
        add_shortcode( 'tablemaster', array( $this, 'render' ) );
    }

    public function render( $atts ) {
        $atts = shortcode_atts( array( 'id' => 0 ), $atts, 'tablemaster' );
        $id   = intval( $atts['id'] );
        if ( ! $id ) {
            return '<p class="tablemaster-error">' . esc_html__( 'Ongeldige tabel ID.', TMP_TEXT_DOMAIN ) . '</p>';
        }

        $table = TableMaster_DB::get_table( $id );
        if ( ! $table ) {
            return '<p class="tablemaster-error">' . esc_html__( 'Tabel niet gevonden.', TMP_TEXT_DOMAIN ) . '</p>';
        }

        TableMaster::enqueue_frontend_assets();

        $settings = json_decode( $table->settings, true );
        $lang     = defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : '';
        $data     = TableMaster_DB::get_table_data( $id, $lang );

        ob_start();
        include TMP_PLUGIN_DIR . 'templates/table-frontend.php';
        return ob_get_clean();
    }
}
