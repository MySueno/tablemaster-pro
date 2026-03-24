<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( esc_html__( 'Geen toegang.', TMP_TEXT_DOMAIN ) );

$table_id  = intval( $_GET['id'] ?? 0 );
$target_lang = sanitize_text_field( $_GET['lang'] ?? '' );

if ( ! $table_id ) wp_die( 'Geen tabel opgegeven.' );

$table = TableMaster_DB::get_table( $table_id );
if ( ! $table ) wp_die( 'Tabel niet gevonden.' );

$data = TableMaster_DB::get_table_data( $table_id, '' );
$columns = $data['columns'] ?? array();
$rows    = $data['rows'] ?? array();
$settings = json_decode( $table->settings, true );

$context = 'tablemaster-pro - Table ' . $table_id;

$active_langs = array();
if ( function_exists( 'icl_get_languages' ) ) {
    $langs = icl_get_languages( 'skip_missing=0' );
    if ( is_array( $langs ) ) {
        foreach ( $langs as $l ) {
            $active_langs[ $l['code'] ] = $l;
        }
    }
}

$default_lang = '';
if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
    $default_lang = ICL_LANGUAGE_CODE;
}
if ( function_exists( 'apply_filters' ) ) {
    $default_lang = apply_filters( 'wpml_default_language', $default_lang );
}

if ( ! $target_lang && count( $active_langs ) > 0 ) {
    foreach ( $active_langs as $code => $l ) {
        if ( $code !== $default_lang ) {
            $target_lang = $code;
            break;
        }
    }
}

$valid_target_langs = array_keys( $active_langs );
if ( $target_lang && ! in_array( $target_lang, $valid_target_langs, true ) ) {
    wp_die( 'Ongeldige doeltaal.' );
}

$source_name = isset( $active_langs[ $default_lang ] ) ? $active_langs[ $default_lang ]['native_name'] : $default_lang;
$target_name = isset( $active_langs[ $target_lang ] ) ? $active_langs[ $target_lang ]['native_name'] : $target_lang;
$source_flag = isset( $active_langs[ $default_lang ]['country_flag_url'] ) ? $active_langs[ $default_lang ]['country_flag_url'] : '';
$target_flag = isset( $active_langs[ $target_lang ]['country_flag_url'] ) ? $active_langs[ $target_lang ]['country_flag_url'] : '';

function tmp_get_translation( $context, $name, $lang ) {
    global $wpdb;
    if ( ! defined( 'WPML_ST_VERSION' ) ) return '';
    $string_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s",
        $context, $name
    ) );
    if ( ! $string_id ) return '';
    $translation = $wpdb->get_var( $wpdb->prepare(
        "SELECT value FROM {$wpdb->prefix}icl_string_translations WHERE string_id = %d AND language = %s AND status = 10",
        $string_id, $lang
    ) );
    return $translation !== null ? $translation : '';
}

$caption = ! empty( $settings['caption'] ) ? $settings['caption'] : '';
?>
<div class="wrap tmp-wrap">
    <h1>
        <?php printf( esc_html__( 'Vertaling: %s', TMP_TEXT_DOMAIN ), esc_html( $table->name ) ); ?>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=tablemaster-edit&id=' . $table_id ) ); ?>" class="page-title-action">
            &larr; <?php esc_html_e( 'Terug naar bewerken', TMP_TEXT_DOMAIN ); ?>
        </a>
    </h1>

    <div class="tmp-translate-header">
        <div class="tmp-translate-lang tmp-translate-source">
            <?php if ( $source_flag ) : ?><img src="<?php echo esc_url( $source_flag ); ?>" alt=""><?php endif; ?>
            <strong><?php esc_html_e( 'Origineel:', TMP_TEXT_DOMAIN ); ?></strong> <?php echo esc_html( $source_name ); ?>
        </div>
        <div class="tmp-translate-lang tmp-translate-target">
            <?php if ( count( $active_langs ) > 2 ) : ?>
                <strong><?php esc_html_e( 'Vertaling naar het:', TMP_TEXT_DOMAIN ); ?></strong>
                <select id="tmp-translate-lang-select">
                    <?php foreach ( $active_langs as $code => $l ) :
                        if ( $code === $default_lang ) continue;
                    ?>
                        <option value="<?php echo esc_attr( $code ); ?>" <?php selected( $code, $target_lang ); ?>>
                            <?php echo esc_html( $l['native_name'] ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php else : ?>
                <?php if ( $target_flag ) : ?><img src="<?php echo esc_url( $target_flag ); ?>" alt=""><?php endif; ?>
                <strong><?php esc_html_e( 'Vertaling naar het:', TMP_TEXT_DOMAIN ); ?></strong> <?php echo esc_html( $target_name ); ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="tmp-translate-table-wrap">
        <table class="tmp-translate-table">
            <thead>
                <tr>
                    <th class="tmp-translate-context"><?php esc_html_e( 'Veld', TMP_TEXT_DOMAIN ); ?></th>
                    <th class="tmp-translate-original"><?php echo esc_html( $source_name ); ?></th>
                    <th class="tmp-translate-translated"><?php echo esc_html( $target_name ); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr class="tmp-translate-section-header">
                    <td colspan="3"><strong><?php esc_html_e( 'Tabel', TMP_TEXT_DOMAIN ); ?></strong></td>
                </tr>
                <tr>
                    <td class="tmp-translate-context"><?php esc_html_e( 'Naam', TMP_TEXT_DOMAIN ); ?></td>
                    <td class="tmp-translate-original"><div class="tmp-translate-original-text"><?php echo esc_html( $table->name ); ?></div></td>
                    <td class="tmp-translate-translated">
                        <input type="text" class="tmp-translate-input" data-string-name="table_name"
                               value="<?php echo esc_attr( tmp_get_translation( $context, 'table_name', $target_lang ) ); ?>"
                               placeholder="<?php echo esc_attr( $table->name ); ?>">
                    </td>
                </tr>
                <?php if ( $caption !== '' ) : ?>
                <tr>
                    <td class="tmp-translate-context"><?php esc_html_e( 'Onderschrift', TMP_TEXT_DOMAIN ); ?></td>
                    <td class="tmp-translate-original"><div class="tmp-translate-original-text"><?php echo esc_html( $caption ); ?></div></td>
                    <td class="tmp-translate-translated">
                        <input type="text" class="tmp-translate-input" data-string-name="caption"
                               value="<?php echo esc_attr( tmp_get_translation( $context, 'caption', $target_lang ) ); ?>"
                               placeholder="<?php echo esc_attr( $caption ); ?>">
                    </td>
                </tr>
                <?php endif; ?>

                <tr class="tmp-translate-section-header">
                    <td colspan="3"><strong><?php esc_html_e( 'Kolomnamen', TMP_TEXT_DOMAIN ); ?></strong></td>
                </tr>
                <?php foreach ( $columns as $col ) :
                    $string_name = 'col_' . $col->id . '_label';
                    $translated  = tmp_get_translation( $context, $string_name, $target_lang );
                ?>
                <tr>
                    <td class="tmp-translate-context"><?php echo esc_html( 'Kolom: ' . $col->label ); ?></td>
                    <td class="tmp-translate-original"><div class="tmp-translate-original-text"><?php echo esc_html( $col->label ); ?></div></td>
                    <td class="tmp-translate-translated">
                        <input type="text" class="tmp-translate-input" data-string-name="<?php echo esc_attr( $string_name ); ?>"
                               value="<?php echo esc_attr( $translated ); ?>"
                               placeholder="<?php echo esc_attr( $col->label ); ?>">
                    </td>
                </tr>
                <?php endforeach; ?>

                <tr class="tmp-translate-section-header">
                    <td colspan="3"><strong><?php esc_html_e( 'Celinhoud', TMP_TEXT_DOMAIN ); ?></strong></td>
                </tr>
                <?php foreach ( $rows as $row ) :
                    $row_label = ucfirst( str_replace( '_', ' ', $row->row_type ) );
                    foreach ( $columns as $col ) :
                        $content = $row->cells[ $col->id ] ?? '';
                        if ( trim( $content ) === '' ) continue;
                        $string_name = 'row_' . $row->id . '_col_' . $col->id;
                        $translated  = tmp_get_translation( $context, $string_name, $target_lang );
                        $is_multiline = ( strpos( $content, "\n" ) !== false || strlen( $content ) > 60 );
                ?>
                <tr>
                    <td class="tmp-translate-context">
                        <span class="tmp-translate-row-type"><?php echo esc_html( $row_label ); ?></span>
                        <?php echo esc_html( $col->label ); ?>
                    </td>
                    <td class="tmp-translate-original"><div class="tmp-translate-original-text"><?php echo esc_html( $content ); ?></div></td>
                    <td class="tmp-translate-translated">
                        <?php if ( $is_multiline ) : ?>
                            <textarea class="tmp-translate-input tmp-translate-textarea" data-string-name="<?php echo esc_attr( $string_name ); ?>"
                                      placeholder="<?php echo esc_attr( $content ); ?>"><?php echo esc_textarea( $translated ); ?></textarea>
                        <?php else : ?>
                            <input type="text" class="tmp-translate-input" data-string-name="<?php echo esc_attr( $string_name ); ?>"
                                   value="<?php echo esc_attr( $translated ); ?>"
                                   placeholder="<?php echo esc_attr( $content ); ?>">
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach;
                endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="tmp-translate-save-bar">
        <button id="tmp-translate-save" class="button button-primary button-large">
            <span class="dashicons dashicons-saved" style="margin-top:3px;margin-right:4px;"></span>
            <?php esc_html_e( 'Vertalingen opslaan', TMP_TEXT_DOMAIN ); ?>
        </button>
        <span id="tmp-translate-status" class="tmp-save-status"></span>
    </div>
</div>

<script>
(function($) {
    var ajaxurl  = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
    var nonce    = '<?php echo esc_js( wp_create_nonce( 'tablemaster_admin' ) ); ?>';
    var tableId  = <?php echo intval( $table_id ); ?>;
    var context  = '<?php echo esc_js( $context ); ?>';
    var lang     = '<?php echo esc_js( $target_lang ); ?>';

    $('#tmp-translate-lang-select').on('change', function() {
        var newLang = $(this).val();
        window.location.href = '<?php echo esc_js( admin_url( 'admin.php?page=tablemaster-translate&id=' . $table_id . '&lang=' ) ); ?>' + newLang;
    });

    $('#tmp-translate-save').on('click', function() {
        var translations = {};
        $('.tmp-translate-input').each(function() {
            var name = $(this).data('string-name');
            var val  = $(this).val();
            if (val.trim() !== '') {
                translations[name] = val;
            }
        });

        var $status = $('#tmp-translate-status');
        $status.html('<span class="tmp-spinner"></span>');

        $.post(ajaxurl, {
            action:       'tablemaster_save_translations',
            nonce:        nonce,
            table_id:     tableId,
            lang:         lang,
            translations: JSON.stringify(translations),
        }, function(res) {
            if (res.success) {
                $status.removeClass('error').addClass('success').text('Vertalingen opgeslagen!');
                setTimeout(function() { $status.text(''); }, 3000);
            } else {
                $status.removeClass('success').addClass('error').text('Fout bij opslaan.');
            }
        }).fail(function() {
            $status.removeClass('success').addClass('error').text('Fout bij opslaan.');
        });
    });
})(jQuery);
</script>
