<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class TableMaster_WPML {

    public function register() {
        add_action( 'tablemaster_after_save_table',     array( $this, 'register_strings' ) );
        add_action( 'tablemaster_after_save_structure', array( $this, 'register_strings' ) );
    }

    public function register_strings( $table_id ) {
        if ( ! function_exists( 'icl_register_string' ) ) {
            return;
        }

        $table = TableMaster_DB::get_table( $table_id );
        if ( ! $table ) return;

        $settings = json_decode( $table->settings, true );
        if ( ! empty( $settings['caption'] ) ) {
            icl_register_string(
                TMP_TEXT_DOMAIN,
                'table_' . $table_id . '_caption',
                $settings['caption']
            );
        }

        $data = TableMaster_DB::get_table_data( $table_id, '' );

        foreach ( $data['columns'] as $col ) {
            icl_register_string(
                TMP_TEXT_DOMAIN,
                'table_' . $table_id . '_col_' . $col->id . '_label',
                $col->label
            );
        }

        foreach ( $data['rows'] as $row ) {
            foreach ( $row->cells as $col_id => $content ) {
                if ( trim( $content ) === '' ) continue;
                icl_register_string(
                    TMP_TEXT_DOMAIN,
                    'table_' . $table_id . '_row_' . $row->id . '_col_' . $col_id,
                    $content
                );
            }
        }
    }
}
