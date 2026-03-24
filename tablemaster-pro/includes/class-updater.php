<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class TableMaster_Updater {

    private $plugin_slug;
    private $plugin_file;
    private $update_url;
    private $cache_key;
    private $cache_ttl = 43200;

    public function __construct() {
        $this->plugin_slug = 'tablemaster-pro';
        $this->plugin_file = 'tablemaster-pro/tablemaster-pro.php';
        $this->update_url  = defined( 'TMP_UPDATE_URL' ) ? TMP_UPDATE_URL : '';
        $this->cache_key   = 'tmp_update_check';
    }

    public function init() {
        if ( empty( $this->update_url ) ) {
            return;
        }
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
        add_filter( 'plugins_api', array( $this, 'plugin_info' ), 20, 3 );
        add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
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
            $obj->package     = $remote->download_url ?? '';
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
        $info->download_link  = $remote->download_url ?? '';
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

        $install_dir = plugin_dir_path( TMP_PLUGIN_FILE );
        $wp_filesystem->move( $result['destination'], $install_dir );
        $result['destination'] = $install_dir;

        if ( is_plugin_active( $this->plugin_file ) ) {
            activate_plugin( $this->plugin_file );
        }

        return $result;
    }

    private function get_remote_info() {
        $cached = get_transient( $this->cache_key );
        if ( $cached !== false ) {
            return $cached;
        }

        $url = trailingslashit( $this->update_url ) . 'api/wp-update/info';

        $response = wp_remote_get( $url, array(
            'timeout' => 15,
            'headers' => array(
                'Accept' => 'application/json',
            ),
        ) );

        if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
            return null;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body );

        if ( empty( $data ) || ! isset( $data->version ) ) {
            return null;
        }

        set_transient( $this->cache_key, $data, $this->cache_ttl );

        return $data;
    }
}
