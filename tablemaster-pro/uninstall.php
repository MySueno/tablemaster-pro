<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tablemaster_cells" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tablemaster_rows" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tablemaster_columns" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tablemaster_tables" );

delete_option( 'tablemaster_db_version' );
delete_option( 'tablemaster_settings' );
