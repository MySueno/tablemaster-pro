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
$per_page          = min( 500, max( -1, intval( $settings['per_page'] ?? 10 ) ) );
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
        <?php foreach ( $columns as $col ) :
            $col_settings = json_decode( $col->settings, true );
            if ( empty( $col_settings['filterable'] ) ) continue;
        ?>
            <div class="tmp-col-filter-item">
                <label><?php echo esc_html( $col->label ); ?></label>
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
    <div class="tmp-table-scroll-wrapper">
        <table class="tmp-table" role="grid" aria-label="<?php echo esc_attr( $caption ?: $table->name ); ?>"
            <?php if ( $table_style ) : ?> style="<?php echo esc_attr( $table_style ); ?>"<?php endif; ?>>
            <?php
            $has_groups = false;
            $col_meta   = array();
            foreach ( $columns as $col ) {
                $cs = json_decode( $col->settings, true );
                $g1 = trim( $cs['header_group1'] ?? '' );
                $g2 = trim( $cs['header_group2'] ?? '' );
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
            <thead>
            <?php if ( $max_depth === 1 ) : ?>
                <tr class="tmp-header-row">
                    <?php if ( $collapsible ) : ?>
                        <th class="tmp-toggle-col" aria-hidden="true"></th>
                    <?php endif; ?>
                    <?php
                    $valid_aligns    = array( 'left', 'center', 'right' );
                    $default_col_w   = $settings['default_col_width'] ?? '';
                    if ( $default_col_w !== '' && ! preg_match( '/^\d{1,4}(px|em|rem|%)$/', $default_col_w ) ) $default_col_w = '';
                    foreach ( $col_meta as $cm ) :
                        $cs     = $cm['cs'];
                        $col    = $cm['col'];
                        $width  = $cs['width'] ?? 'auto';
                        if ( $width !== 'auto' && ! preg_match( '/^\d{1,4}(px|em|rem|%)$/', $width ) ) $width = 'auto';
                        if ( $width === 'auto' && $default_col_w !== '' ) $width = $default_col_w;
                        $align  = in_array( $cs['align'] ?? 'left', $valid_aligns, true ) ? $cs['align'] : 'left';
                        $sort   = ! empty( $cs['sortable'] );
                        $th_class = 'tmp-th';
                        if ( $sort )   $th_class .= ' tmp-sortable';
                        $style  = '';
                        if ( $width !== 'auto' ) $style = 'width:' . esc_attr( $width ) . ';';
                        $style .= 'text-align:' . esc_attr( $align ) . ';';
                    ?>
                        <th class="<?php echo esc_attr( $th_class ); ?>"
                            style="<?php echo esc_attr( $style ); ?>"
                            data-col-id="<?php echo esc_attr( $col->id ); ?>"
                            data-col-type="<?php echo esc_attr( $col->type ); ?>"
                            <?php echo $sort ? 'role="columnheader" aria-sort="none" tabindex="0"' : ''; ?>>
                            <?php echo esc_html( $col->label ); ?>
                            <?php if ( $sort ) : ?>
                                <span class="tmp-sort-icon" aria-hidden="true"></span>
                            <?php endif; ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            <?php else :
                $g1_groups = array();
                $g2_groups = array();
                $ungrouped = array();
                foreach ( $col_meta as $idx => $cm ) {
                    if ( $cm['g1'] === '' ) {
                        $ungrouped[] = $idx;
                    } else {
                        $g1_groups[ $cm['g1'] ][] = $idx;
                        if ( $cm['g2'] !== '' ) {
                            $g2_groups[ $cm['g1'] . '|||' . $cm['g2'] ][] = $idx;
                        }
                    }
                }
            ?>
                <tr class="tmp-header-row tmp-header-row-1">
                    <?php if ( $collapsible ) : ?>
                        <th class="tmp-toggle-col" rowspan="<?php echo $max_depth; ?>" aria-hidden="true"></th>
                    <?php endif; ?>
                    <?php
                    $prev_g1 = null;
                    foreach ( $col_meta as $idx => $cm ) :
                        if ( $cm['g1'] === '' ) :
                            $uw  = $cm['cs']['width'] ?? 'auto';
                            if ( $uw !== 'auto' && ! preg_match( '/^\d{1,4}(px|em|rem|%)$/', $uw ) ) $uw = 'auto';
                            if ( $uw === 'auto' && $default_col_w !== '' ) $uw = $default_col_w;
                            $us  = $uw !== 'auto' ? 'width:' . esc_attr( $uw ) . ';' : '';
                        ?>
                            <th class="tmp-th tmp-th-grouped" rowspan="<?php echo $max_depth; ?>"<?php if ( $us ) echo ' style="' . esc_attr( $us ) . '"'; ?>><?php echo esc_html( $cm['col']->label ); ?></th>
                        <?php elseif ( $cm['g1'] !== $prev_g1 ) :
                            $colspan = count( $g1_groups[ $cm['g1'] ] );
                        ?>
                            <th class="tmp-th tmp-th-grouped" colspan="<?php echo $colspan; ?>"><?php echo esc_html( $cm['g1'] ); ?></th>
                        <?php endif;
                        $prev_g1 = $cm['g1'];
                    endforeach; ?>
                </tr>
                <?php if ( $max_depth >= 2 ) : ?>
                <tr class="tmp-header-row tmp-header-row-2">
                    <?php
                    $prev_g2_key = null;
                    foreach ( $col_meta as $idx => $cm ) :
                        if ( $cm['g1'] === '' ) continue;
                        $lw  = $cm['cs']['width'] ?? 'auto';
                        if ( $lw !== 'auto' && ! preg_match( '/^\d{1,4}(px|em|rem|%)$/', $lw ) ) $lw = 'auto';
                        if ( $lw === 'auto' && $default_col_w !== '' ) $lw = $default_col_w;
                        $ls  = $lw !== 'auto' ? 'width:' . esc_attr( $lw ) . ';' : '';
                        if ( $cm['g2'] === '' && $max_depth === 2 ) : ?>
                            <th class="tmp-th tmp-th-grouped"
                                data-col-id="<?php echo esc_attr( $cm['col']->id ); ?>"
                                data-col-type="<?php echo esc_attr( $cm['col']->type ); ?>"
                                <?php if ( $ls ) echo 'style="' . esc_attr( $ls ) . '"'; ?>>
                                <?php echo esc_html( $cm['col']->label ); ?>
                            </th>
                        <?php elseif ( $cm['g2'] === '' && $max_depth === 3 ) : ?>
                            <th class="tmp-th tmp-th-grouped" rowspan="2"
                                data-col-id="<?php echo esc_attr( $cm['col']->id ); ?>"
                                data-col-type="<?php echo esc_attr( $cm['col']->type ); ?>"
                                <?php if ( $ls ) echo 'style="' . esc_attr( $ls ) . '"'; ?>>
                                <?php echo esc_html( $cm['col']->label ); ?>
                            </th>
                        <?php else :
                            $g2_key = $cm['g1'] . '|||' . $cm['g2'];
                            if ( $g2_key !== $prev_g2_key ) :
                                $g2_colspan = count( $g2_groups[ $g2_key ] );
                        ?>
                            <th class="tmp-th tmp-th-grouped" colspan="<?php echo $g2_colspan; ?>"><?php echo esc_html( $cm['g2'] ); ?></th>
                        <?php endif;
                            $prev_g2_key = $g2_key;
                        endif;
                    endforeach; ?>
                </tr>
                <?php endif; ?>
                <?php if ( $max_depth === 3 ) : ?>
                <tr class="tmp-header-row tmp-header-row-3">
                    <?php foreach ( $col_meta as $idx => $cm ) :
                        if ( $cm['g1'] === '' ) continue;
                        if ( $cm['g2'] === '' ) continue;
                        $cs     = $cm['cs'];
                        $sort   = ! empty( $cs['sortable'] );
                        $lw3  = $cs['width'] ?? 'auto';
                        if ( $lw3 !== 'auto' && ! preg_match( '/^\d{1,4}(px|em|rem|%)$/', $lw3 ) ) $lw3 = 'auto';
                        if ( $lw3 === 'auto' && $default_col_w !== '' ) $lw3 = $default_col_w;
                        $ls3  = $lw3 !== 'auto' ? 'width:' . esc_attr( $lw3 ) . ';' : '';
                        $th_class = 'tmp-th tmp-th-grouped';
                        if ( $sort )   $th_class .= ' tmp-sortable';
                    ?>
                        <th class="<?php echo esc_attr( $th_class ); ?>"
                            data-col-id="<?php echo esc_attr( $cm['col']->id ); ?>"
                            data-col-type="<?php echo esc_attr( $cm['col']->type ); ?>"
                            <?php if ( $ls3 ) echo 'style="' . esc_attr( $ls3 ) . '"'; ?>
                            <?php echo $sort ? 'role="columnheader" aria-sort="none" tabindex="0"' : ''; ?>>
                            <?php echo esc_html( $cm['col']->label ); ?>
                            <?php if ( $sort ) : ?>
                                <span class="tmp-sort-icon" aria-hidden="true"></span>
                            <?php endif; ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
                <?php endif; ?>
            <?php endif; ?>
            </thead>
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
                            foreach ( $columns as $fcol ) {
                                $fc = trim( $row->cells[ $fcol->id ] ?? '' );
                                if ( $fc !== '' ) $footer_content_parts[] = $fc;
                            }
                            $footer_label = implode( ' ', $footer_content_parts );
                        ?>
                            <td class="tmp-td tmp-footer-cell" colspan="<?php echo esc_attr( $total_cols_footer ); ?>">
                                <?php echo wp_kses_post( $footer_label ); ?>
                            </td>
                        <?php elseif ( $is_group ) :
                            $group_cells = array();
                            foreach ( $columns as $gi => $gcol ) {
                                $group_cells[] = array(
                                    'col'     => $gcol,
                                    'content' => trim( $row->cells[ $gcol->id ] ?? '' ),
                                    'index'   => $gi,
                                );
                            }

                            $has_filled = false;
                            foreach ( $group_cells as $gc ) {
                                if ( $gc['content'] !== '' ) { $has_filled = true; break; }
                            }
                            $filled_count = 0;
                            foreach ( $group_cells as $gc ) {
                                if ( $gc['content'] !== '' ) $filled_count++;
                            }

                            if ( $filled_count <= 1 || $row->row_type === 'group_1' ) :
                                $total_cols = count( $columns );
                                if ( $collapsible ) $total_cols++;
                                $group_label = '';
                                foreach ( $group_cells as $gc ) {
                                    if ( $gc['content'] !== '' ) { $group_label = $gc['content']; break; }
                                }
                        ?>
                            <td class="tmp-td tmp-group-cell" colspan="<?php echo esc_attr( $total_cols ); ?>" style="text-align:left;padding-left:<?php echo ( $indent_lvl * 24 + 12 ); ?>px;">
                                <div class="tmp-group-cell-inner">
                                    <?php if ( $collapsible ) : ?>
                                        <button class="tmp-toggle-btn" aria-expanded="<?php echo $row->is_collapsed ? 'false' : 'true'; ?>" aria-label="<?php esc_attr_e( 'In-/uitklappen', TMP_TEXT_DOMAIN ); ?>">
                                            <span class="tmp-toggle-icon"><?php echo $row->is_collapsed ? '▶' : '▼'; ?></span>
                                        </button>
                                    <?php endif; ?>
                                    <span class="tmp-group-label"><?php echo wp_kses_post( $group_label ); ?></span>
                                </div>
                            </td>
                            <?php else :
                                if ( $collapsible ) : ?>
                                    <td class="tmp-toggle-cell">
                                        <button class="tmp-toggle-btn" aria-expanded="<?php echo $row->is_collapsed ? 'false' : 'true'; ?>" aria-label="<?php esc_attr_e( 'In-/uitklappen', TMP_TEXT_DOMAIN ); ?>">
                                            <span class="tmp-toggle-icon"><?php echo $row->is_collapsed ? '▶' : '▼'; ?></span>
                                        </button>
                                    </td>
                                <?php endif;

                                $col_arr = array_values( (array) $columns );
                                $total_c = count( $col_arr );

                                foreach ( $col_arr as $gi2 => $gcol2 ) :
                                    $gc_content = trim( $row->cells[ $gcol2->id ] ?? '' );
                                    $cs_g       = json_decode( $gcol2->settings, true );
                                    $align_g    = $cs_g['align'] ?? 'left';
                                    $pad        = '';
                            ?>
                                <td class="tmp-td tmp-group-cell"
                                    style="text-align:<?php echo esc_attr( $align_g ); ?>;<?php echo $pad; ?>">
                                    <?php echo wp_kses_post( $gc_content ); ?>
                                </td>
                            <?php endforeach;
                            endif; ?>
                        <?php else : ?>
                            <?php if ( $collapsible ) : ?>
                                <td class="tmp-toggle-cell">&nbsp;</td>
                            <?php endif; ?>

                            <?php foreach ( $columns as $col ) :
                                $cs       = json_decode( $col->settings, true );
                                $align    = in_array( $cs['align'] ?? 'left', $valid_aligns, true ) ? $cs['align'] : 'left';
                                $col_type = sanitize_text_field( $col->type );
                                $td_class = 'tmp-td';

                                $raw_content = $row->cells[$col->id] ?? '';
                            ?>
                                <td class="<?php echo esc_attr( $td_class ); ?>"
                                    style="text-align:<?php echo esc_attr( $align ); ?>;"
                                    data-col-id="<?php echo esc_attr( $col->id ); ?>"
                                    data-col-type="<?php echo esc_attr( $col_type ); ?>"
                                    data-label="<?php echo esc_attr( $col->label ); ?>">
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
        </table>
    </div>

    <div class="tmp-controls tmp-controls-bottom">
        <?php if ( $show_pagination ) : ?>
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
    $export_enabled = TableMaster_Settings::get( 'enable_export' );
    if ( $export_enabled ) :
    ?>
    <div class="tmp-export-bar">
        <button class="button tmp-export-btn" data-format="csv"><?php esc_html_e( 'CSV exporteren', TMP_TEXT_DOMAIN ); ?></button>
        <button class="button tmp-export-btn" data-format="print"><?php esc_html_e( 'Afdrukken', TMP_TEXT_DOMAIN ); ?></button>
    </div>
    <?php endif; ?>

</div><!-- end tmp-wrapper -->
