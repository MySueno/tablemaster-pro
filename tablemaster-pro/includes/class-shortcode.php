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
        $lang     = TableMaster_WPML::get_current_language();
        $default_lang = TableMaster_WPML::get_default_language();

        $use_translation = false;
        if ( TableMaster_WPML::is_active() && $lang && $lang !== $default_lang ) {
            $use_translation = true;
        }

        $data = TableMaster_DB::get_table_data( $id, $use_translation ? $lang : '' );

        if ( $use_translation ) {
            $context  = TableMaster_WPML::get_context( $id );
            $settings = self::translate_settings( $settings, $context );
            $data     = self::translate_data( $data, $context );
        }

        ob_start();
        include TMP_PLUGIN_DIR . 'templates/table-frontend.php';
        return ob_get_clean();
    }

    private static function translate_settings( $settings, $context ) {
        if ( ! empty( $settings['caption'] ) ) {
            $settings['caption'] = TableMaster_WPML::translate_string(
                $context,
                'caption',
                $settings['caption']
            );
        }
        return $settings;
    }

    private static function translate_data( $data, $context ) {
        foreach ( $data['columns'] as &$col ) {
            $col->label = TableMaster_WPML::translate_string(
                $context,
                'col_' . $col->id . '_label',
                $col->label
            );
        }
        unset( $col );

        foreach ( $data['rows'] as &$row ) {
            foreach ( $row->cells as $col_id => &$content ) {
                if ( trim( $content ) === '' ) continue;
                $content = TableMaster_WPML::translate_string(
                    $context,
                    'row_' . $row->id . '_col_' . $col_id,
                    $content
                );
            }
            unset( $content );
        }
        unset( $row );

        return $data;
    }
}
