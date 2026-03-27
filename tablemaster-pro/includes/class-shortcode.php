<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class TableMaster_Shortcode {

    public function register() {
        add_shortcode( 'tablemaster', array( $this, 'render' ) );
    }

    public function render( $atts ) {
        $atts = shortcode_atts( array( 'id' => 0 ), $atts, 'tablemaster' );
        $id   = intval( $atts['id'] );

        if ( ! $id && TableMaster_WPML::is_active() ) {
            $id = self::resolve_table_id_from_original_post();
        }

        if ( ! $id ) {
            return '';
        }

        $table = TableMaster_DB::get_table( $id );

        if ( ! $table && TableMaster_WPML::is_active() ) {
            $resolved_id = self::resolve_table_id_from_original_post();
            if ( $resolved_id && $resolved_id !== $id ) {
                $id    = $resolved_id;
                $table = TableMaster_DB::get_table( $id );
            }
        }

        if ( ! $table ) {
            return '';
        }

        TableMaster::enqueue_frontend_assets();

        $settings = json_decode( $table->settings, true );
        $lang     = TableMaster_WPML::get_current_language();
        $default_lang = TableMaster_WPML::get_default_language();

        $use_translation = false;
        if ( TableMaster_WPML::is_active() && $lang && $lang !== $default_lang ) {
            $progress = TableMaster_WPML::get_translation_progress( $id, $lang );
            if ( $progress['percent'] >= 100 ) {
                $use_translation = true;
            }
        }

        $data = TableMaster_DB::get_table_data( $id, $use_translation ? $lang : '' );

        if ( $use_translation ) {
            $context  = TableMaster_WPML::get_context( $id );
            $settings = self::translate_settings( $settings, $context, $lang );
            $data     = self::translate_data( $data, $context, $lang );
        }

        ob_start();
        include TMP_PLUGIN_DIR . 'templates/table-frontend.php';
        return ob_get_clean();
    }

    private static function resolve_table_id_from_original_post() {
        $post_id = get_the_ID();
        if ( ! $post_id ) {
            return 0;
        }

        $default_lang = TableMaster_WPML::get_default_language();
        if ( ! $default_lang ) {
            return 0;
        }

        $original_id = apply_filters( 'wpml_object_id', $post_id, get_post_type( $post_id ), true, $default_lang );
        if ( ! $original_id || $original_id === $post_id ) {
            return 0;
        }

        $original_post = get_post( $original_id );
        if ( ! $original_post ) {
            return 0;
        }

        $content = $original_post->post_content;

        if ( preg_match( '/\[tablemaster\s+id=["\']?(\d+)["\']?\s*\]/', $content, $m ) ) {
            return intval( $m[1] );
        }

        $elementor_data = get_post_meta( $original_id, '_elementor_data', true );
        if ( ! empty( $elementor_data ) && preg_match( '/"table_id"\s*:\s*"?(\d+)"?/', $elementor_data, $m ) ) {
            return intval( $m[1] );
        }

        return 0;
    }

    private static function translate_settings( $settings, $context, $lang ) {
        if ( ! empty( $settings['caption'] ) ) {
            $settings['caption'] = TableMaster_WPML::translate_string(
                $context,
                'caption',
                $settings['caption'],
                $lang
            );
        }
        return $settings;
    }

    private static function translate_data( $data, $context, $lang ) {
        foreach ( $data['columns'] as &$col ) {
            $col->label = TableMaster_WPML::translate_string(
                $context,
                'col_' . $col->id . '_label',
                $col->label,
                $lang
            );

            $cs = json_decode( $col->settings, true );
            $changed = false;
            $g1 = trim( $cs['header_group1'] ?? '' );
            $g2 = trim( $cs['header_group2'] ?? '' );
            if ( $g1 !== '' ) {
                $cs['header_group1'] = TableMaster_WPML::translate_string(
                    $context, 'header_group1_' . md5( $g1 ), $g1, $lang
                );
                $changed = true;
            }
            if ( $g2 !== '' ) {
                $cs['header_group2'] = TableMaster_WPML::translate_string(
                    $context, 'header_group2_' . md5( $g2 ), $g2, $lang
                );
                $changed = true;
            }
            if ( $changed ) {
                $col->settings = wp_json_encode( $cs );
            }
        }
        unset( $col );

        foreach ( $data['rows'] as &$row ) {
            foreach ( $row->cells as $col_id => &$content ) {
                if ( trim( $content ) === '' ) continue;
                $content = TableMaster_WPML::translate_string(
                    $context,
                    'row_' . $row->id . '_col_' . $col_id,
                    $content,
                    $lang
                );
            }
            unset( $content );
        }
        unset( $row );

        return $data;
    }
}
