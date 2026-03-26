<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class TableMaster_Updater {

    private $plugin_slug;
    private $plugin_file;
    private $update_url;
    private $license_key;
    private $cache_key;
    private $cache_ttl = 43200;
    private $error_cache_key;
    private $error_cache_ttl = 1800;

    public function __construct() {
        $this->plugin_slug   = 'tablemaster-pro';
        $this->plugin_file   = 'tablemaster-pro/tablemaster-pro.php';
        $this->update_url    = '';
        $this->license_key   = '';
        $this->cache_key     = 'tmp_update_check';
        $this->error_cache_key = 'tmp_update_error';
    }

    public function init() {
        $settings = TableMaster_Settings::get();
        $this->update_url  = TableMaster_Settings::get_update_url();
        $this->license_key = $settings['license_key'] ?? '';

        if ( empty( $this->update_url ) || empty( $this->license_key ) ) {
            return;
        }
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
        add_filter( 'plugins_api', array( $this, 'plugin_info' ), 20, 3 );
        add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
        add_action( 'admin_notices', array( $this, 'connection_error_notice' ) );
    }

    private function get_download_url() {
        return trailingslashit( $this->update_url ) . 'api/wp-update/download?license_key=' . urlencode( $this->license_key );
    }

    public function check_update( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $remote = $this->get_remote_info();
        if ( ! $remote || empty( $remote->version ) ) {
            return $transient;
        }

        $current_version = $transient->checked[ $this->plugin_file ] ?? TMP_VERSION;

        if ( version_compare( $remote->version, $current_version, '>' ) ) {
            $obj              = new stdClass();
            $obj->slug        = $this->plugin_slug;
            $obj->plugin      = $this->plugin_file;
            $obj->new_version = $remote->version;
            $obj->url         = $remote->author_profile ?? '';
            $obj->package     = $this->get_download_url();
            $obj->tested      = $remote->tested ?? '';
            $obj->requires    = $remote->requires ?? '';
            $obj->requires_php = $remote->requires_php ?? '';

            $transient->response[ $this->plugin_file ] = $obj;
        } else {
            $obj              = new stdClass();
            $obj->slug        = $this->plugin_slug;
            $obj->plugin      = $this->plugin_file;
            $obj->new_version = $remote->version;
            $obj->url         = '';
            $obj->package     = '';

            $transient->no_update[ $this->plugin_file ] = $obj;
        }

        return $transient;
    }

    public function plugin_info( $result, $action, $args ) {
        if ( $action !== 'plugin_information' ) {
            return $result;
        }

        if ( ! isset( $args->slug ) || $args->slug !== $this->plugin_slug ) {
            return $result;
        }

        $remote = $this->get_remote_info();
        if ( ! $remote ) {
            return $result;
        }

        $info                 = new stdClass();
        $info->name           = $remote->name ?? 'TableMaster Pro';
        $info->slug           = $this->plugin_slug;
        $info->version        = $remote->version ?? TMP_VERSION;
        $info->author         = $remote->author ?? 'TableMaster Pro';
        $info->author_profile = $remote->author_profile ?? '';
        $info->requires       = $remote->requires ?? '5.8';
        $info->tested         = $remote->tested ?? '';
        $info->requires_php   = $remote->requires_php ?? '7.4';
        $info->download_link  = $this->get_download_url();
        $info->last_updated   = $remote->last_updated ?? '';

        $info->sections = array();
        if ( isset( $remote->sections ) ) {
            foreach ( $remote->sections as $key => $val ) {
                $info->sections[ $key ] = $val;
            }
        }

        if ( isset( $remote->banners ) ) {
            $info->banners = (array) $remote->banners;
        }

        return $info;
    }

    public function after_install( $response, $hook_extra, $result ) {
        global $wp_filesystem;

        if ( ! isset( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->plugin_file ) {
            return $response;
        }

        $zip_path = $result['destination'] . '/' . $this->plugin_slug . '.zip';
        if ( ! $this->verify_package( $result['destination'] ) ) {
            $wp_filesystem->delete( $result['destination'], true );
            return new WP_Error(
                'tmp_signature_failed',
                __( 'Update-pakket kon niet worden geverifieerd. Installatie geannuleerd.', TMP_TEXT_DOMAIN )
            );
        }

        $install_dir = plugin_dir_path( TMP_PLUGIN_FILE );
        $wp_filesystem->move( $result['destination'], $install_dir );
        $result['destination'] = $install_dir;

        if ( is_plugin_active( $this->plugin_file ) ) {
            activate_plugin( $this->plugin_file );
        }

        return $result;
    }

    private function verify_package( $package_dir ) {
        $remote = $this->get_remote_info();
        if ( ! $remote || empty( $remote->sha256 ) ) {
            return false;
        }

        $expected_hash = sanitize_text_field( $remote->sha256 );
        if ( ! preg_match( '/^[a-f0-9]{64}$/', $expected_hash ) ) {
            return false;
        }

        $main_file = $package_dir . '/tablemaster-pro.php';
        if ( ! file_exists( $main_file ) ) {
            return false;
        }

        $actual_hash = hash_file( 'sha256', $main_file );

        return hash_equals( $expected_hash, $actual_hash );
    }

    public function connection_error_notice() {
        $error = get_transient( $this->error_cache_key );
        if ( empty( $error ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $screen = get_current_screen();
        if ( ! $screen || ( $screen->id !== 'plugins' && $screen->id !== 'update-core' ) ) {
            return;
        }

        printf(
            '<div class="notice notice-warning is-dismissible"><p><strong>TableMaster Pro:</strong> %s</p></div>',
            esc_html( $error )
        );
    }

    private function get_remote_info() {
        $cached = get_transient( $this->cache_key );
        if ( $cached !== false ) {
            return $cached;
        }

        $recent_error = get_transient( $this->error_cache_key );
        if ( $recent_error !== false ) {
            return null;
        }

        $url = trailingslashit( $this->update_url ) . 'api/wp-update/info';

        $response = wp_remote_get( $url, array(
            'timeout'   => 15,
            'sslverify' => true,
            'headers'   => array(
                'Accept'        => 'application/json',
                'Connection'    => 'close',
                'X-License-Key' => $this->license_key,
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            $this->set_error_transient( $response->get_error_message() );
            return null;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        if ( $status_code !== 200 ) {
            $this->set_error_transient( sprintf( 'HTTP %d van update server', $status_code ) );
            return null;
        }

        $body = wp_remote_retrieve_body( $response );
        $decoded = json_decode( $body );

        if ( empty( $decoded ) || ! isset( $decoded->version ) ) {
            $this->set_error_transient( 'Ongeldig antwoord van update server' );
            return null;
        }

        set_transient( $this->cache_key, $decoded, $this->cache_ttl );
        delete_transient( $this->error_cache_key );
        return $decoded;
    }

    private function set_error_transient( $message ) {
        set_transient(
            $this->error_cache_key,
            sprintf( 'Kan update server niet bereiken (%s). Volgende poging over 30 minuten.', $message ),
            $this->error_cache_ttl
        );
    }
}
