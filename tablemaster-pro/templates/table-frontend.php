<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$colors   = $settings['colors']         ?? array();
$table_uid = 'tmp-' . $table->id . '-' . wp_rand( 1000, 9999 );
$global_settings = TableMaster_Settings::get();
$border_radius   = intval( $global_settings['border_radius'] ?? 4 );

$header_bg    = esc_attr( $colors['header_bg']    ?? '#2e7d32' );
$header_text  = esc_attr( $colors['header_text']  ?? '#ffffff' );
$group1_bg    = esc_attr( $colors['group1_bg']    ?? '#4caf50' );
$group1_text  = esc_attr( $colors['group1_text']  ?? '#ffffff' );
$group2_bg    = esc_attr( $colors['group2_bg']    ?? '#81c784' );
$group2_text  = esc_attr( $colors['group2_text']  ?? '#1a1a1a' );
$group3_bg    = esc_attr( $colors['group3_bg']    ?? '#c8e6c9' );
$group3_text  = esc_attr( $colors['group3_text']  ?? '#1a1a1a' );
$odd_bg       = esc_attr( $colors['odd_bg']       ?? '#ffffff' );
$even_bg      = esc_attr( $colors['even_bg']      ?? '#f1f8e9' );
$hover_bg     = esc_attr( $colors['hover_bg']     ?? '#dcedc8' );
$border_color = esc_attr( $colors['border_color'] ?? '#a5d6a7' );
$accent_color = esc_attr( $colors['accent_color'] ?? '#2e7d32' );

$show_search       = ! empty( $settings['search'] );
$search_pos        = $settings['search_position']   ?? 'right';
$show_pagination   = ! empty( $settings['pagination'] );
$per_page          = intval( $settings['per_page']   ?? 10 );
$show_pp_selector  = ! empty( $settings['per_page_selector'] );
$collapsible       = ! empty( $settings['collapsible_groups'] );
$mobile_mode       = $settings['mobile_mode']        ?? 'scroll';
$show_col_filters  = ! empty( $settings['column_filters'] );
$caption           = $settings['caption']             ?? '';
$default_sort_col  = $settings['default_sort_col']    ?? '';
$default_sort_dir  = $settings['default_sort_dir']    ?? 'asc';
$inline_html       = ! empty( $settings['inline_html'] );
$sticky_first_col  = ! empty( $settings['sticky_first_col'] );

$columns = $data['columns'];
$rows    = $data['rows'];
?>
<style>
#<?php echo esc_attr( $table_uid ); ?> {
    --tmp-header-bg:    <?php echo $header_bg; ?>;
    --tmp-header-text:  <?php echo $header_text; ?>;
    --tmp-group1-bg:    <?php echo $group1_bg; ?>;
    --tmp-group1-text:  <?php echo $group1_text; ?>;
    --tmp-group2-bg:    <?php echo $group2_bg; ?>;
    --tmp-group2-text:  <?php echo $group2_text; ?>;
    --tmp-group3-bg:    <?php echo $group3_bg; ?>;
    --tmp-group3-text:  <?php echo $group3_text; ?>;
    --tmp-odd-bg:       <?php echo $odd_bg; ?>;
    --tmp-even-bg:      <?php echo $even_bg; ?>;
    --tmp-hover-bg:     <?php echo $hover_bg; ?>;
    --tmp-border:       <?php echo $border_color; ?>;
    --tmp-accent:       <?php echo $accent_color; ?>;
    --tmp-radius:       <?php echo $border_radius; ?>px;
}
</style>

<div id="<?php echo esc_attr( $table_uid ); ?>"
     class="tmp-wrapper tmp-mobile-<?php echo esc_attr( $mobile_mode ); ?><?php echo $sticky_first_col ? ' tmp-sticky-first' : ''; ?>"
     data-table-id="<?php echo esc_attr( $table->id ); ?>"
     data-per-page="<?php echo esc_attr( $per_page ); ?>"
     data-collapsible="<?php echo $collapsible ? '1' : '0'; ?>"
     data-default-sort-col="<?php echo esc_attr( $default_sort_col ); ?>"
     data-default-sort-dir="<?php echo esc_attr( $default_sort_dir ); ?>"
     data-mobile-mode="<?php echo esc_attr( $mobile_mode ); ?>">

    <?php if ( $caption ) : ?>
        <div class="tmp-caption"><?php echo esc_html( $caption ); ?></div>
    <?php endif; ?>

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

    <div class="tmp-table-scroll-wrapper">
        <table class="tmp-table" role="grid" aria-label="<?php echo esc_attr( $caption ?: $table->name ); ?>">
            <thead>
                <tr class="tmp-header-row">
                    <?php if ( $collapsible ) : ?>
                        <th class="tmp-toggle-col" aria-hidden="true"></th>
                    <?php endif; ?>
                    <?php foreach ( $columns as $col ) :
                        $cs      = json_decode( $col->settings, true );
                        $width   = $cs['width'] ?? 'auto';
                        $align   = $cs['align'] ?? 'left';
                        $sort    = ! empty( $cs['sortable'] );
                        $hide_m  = ! empty( $cs['hide_mobile'] );
                        $th_class = 'tmp-th';
                        if ( $sort )   $th_class .= ' tmp-sortable';
                        if ( $hide_m ) $th_class .= ' tmp-hide-mobile';
                        $style   = '';
                        if ( $width !== 'auto' ) $style = 'width:' . esc_attr( $width ) . ';';
                        $style  .= 'text-align:' . esc_attr( $align ) . ';';
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
            </thead>
            <tbody class="tmp-tbody">
                <?php
                $data_row_index = 0;
                foreach ( $rows as $row ) :
                    $row_class   = 'tmp-row tmp-type-' . esc_attr( $row->row_type );
                    $is_group    = in_array( $row->row_type, array( 'group_1', 'group_2', 'group_3' ), true );
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

                        <?php if ( $is_group ) :
                            $total_cols = count( $columns );
                            if ( $collapsible ) $total_cols++;
                            $first_col  = array_values( (array) $columns )[0] ?? null;
                            $group_label = $first_col ? ( $row->cells[ $first_col->id ] ?? '' ) : '';
                        ?>
                            <td class="tmp-td tmp-group-cell" colspan="<?php echo esc_attr( $total_cols ); ?>" style="padding-left:<?php echo ( $indent_lvl * 24 + 12 ); ?>px;">
                                <div class="tmp-group-cell-inner">
                                    <?php if ( $collapsible ) : ?>
                                        <button class="tmp-toggle-btn" aria-expanded="<?php echo $row->is_collapsed ? 'false' : 'true'; ?>" aria-label="<?php esc_attr_e( 'In-/uitklappen', TMP_TEXT_DOMAIN ); ?>">
                                            <span class="tmp-toggle-icon"><?php echo $row->is_collapsed ? '▶' : '▼'; ?></span>
                                        </button>
                                    <?php endif; ?>
                                    <span class="tmp-group-label"><?php echo esc_html( $group_label ); ?></span>
                                </div>
                            </td>
                        <?php else : ?>
                            <?php if ( $collapsible ) : ?>
                                <td class="tmp-toggle-cell">&nbsp;</td>
                            <?php endif; ?>

                            <?php foreach ( $columns as $col ) :
                                $cs       = json_decode( $col->settings, true );
                                $align    = $cs['align']       ?? 'left';
                                $hide_m   = ! empty( $cs['hide_mobile'] );
                                $col_type = $col->type;
                                $td_class = 'tmp-td';
                                if ( $hide_m ) $td_class .= ' tmp-hide-mobile';

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
        <div class="tmp-info-text" aria-live="polite" aria-atomic="true"></div>
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
