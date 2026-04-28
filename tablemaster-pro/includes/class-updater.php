<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class TableMaster_Updater {

    private $plugin_slug;
    private $plugin_file;
    private $update_url;
    private $license_key;
    private $cache_key;
    private $cache_ttl = 3600;
    private $error_cache_key;
    private $error_cache_ttl = 300;
    private $force_check_throttle = 300;
    private $last_check_option = 'tmp_last_successful_check';
    private $force_check_meta  = 'tmp_last_force_check';

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

        add_action( 'admin_post_tmp_force_update_check', array( $this, 'handle_force_check' ) );
        add_action( 'wp_ajax_tmp_test_connection',       array( $this, 'ajax_test_connection' ) );
        add_action( 'admin_notices',                     array( $this, 'maybe_force_check_notice' ) );

        if ( empty( $this->update_url ) || empty( $this->license_key ) ) {
            return;
        }

        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
        add_filter( 'plugins_api', array( $this, 'plugin_info' ), 20, 3 );
        add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
        add_action( 'admin_notices', array( $this, 'connection_error_notice' ) );

        add_action( 'load-plugins.php',     array( $this, 'maybe_auto_bust_cache' ) );
        add_action( 'load-update-core.php', array( $this, 'maybe_auto_bust_cache' ) );
    }

    public function maybe_auto_bust_cache() {
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            return;
        }
        $last = (int) get_user_meta( $user_id, $this->force_check_meta, true );
        if ( $last && ( time() - $last ) < $this->force_check_throttle ) {
            return;
        }
        update_user_meta( $user_id, $this->force_check_meta, time() );
        $this->bust_caches();
    }

    private function bust_caches() {
        delete_transient( $this->cache_key );
        delete_transient( $this->error_cache_key );
        delete_site_transient( 'update_plugins' );
    }

    public function handle_force_check() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Geen toegang.', TMP_TEXT_DOMAIN ) );
        }
        check_admin_referer( 'tmp_force_update_check' );

        $this->bust_caches();

        $remote = null;
        if ( ! empty( $this->update_url ) && ! empty( $this->license_key ) ) {
            $remote = $this->get_remote_info();
        }

        if ( function_exists( 'wp_update_plugins' ) ) {
            wp_update_plugins();
        }

        $args = array( 'page' => 'tablemaster-settings' );
        if ( is_object( $remote ) && ! empty( $remote->version ) ) {
            $args['tmp_check']  = 'ok';
            $args['tmp_remote'] = rawurlencode( $remote->version );
        } else {
            $error = get_transient( $this->error_cache_key );
            if ( ! empty( $error ) ) {
                $args['tmp_check'] = 'err';
                $args['tmp_msg']   = rawurlencode( $error );
            } elseif ( empty( $this->license_key ) ) {
                $args['tmp_check'] = 'nolicense';
            } else {
                $args['tmp_check'] = 'unknown';
            }
        }

        wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
        exit;
    }

    public function maybe_force_check_notice() {
        if ( empty( $_GET['tmp_check'] ) || empty( $_GET['page'] ) || $_GET['page'] !== 'tablemaster-settings' ) {
            return;
        }
        $check = sanitize_key( wp_unslash( $_GET['tmp_check'] ) );
        if ( $check === 'ok' ) {
            $remote = isset( $_GET['tmp_remote'] ) ? sanitize_text_field( wp_unslash( $_GET['tmp_remote'] ) ) : '';
            printf(
                '<div class="notice notice-success is-dismissible"><p><strong>TableMaster Pro:</strong> %s</p></div>',
                esc_html( sprintf( __( 'Verse update-controle uitgevoerd. Nieuwste beschikbare versie: %1$s. Geïnstalleerd: %2$s.', TMP_TEXT_DOMAIN ), $remote, TMP_VERSION ) )
            );
        } elseif ( $check === 'err' ) {
            $msg = isset( $_GET['tmp_msg'] ) ? sanitize_text_field( wp_unslash( $_GET['tmp_msg'] ) ) : '';
            printf(
                '<div class="notice notice-error is-dismissible"><p><strong>TableMaster Pro:</strong> %s</p></div>',
                esc_html( sprintf( __( 'Update-controle mislukt: %s', TMP_TEXT_DOMAIN ), $msg ) )
            );
        } elseif ( $check === 'nolicense' ) {
            printf(
                '<div class="notice notice-warning is-dismissible"><p><strong>TableMaster Pro:</strong> %s</p></div>',
                esc_html__( 'Caches gewist, maar er is geen licentiecode ingesteld. Vul een licentiecode in om updates te ontvangen.', TMP_TEXT_DOMAIN )
            );
        } else {
            printf(
                '<div class="notice notice-info is-dismissible"><p><strong>TableMaster Pro:</strong> %s</p></div>',
                esc_html__( 'Caches gewist. Geen update-info ontvangen.', TMP_TEXT_DOMAIN )
            );
        }
    }

    public function ajax_test_connection() {
        check_ajax_referer( 'tmp_test_connection', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Geen toegang.', TMP_TEXT_DOMAIN ) ), 403 );
        }

        $update_url  = TableMaster_Settings::get_update_url();
        $settings    = TableMaster_Settings::get();
        $license_key = $settings['license_key'] ?? '';

        if ( empty( $update_url ) || empty( $license_key ) ) {
            wp_send_json_error( array(
                'message' => __( 'Update-URL of licentiecode ontbreekt.', TMP_TEXT_DOMAIN ),
            ) );
        }

        $url = trailingslashit( $update_url ) . 'api/wp-update/info';
        $start = microtime( true );
        $response = wp_remote_get( $url, array(
            'timeout'   => 15,
            'sslverify' => true,
            'headers'   => array(
                'Accept'        => 'application/json',
                'Connection'    => 'close',
                'X-License-Key' => $license_key,
            ),
        ) );
        $elapsed_ms = (int) round( ( microtime( true ) - $start ) * 1000 );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array(
                'message'     => $response->get_error_message(),
                'elapsed_ms'  => $elapsed_ms,
                'url'         => $url,
            ) );
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body        = wp_remote_retrieve_body( $response );
        $decoded     = json_decode( $body );
        $remote_ver  = ( is_object( $decoded ) && ! empty( $decoded->version ) ) ? $decoded->version : null;

        wp_send_json_success( array(
            'http_status'    => $status_code,
            'remote_version' => $remote_ver,
            'installed'      => TMP_VERSION,
            'url'            => $url,
            'elapsed_ms'     => $elapsed_ms,
        ) );
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
        if ( ! $remote || empty( $remote->content_hash ) || empty( $remote->signature ) ) {
            return false;
        }

        $expected_hash = sanitize_text_field( $remote->content_hash );
        if ( ! preg_match( '/^[a-f0-9]{64}$/', $expected_hash ) ) {
            return false;
        }

        $signature_hex = sanitize_text_field( $remote->signature );
        if ( ! preg_match( '/^[a-f0-9]{128}$/', $signature_hex ) ) {
            return false;
        }

        if ( ! $this->verify_signature( $expected_hash, $signature_hex ) ) {
            return false;
        }

        $actual_hash = $this->compute_content_hash( $package_dir );
        if ( $actual_hash === false ) {
            return false;
        }

        return hash_equals( $expected_hash, $actual_hash );
    }

    private function compute_content_hash( $dir ) {
        $files = $this->get_all_files( $dir );
        if ( empty( $files ) ) {
            return false;
        }
        sort( $files );
        $ctx = hash_init( 'sha256' );
        foreach ( $files as $rel_path ) {
            hash_update( $ctx, $rel_path );
            $content = file_get_contents( $dir . '/' . $rel_path );
            if ( $content === false ) {
                return false;
            }
            hash_update( $ctx, $content );
        }
        return hash_final( $ctx );
    }

    private function get_all_files( $dir, $base = '' ) {
        $results = array();
        $entries = scandir( $dir );
        if ( $entries === false ) {
            return $results;
        }
        foreach ( $entries as $entry ) {
            if ( $entry === '.' || $entry === '..' ) continue;
            $full = $dir . '/' . $entry;
            $rel  = $base === '' ? $entry : $base . '/' . $entry;
            if ( is_dir( $full ) ) {
                $results = array_merge( $results, $this->get_all_files( $full, $rel ) );
            } else {
                $results[] = $rel;
            }
        }
        return $results;
    }

    private function verify_signature( $hash_hex, $signature_hex ) {
        if ( ! function_exists( 'sodium_crypto_sign_verify_detached' ) ) {
            return false;
        }

        $public_key = hex2bin( TMP_SIGNING_PUBLIC_KEY );
        if ( strlen( $public_key ) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES ) {
            return false;
        }

        $signature = hex2bin( $signature_hex );
        if ( strlen( $signature ) !== SODIUM_CRYPTO_SIGN_BYTES ) {
            return false;
        }

        try {
            return sodium_crypto_sign_verify_detached( $signature, $hash_hex, $public_key );
        } catch ( \Exception $e ) {
            return false;
        }
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
            if ( is_object( $cached ) && ! empty( $cached->version )
                 && version_compare( $cached->version, TMP_VERSION, '<' ) ) {
                delete_transient( $this->cache_key );
            } else {
                return $cached;
            }
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
        update_option( $this->last_check_option, time() );
        return $decoded;
    }

    private function set_error_transient( $message ) {
        set_transient(
            $this->error_cache_key,
            sprintf( 'Kan update server niet bereiken (%s). Volgende poging over 5 minuten.', $message ),
            $this->error_cache_ttl
        );
    }
}
