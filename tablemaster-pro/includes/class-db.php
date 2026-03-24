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
            label varchar(255) NOT NULL DEFAULT '',
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
            PRIMARY KEY  (id),
            KEY row_id (row_id),
            KEY column_id (column_id)
        ) $charset_collate;" );

        update_option( 'tablemaster_db_version', TMP_VERSION );

        self::insert_demo_data();
    }

    public static function uninstall() {
        global $wpdb;
        $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tablemaster_cells" );
        $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tablemaster_rows" );
        $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tablemaster_columns" );
        $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tablemaster_tables" );
        delete_option( 'tablemaster_db_version' );
        delete_option( 'tablemaster_settings' );
    }

    private static function insert_demo_data() {
        global $wpdb;

        $existing = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}tablemaster_tables" );
        if ( $existing > 0 ) {
            return;
        }

        $green_settings = json_encode( array(
            'caption'            => 'Medewerkers per Bedrijf',
            'search'             => true,
            'search_position'    => 'right',
            'pagination'         => true,
            'per_page'           => 10,
            'per_page_selector'  => true,
            'collapsible_groups' => true,
            'mobile_mode'        => 'scroll',
            'default_sort_col'   => '',
            'default_sort_dir'   => 'asc',
            'inline_html'        => false,
            'theme'              => 'green',
            'colors'             => array(
                'header_bg'       => '#2e7d32',
                'header_text'     => '#ffffff',
                'group1_bg'       => '#4caf50',
                'group1_text'     => '#ffffff',
                'group2_bg'       => '#81c784',
                'group2_text'     => '#1a1a1a',
                'group3_bg'       => '#c8e6c9',
                'group3_text'     => '#1a1a1a',
                'odd_bg'          => '#ffffff',
                'even_bg'         => '#f1f8e9',
                'hover_bg'        => '#dcedc8',
                'border_color'    => '#a5d6a7',
                'accent_color'    => '#2e7d32',
            ),
        ) );

        $now = current_time( 'mysql' );

        $wpdb->insert(
            "{$wpdb->prefix}tablemaster_tables",
            array(
                'name'       => 'Medewerkers Overzicht (Demo)',
                'slug'       => 'medewerkers-overzicht-demo',
                'settings'   => $green_settings,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => 1,
            )
        );
        $t1 = $wpdb->insert_id;

        $cols1 = array(
            array( 'label' => 'Achternaam',  'type' => 'text',   'order_index' => 0,
                   'settings' => json_encode( array( 'width' => 'auto', 'align' => 'left',   'sortable' => true,  'filterable' => true,  'hide_mobile' => false ) ) ),
            array( 'label' => 'Voornaam',    'type' => 'text',   'order_index' => 1,
                   'settings' => json_encode( array( 'width' => 'auto', 'align' => 'left',   'sortable' => true,  'filterable' => true,  'hide_mobile' => false ) ) ),
            array( 'label' => 'Bedrijf',     'type' => 'text',   'order_index' => 2,
                   'settings' => json_encode( array( 'width' => 'auto', 'align' => 'left',   'sortable' => true,  'filterable' => true,  'hide_mobile' => true  ) ) ),
            array( 'label' => 'Land',        'type' => 'text',   'order_index' => 3,
                   'settings' => json_encode( array( 'width' => '120px','align' => 'left',   'sortable' => true,  'filterable' => true,  'hide_mobile' => true  ) ) ),
            array( 'label' => 'Verjaardag',  'type' => 'date',   'order_index' => 4,
                   'settings' => json_encode( array( 'width' => '120px','align' => 'center', 'sortable' => true,  'filterable' => false, 'hide_mobile' => true  ) ) ),
        );

        $col_ids1 = array();
        foreach ( $cols1 as $col ) {
            $wpdb->insert( "{$wpdb->prefix}tablemaster_columns", array_merge( array( 'table_id' => $t1 ), $col ) );
            $col_ids1[] = $wpdb->insert_id;
        }

        $demo_rows = array(
            array( 'type' => 'group_1', 'parent' => null,  'data' => array( 'Adobe', '', 'Adobe', '', '' ) ),
            array( 'type' => 'data',    'parent' => 0,     'data' => array( 'Houston',   'Jordan',   'Adobe', 'Canada',         '1985-03-05' ) ),
            array( 'type' => 'data',    'parent' => 0,     'data' => array( 'Gutierrez',  'Diana',    'Adobe', 'Mexico',         '1990-07-14' ) ),
            array( 'type' => 'data',    'parent' => 0,     'data' => array( 'Nakamura',   'Kenji',    'Adobe', 'Japan',          '1988-11-22' ) ),
            array( 'type' => 'group_1', 'parent' => null,  'data' => array( 'Apple', '', 'Apple', '', '' ) ),
            array( 'type' => 'data',    'parent' => 4,     'data' => array( 'Smith',      'Emily',    'Apple', 'United States',  '1993-01-30' ) ),
            array( 'type' => 'data',    'parent' => 4,     'data' => array( 'Müller',     'Hans',     'Apple', 'Germany',        '1979-06-18' ) ),
            array( 'type' => 'data',    'parent' => 4,     'data' => array( 'Okonkwo',    'Chisom',   'Apple', 'Nigeria',        '1995-09-03' ) ),
            array( 'type' => 'group_1', 'parent' => null,  'data' => array( 'Cisco', '', 'Cisco', '', '' ) ),
            array( 'type' => 'data',    'parent' => 8,     'data' => array( 'Patel',      'Priya',    'Cisco', 'India',          '1987-04-12' ) ),
            array( 'type' => 'data',    'parent' => 8,     'data' => array( 'Leblanc',    'François', 'Cisco', 'France',         '1982-12-28' ) ),
            array( 'type' => 'data',    'parent' => 8,     'data' => array( 'Chen',       'Wei',      'Cisco', 'China',          '1991-08-07' ) ),
        );

        $row_ids1   = array();
        $parent_map = array();
        foreach ( $demo_rows as $idx => $r ) {
            $parent_id = null;
            if ( is_int( $r['parent'] ) ) {
                $parent_id = $parent_map[ $r['parent'] ] ?? null;
            }
            $wpdb->insert( "{$wpdb->prefix}tablemaster_rows", array(
                'table_id'    => $t1,
                'parent_id'   => $parent_id,
                'row_type'    => $r['type'],
                'order_index' => $idx,
                'is_collapsed'=> 0,
            ) );
            $rid           = $wpdb->insert_id;
            $row_ids1[]    = $rid;
            $parent_map[$idx] = $rid;

            foreach ( $r['data'] as $ci => $content ) {
                if ( $content === '' ) continue;
                $wpdb->insert( "{$wpdb->prefix}tablemaster_cells", array(
                    'row_id'    => $rid,
                    'column_id' => $col_ids1[$ci],
                    'content'   => $content,
                    'lang'      => '',
                ) );
            }
        }

        $red_settings = json_encode( array(
            'caption'            => 'Medische Behandelingen',
            'search'             => true,
            'search_position'    => 'right',
            'pagination'         => true,
            'per_page'           => 10,
            'per_page_selector'  => true,
            'collapsible_groups' => true,
            'mobile_mode'        => 'card',
            'default_sort_col'   => '',
            'default_sort_dir'   => 'asc',
            'inline_html'        => false,
            'theme'              => 'red',
            'colors'             => array(
                'header_bg'    => '#c62828',
                'header_text'  => '#ffffff',
                'group1_bg'    => '#e53935',
                'group1_text'  => '#ffffff',
                'group2_bg'    => '#ef9a9a',
                'group2_text'  => '#1a1a1a',
                'group3_bg'    => '#ffcdd2',
                'group3_text'  => '#1a1a1a',
                'odd_bg'       => '#ffffff',
                'even_bg'      => '#fce4ec',
                'hover_bg'     => '#f8bbd0',
                'border_color' => '#ef9a9a',
                'accent_color' => '#c62828',
            ),
        ) );

        $wpdb->insert(
            "{$wpdb->prefix}tablemaster_tables",
            array(
                'name'       => 'Medische Behandelingen (Demo)',
                'slug'       => 'medische-behandelingen-demo',
                'settings'   => $red_settings,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => 1,
            )
        );
        $t2 = $wpdb->insert_id;

        $cols2 = array(
            array( 'label' => 'Behandeling',   'type' => 'text', 'order_index' => 0,
                   'settings' => json_encode( array( 'width' => 'auto', 'align' => 'left', 'sortable' => true, 'filterable' => true, 'hide_mobile' => false ) ) ),
            array( 'label' => 'Categorie',     'type' => 'text', 'order_index' => 1,
                   'settings' => json_encode( array( 'width' => 'auto', 'align' => 'left', 'sortable' => true, 'filterable' => true, 'hide_mobile' => true  ) ) ),
            array( 'label' => 'Indicatie',     'type' => 'text', 'order_index' => 2,
                   'settings' => json_encode( array( 'width' => 'auto', 'align' => 'left', 'sortable' => false,'filterable' => false,'hide_mobile' => true  ) ) ),
            array( 'label' => 'Prijs (€)',     'type' => 'number','order_index' => 3,
                   'settings' => json_encode( array( 'width' => '100px','align' => 'right','sortable' => true, 'filterable' => false,'hide_mobile' => false ) ) ),
        );

        $col_ids2 = array();
        foreach ( $cols2 as $col ) {
            $wpdb->insert( "{$wpdb->prefix}tablemaster_columns", array_merge( array( 'table_id' => $t2 ), $col ) );
            $col_ids2[] = $wpdb->insert_id;
        }

        $demo2 = array(
            array( 'type' => 'group_1', 'parent' => null, 'data' => array( 'Massage', 'Fysiotherapie', '', '' ) ),
            array( 'type' => 'group_2', 'parent' => 0,    'data' => array( 'Klassieke massage', 'Fysiotherapie', '', '' ) ),
            array( 'type' => 'data',    'parent' => 1,    'data' => array( 'Rugmassage 30 min',       'Fysiotherapie', 'Spierspanning',   '45' ) ),
            array( 'type' => 'data',    'parent' => 1,    'data' => array( 'Rugmassage 60 min',       'Fysiotherapie', 'Spierspanning',   '75' ) ),
            array( 'type' => 'group_2', 'parent' => 0,    'data' => array( 'Sportmassage', 'Fysiotherapie', '', '' ) ),
            array( 'type' => 'data',    'parent' => 4,    'data' => array( 'Sportmassage 45 min',     'Fysiotherapie', 'Sportblessure',   '60' ) ),
            array( 'type' => 'group_1', 'parent' => null, 'data' => array( 'Injecties', 'Medisch', '', '' ) ),
            array( 'type' => 'group_2', 'parent' => 6,    'data' => array( 'Botox', 'Medisch', '', '' ) ),
            array( 'type' => 'group_3', 'parent' => 7,    'data' => array( 'Voorhoofd Botox', 'Medisch', 'Rimpels', '' ) ),
            array( 'type' => 'data',    'parent' => 8,    'data' => array( 'Botox 1 zone',            'Medisch',       'Rimpels',         '150' ) ),
            array( 'type' => 'data',    'parent' => 8,    'data' => array( 'Botox 3 zones',           'Medisch',       'Rimpels',         '350' ) ),
            array( 'type' => 'group_1', 'parent' => null, 'data' => array( 'Curettage', 'Dermatologie', '', '' ) ),
            array( 'type' => 'data',    'parent' => 11,   'data' => array( 'Curettage wrat',          'Dermatologie',  'Wratten',         '80' ) ),
            array( 'type' => 'data',    'parent' => 11,   'data' => array( 'Curettage fibroom',       'Dermatologie',  'Huidafwijking',   '95' ) ),
        );

        $pm2 = array();
        foreach ( $demo2 as $idx => $r ) {
            $parent_id = null;
            if ( is_int( $r['parent'] ) ) {
                $parent_id = $pm2[ $r['parent'] ] ?? null;
            }
            $wpdb->insert( "{$wpdb->prefix}tablemaster_rows", array(
                'table_id'    => $t2,
                'parent_id'   => $parent_id,
                'row_type'    => $r['type'],
                'order_index' => $idx,
                'is_collapsed'=> 0,
            ) );
            $rid = $wpdb->insert_id;
            $pm2[$idx] = $rid;
            foreach ( $r['data'] as $ci => $content ) {
                if ( $content === '' ) continue;
                $wpdb->insert( "{$wpdb->prefix}tablemaster_cells", array(
                    'row_id'    => $rid,
                    'column_id' => $col_ids2[$ci],
                    'content'   => $content,
                    'lang'      => '',
                ) );
            }
        }

        $anato_settings = json_encode( array(
            'caption'            => 'Tabel 3 | Anatomopathologie',
            'search'             => true,
            'search_position'    => 'right',
            'pagination'         => false,
            'per_page'           => -1,
            'per_page_selector'  => false,
            'collapsible_groups' => false,
            'mobile_mode'        => 'card',
            'default_sort_col'   => '',
            'default_sort_dir'   => 'asc',
            'inline_html'        => false,
            'theme'              => 'red',
            'colors'             => array(
                'header_bg'    => '#c62828',
                'header_text'  => '#ffffff',
                'group1_bg'    => '#c62828',
                'group1_text'  => '#ffffff',
                'group2_bg'    => '#ef9a9a',
                'group2_text'  => '#1a1a1a',
                'group3_bg'    => '#ffcdd2',
                'group3_text'  => '#1a1a1a',
                'odd_bg'       => '#ffffff',
                'even_bg'      => '#fafafa',
                'hover_bg'     => '#fce4ec',
                'border_color' => '#e0e0e0',
                'accent_color' => '#c62828',
            ),
        ) );

        $wpdb->insert(
            "{$wpdb->prefix}tablemaster_tables",
            array(
                'name'       => 'Anatomopathologie (Kiemen)',
                'slug'       => 'anatomopathologie-kiemen',
                'settings'   => $anato_settings,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => 1,
            )
        );
        $t3 = $wpdb->insert_id;

        $cols3 = array(
            array( 'label' => 'Kiemen',               'type' => 'text',   'order_index' => 0,
                   'settings' => json_encode( array( 'width' => 'auto', 'align' => 'left',   'sortable' => true,  'filterable' => true,  'hide_mobile' => false ) ) ),
            array( 'label' => 'Percentage 2024-2025',  'type' => 'text',   'order_index' => 1,
                   'settings' => json_encode( array( 'width' => '200px','align' => 'left',   'sortable' => true,  'filterable' => false, 'hide_mobile' => false ) ) ),
            array( 'label' => 'Percentage 2018',       'type' => 'text',   'order_index' => 2,
                   'settings' => json_encode( array( 'width' => '200px','align' => 'left',   'sortable' => true,  'filterable' => false, 'hide_mobile' => false ) ) ),
        );

        $col_ids3 = array();
        foreach ( $cols3 as $col ) {
            $wpdb->insert( "{$wpdb->prefix}tablemaster_columns", array_merge( array( 'table_id' => $t3 ), $col ) );
            $col_ids3[] = $wpdb->insert_id;
        }

        $demo3 = array(
            array( 'type' => 'data',    'parent' => null, 'data' => array( 'Campylobacter spp.',      '63,8%', '71,7%' ) ),
            array( 'type' => 'data',    'parent' => null, 'data' => array( 'Campylobacter jejuni',    '77,5%', '86,8%' ) ),
            array( 'type' => 'data',    'parent' => null, 'data' => array( 'Campylobacter coli',      '11,3%', '11,6%' ) ),
            array( 'type' => 'data',    'parent' => null, 'data' => array( 'Campylobacter andere',    '11,2%', '1,6%'  ) ),
            array( 'type' => 'data',    'parent' => null, 'data' => array( 'Aeromonas spp.',          '14,0%', '11,8%' ) ),
            array( 'type' => 'data',    'parent' => null, 'data' => array( 'Salmonella spp.',         '10,5%', '11,4%' ) ),
            array( 'type' => 'data',    'parent' => null, 'data' => array( 'Shigella spp.',           '2,1%',  '2,0%'  ) ),
            array( 'type' => 'data',    'parent' => null, 'data' => array( 'Yersinia enterocolitica', '5,4%',  '2,0%'  ) ),
            array( 'type' => 'data',    'parent' => null, 'data' => array( 'E.coli O157',             '0,3%',  '0,1%'  ) ),
            array( 'type' => 'data',    'parent' => null, 'data' => array( 'Andere',                  '3,9%',  '1,0%'  ) ),
            array( 'type' => 'group_1', 'parent' => null, 'data' => array( 'Som',                     '100%',  '100%'  ) ),
        );

        foreach ( $demo3 as $idx => $r ) {
            $wpdb->insert( "{$wpdb->prefix}tablemaster_rows", array(
                'table_id'    => $t3,
                'parent_id'   => null,
                'row_type'    => $r['type'],
                'order_index' => $idx,
                'is_collapsed'=> 0,
            ) );
            $rid = $wpdb->insert_id;
            foreach ( $r['data'] as $ci => $content ) {
                if ( $content === '' ) continue;
                $wpdb->insert( "{$wpdb->prefix}tablemaster_cells", array(
                    'row_id'    => $rid,
                    'column_id' => $col_ids3[$ci],
                    'content'   => $content,
                    'lang'      => '',
                ) );
            }
        }
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
            $in = implode( ',', array_map( 'intval', $row_ids ) );
            $wpdb->query( "DELETE FROM {$wpdb->prefix}tablemaster_cells WHERE row_id IN ($in)" );
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
        $wpdb->insert(
            "{$wpdb->prefix}tablemaster_tables",
            array(
                'name'       => $table->name . ' (kopie)',
                'slug'       => $new_slug,
                'settings'   => $table->settings,
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
            $new_row_id          = $wpdb->insert_id;
            $row_map[$row->id]   = $new_row_id;

            $cells = $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tablemaster_cells WHERE row_id = %d", $row->id
            ) );
            foreach ( $cells as $cell ) {
                $new_col_id = $col_map[$cell->column_id] ?? null;
                if ( $new_col_id ) {
                    $wpdb->insert(
                        "{$wpdb->prefix}tablemaster_cells",
                        array(
                            'row_id'    => $new_row_id,
                            'column_id' => $new_col_id,
                            'content'   => $cell->content,
                            'lang'      => $cell->lang,
                        )
                    );
                }
            }
        }
        return $new_table_id;
    }

    public static function flush_table_cache( $table_id ) {
        global $wpdb;
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_tmp_data_' . intval( $table_id ) . '_%'
        ) );
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_timeout_tmp_data_' . intval( $table_id ) . '_%'
        ) );
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

        $row_ids = wp_list_pluck( $rows, 'id' );
        $cells   = array();
        if ( $row_ids ) {
            $in = implode( ',', array_map( 'intval', $row_ids ) );
            $lang_clause = '';
            if ( $lang ) {
                $lang_clause = $wpdb->prepare( " AND (lang = %s OR lang = '')", $lang );
            }
            $raw_cells = $wpdb->get_results(
                "SELECT * FROM {$wpdb->prefix}tablemaster_cells WHERE row_id IN ($in)$lang_clause ORDER BY lang DESC"
            );
            foreach ( $raw_cells as $cell ) {
                if ( ! isset( $cells[$cell->row_id][$cell->column_id] ) ) {
                    $cells[$cell->row_id][$cell->column_id] = $cell->content;
                }
            }
        }

        foreach ( $rows as &$row ) {
            $row->cells = $cells[$row->id] ?? array();
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
            $col_settings = array(
                'width'       => sanitize_text_field( $col['settings']['width']       ?? 'auto' ),
                'align'       => sanitize_text_field( $col['settings']['align']       ?? 'left' ),
                'sortable'    => ! empty( $col['settings']['sortable'] ),
                'filterable'  => ! empty( $col['settings']['filterable'] ),
                'hide_mobile' => ! empty( $col['settings']['hide_mobile'] ),
            );
            $temp_key = sanitize_text_field( $col['temp_key'] ?? '' );

            if ( ! empty( $col['id'] ) ) {
                $db_col_id = intval( $col['id'] );
                $wpdb->update(
                    "{$wpdb->prefix}tablemaster_columns",
                    array(
                        'label'       => sanitize_text_field( $col['label'] ),
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
                        'label'       => sanitize_text_field( $col['label'] ),
                        'type'        => sanitize_text_field( $col['type'] ),
                        'order_index' => $order_index,
                        'settings'    => wp_json_encode( $col_settings ),
                    )
                );
                $new_col_id = $wpdb->insert_id;
                if ( $temp_key ) {
                    $col_id_map[ $temp_key ] = $new_col_id;
                }
                $col_id_map[ 'new_' . $order_index ] = $new_col_id;
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
        $allowed_types = array( 'data', 'group_1', 'group_2', 'group_3' );

        foreach ( $rows_data as $order_index => $row ) {
            $row_type  = in_array( $row['row_type'], $allowed_types ) ? $row['row_type'] : 'data';
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

            if ( ! empty( $row['cells'] ) ) {
                foreach ( $row['cells'] as $temp_col_key => $content ) {
                    $col_db_id = $col_id_map[$temp_col_key] ?? null;
                    if ( ! $col_db_id ) continue;

                    $existing_cell = $wpdb->get_row( $wpdb->prepare(
                        "SELECT id FROM {$wpdb->prefix}tablemaster_cells WHERE row_id = %d AND column_id = %d AND lang = %s",
                        $row_db_id, $col_db_id, $lang
                    ) );

                    $sanitized_content = wp_kses_post( $content );

                    if ( $existing_cell ) {
                        $wpdb->update(
                            "{$wpdb->prefix}tablemaster_cells",
                            array( 'content' => $sanitized_content ),
                            array( 'id' => $existing_cell->id )
                        );
                    } else {
                        $wpdb->insert(
                            "{$wpdb->prefix}tablemaster_cells",
                            array(
                                'row_id'    => $row_db_id,
                                'column_id' => $col_db_id,
                                'content'   => $sanitized_content,
                                'lang'      => $lang,
                            )
                        );
                    }
                }
            }
        }

        return true;
    }
}
