<?php
/**
 * Plugin Name: TableMaster Pro
 * Plugin URI:  https://example.com/tablemaster-pro
 * Description: Maak krachtige, interactieve tabellen met groepering, sortering, filtering en paginering. Beheer via een intuïtief dashboard en publiceer via shortcode of Gutenberg block.
 * Version:     1.3.2
 * Author:      TableMaster Pro
 * Author URI:  https://example.com
 * License:     GPL-2.0-or-later
 * Text Domain: tablemaster-pro
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'TMP_VERSION',     '1.3.2' );
define( 'TMP_PLUGIN_FILE', __FILE__ );
define( 'TMP_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'TMP_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'TMP_TEXT_DOMAIN', 'tablemaster-pro' );

$tmp_settings = get_option( 'tablemaster_settings', array() );
if ( ! empty( $tmp_settings['update_url'] ) ) {
    define( 'TMP_UPDATE_URL', $tmp_settings['update_url'] );
}

require_once TMP_PLUGIN_DIR . 'includes/class-db.php';
require_once TMP_PLUGIN_DIR . 'includes/class-shortcode.php';
require_once TMP_PLUGIN_DIR . 'includes/class-ajax.php';
require_once TMP_PLUGIN_DIR . 'includes/class-wpml.php';
require_once TMP_PLUGIN_DIR . 'includes/class-settings.php';
require_once TMP_PLUGIN_DIR . 'includes/class-tablemaster.php';
require_once TMP_PLUGIN_DIR . 'admin/class-admin.php';
require_once TMP_PLUGIN_DIR . 'includes/class-block.php';
require_once TMP_PLUGIN_DIR . 'includes/class-elementor.php';
require_once TMP_PLUGIN_DIR . 'includes/class-updater.php';

register_activation_hook( __FILE__, array( 'TableMaster_DB', 'install' ) );

function tablemaster_pro_init() {
    $plugin = new TableMaster();
    $plugin->run();

    $updater = new TableMaster_Updater();
    $updater->init();
}
add_action( 'plugins_loaded', 'tablemaster_pro_init' );
