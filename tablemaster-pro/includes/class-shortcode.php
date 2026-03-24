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

        if ( function_exists( 'icl_t' ) ) {
            $settings = self::translate_settings( $settings, $id );
            $data     = self::translate_data( $data, $id );
        }

        ob_start();
        include TMP_PLUGIN_DIR . 'templates/table-frontend.php';
        return ob_get_clean();
    }

    private static function translate_settings( $settings, $table_id ) {
        if ( ! empty( $settings['caption'] ) ) {
            $settings['caption'] = icl_t(
                TMP_TEXT_DOMAIN,
                'table_' . $table_id . '_caption',
                $settings['caption']
            );
        }
        return $settings;
    }

    private static function translate_data( $data, $table_id ) {
        foreach ( $data['columns'] as &$col ) {
            $col->label = icl_t(
                TMP_TEXT_DOMAIN,
                'table_' . $table_id . '_col_' . $col->id . '_label',
                $col->label
            );
        }
        unset( $col );

        foreach ( $data['rows'] as &$row ) {
            foreach ( $row->cells as $col_id => &$content ) {
                if ( trim( $content ) === '' ) continue;
                $content = icl_t(
                    TMP_TEXT_DOMAIN,
                    'table_' . $table_id . '_row_' . $row->id . '_col_' . $col_id,
                    $content
                );
            }
            unset( $content );
        }
        unset( $row );

        return $data;
    }
}
