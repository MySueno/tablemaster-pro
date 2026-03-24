<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class TableMaster_WPML {

    public function register() {
        add_action( 'tablemaster_after_save_table',     array( $this, 'register_strings' ) );
        add_action( 'tablemaster_after_save_structure', array( $this, 'register_strings' ) );
        add_action( 'admin_init', array( $this, 'maybe_register_all_strings' ) );
    }

    public static function is_active() {
        return defined( 'ICL_SITEPRESS_VERSION' ) && function_exists( 'icl_register_string' );
    }

    public static function is_string_translation_active() {
        return defined( 'WPML_ST_VERSION' );
    }

    public static function get_translate_url( $table_id ) {
        return admin_url( 'admin.php?page=tablemaster-translate&id=' . intval( $table_id ) );
    }

    public static function get_context( $table_id ) {
        return 'tablemaster-pro - Table ' . $table_id;
    }

    public function maybe_register_all_strings() {
        if ( ! self::is_active() ) {
            return;
        }

        $registered_version = get_option( 'tablemaster_wpml_registered', '' );
        if ( $registered_version === TMP_VERSION ) {
            return;
        }

        $tables = TableMaster_DB::get_all_tables();
        if ( ! empty( $tables ) ) {
            foreach ( $tables as $table ) {
                $this->register_strings( $table->id );
            }
        }

        update_option( 'tablemaster_wpml_registered', TMP_VERSION );
    }

    public function register_strings( $table_id ) {
        if ( ! self::is_active() ) {
            return;
        }

        $table = TableMaster_DB::get_table( $table_id );
        if ( ! $table ) return;

        $context  = self::get_context( $table_id );
        $settings = json_decode( $table->settings, true );

        icl_register_string(
            $context,
            'table_name',
            $table->name
        );

        if ( ! empty( $settings['caption'] ) ) {
            icl_register_string(
                $context,
                'caption',
                $settings['caption']
            );
        }

        $data = TableMaster_DB::get_table_data( $table_id, '' );

        foreach ( $data['columns'] as $col ) {
            icl_register_string(
                $context,
                'col_' . $col->id . '_label',
                $col->label
            );
        }

        foreach ( $data['rows'] as $row ) {
            foreach ( $row->cells as $col_id => $content ) {
                if ( trim( $content ) === '' ) continue;
                icl_register_string(
                    $context,
                    'row_' . $row->id . '_col_' . $col_id,
                    $content
                );
            }
        }
    }

    public static function translate_string( $context, $name, $value ) {
        if ( function_exists( 'icl_t' ) ) {
            return icl_t( $context, $name, $value );
        }
        return $value;
    }

    public static function get_translation_progress( $table_id, $lang ) {
        global $wpdb;

        if ( ! self::is_active() || ! self::is_string_translation_active() ) {
            return array( 'total' => 0, 'translated' => 0, 'percent' => 0 );
        }

        $context = self::get_context( $table_id );

        $total = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}icl_strings WHERE context = %s",
            $context
        ) );

        if ( $total === 0 ) {
            return array( 'total' => 0, 'translated' => 0, 'percent' => 0 );
        }

        $translated = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}icl_strings s
             INNER JOIN {$wpdb->prefix}icl_string_translations t ON t.string_id = s.id
             WHERE s.context = %s AND t.language = %s AND t.status = 10 AND t.value != ''",
            $context, $lang
        ) );

        $percent = $total > 0 ? round( ( $translated / $total ) * 100 ) : 0;

        return array( 'total' => $total, 'translated' => $translated, 'percent' => $percent );
    }

    public static function get_non_default_languages() {
        $active_langs = array();
        if ( function_exists( 'icl_get_languages' ) ) {
            $langs = icl_get_languages( 'skip_missing=0' );
            if ( is_array( $langs ) ) {
                $active_langs = $langs;
            }
        }
        $default_lang = '';
        if ( function_exists( 'apply_filters' ) ) {
            $default_lang = apply_filters( 'wpml_default_language', $default_lang );
        }
        $result = array();
        foreach ( $active_langs as $code => $l ) {
            if ( $code !== $default_lang ) {
                $result[ $code ] = $l;
            }
        }
        return $result;
    }
}
