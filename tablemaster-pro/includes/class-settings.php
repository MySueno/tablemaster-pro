<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class TableMaster_Settings {

    public static function get( $key = null ) {
        $defaults = array(
            'default_theme'            => 'red',
            'default_per_page'         => 10,
            'enable_export'            => false,
            'border_radius'            => '15',
            'update_url'               => '',
            'delete_data_on_uninstall' => '0',
        );
        $options = get_option( 'tablemaster_settings', array() );
        $merged  = wp_parse_args( $options, $defaults );
        if ( $key ) {
            return $merged[$key] ?? null;
        }
        return $merged;
    }

    public static function save( $data ) {
        $allowed_themes = array( 'red', 'blue', 'grey', 'custom' );
        $theme = sanitize_text_field( $data['default_theme'] ?? 'red' );
        if ( ! in_array( $theme, $allowed_themes, true ) ) {
            $theme = 'red';
        }
        $clean = array(
            'default_theme'    => $theme,
            'default_per_page' => min( 500, max( 1, intval( $data['default_per_page'] ?? 10 ) ) ),
            'enable_export'    => ! empty( $data['enable_export'] ),
            'border_radius'    => min( 50, max( 0, intval( $data['border_radius'] ?? 4 ) ) ),
            'update_url'               => esc_url_raw( $data['update_url'] ?? '' ),
            'delete_data_on_uninstall' => ! empty( $data['delete_data_on_uninstall'] ) ? '1' : '0',
        );
        update_option( 'tablemaster_settings', $clean );
        delete_transient( 'tmp_update_check' );
    }

    public static function sanitize_hex_color( $color, $default = '#000000' ) {
        if ( preg_match( '/^#([A-Fa-f0-9]{3}){1,2}$/', $color ) ) {
            return $color;
        }
        return $default;
    }

    public static function sanitize_colors( $colors ) {
        $defaults = array(
            'header_bg'    => '#D32637', 'header_text'  => '#ffffff',
            'group1_bg'    => '#D32637', 'group1_text'  => '#ffffff',
            'group2_bg'    => '#F9E6E7', 'group2_text'  => '#D32637',
            'group3_bg'    => '#ffffff', 'group3_text'  => '#1a1a1a',
            'footer_bg'   => '#D32637', 'footer_text'  => '#ffffff',
            'odd_bg'       => '#F8F8F8', 'even_bg'      => '#ffffff',
            'hover_bg'     => '#fce4e4', 'border_color' => '#e8e8e8',
            'accent_color' => '#D32637',
        );
        $safe = array();
        foreach ( $defaults as $key => $fallback ) {
            $safe[ $key ] = self::sanitize_hex_color( $colors[ $key ] ?? '', $fallback );
        }
        return $safe;
    }

    public static function get_color_presets() {
        return array(
            'red' => array(
                'header_bg'    => '#D32637', 'header_text'  => '#ffffff',
                'group1_bg'    => '#D32637', 'group1_text'  => '#ffffff',
                'group2_bg'    => '#F9E6E7', 'group2_text'  => '#D32637',
                'group3_bg'    => '#ffffff', 'group3_text'  => '#1a1a1a',
                'footer_bg'   => '#D32637', 'footer_text'  => '#ffffff',
                'odd_bg'       => '#F8F8F8', 'even_bg'      => '#ffffff',
                'hover_bg'     => '#fce4e4', 'border_color' => '#e8e8e8',
                'accent_color' => '#D32637',
            ),
            'blue' => array(
                'header_bg'    => '#1565c0', 'header_text'  => '#ffffff',
                'group1_bg'    => '#1976d2', 'group1_text'  => '#ffffff',
                'group2_bg'    => '#90caf9', 'group2_text'  => '#1a1a1a',
                'group3_bg'    => '#e3f2fd', 'group3_text'  => '#1a1a1a',
                'footer_bg'   => '#1565c0', 'footer_text'  => '#ffffff',
                'odd_bg'       => '#ffffff', 'even_bg'      => '#e8f4fd',
                'hover_bg'     => '#bbdefb', 'border_color' => '#90caf9',
                'accent_color' => '#1565c0',
            ),
            'grey' => array(
                'header_bg'    => '#424242', 'header_text'  => '#ffffff',
                'group1_bg'    => '#616161', 'group1_text'  => '#ffffff',
                'group2_bg'    => '#bdbdbd', 'group2_text'  => '#1a1a1a',
                'group3_bg'    => '#eeeeee', 'group3_text'  => '#1a1a1a',
                'footer_bg'   => '#424242', 'footer_text'  => '#ffffff',
                'odd_bg'       => '#ffffff', 'even_bg'      => '#f5f5f5',
                'hover_bg'     => '#e0e0e0', 'border_color' => '#bdbdbd',
                'accent_color' => '#424242',
            ),
        );
    }
}
