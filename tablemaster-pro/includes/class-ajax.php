<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class TableMaster_Ajax {

    public function register() {
        $actions = array(
            'tablemaster_save_table'      => 'save_table',
            'tablemaster_delete_table'    => 'delete_table',
            'tablemaster_duplicate_table' => 'duplicate_table',
            'tablemaster_save_structure'  => 'save_structure',
            'tablemaster_get_structure'   => 'get_structure',
        );

        foreach ( $actions as $hook => $method ) {
            add_action( 'wp_ajax_' . $hook, array( $this, $method ) );
        }
    }

    private function verify_nonce() {
        if ( ! check_ajax_referer( 'tablemaster_admin', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Beveiligingscontrole mislukt.', TMP_TEXT_DOMAIN ) ), 403 );
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Onvoldoende rechten.', TMP_TEXT_DOMAIN ) ), 403 );
        }
    }

    public function save_table() {
        $this->verify_nonce();

        $id       = ! empty( $_POST['id'] )   ? intval( $_POST['id'] )   : 0;
        $name     = ! empty( $_POST['name'] )  ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
        $settings = ! empty( $_POST['settings'] ) ? json_decode( wp_unslash( $_POST['settings'] ), true ) : array();

        if ( ! $name ) {
            wp_send_json_error( array( 'message' => __( 'Naam is verplicht.', TMP_TEXT_DOMAIN ) ) );
        }

        $new_id = TableMaster_DB::save_table( array(
            'id'       => $id,
            'name'     => $name,
            'settings' => $settings,
        ) );

        do_action( 'tablemaster_after_save_table', $new_id );
        wp_send_json_success( array( 'id' => $new_id ) );
    }

    public function delete_table() {
        $this->verify_nonce();
        $id = intval( $_POST['id'] ?? 0 );
        TableMaster_DB::delete_table( $id );
        wp_send_json_success();
    }

    public function duplicate_table() {
        $this->verify_nonce();
        $id     = intval( $_POST['id'] ?? 0 );
        $new_id = TableMaster_DB::duplicate_table( $id );
        wp_send_json_success( array( 'id' => $new_id ) );
    }

    public function save_structure() {
        $this->verify_nonce();

        $table_id    = intval( $_POST['table_id'] ?? 0 );
        $columns     = json_decode( wp_unslash( $_POST['columns'] ?? '[]' ), true );
        $rows        = json_decode( wp_unslash( $_POST['rows']    ?? '[]' ), true );
        $lang        = sanitize_text_field( wp_unslash( $_POST['lang'] ?? '' ) );

        if ( ! $table_id ) {
            wp_send_json_error( array( 'message' => 'Geen tabel ID.' ) );
        }

        TableMaster_DB::save_table_structure( $table_id, $columns, $rows, $lang );
        do_action( 'tablemaster_after_save_structure', $table_id );
        wp_send_json_success();
    }

    public function get_structure() {
        $this->verify_nonce();
        $table_id = intval( $_POST['table_id'] ?? 0 );
        $lang     = sanitize_text_field( wp_unslash( $_POST['lang'] ?? '' ) );
        $data     = TableMaster_DB::get_table_data( $table_id, $lang );
        wp_send_json_success( $data );
    }
}
