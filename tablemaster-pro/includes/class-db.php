<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class TableMaster_DB {

    public static function install() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta( "CREATE TABLE {$wpdb->prefix}tablemaster_tables (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL DEFAULT '',
            slug varchar(255) NOT NULL DEFAULT '',
            settings longtext NOT NULL,
            created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            updated_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            created_by bigint(20) unsigned NOT NULL DEFAULT 0,
            PRIMARY KEY  (id),
            UNIQUE KEY slug (slug)
        ) $charset_collate;" );

        dbDelta( "CREATE TABLE {$wpdb->prefix}tablemaster_columns (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            table_id bigint(20) unsigned NOT NULL,
            label varchar(500) NOT NULL DEFAULT '',
            type varchar(50) NOT NULL DEFAULT 'text',
            order_index int(11) NOT NULL DEFAULT 0,
            settings text NOT NULL,
            PRIMARY KEY  (id),
            KEY table_id (table_id)
        ) $charset_collate;" );

        dbDelta( "CREATE TABLE {$wpdb->prefix}tablemaster_rows (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            table_id bigint(20) unsigned NOT NULL,
            parent_id bigint(20) unsigned DEFAULT NULL,
            row_type varchar(20) NOT NULL DEFAULT 'data',
            order_index int(11) NOT NULL DEFAULT 0,
            is_collapsed tinyint(1) NOT NULL DEFAULT 0,
            PRIMARY KEY  (id),
            KEY table_id (table_id),
            KEY parent_id (parent_id)
        ) $charset_collate;" );

        dbDelta( "CREATE TABLE {$wpdb->prefix}tablemaster_cells (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            row_id bigint(20) unsigned NOT NULL,
            column_id bigint(20) unsigned NOT NULL,
            content longtext NOT NULL,
            lang varchar(10) NOT NULL DEFAULT '',
            align varchar(10) NOT NULL DEFAULT '',
            colspan int(11) NOT NULL DEFAULT 1,
            PRIMARY KEY  (id),
            KEY row_id (row_id),
            KEY column_id (column_id)
        ) $charset_collate;" );

        update_option( 'tablemaster_db_version', TMP_VERSION );

        self::migrate_default_lang_cells();

        self::insert_demo_data();
    }

    public static function migrate_default_lang_cells() {
        global $wpdb;

        if ( ! class_exists( 'TableMaster_WPML' ) || ! TableMaster_WPML::is_active() ) {
            return;
        }

        $default_lang = TableMaster_WPML::get_default_language();
        if ( $default_lang === '' ) {
            return;
        }

        $migrated_for = get_option( 'tablemaster_lang_cells_migrated', '' );
        if ( $migrated_for === $default_lang ) {
            return;
        }

        $affected = $wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->prefix}tablemaster_cells SET lang = '' WHERE lang = %s",
            $default_lang
        ) );

        if ( $affected > 0 ) {
            $tables = self::get_all_tables();
            if ( ! empty( $tables ) ) {
                foreach ( $tables as $table ) {
                    self::flush_table_cache( $table->id );
                }
            }
        }

        update_option( 'tablemaster_lang_cells_migrated', $default_lang );
    }

    public static function uninstall() {
        global $wpdb;
        $settings = get_option( 'tablemaster_settings', array() );
        $delete_data = isset( $settings['delete_data_on_uninstall'] ) && $settings['delete_data_on_uninstall'] === '1';

        if ( $delete_data ) {
            $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tablemaster_cells" );
            $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tablemaster_rows" );
            $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tablemaster_columns" );
            $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tablemaster_tables" );
            delete_option( 'tablemaster_db_version' );
            delete_option( 'tablemaster_settings' );
        }
    }

    private static function insert_demo_data() {
        global $wpdb;

        $existing = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}tablemaster_tables" );
        if ( $existing > 0 ) {
            return;
        }

        $default_settings = json_encode( array(
            'caption'            => '',
            'search'             => true,
            'search_position'    => 'right',
            'pagination'         => true,
            'per_page'           => 10,
            'per_page_selector'  => true,
            'collapsible_groups' => false,
            'mobile_mode'        => 'scroll',
            'default_sort_col'   => '',
            'default_sort_dir'   => 'asc',
            'inline_html'        => false,
            'theme'              => 'red',
            'colors'             => array(
                'header_bg'    => '#D32637',
                'header_text'  => '#ffffff',
                'group1_bg'    => '#D32637',
                'group1_text'  => '#ffffff',
                'group2_bg'    => '#F9E6E7',
                'group2_text'  => '#D32637',
                'group3_bg'    => '#ffffff',
                'group3_text'  => '#1a1a1a',
                'footer_bg'    => '#D32637',
                'footer_text'  => '#ffffff',
                'odd_bg'       => '#F8F8F8',
                'even_bg'      => '#ffffff',
                'hover_bg'     => '#fce4e4',
                'border_color' => '#e8e8e8',
                'accent_color' => '#D32637',
            ),
        ) );

        $now = current_time( 'mysql' );

        $wpdb->insert(
            "{$wpdb->prefix}tablemaster_tables",
            array(
                'name'       => 'Mijn eerste tabel',
                'slug'       => 'mijn-eerste-tabel',
                'settings'   => $default_settings,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => get_current_user_id() ?: 1,
            )
        );
        $table_id = $wpdb->insert_id;

        $col_settings = json_encode( array( 'width' => 'auto', 'align' => 'left', 'sortable' => true, 'filterable' => true ) );
        $col_ids = array();
        for ( $i = 1; $i <= 4; $i++ ) {
            $wpdb->insert( "{$wpdb->prefix}tablemaster_columns", array(
                'table_id'    => $table_id,
                'label'       => 'Kolom ' . $i,
                'type'        => 'text',
                'order_index' => $i - 1,
                'settings'    => $col_settings,
            ) );
            $col_ids[] = $wpdb->insert_id;
        }

        for ( $r = 0; $r < 3; $r++ ) {
            $wpdb->insert( "{$wpdb->prefix}tablemaster_rows", array(
                'table_id'    => $table_id,
                'parent_id'   => null,
                'row_type'    => 'data',
                'order_index' => $r,
                'is_collapsed'=> 0,
            ) );
        }

        $wpdb->insert( "{$wpdb->prefix}tablemaster_rows", array(
            'table_id'    => $table_id,
            'parent_id'   => null,
            'row_type'    => 'footer',
            'order_index' => 3,
            'is_collapsed'=> 0,
        ) );
    }

    public static function get_all_tables() {
        global $wpdb;
        return $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}tablemaster_tables ORDER BY created_at DESC" );
    }

    public static function get_table( $id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}tablemaster_tables WHERE id = %d",
            intval( $id )
        ) );
    }

    public static function save_table( $data ) {
        global $wpdb;
        $now = current_time( 'mysql' );

        if ( ! empty( $data['id'] ) ) {
            self::flush_table_cache( intval( $data['id'] ) );
            $wpdb->update(
                "{$wpdb->prefix}tablemaster_tables",
                array(
                    'name'       => sanitize_text_field( $data['name'] ),
                    'settings'   => wp_json_encode( $data['settings'] ),
                    'updated_at' => $now,
                ),
                array( 'id' => intval( $data['id'] ) )
            );
            return intval( $data['id'] );
        } else {
            $slug = sanitize_title( $data['name'] ) . '-' . time();
            $wpdb->insert(
                "{$wpdb->prefix}tablemaster_tables",
                array(
                    'name'       => sanitize_text_field( $data['name'] ),
                    'slug'       => $slug,
                    'settings'   => wp_json_encode( $data['settings'] ),
                    'created_at' => $now,
                    'updated_at' => $now,
                    'created_by' => get_current_user_id(),
                )
            );
            return $wpdb->insert_id;
        }
    }

    public static function delete_table( $id ) {
        global $wpdb;
        $id = intval( $id );
        self::flush_table_cache( $id );
        $row_ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}tablemaster_rows WHERE table_id = %d", $id
        ) );
        if ( $row_ids ) {
            $placeholders = implode( ',', array_fill( 0, count( $row_ids ), '%d' ) );
            $wpdb->query( $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}tablemaster_cells WHERE row_id IN ($placeholders)",
                array_map( 'intval', $row_ids )
            ) );
        }
        $wpdb->delete( "{$wpdb->prefix}tablemaster_rows",   array( 'table_id' => $id ) );
        $wpdb->delete( "{$wpdb->prefix}tablemaster_columns", array( 'table_id' => $id ) );
        $wpdb->delete( "{$wpdb->prefix}tablemaster_tables",  array( 'id'       => $id ) );
    }

    public static function duplicate_table( $id ) {
        global $wpdb;
        $table = self::get_table( $id );
        if ( ! $table ) return false;

        $new_slug = $table->slug . '-copy-' . time();
        $now = current_time( 'mysql' );
        $wpdb->insert(
            "{$wpdb->prefix}tablemaster_tables",
            array(
                'name'       => $table->name . ' (kopie)',
                'slug'       => $new_slug,
                'settings'   => $table->settings,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => get_current_user_id(),
            )
        );
        $new_table_id = $wpdb->insert_id;

        $columns = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}tablemaster_columns WHERE table_id = %d ORDER BY order_index", $id
        ) );
        $col_map = array();
        foreach ( $columns as $col ) {
            $wpdb->insert(
                "{$wpdb->prefix}tablemaster_columns",
                array(
                    'table_id'    => $new_table_id,
                    'label'       => $col->label,
                    'type'        => $col->type,
                    'order_index' => $col->order_index,
                    'settings'    => $col->settings,
                )
            );
            $col_map[$col->id] = $wpdb->insert_id;
        }

        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}tablemaster_rows WHERE table_id = %d ORDER BY order_index", $id
        ) );
        $row_map = array();
        foreach ( $rows as $row ) {
            $new_parent = ( $row->parent_id && isset( $row_map[$row->parent_id] ) ) ? $row_map[$row->parent_id] : null;
            $wpdb->insert(
                "{$wpdb->prefix}tablemaster_rows",
                array(
                    'table_id'    => $new_table_id,
                    'parent_id'   => $new_parent,
                    'row_type'    => $row->row_type,
                    'order_index' => $row->order_index,
                    'is_collapsed'=> $row->is_collapsed,
                )
            );
            $row_map[$row->id] = $wpdb->insert_id;
        }

        $old_row_ids = array_keys( $row_map );
        if ( ! empty( $old_row_ids ) && ! empty( $col_map ) ) {
            $placeholders = implode( ',', array_fill( 0, count( $old_row_ids ), '%d' ) );
            $all_cells = $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tablemaster_cells WHERE row_id IN ($placeholders)",
                $old_row_ids
            ) );

            $batch = array();
            $batch_count = 0;
            foreach ( $all_cells as $cell ) {
                $new_row_id = $row_map[ $cell->row_id ] ?? null;
                $new_col_id = $col_map[ $cell->column_id ] ?? null;
                if ( ! $new_row_id || ! $new_col_id ) continue;

                $batch[] = $wpdb->prepare(
                    '(%d,%d,%s,%s,%s,%d)',
                    $new_row_id,
                    $new_col_id,
                    $cell->content,
                    $cell->lang,
                    isset( $cell->align ) ? $cell->align : '',
                    isset( $cell->colspan ) ? intval( $cell->colspan ) : 1
                );
                $batch_count++;

                if ( $batch_count >= 500 ) {
                    $wpdb->query(
                        "INSERT INTO {$wpdb->prefix}tablemaster_cells (row_id, column_id, content, lang, align, colspan) VALUES " . implode( ',', $batch )
                    );
                    $batch = array();
                    $batch_count = 0;
                }
            }
            if ( ! empty( $batch ) ) {
                $wpdb->query(
                    "INSERT INTO {$wpdb->prefix}tablemaster_cells (row_id, column_id, content, lang, align, colspan) VALUES " . implode( ',', $batch )
                );
            }
        }

        return $new_table_id;
    }

    private static function clean_group_value( $value, $max_len = 500 ) {
        $sanitized = wp_kses_post( $value );
        $text_only = trim( html_entity_decode( wp_strip_all_tags( str_replace( array( '&nbsp;', "\xC2\xA0" ), ' ', $sanitized ) ), ENT_QUOTES, 'UTF-8' ) );
        if ( $text_only === '' ) return '';
        return mb_substr( $sanitized, 0, $max_len );
    }

    public static function flush_table_cache( $table_id ) {
        $table_id = intval( $table_id );
        $langs = array( 'default', 'en', 'nl', 'fr', 'de', 'es', 'it', 'pt', 'pl', 'ru', 'ja', 'zh', 'ko', 'ar', 'tr', 'sv', 'da', 'no', 'fi' );
        foreach ( $langs as $lang ) {
            delete_transient( 'tmp_data_' . $table_id . '_' . $lang );
        }
        $wpml_langs = apply_filters( 'wpml_active_languages', array(), 'skip_missing=0' );
        if ( is_array( $wpml_langs ) ) {
            foreach ( array_keys( $wpml_langs ) as $code ) {
                delete_transient( 'tmp_data_' . $table_id . '_' . sanitize_key( $code ) );
            }
        }

        if ( function_exists( 'wp_cache_flush' ) ) {
            wp_cache_flush();
        }

        if ( class_exists( 'LiteSpeed_Cache_API' ) ) {
            do_action( 'litespeed_purge_all' );
        }

        if ( function_exists( 'wp_cache_clear_cache' ) ) {
            wp_cache_clear_cache();
        }

        if ( function_exists( 'w3tc_flush_all' ) ) {
            w3tc_flush_all();
        }

        if ( function_exists( 'rocket_clean_domain' ) ) {
            rocket_clean_domain();
        }
    }

    public static function get_table_data( $table_id, $lang = '' ) {
        global $wpdb;
        $table_id = intval( $table_id );

        $cache_key = 'tmp_data_' . $table_id . '_' . ( $lang ?: 'default' );
        $cached    = get_transient( $cache_key );
        if ( false !== $cached ) {
            return $cached;
        }

        $columns = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}tablemaster_columns WHERE table_id = %d ORDER BY order_index",
            $table_id
        ) );

        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}tablemaster_rows WHERE table_id = %d ORDER BY order_index",
            $table_id
        ) );

        $row_ids     = wp_list_pluck( $rows, 'id' );
        $cells       = array();
        $cell_aligns = array();
        $cell_merges = array();
        if ( $row_ids ) {
            $placeholders = implode( ',', array_fill( 0, count( $row_ids ), '%d' ) );
            $lang_clause = '';
            $query_args = array_map( 'intval', $row_ids );
            if ( $lang ) {
                $default_lang_code = '';
                if ( class_exists( 'TableMaster_WPML' ) && TableMaster_WPML::is_active() ) {
                    $default_lang_code = TableMaster_WPML::get_default_language();
                }
                if ( $default_lang_code !== '' && $default_lang_code !== $lang ) {
                    $lang_clause = ' AND (lang = %s OR lang = %s OR lang = %s)';
                    $query_args[] = $lang;
                    $query_args[] = '';
                    $query_args[] = $default_lang_code;
                    $order_clause = $wpdb->prepare(
                        ' ORDER BY CASE WHEN lang = %s THEN 1 WHEN lang = %s THEN 2 ELSE 3 END',
                        $lang, ''
                    );
                } else {
                    $lang_clause = ' AND (lang = %s OR lang = %s)';
                    $query_args[] = $lang;
                    $query_args[] = '';
                    $order_clause = ' ORDER BY lang DESC';
                }
            }
            $raw_cells = $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tablemaster_cells WHERE row_id IN ($placeholders)$lang_clause" . ( $order_clause ?? ' ORDER BY lang DESC' ),
                $query_args
            ) );
            foreach ( $raw_cells as $cell ) {
                if ( ! isset( $cells[$cell->row_id][$cell->column_id] ) ) {
                    $cells[$cell->row_id][$cell->column_id] = $cell->content;
                }
                $cell_align = isset( $cell->align ) ? $cell->align : '';
                if ( $cell_align !== '' && ! isset( $cell_aligns[$cell->row_id][$cell->column_id] ) ) {
                    $cell_aligns[$cell->row_id][$cell->column_id] = $cell_align;
                }
                $cell_colspan = isset( $cell->colspan ) ? intval( $cell->colspan ) : 1;
                if ( $cell_colspan > 1 && ! isset( $cell_merges[$cell->row_id][$cell->column_id] ) ) {
                    $cell_merges[$cell->row_id][$cell->column_id] = $cell_colspan;
                }
            }
        }

        foreach ( $rows as &$row ) {
            $row->cells = $cells[$row->id] ?? array();
            $row->cell_aligns = $cell_aligns[$row->id] ?? array();
            $row->cell_merges = $cell_merges[$row->id] ?? array();
        }

        $result = array(
            'columns' => $columns,
            'rows'    => $rows,
        );

        set_transient( $cache_key, $result, HOUR_IN_SECONDS );

        return $result;
    }

    public static function save_table_structure( $table_id, $columns_data, $rows_data, $lang = '' ) {
        global $wpdb;
        $table_id = intval( $table_id );

        if ( $lang !== '' && class_exists( 'TableMaster_WPML' ) && TableMaster_WPML::is_active() ) {
            $default_lang = TableMaster_WPML::get_default_language();
            if ( $lang === $default_lang ) {
                $lang = '';
            }
        }

        self::flush_table_cache( $table_id );

        $existing_col_ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}tablemaster_columns WHERE table_id = %d", $table_id
        ) );
        $submitted_col_ids = array_map( 'intval', array_filter( array_column( $columns_data, 'id' ) ) );
        foreach ( $existing_col_ids as $ecid ) {
            if ( ! in_array( intval( $ecid ), $submitted_col_ids, true ) ) {
                $wpdb->delete( "{$wpdb->prefix}tablemaster_cells",  array( 'column_id' => $ecid ) );
                $wpdb->delete( "{$wpdb->prefix}tablemaster_columns", array( 'id' => $ecid ) );
            }
        }

        $col_id_map = array();
        foreach ( $columns_data as $order_index => $col ) {
            $allowed_aligns = array( 'left', 'center', 'right' );
            $raw_align = sanitize_text_field( $col['settings']['align'] ?? 'left' );
            $raw_width = sanitize_text_field( $col['settings']['width'] ?? 'auto' );
            if ( $raw_width !== 'auto' && ! preg_match( '/^\d{1,4}(px|em|rem|%)$/', $raw_width ) ) {
                $raw_width = 'auto';
            }
            $col_settings = array(
                'width'         => $raw_width,
                'align'         => in_array( $raw_align, $allowed_aligns, true ) ? $raw_align : 'left',
                'sortable'      => ! empty( $col['settings']['sortable'] ),
                'filterable'    => ! empty( $col['settings']['filterable'] ),
                'header_group1' => self::clean_group_value( $col['settings']['header_group1'] ?? '', 500 ),
                'header_group2' => self::clean_group_value( $col['settings']['header_group2'] ?? '', 200 ),
            );
            $temp_key = sanitize_text_field( $col['temp_key'] ?? '' );

            if ( ! empty( $col['id'] ) ) {
                $db_col_id = intval( $col['id'] );
                $wpdb->update(
                    "{$wpdb->prefix}tablemaster_columns",
                    array(
                        'label'       => mb_substr( wp_kses_post( $col['label'] ), 0, 500 ),
                        'type'        => sanitize_text_field( $col['type'] ),
                        'order_index' => $order_index,
                        'settings'    => wp_json_encode( $col_settings ),
                    ),
                    array( 'id' => $db_col_id )
                );
                // Map by both DB id and temp_key for cell lookups
                $col_id_map[ $db_col_id ]  = $db_col_id;
                if ( $temp_key ) {
                    $col_id_map[ $temp_key ] = $db_col_id;
                }
            } else {
                $wpdb->insert(
                    "{$wpdb->prefix}tablemaster_columns",
                    array(
                        'table_id'    => $table_id,
                        'label'       => mb_substr( wp_kses_post( $col['label'] ), 0, 500 ),
                        'type'        => sanitize_text_field( $col['type'] ),
                        'order_index' => $order_index,
                        'settings'    => wp_json_encode( $col_settings ),
                    )
                );
                $new_col_id = $wpdb->insert_id;
                if ( $temp_key ) {
                    $col_id_map[ $temp_key ] = $new_col_id;
                }
            }
        }

        $existing_row_ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}tablemaster_rows WHERE table_id = %d", $table_id
        ) );
        $submitted_row_ids = array_map( 'intval', array_filter( array_column( $rows_data, 'id' ) ) );
        foreach ( $existing_row_ids as $erid ) {
            if ( ! in_array( intval( $erid ), $submitted_row_ids, true ) ) {
                $wpdb->delete( "{$wpdb->prefix}tablemaster_cells", array( 'row_id' => $erid ) );
                $wpdb->delete( "{$wpdb->prefix}tablemaster_rows",  array( 'id' => $erid ) );
            }
        }

        $row_id_map = array();
        $pending_cells = array();
        $allowed_types = array( 'data', 'group_1', 'group_2', 'group_3', 'footer' );

        foreach ( $rows_data as $order_index => $row ) {
            $row_type  = in_array( $row['row_type'], $allowed_types, true ) ? $row['row_type'] : 'data';
            $parent_id = null;
            if ( ! empty( $row['parent_temp_id'] ) && isset( $row_id_map[ $row['parent_temp_id'] ] ) ) {
                $parent_id = $row_id_map[ $row['parent_temp_id'] ];
            } elseif ( ! empty( $row['parent_id'] ) ) {
                $parent_id = intval( $row['parent_id'] );
            }

            if ( ! empty( $row['id'] ) ) {
                $wpdb->update(
                    "{$wpdb->prefix}tablemaster_rows",
                    array(
                        'parent_id'    => $parent_id,
                        'row_type'     => $row_type,
                        'order_index'  => $order_index,
                        'is_collapsed' => ! empty( $row['is_collapsed'] ) ? 1 : 0,
                    ),
                    array( 'id' => intval( $row['id'] ) )
                );
                $row_db_id = intval( $row['id'] );
            } else {
                $wpdb->insert(
                    "{$wpdb->prefix}tablemaster_rows",
                    array(
                        'table_id'     => $table_id,
                        'parent_id'    => $parent_id,
                        'row_type'     => $row_type,
                        'order_index'  => $order_index,
                        'is_collapsed' => ! empty( $row['is_collapsed'] ) ? 1 : 0,
                    )
                );
                $row_db_id = $wpdb->insert_id;
            }

            $temp_id = $row['temp_id'] ?? ( 'r' . $order_index );
            $row_id_map[$temp_id] = $row_db_id;

            $row_cell_aligns = isset( $row['cell_aligns'] ) && is_array( $row['cell_aligns'] ) ? $row['cell_aligns'] : array();
            $row_cell_merges = isset( $row['cell_merges'] ) && is_array( $row['cell_merges'] ) ? $row['cell_merges'] : array();

            if ( ! empty( $row['cells'] ) ) {
                $pending_cells[] = array(
                    'row_db_id'    => $row_db_id,
                    'cells'        => $row['cells'],
                    'cell_aligns'  => $row_cell_aligns,
                    'cell_merges'  => $row_cell_merges,
                );
            }
        }

        if ( ! empty( $pending_cells ) ) {
            $all_row_db_ids = array_unique( array_column( $pending_cells, 'row_db_id' ) );
            $ph = implode( ',', array_fill( 0, count( $all_row_db_ids ), '%d' ) );
            $existing_cells_raw = $wpdb->get_results( $wpdb->prepare(
                "SELECT id, row_id, column_id FROM {$wpdb->prefix}tablemaster_cells WHERE row_id IN ($ph) AND lang = %s",
                array_merge( $all_row_db_ids, array( $lang ) )
            ) );
            $existing_map = array();
            foreach ( $existing_cells_raw as $ec ) {
                $existing_map[ $ec->row_id . '_' . $ec->column_id ] = $ec->id;
            }

            $insert_batch = array();
            $insert_count = 0;
            foreach ( $pending_cells as $pc ) {
                $row_db_id    = $pc['row_db_id'];
                $cell_aligns  = $pc['cell_aligns'];
                $cell_merges  = $pc['cell_merges'];
                foreach ( $pc['cells'] as $temp_col_key => $content ) {
                    $col_db_id = $col_id_map[ $temp_col_key ] ?? null;
                    if ( ! $col_db_id ) continue;

                    $sanitized_content = wp_kses_post( $content );
                    $cell_align = '';
                    if ( isset( $cell_aligns[ $temp_col_key ] ) ) {
                        $cell_align = in_array( $cell_aligns[ $temp_col_key ], array( 'left', 'center', 'right' ), true ) ? $cell_aligns[ $temp_col_key ] : '';
                    }
                    $cell_colspan = 1;
                    if ( isset( $cell_merges[ $temp_col_key ] ) ) {
                        $cell_colspan = max( 1, intval( $cell_merges[ $temp_col_key ] ) );
                    }

                    $lookup_key = $row_db_id . '_' . $col_db_id;
                    if ( isset( $existing_map[ $lookup_key ] ) ) {
                        $wpdb->update(
                            "{$wpdb->prefix}tablemaster_cells",
                            array( 'content' => $sanitized_content, 'align' => $cell_align, 'colspan' => $cell_colspan ),
                            array( 'id' => $existing_map[ $lookup_key ] )
                        );
                    } else {
                        $insert_batch[] = $wpdb->prepare(
                            '(%d,%d,%s,%s,%s,%d)',
                            $row_db_id, $col_db_id, $sanitized_content, $lang, $cell_align, $cell_colspan
                        );
                        $insert_count++;
                        if ( $insert_count >= 500 ) {
                            $wpdb->query(
                                "INSERT INTO {$wpdb->prefix}tablemaster_cells (row_id, column_id, content, lang, align, colspan) VALUES " . implode( ',', $insert_batch )
                            );
                            $insert_batch = array();
                            $insert_count = 0;
                        }
                    }
                }
            }
            if ( ! empty( $insert_batch ) ) {
                $wpdb->query(
                    "INSERT INTO {$wpdb->prefix}tablemaster_cells (row_id, column_id, content, lang, align, colspan) VALUES " . implode( ',', $insert_batch )
                );
            }
        }

        return true;
    }
}
