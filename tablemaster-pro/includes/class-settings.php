<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class TableMaster_Settings {

    public static function get( $key = null ) {
        $defaults = array(
            'default_theme'   => 'green',
            'default_per_page'=> 10,
            'enable_export'   => false,
        );
        $options = get_option( 'tablemaster_settings', array() );
        $merged  = wp_parse_args( $options, $defaults );
        if ( $key ) {
            return $merged[$key] ?? null;
        }
        return $merged;
    }

    public static function save( $data ) {
        $clean = array(
            'default_theme'    => sanitize_text_field( $data['default_theme']    ?? 'green' ),
            'default_per_page' => intval( $data['default_per_page']               ?? 10 ),
            'enable_export'    => ! empty( $data['enable_export'] ),
        );
        update_option( 'tablemaster_settings', $clean );
    }

    public static function get_color_presets() {
        return array(
            'green' => array(
                'header_bg'    => '#2e7d32', 'header_text'  => '#ffffff',
                'group1_bg'    => '#4caf50', 'group1_text'  => '#ffffff',
                'group2_bg'    => '#81c784', 'group2_text'  => '#1a1a1a',
                'group3_bg'    => '#c8e6c9', 'group3_text'  => '#1a1a1a',
                'odd_bg'       => '#ffffff', 'even_bg'      => '#f1f8e9',
                'hover_bg'     => '#dcedc8', 'border_color' => '#a5d6a7',
                'accent_color' => '#2e7d32',
            ),
            'red' => array(
                'header_bg'    => '#c62828', 'header_text'  => '#ffffff',
                'group1_bg'    => '#e53935', 'group1_text'  => '#ffffff',
                'group2_bg'    => '#ef9a9a', 'group2_text'  => '#1a1a1a',
                'group3_bg'    => '#ffcdd2', 'group3_text'  => '#1a1a1a',
                'odd_bg'       => '#ffffff', 'even_bg'      => '#fce4ec',
                'hover_bg'     => '#f8bbd0', 'border_color' => '#ef9a9a',
                'accent_color' => '#c62828',
            ),
            'blue' => array(
                'header_bg'    => '#1565c0', 'header_text'  => '#ffffff',
                'group1_bg'    => '#1976d2', 'group1_text'  => '#ffffff',
                'group2_bg'    => '#90caf9', 'group2_text'  => '#1a1a1a',
                'group3_bg'    => '#e3f2fd', 'group3_text'  => '#1a1a1a',
                'odd_bg'       => '#ffffff', 'even_bg'      => '#e8f4fd',
                'hover_bg'     => '#bbdefb', 'border_color' => '#90caf9',
                'accent_color' => '#1565c0',
            ),
            'grey' => array(
                'header_bg'    => '#424242', 'header_text'  => '#ffffff',
                'group1_bg'    => '#616161', 'group1_text'  => '#ffffff',
                'group2_bg'    => '#bdbdbd', 'group2_text'  => '#1a1a1a',
                'group3_bg'    => '#eeeeee', 'group3_text'  => '#1a1a1a',
                'odd_bg'       => '#ffffff', 'even_bg'      => '#f5f5f5',
                'hover_bg'     => '#e0e0e0', 'border_color' => '#bdbdbd',
                'accent_color' => '#424242',
            ),
        );
    }
}
