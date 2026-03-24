<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class TableMaster_WPML {

    public function register() {
        add_action( 'tablemaster_after_save_table',     array( $this, 'register_strings' ) );
        add_action( 'tablemaster_after_save_structure', array( $this, 'register_strings' ) );
    }

    public static function is_active() {
        return function_exists( 'icl_register_string' );
    }

    public static function get_translate_url( $table_id ) {
        $context = self::get_context( $table_id );
        return admin_url( 'admin.php?page=wpml-string-translation%2Fmenu%2Fstring-translation.php&context=' . urlencode( $context ) );
    }

    private static function get_context( $table_id ) {
        return TMP_TEXT_DOMAIN . ' - Table ' . $table_id;
    }

    public function register_strings( $table_id ) {
        if ( ! self::is_active() ) {
            return;
        }

        $table = TableMaster_DB::get_table( $table_id );
        if ( ! $table ) return;

        $context  = self::get_context( $table_id );
        $settings = json_decode( $table->settings, true );

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
}
