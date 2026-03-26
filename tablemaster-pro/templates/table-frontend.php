<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$colors   = TableMaster_Settings::sanitize_colors( $settings['colors'] ?? array() );
$table_uid = 'tmp-' . intval( $table->id ) . '-' . wp_rand( 1000, 9999 );
$global_settings = TableMaster_Settings::get();
$border_radius   = min( 50, max( 0, intval( $global_settings['border_radius'] ?? 4 ) ) );

$header_bg    = $colors['header_bg'];
$header_text  = $colors['header_text'];
$group1_bg    = $colors['group1_bg'];
$group1_text  = $colors['group1_text'];
$group2_bg    = $colors['group2_bg'];
$group2_text  = $colors['group2_text'];
$group3_bg    = $colors['group3_bg'];
$group3_text  = $colors['group3_text'];
$footer_bg    = $colors['footer_bg'];
$footer_text  = $colors['footer_text'];
$odd_bg       = $colors['odd_bg'];
$even_bg      = $colors['even_bg'];
$hover_bg     = $colors['hover_bg'];
$border_color = $colors['border_color'];
$accent_color = $colors['accent_color'];

$allowed_search_pos = array( 'left', 'right', 'top', 'bottom' );
$allowed_mobile     = array( 'scroll' );
$allowed_sort_dir   = array( 'asc', 'desc' );

$show_search       = ! empty( $settings['search'] );
$search_pos        = in_array( $settings['search_position'] ?? '', $allowed_search_pos, true ) ? $settings['search_position'] : 'right';
$show_pagination   = ! empty( $settings['pagination'] );
$per_page          = $show_pagination ? min( 500, max( -1, intval( $settings['per_page'] ?? 10 ) ) ) : -1;
$show_pp_selector  = ! empty( $settings['per_page_selector'] );
$collapsible       = ! empty( $settings['collapsible_groups'] );
$mobile_mode       = 'scroll';
$show_col_filters  = ! empty( $settings['column_filters'] );
$caption           = sanitize_text_field( $settings['caption'] ?? '' );
$default_sort_col  = sanitize_text_field( $settings['default_sort_col'] ?? '' );
$default_sort_dir  = in_array( $settings['default_sort_dir'] ?? '', $allowed_sort_dir, true ) ? $settings['default_sort_dir'] : 'asc';
$inline_html       = ! empty( $settings['inline_html'] );
$sticky_first_col  = ! empty( $settings['sticky_first_col'] );
$sticky_header     = ! empty( $settings['sticky_header'] );
$table_sortable    = $settings['sortable'] ?? true;
$fonts             = $settings['fonts'] ?? array();
$max_width         = sanitize_text_field( $settings['max_width'] ?? '' );
if ( $max_width !== '' && ! preg_match( '/^\d{1,4}(px|em|rem|%|vw)$/', $max_width ) ) $max_width = '';
$max_height        = sanitize_text_field( $settings['max_height'] ?? '' );
if ( $max_height !== '' && ! preg_match( '/^\d{1,4}(px|em|rem|%|vh)$/', $max_height ) ) $max_height = '';

$columns = $data['columns'];
$rows    = $data['rows'];
?>
<style>
#<?php echo esc_attr( $table_uid ); ?> {
    --tmp-header-bg:    <?php echo esc_attr( $header_bg ); ?>;
    --tmp-header-text:  <?php echo esc_attr( $header_text ); ?>;
    --tmp-group1-bg:    <?php echo esc_attr( $group1_bg ); ?>;
    --tmp-group1-text:  <?php echo esc_attr( $group1_text ); ?>;
    --tmp-group2-bg:    <?php echo esc_attr( $group2_bg ); ?>;
    --tmp-group2-text:  <?php echo esc_attr( $group2_text ); ?>;
    --tmp-group3-bg:    <?php echo esc_attr( $group3_bg ); ?>;
    --tmp-group3-text:  <?php echo esc_attr( $group3_text ); ?>;
    --tmp-footer-bg:    <?php echo esc_attr( $footer_bg ); ?>;
    --tmp-footer-text:  <?php echo esc_attr( $footer_text ); ?>;
    --tmp-odd-bg:       <?php echo esc_attr( $odd_bg ); ?>;
    --tmp-even-bg:      <?php echo esc_attr( $even_bg ); ?>;
    --tmp-hover-bg:     <?php echo esc_attr( $hover_bg ); ?>;
    --tmp-border:       <?php echo esc_attr( $border_color ); ?>;
    --tmp-accent:       <?php echo esc_attr( $accent_color ); ?>;
    --tmp-radius:       <?php echo intval( $border_radius ); ?>px;
}
<?php
$font_css_map = array(
    'header'  => '.tmp-th',
    'group_1' => '.tmp-type-group_1 td',
    'group_2' => '.tmp-type-group_2 td',
    'group_3' => '.tmp-type-group_3 td',
    'footer'  => '.tmp-type-footer td',
    'data'    => '.tmp-type-data td',
);
foreach ( $font_css_map as $fk => $selector ) :
    $f = $fonts[ $fk ] ?? array();
    $rules = array();
    if ( ! empty( $f['size'] ) )   $rules[] = 'font-size:' . esc_attr( $f['size'] );
    if ( ! empty( $f['bold'] ) )   $rules[] = 'font-weight:bold';
    if ( ! empty( $f['italic'] ) ) $rules[] = 'font-style:italic';
    if ( ! empty( $rules ) ) :
?>
#<?php echo esc_attr( $table_uid ); ?> <?php echo $selector; ?> { <?php echo implode( ';', $rules ); ?>; }
<?php endif; endforeach; ?>
<?php if ( $max_width !== '' ) : ?>
#<?php echo esc_attr( $table_uid ); ?> { max-width: <?php echo esc_attr( $max_width ); ?>; }
<?php endif; ?>
</style>

<div id="<?php echo esc_attr( $table_uid ); ?>"
     class="tmp-wrapper tmp-mobile-<?php echo esc_attr( $mobile_mode ); ?><?php echo $sticky_first_col ? ' tmp-sticky-first' : ''; ?><?php echo $sticky_header ? ' tmp-sticky-header' : ''; ?>"
     data-table-id="<?php echo esc_attr( $table->id ); ?>"
     data-per-page="<?php echo esc_attr( $per_page ); ?>"
     data-collapsible="<?php echo $collapsible ? '1' : '0'; ?>"
     data-default-sort-col="<?php echo esc_attr( $default_sort_col ); ?>"
     data-default-sort-dir="<?php echo esc_attr( $default_sort_dir ); ?>"
     data-mobile-mode="<?php echo esc_attr( $mobile_mode ); ?>">

    <?php if ( $show_search && $search_pos === 'top' ) : ?>
        <div class="tmp-controls tmp-controls-top tmp-search-center">
            <div class="tmp-search-wrap">
                <label class="screen-reader-text" for="<?php echo esc_attr( $table_uid ); ?>-search"><?php esc_html_e( 'Zoeken', TMP_TEXT_DOMAIN ); ?></label>
                <input type="search" id="<?php echo esc_attr( $table_uid ); ?>-search" class="tmp-search" placeholder="<?php esc_attr_e( 'Zoeken…', TMP_TEXT_DOMAIN ); ?>">
            </div>
        </div>
    <?php endif; ?>

    <div class="tmp-controls tmp-controls-top">
        <?php if ( $show_search && $search_pos === 'left' ) : ?>
            <div class="tmp-search-wrap">
                <label class="screen-reader-text" for="<?php echo esc_attr( $table_uid ); ?>-search"><?php esc_html_e( 'Zoeken', TMP_TEXT_DOMAIN ); ?></label>
                <input type="search" id="<?php echo esc_attr( $table_uid ); ?>-search" class="tmp-search" placeholder="<?php esc_attr_e( 'Zoeken…', TMP_TEXT_DOMAIN ); ?>">
            </div>
        <?php endif; ?>

        <div class="tmp-controls-right">
            <?php if ( $show_search && $search_pos === 'right' ) : ?>
                <div class="tmp-search-wrap">
                    <label class="screen-reader-text" for="<?php echo esc_attr( $table_uid ); ?>-search"><?php esc_html_e( 'Zoeken', TMP_TEXT_DOMAIN ); ?></label>
                    <input type="search" id="<?php echo esc_attr( $table_uid ); ?>-search" class="tmp-search" placeholder="<?php esc_attr_e( 'Zoeken…', TMP_TEXT_DOMAIN ); ?>">
                </div>
            <?php endif; ?>
            <?php if ( $show_pagination && $show_pp_selector ) : ?>
                <div class="tmp-per-page-wrap">
                    <label for="<?php echo esc_attr( $table_uid ); ?>-per-page"><?php esc_html_e( 'Per pagina:', TMP_TEXT_DOMAIN ); ?></label>
                    <select id="<?php echo esc_attr( $table_uid ); ?>-per-page" class="tmp-per-page-select">
                        <?php foreach ( array( 5, 10, 25, 50, 100, -1 ) as $opt ) : ?>
                            <option value="<?php echo esc_attr( $opt ); ?>" <?php selected( $opt, $per_page ); ?>>
                                <?php echo $opt === -1 ? esc_html__( 'Alle', TMP_TEXT_DOMAIN ) : esc_html( $opt ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ( $show_col_filters && ! empty( $columns ) ) : ?>
    <div class="tmp-col-filters">
        <?php foreach ( $columns as $col ) : ?>
            <div class="tmp-col-filter-item">
                <label><?php echo esc_html( wp_strip_all_tags( $col->label ) ); ?></label>
                <select class="tmp-col-filter" data-col-id="<?php echo esc_attr( $col->id ); ?>">
                    <option value=""><?php esc_html_e( '— Alle —', TMP_TEXT_DOMAIN ); ?></option>
                </select>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php
    $col_count     = count( $columns ) + ( $collapsible ? 1 : 0 );
    $min_col_width = 120;
    $table_min_w   = max( 400, $col_count * $min_col_width );
    $table_style   = $mobile_mode === 'scroll' ? 'min-width:' . intval( $table_min_w ) . 'px;' : '';
    ?>
    <div class="tmp-table-scroll-wrapper"<?php if ( $max_height !== '' ) : ?> style="max-height:<?php echo esc_attr( $max_height ); ?>;overflow-y:auto;"<?php endif; ?>>
        <table class="tmp-table" role="grid" aria-label="<?php echo esc_attr( $caption ?: $table->name ); ?>"
            <?php if ( $table_style ) : ?> style="<?php echo esc_attr( $table_style ); ?>"<?php endif; ?>>
            <?php
            $has_groups = false;
            $col_meta   = array();
            foreach ( $columns as $col ) {
                $cs = json_decode( $col->settings, true );
                if ( ! is_array( $cs ) ) $cs = array();
                $g1_raw = $cs['header_group1'] ?? '';
                $g2_raw = $cs['header_group2'] ?? '';
                $g1 = trim( wp_strip_all_tags( html_entity_decode( str_replace( array( '&nbsp;', "\xC2\xA0" ), ' ', $g1_raw ), ENT_QUOTES, 'UTF-8' ) ) ) !== '' ? trim( $g1_raw ) : '';
                $g2 = trim( wp_strip_all_tags( html_entity_decode( str_replace( array( '&nbsp;', "\xC2\xA0" ), ' ', $g2_raw ), ENT_QUOTES, 'UTF-8' ) ) ) !== '' ? trim( $g2_raw ) : '';
                if ( $g2 !== '' && $g1 === '' ) $g1 = $g2;
                if ( $g1 !== '' ) $has_groups = true;
                $col_meta[] = array(
                    'col'   => $col,
                    'cs'    => $cs,
                    'g1'    => $g1,
                    'g2'    => $g2,
                );
            }
            $max_depth = 1;
            if ( $has_groups ) {
                $max_depth = 2;
                foreach ( $col_meta as $cm ) {
                    if ( $cm['g2'] !== '' ) { $max_depth = 3; break; }
                }
            }
            ?>
            <?php
            $valid_aligns    = array( 'left', 'center', 'right' );
            $default_col_w   = $settings['default_col_width'] ?? '150px';
            if ( $default_col_w !== '' && ! preg_match( '/^\d{1,4}(px|em|rem|%)$/', $default_col_w ) ) $default_col_w = '150px';
            $first_col_w     = $settings['first_col_width'] ?? '';
            if ( $first_col_w !== '' && ! preg_match( '/^\d{1,4}(px|em|rem|%)$/', $first_col_w ) ) $first_col_w = '';
            ?>
            <colgroup>
                <?php if ( $collapsible ) : ?>
                    <col style="width:36px;">
                <?php endif; ?>
                <?php $col_idx = 0; foreach ( $col_meta as $cg_cm ) :
                    $cg_w = ( $col_idx === 0 && $first_col_w !== '' ) ? $first_col_w : $default_col_w;
                    $col_idx++;
                ?>
                    <col<?php if ( $cg_w !== 'auto' ) : ?> style="width:<?php echo esc_attr( $cg_w ); ?>;"<?php endif; ?>>
                <?php endforeach; ?>
            </colgroup>
            <thead>
            <?php if ( $max_depth === 1 ) : ?>
                <tr class="tmp-header-row">
                    <?php if ( $collapsible ) : ?>
                        <th class="tmp-toggle-col" aria-hidden="true"></th>
                    <?php endif; ?>
                    <?php
                    foreach ( $col_meta as $cm ) :
                        $cs     = $cm['cs'];
                        $col    = $cm['col'];
                        $sort   = ! empty( $table_sortable );
                        $th_class = 'tmp-th';
                        if ( $sort )   $th_class .= ' tmp-sortable';
                    ?>
                        <th class="<?php echo esc_attr( $th_class ); ?>"
                            data-col-id="<?php echo esc_attr( $col->id ); ?>"
                            data-col-type="<?php echo esc_attr( $col->type ); ?>"
                            <?php echo $sort ? 'role="columnheader" aria-sort="none" tabindex="0"' : ''; ?>>
                            <?php echo wp_kses_post( $col->label ); ?>
                            <?php if ( $sort ) : ?>
                                <span class="tmp-sort-icon" aria-hidden="true"></span>
                            <?php endif; ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            <?php elseif ( $max_depth === 2 ) : ?>
                <tr class="tmp-header-row">
                    <?php if ( $collapsible ) : ?>
                        <th class="tmp-toggle-col" aria-hidden="true"></th>
                    <?php endif; ?>
                    <?php
                    $prev_g1 = null;
                    $col_count_total = count( $col_meta );
                    foreach ( $col_meta as $idx => $cm ) :
                        if ( $cm['g1'] === '' ) :
                            $ug_sort = ! empty( $table_sortable );
                            $ug_class = 'tmp-th';
                            if ( $ug_sort ) $ug_class .= ' tmp-sortable';
                        ?>
                            <th class="<?php echo esc_attr( $ug_class ); ?>"
                                data-col-id="<?php echo esc_attr( $cm['col']->id ); ?>"
                                data-col-type="<?php echo esc_attr( $cm['col']->type ); ?>"
                                <?php echo $ug_sort ? 'role="columnheader" aria-sort="none" tabindex="0"' : ''; ?>>
                                <?php echo wp_kses_post( $cm['col']->label ); ?>
                                <?php if ( $ug_sort ) : ?><span class="tmp-sort-icon" aria-hidden="true"></span><?php endif; ?>
                            </th>
                        <?php elseif ( $cm['g1'] !== $prev_g1 ) :
                            $g1_colspan = 1;
                            for ( $j = $idx + 1; $j < $col_count_total; $j++ ) {
                                if ( $col_meta[ $j ]['g1'] === $cm['g1'] ) $g1_colspan++;
                                else break;
                            }
                            $g1_sort = ! empty( $table_sortable );
                            $g1_class = 'tmp-th';
                            if ( $g1_sort ) $g1_class .= ' tmp-sortable';
                        ?>
                            <th class="<?php echo esc_attr( $g1_class ); ?>" colspan="<?php echo intval( $g1_colspan ); ?>"
                                data-col-id="<?php echo esc_attr( $cm['col']->id ); ?>"
                                data-col-type="<?php echo esc_attr( $cm['col']->type ); ?>"
                                <?php echo $g1_sort ? 'role="columnheader" aria-sort="none" tabindex="0"' : ''; ?>>
                                <?php echo wp_kses_post( $cm['g1'] ); ?>
                                <?php if ( $g1_sort ) : ?><span class="tmp-sort-icon" aria-hidden="true"></span><?php endif; ?>
                            </th>
                        <?php endif;
                        $prev_g1 = $cm['g1'];
                    endforeach; ?>
                </tr>
            <?php else : ?>
                <?php
                $g1_has_g2 = array();
                foreach ( $col_meta as $cm ) {
                    if ( $cm['g1'] !== '' && $cm['g2'] !== '' ) {
                        $g1_has_g2[ $cm['g1'] ] = true;
                    }
                }
                $rows_below = ( $max_depth - 1 );
                ?>
                <tr class="tmp-header-row tmp-header-row-1">
                    <?php if ( $collapsible ) : ?>
                        <th class="tmp-toggle-col" rowspan="<?php echo $max_depth; ?>" aria-hidden="true"></th>
                    <?php endif; ?>
                    <?php
                    $prev_g1 = null;
                    $col_count_total = count( $col_meta );
                    foreach ( $col_meta as $idx => $cm ) :
                        if ( $cm['g1'] === '' ) :
                            $ug_sort = ! empty( $table_sortable );
                            $ug_class = 'tmp-th tmp-th-grouped';
                            if ( $ug_sort ) $ug_class .= ' tmp-sortable';
                        ?>
                            <th class="<?php echo esc_attr( $ug_class ); ?>" rowspan="<?php echo $max_depth; ?>"
                                data-col-id="<?php echo esc_attr( $cm['col']->id ); ?>"
                                data-col-type="<?php echo esc_attr( $cm['col']->type ); ?>"
                                <?php echo $ug_sort ? 'role="columnheader" aria-sort="none" tabindex="0"' : ''; ?>>
                                <?php echo wp_kses_post( $cm['col']->label ); ?>
                                <?php if ( $ug_sort ) : ?><span class="tmp-sort-icon" aria-hidden="true"></span><?php endif; ?>
                            </th>
                        <?php elseif ( $cm['g1'] !== $prev_g1 ) :
                            $g1_colspan = 1;
                            for ( $j = $idx + 1; $j < $col_count_total; $j++ ) {
                                if ( $col_meta[ $j ]['g1'] === $cm['g1'] ) $g1_colspan++;
                                else break;
                            }
                            $g1_no_sub = ! isset( $g1_has_g2[ $cm['g1'] ] );
                            $g1_sort   = $g1_no_sub && ! empty( $table_sortable );
                            $g1_class  = 'tmp-th tmp-th-grouped';
                            if ( $g1_sort ) $g1_class .= ' tmp-sortable';
                        ?>
                            <th class="<?php echo esc_attr( $g1_class ); ?>"
                                colspan="<?php echo intval( $g1_colspan ); ?>"
                                <?php if ( $g1_no_sub ) : ?>rowspan="<?php echo intval( $rows_below + 1 ); ?>"<?php endif; ?>
                                <?php if ( $g1_no_sub ) : ?>data-col-id="<?php echo esc_attr( $cm['col']->id ); ?>"<?php endif; ?>
                                <?php if ( $g1_no_sub ) : ?>data-col-type="<?php echo esc_attr( $cm['col']->type ); ?>"<?php endif; ?>
                                <?php echo $g1_sort ? 'role="columnheader" aria-sort="none" tabindex="0"' : ''; ?>>
                                <?php echo wp_kses_post( $cm['g1'] ); ?>
                                <?php if ( $g1_sort ) : ?><span class="tmp-sort-icon" aria-hidden="true"></span><?php endif; ?>
                            </th>
                        <?php endif;
                        $prev_g1 = $cm['g1'];
                    endforeach; ?>
                </tr>
                <tr class="tmp-header-row tmp-header-row-2">
                    <?php
                    $prev_g2_key = null;
                    foreach ( $col_meta as $idx => $cm ) :
                        if ( $cm['g1'] === '' ) continue;
                        if ( ! isset( $g1_has_g2[ $cm['g1'] ] ) ) continue;
                        $sort_r2 = ! empty( $table_sortable );
                        if ( $cm['g2'] === '' ) :
                            $th2_class = 'tmp-th tmp-th-grouped';
                            if ( $sort_r2 ) $th2_class .= ' tmp-sortable';
                        ?>
                            <th class="<?php echo esc_attr( $th2_class ); ?>" rowspan="2"
                                data-col-id="<?php echo esc_attr( $cm['col']->id ); ?>"
                                data-col-type="<?php echo esc_attr( $cm['col']->type ); ?>"
                                <?php echo $sort_r2 ? 'role="columnheader" aria-sort="none" tabindex="0"' : ''; ?>>
                                <?php echo wp_kses_post( $cm['col']->label ); ?>
                                <?php if ( $sort_r2 ) : ?><span class="tmp-sort-icon" aria-hidden="true"></span><?php endif; ?>
                            </th>
                        <?php else :
                            $g2_key = $cm['g1'] . '|||' . $cm['g2'];
                            if ( $g2_key !== $prev_g2_key ) :
                                $g2_colspan = 1;
                                for ( $j = $idx + 1; $j < $col_count_total; $j++ ) {
                                    $j_g2_key = $col_meta[ $j ]['g1'] . '|||' . $col_meta[ $j ]['g2'];
                                    if ( $j_g2_key === $g2_key ) $g2_colspan++;
                                    else break;
                                }
                        ?>
                            <th class="tmp-th tmp-th-grouped" colspan="<?php echo intval( $g2_colspan ); ?>"><?php echo wp_kses_post( $cm['g2'] ); ?></th>
                        <?php endif;
                            $prev_g2_key = $g2_key;
                        endif;
                    endforeach; ?>
                </tr>
                <tr class="tmp-header-row tmp-header-row-3">
                    <?php foreach ( $col_meta as $idx => $cm ) :
                        if ( $cm['g1'] === '' ) continue;
                        if ( ! isset( $g1_has_g2[ $cm['g1'] ] ) ) continue;
                        if ( $cm['g2'] === '' ) continue;
                        $sort   = ! empty( $table_sortable );
                        $th_class = 'tmp-th tmp-th-grouped';
                        if ( $sort )   $th_class .= ' tmp-sortable';
                    ?>
                        <th class="<?php echo esc_attr( $th_class ); ?>"
                            data-col-id="<?php echo esc_attr( $cm['col']->id ); ?>"
                            data-col-type="<?php echo esc_attr( $cm['col']->type ); ?>"
                            <?php echo $sort ? 'role="columnheader" aria-sort="none" tabindex="0"' : ''; ?>>
                            <?php echo wp_kses_post( $cm['col']->label ); ?>
                            <?php if ( $sort ) : ?>
                                <span class="tmp-sort-icon" aria-hidden="true"></span>
                            <?php endif; ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            <?php endif; ?>
            </thead>
            <?php ?>
            <tbody class="tmp-tbody">
                <?php
                $data_row_index = 0;
                foreach ( $rows as $row ) :
                    $row_class   = 'tmp-row tmp-type-' . esc_attr( $row->row_type );
                    $is_group    = in_array( $row->row_type, array( 'group_1', 'group_2', 'group_3' ), true );
                    $is_footer   = $row->row_type === 'footer';
                    $indent_lvl  = 0;
                    switch ( $row->row_type ) {
                        case 'group_2': $indent_lvl = 1; break;
                        case 'group_3': $indent_lvl = 2; break;
                        case 'data':    $indent_lvl = 0; break;
                    }

                    if ( $row->row_type === 'data' ) {
                        $row_class .= $data_row_index % 2 === 0 ? ' tmp-odd' : ' tmp-even';
                        $data_row_index++;
                    }
                ?>
                    <tr class="<?php echo esc_attr( $row_class ); ?>"
                        data-row-id="<?php echo esc_attr( $row->id ); ?>"
                        data-row-type="<?php echo esc_attr( $row->row_type ); ?>"
                        data-parent-id="<?php echo esc_attr( $row->parent_id ?? '' ); ?>"
                        <?php echo $row->is_collapsed ? 'data-collapsed="1"' : ''; ?>>

                        <?php if ( $is_footer ) :
                            $total_cols_footer = count( $columns );
                            if ( $collapsible ) $total_cols_footer++;
                            $footer_content_parts = array();
                            $footer_align = '';
                            foreach ( $columns as $fcol ) {
                                $fc_raw = $row->cells[ $fcol->id ] ?? '';
                                $fc = trim( strip_tags( str_replace( array( '&nbsp;', "\xC2\xA0" ), ' ', $fc_raw ) ) );
                                if ( $fc !== '' ) {
                                    $footer_content_parts[] = $fc_raw;
                                    if ( $footer_align === '' && isset( $row->cell_aligns[ $fcol->id ] ) && in_array( $row->cell_aligns[ $fcol->id ], $valid_aligns, true ) ) {
                                        $footer_align = $row->cell_aligns[ $fcol->id ];
                                    }
                                }
                            }
                            $footer_label = implode( ' ', $footer_content_parts );
                            $footer_align_style = $footer_align !== '' ? 'text-align:' . esc_attr( $footer_align ) . ';' : '';
                        ?>
                            <?php if ( $sticky_first_col && $total_cols_footer > 1 ) : ?>
                                <td class="tmp-td tmp-footer-cell tmp-sticky-label"<?php echo $footer_align_style ? ' style="' . esc_attr( $footer_align_style ) . '"' : ''; ?>>
                                    <?php echo wp_kses_post( $footer_label ); ?>
                                </td>
                                <td class="tmp-td tmp-footer-cell" colspan="<?php echo esc_attr( $total_cols_footer - 1 ); ?>"></td>
                            <?php else : ?>
                                <td class="tmp-td tmp-footer-cell" colspan="<?php echo esc_attr( $total_cols_footer ); ?>"<?php echo $footer_align_style ? ' style="' . esc_attr( $footer_align_style ) . '"' : ''; ?>>
                                    <?php echo wp_kses_post( $footer_label ); ?>
                                </td>
                            <?php endif; ?>
                        <?php elseif ( $is_group ) :
                            $filled_count   = 0;
                            $first_filled   = '';
                            $group_align    = '';
                            $filled_cells   = array();
                            foreach ( $columns as $gcol ) {
                                $gc_raw = $row->cells[ $gcol->id ] ?? '';
                                $gc_text = trim( strip_tags( str_replace( array( '&nbsp;', "\xC2\xA0" ), ' ', $gc_raw ) ) );
                                if ( $gc_text !== '' ) {
                                    $filled_count++;
                                    $filled_cells[ $gcol->id ] = $gc_raw;
                                    if ( $first_filled === '' ) {
                                        $first_filled = $gc_raw;
                                        if ( isset( $row->cell_aligns[ $gcol->id ] ) && in_array( $row->cell_aligns[ $gcol->id ], $valid_aligns, true ) ) {
                                            $group_align = $row->cell_aligns[ $gcol->id ];
                                        }
                                    }
                                }
                            }
                            $g1_has_multi_content = false;
                            if ( $row->row_type === 'group_1' && $filled_count > 1 ) {
                                $g1_has_multi_content = true;
                            }
                            $use_colspan = ( ( $row->row_type === 'group_1' && ! $g1_has_multi_content ) || $filled_count <= 1 );
                            $group_align_style = $group_align !== '' ? ' style="text-align:' . esc_attr( $group_align ) . ';"' : '';

                            if ( $use_colspan ) :
                                $total_cols = count( $columns );
                                if ( $collapsible ) $total_cols++;
                        ?>
                            <?php if ( $sticky_first_col && $total_cols > 1 ) : ?>
                                <td class="tmp-td tmp-group-cell tmp-sticky-label"<?php echo $group_align_style; ?>>
                                    <div class="tmp-group-cell-inner">
                                        <?php if ( $collapsible ) : ?>
                                            <button class="tmp-toggle-btn" aria-expanded="<?php echo $row->is_collapsed ? 'false' : 'true'; ?>" aria-label="<?php esc_attr_e( 'In-/uitklappen', TMP_TEXT_DOMAIN ); ?>">
                                                <span class="tmp-toggle-icon"><?php echo $row->is_collapsed ? '▶' : '▼'; ?></span>
                                            </button>
                                        <?php endif; ?>
                                        <span class="tmp-group-label"><?php echo wp_kses_post( $first_filled ); ?></span>
                                    </div>
                                </td>
                                <td class="tmp-td tmp-group-cell" colspan="<?php echo esc_attr( $total_cols - 1 ); ?>"></td>
                            <?php else : ?>
                                <td class="tmp-td tmp-group-cell" colspan="<?php echo esc_attr( $total_cols ); ?>"<?php echo $group_align_style; ?>>
                                    <div class="tmp-group-cell-inner">
                                        <?php if ( $collapsible ) : ?>
                                            <button class="tmp-toggle-btn" aria-expanded="<?php echo $row->is_collapsed ? 'false' : 'true'; ?>" aria-label="<?php esc_attr_e( 'In-/uitklappen', TMP_TEXT_DOMAIN ); ?>">
                                                <span class="tmp-toggle-icon"><?php echo $row->is_collapsed ? '▶' : '▼'; ?></span>
                                            </button>
                                        <?php endif; ?>
                                        <span class="tmp-group-label"><?php echo wp_kses_post( $first_filled ); ?></span>
                                    </div>
                                </td>
                            <?php endif; ?>
                            <?php else :
                                if ( $collapsible ) : ?>
                                    <td class="tmp-toggle-cell">
                                        <button class="tmp-toggle-btn" aria-expanded="<?php echo $row->is_collapsed ? 'false' : 'true'; ?>" aria-label="<?php esc_attr_e( 'In-/uitklappen', TMP_TEXT_DOMAIN ); ?>">
                                            <span class="tmp-toggle-icon"><?php echo $row->is_collapsed ? '▶' : '▼'; ?></span>
                                        </button>
                                    </td>
                                <?php endif;
                                $has_explicit_merges = false;
                                if ( ! empty( $row->cell_merges ) ) {
                                    foreach ( $row->cell_merges as $_cm_v ) {
                                        if ( intval( $_cm_v ) > 1 ) { $has_explicit_merges = true; break; }
                                    }
                                }
                                if ( $has_explicit_merges ) :
                                    $grp_skip = 0;
                                    $grp_col_total = count( $columns );
                                    foreach ( $columns as $grp_col_idx => $grp_col ) :
                                        if ( $grp_skip > 0 ) { $grp_skip--; continue; }
                                        $grp_remaining = $grp_col_total - $grp_col_idx;
                                        $grp_cspan = isset( $row->cell_merges[ $grp_col->id ] ) ? min( max( 1, intval( $row->cell_merges[ $grp_col->id ] ) ), $grp_remaining ) : 1;
                                        if ( $grp_cspan > 1 ) $grp_skip = $grp_cspan - 1;
                                        $grp_content = $row->cells[ $grp_col->id ] ?? '';
                                        $grp_cell_align = isset( $row->cell_aligns[ $grp_col->id ] ) && in_array( $row->cell_aligns[ $grp_col->id ], $valid_aligns, true ) ? $row->cell_aligns[ $grp_col->id ] : '';
                                        $grp_cs = json_decode( $grp_col->settings, true );
                                        if ( ! is_array( $grp_cs ) ) $grp_cs = array();
                                        $grp_col_align = in_array( $grp_cs['align'] ?? 'left', $valid_aligns, true ) ? $grp_cs['align'] : 'left';
                                        $grp_align = $grp_cell_align !== '' ? $grp_cell_align : $grp_col_align;
                                    ?>
                                        <td class="tmp-td tmp-group-cell"
                                            style="text-align:<?php echo esc_attr( $grp_align ); ?>;"
                                            <?php if ( $grp_cspan > 1 ) : ?>colspan="<?php echo intval( $grp_cspan ); ?>"<?php endif; ?>>
                                            <?php echo wp_kses_post( $grp_content ); ?>
                                        </td>
                                    <?php endforeach;
                                else :
                                    $auto_cells = array();
                                    foreach ( $columns as $ac_col ) {
                                        $ac_raw = $row->cells[ $ac_col->id ] ?? '';
                                        $ac_has_text = trim( strip_tags( str_replace( array( '&nbsp;', "\xC2\xA0" ), ' ', $ac_raw ) ) ) !== '';
                                        $ac_cell_align = isset( $row->cell_aligns[ $ac_col->id ] ) && in_array( $row->cell_aligns[ $ac_col->id ], $valid_aligns, true ) ? $row->cell_aligns[ $ac_col->id ] : '';
                                        $ac_cs = json_decode( $ac_col->settings, true );
                                        if ( ! is_array( $ac_cs ) ) $ac_cs = array();
                                        $ac_col_align = in_array( $ac_cs['align'] ?? 'left', $valid_aligns, true ) ? $ac_cs['align'] : 'left';
                                        if ( $ac_has_text ) {
                                            $auto_cells[] = array(
                                                'content' => $ac_raw,
                                                'cols'    => 1,
                                                'align'   => $ac_cell_align !== '' ? $ac_cell_align : $ac_col_align,
                                            );
                                        } elseif ( ! empty( $auto_cells ) ) {
                                            $auto_cells[ count( $auto_cells ) - 1 ]['cols']++;
                                        } else {
                                            $auto_cells[] = array(
                                                'content' => '',
                                                'cols'    => 1,
                                                'align'   => $ac_col_align,
                                            );
                                        }
                                    }
                                    foreach ( $auto_cells as $ac ) : ?>
                                        <td class="tmp-td tmp-group-cell"
                                            style="text-align:<?php echo esc_attr( $ac['align'] ); ?>;"
                                            <?php if ( $ac['cols'] > 1 ) : ?>colspan="<?php echo intval( $ac['cols'] ); ?>"<?php endif; ?>>
                                            <?php echo wp_kses_post( $ac['content'] ); ?>
                                        </td>
                                    <?php endforeach;
                                endif;
                            endif; ?>
                        <?php else : ?>
                            <?php if ( $collapsible ) : ?>
                                <td class="tmp-toggle-cell">&nbsp;</td>
                            <?php endif; ?>

                            <?php
                            $data_skip = 0;
                            $data_col_total = count( $columns );
                            foreach ( $columns as $data_col_idx => $col ) :
                                if ( $data_skip > 0 ) { $data_skip--; continue; }
                                $cs       = json_decode( $col->settings, true );
                                $col_align = in_array( $cs['align'] ?? 'left', $valid_aligns, true ) ? $cs['align'] : 'left';
                                $cell_align_val = isset( $row->cell_aligns[ $col->id ] ) && in_array( $row->cell_aligns[ $col->id ], $valid_aligns, true ) ? $row->cell_aligns[ $col->id ] : '';
                                $align    = $cell_align_val !== '' ? $cell_align_val : $col_align;
                                $col_type = sanitize_text_field( $col->type );
                                $td_class = 'tmp-td';
                                $remaining = $data_col_total - $data_col_idx;
                                $cell_colspan = isset( $row->cell_merges[ $col->id ] ) ? min( max( 1, intval( $row->cell_merges[ $col->id ] ) ), $remaining ) : 1;
                                if ( $cell_colspan > 1 ) $data_skip = $cell_colspan - 1;

                                $raw_content = $row->cells[$col->id] ?? '';
                            ?>
                                <td class="<?php echo esc_attr( $td_class ); ?>"
                                    style="text-align:<?php echo esc_attr( $align ); ?>;"
                                    data-col-id="<?php echo esc_attr( $col->id ); ?>"
                                    data-col-type="<?php echo esc_attr( $col_type ); ?>"
                                    data-label="<?php echo esc_attr( wp_strip_all_tags( $col->label ) ); ?>"
                                    <?php if ( $cell_colspan > 1 ) : ?>colspan="<?php echo intval( $cell_colspan ); ?>"<?php endif; ?>>
                                    <?php if ( $col_type === 'link' ) : ?>
                                        <?php if ( filter_var( $raw_content, FILTER_VALIDATE_URL ) ) : ?>
                                            <a href="<?php echo esc_url( $raw_content ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $raw_content ); ?></a>
                                        <?php else : ?>
                                            <?php echo wp_kses_post( $raw_content ); ?>
                                        <?php endif; ?>
                                    <?php elseif ( $col_type === 'image' ) : ?>
                                        <?php if ( $raw_content ) : ?>
                                            <img src="<?php echo esc_url( $raw_content ); ?>" alt="" class="tmp-cell-image" loading="lazy">
                                        <?php endif; ?>
                                    <?php elseif ( $col_type === 'html' && $inline_html ) : ?>
                                        <?php echo wp_kses_post( $raw_content ); ?>
                                    <?php else : ?>
                                        <?php echo wp_kses_post( $raw_content ); ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table></div>

    <div class="tmp-controls tmp-controls-bottom">
        <?php if ( $show_pagination ) : ?>
            <span class="tmp-info-text"></span>
            <nav class="tmp-pagination" aria-label="<?php esc_attr_e( 'Tabel paginering', TMP_TEXT_DOMAIN ); ?>"></nav>
        <?php endif; ?>
    </div>

    <?php if ( $show_search && $search_pos === 'bottom' ) : ?>
        <div class="tmp-controls tmp-search-center" style="margin-top:0.75em;">
            <div class="tmp-search-wrap">
                <label class="screen-reader-text" for="<?php echo esc_attr( $table_uid ); ?>-search"><?php esc_html_e( 'Zoeken', TMP_TEXT_DOMAIN ); ?></label>
                <input type="search" id="<?php echo esc_attr( $table_uid ); ?>-search" class="tmp-search" placeholder="<?php esc_attr_e( 'Zoeken…', TMP_TEXT_DOMAIN ); ?>">
            </div>
        </div>
    <?php endif; ?>

    <?php
    $export_enabled = ! empty( $settings['enable_export'] );
    if ( $export_enabled ) :
    ?>
    <div class="tmp-export-bar">
        <button class="button tmp-export-btn" data-format="csv"><?php esc_html_e( 'CSV exporteren', TMP_TEXT_DOMAIN ); ?></button>
        <button class="button tmp-export-btn" data-format="print"><?php esc_html_e( 'Afdrukken', TMP_TEXT_DOMAIN ); ?></button>
    </div>
    <?php endif; ?>

</div><!-- end tmp-wrapper -->
