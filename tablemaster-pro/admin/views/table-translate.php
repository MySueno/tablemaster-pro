<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( esc_html__( 'Geen toegang.', TMP_TEXT_DOMAIN ) );

$table_id    = intval( $_GET['id'] ?? 0 );
$target_lang = sanitize_text_field( $_GET['lang'] ?? '' );

if ( ! $table_id ) wp_die( 'Geen tabel opgegeven.' );

$table = TableMaster_DB::get_table( $table_id );
if ( ! $table ) wp_die( 'Tabel niet gevonden.' );

$data     = TableMaster_DB::get_table_data( $table_id, '' );
$columns  = $data['columns'] ?? array();
$rows     = $data['rows'] ?? array();
$settings = json_decode( $table->settings, true );
$context  = TableMaster_WPML::get_context( $table_id );

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

if ( ! TableMaster_WPML::is_active() || ! TableMaster_WPML::is_string_translation_active() ) {
    echo '<div class="wrap tmp-wrap"><h1>' . esc_html__( 'Vertaling', TMP_TEXT_DOMAIN ) . '</h1>';
    echo '<div class="notice notice-warning"><p>' . esc_html__( 'WPML en WPML String Translation moeten actief zijn om tabellen te vertalen.', TMP_TEXT_DOMAIN ) . '</p></div>';
    echo '<p><a href="' . esc_url( admin_url( 'admin.php?page=tablemaster-edit&id=' . $table_id ) ) . '" class="button">&larr; ' . esc_html__( 'Terug naar bewerken', TMP_TEXT_DOMAIN ) . '</a></p>';
    echo '</div>';
    return;
}

$non_default_langs = array_filter( $active_langs, function( $l ) use ( $default_lang ) {
    return $l['code'] !== $default_lang;
} );

if ( empty( $non_default_langs ) ) {
    echo '<div class="wrap tmp-wrap"><h1>' . esc_html__( 'Vertaling', TMP_TEXT_DOMAIN ) . '</h1>';
    echo '<div class="notice notice-info"><p>' . esc_html__( 'Er is momenteel slechts één taal ingesteld in WPML. Voeg eerst een extra taal toe in WPML voordat je tabellen kunt vertalen.', TMP_TEXT_DOMAIN ) . '</p></div>';
    echo '<p><a href="' . esc_url( admin_url( 'admin.php?page=tablemaster-edit&id=' . $table_id ) ) . '" class="button">&larr; ' . esc_html__( 'Terug naar bewerken', TMP_TEXT_DOMAIN ) . '</a></p>';
    echo '</div>';
    return;
}

$valid_target_langs = array_keys( $active_langs );
if ( $target_lang && ! in_array( $target_lang, $valid_target_langs, true ) ) {
    wp_die( 'Ongeldige doeltaal.' );
}

$source_name = isset( $active_langs[ $default_lang ] ) ? $active_langs[ $default_lang ]['native_name'] : $default_lang;
$target_name = isset( $active_langs[ $target_lang ] ) ? $active_langs[ $target_lang ]['native_name'] : $target_lang;
$source_flag = isset( $active_langs[ $default_lang ]['country_flag_url'] ) ? $active_langs[ $default_lang ]['country_flag_url'] : '';
$target_flag = isset( $active_langs[ $target_lang ]['country_flag_url'] ) ? $active_langs[ $target_lang ]['country_flag_url'] : '';

if ( ! function_exists( 'tmp_get_translation' ) ) {
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
}

$caption = ! empty( $settings['caption'] ) ? $settings['caption'] : '';

$total_fields      = 0;
$translated_fields = 0;

$translate_rows = array();

$translate_rows[] = array(
    'section' => 'Tabel',
    'label'   => __( 'Naam', TMP_TEXT_DOMAIN ),
    'name'    => 'table_name',
    'original'=> $table->name,
    'type'    => 'input',
);
$total_fields++;
$tn = tmp_get_translation( $context, 'table_name', $target_lang );
if ( $tn !== '' ) $translated_fields++;

if ( $caption !== '' ) {
    $translate_rows[] = array(
        'section' => '',
        'label'   => __( 'Onderschrift', TMP_TEXT_DOMAIN ),
        'name'    => 'caption',
        'original'=> $caption,
        'type'    => 'input',
    );
    $total_fields++;
    $tc = tmp_get_translation( $context, 'caption', $target_lang );
    if ( $tc !== '' ) $translated_fields++;
}

$translate_rows[] = array( 'section' => __( 'Kolomnamen', TMP_TEXT_DOMAIN ) );

foreach ( $columns as $col ) {
    $string_name = 'col_' . $col->id . '_label';
    $translated  = tmp_get_translation( $context, $string_name, $target_lang );
    $translate_rows[] = array(
        'section' => '',
        'label'   => $col->label,
        'name'    => $string_name,
        'original'=> $col->label,
        'type'    => 'input',
    );
    $total_fields++;
    if ( $translated !== '' ) $translated_fields++;
}

$has_cell_rows = false;
$cell_rows     = array();
foreach ( $rows as $row ) {
    $row_label = ucfirst( str_replace( '_', ' ', $row->row_type ) );
    foreach ( $columns as $col ) {
        $content = $row->cells[ $col->id ] ?? '';
        if ( trim( $content ) === '' ) continue;
        $has_cell_rows   = true;
        $string_name     = 'row_' . $row->id . '_col_' . $col->id;
        $translated      = tmp_get_translation( $context, $string_name, $target_lang );
        $is_multiline    = ( strpos( $content, "\n" ) !== false || mb_strlen( $content ) > 60 );
        $cell_rows[] = array(
            'section'  => '',
            'label'    => $col->label,
            'badge'    => $row_label,
            'name'     => $string_name,
            'original' => $content,
            'type'     => $is_multiline ? 'textarea' : 'input',
        );
        $total_fields++;
        if ( $translated !== '' ) $translated_fields++;
    }
}

if ( $has_cell_rows ) {
    $translate_rows[] = array( 'section' => __( 'Celinhoud', TMP_TEXT_DOMAIN ) );
    $translate_rows   = array_merge( $translate_rows, $cell_rows );
}
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
        <div class="tmp-translate-lang-arrow">&#10132;</div>
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
        <div class="tmp-translate-progress">
            <span class="tmp-translate-progress-count" id="tmp-progress-count"><?php echo intval( $translated_fields ); ?></span>
            / <?php echo intval( $total_fields ); ?>
            <span class="tmp-translate-progress-label"><?php esc_html_e( 'vertaald', TMP_TEXT_DOMAIN ); ?></span>
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
                <?php
                $current_section = '';
                foreach ( $translate_rows as $tr_row ) :
                    if ( isset( $tr_row['section'] ) && $tr_row['section'] !== '' && ! isset( $tr_row['name'] ) ) :
                ?>
                    <tr class="tmp-translate-section-header">
                        <td colspan="3"><strong><?php echo esc_html( $tr_row['section'] ); ?></strong></td>
                    </tr>
                    <?php
                        continue;
                    endif;

                    if ( ! isset( $tr_row['name'] ) ) continue;

                    $translated = tmp_get_translation( $context, $tr_row['name'], $target_lang );
                    $has_value  = ( $translated !== '' );
                    $row_class  = $has_value ? 'tmp-translate-row tmp-translate-done' : 'tmp-translate-row';
                ?>
                <tr class="<?php echo esc_attr( $row_class ); ?>">
                    <td class="tmp-translate-context">
                        <?php if ( ! empty( $tr_row['badge'] ) ) : ?>
                            <span class="tmp-translate-row-type"><?php echo esc_html( $tr_row['badge'] ); ?></span>
                        <?php endif; ?>
                        <?php echo esc_html( $tr_row['label'] ); ?>
                    </td>
                    <td class="tmp-translate-original">
                        <div class="tmp-translate-original-text"><?php echo esc_html( $tr_row['original'] ); ?></div>
                    </td>
                    <td class="tmp-translate-translated">
                        <div class="tmp-translate-field-wrap">
                            <?php if ( $tr_row['type'] === 'textarea' ) : ?>
                                <textarea class="tmp-translate-input tmp-translate-textarea" data-string-name="<?php echo esc_attr( $tr_row['name'] ); ?>"
                                          placeholder="<?php echo esc_attr( $tr_row['original'] ); ?>"><?php echo esc_textarea( $translated ); ?></textarea>
                            <?php else : ?>
                                <input type="text" class="tmp-translate-input" data-string-name="<?php echo esc_attr( $tr_row['name'] ); ?>"
                                       value="<?php echo esc_attr( $translated ); ?>"
                                       placeholder="<?php echo esc_attr( $tr_row['original'] ); ?>">
                            <?php endif; ?>
                            <button type="button" class="tmp-translate-copy-btn" title="<?php esc_attr_e( 'Kopieer origineel', TMP_TEXT_DOMAIN ); ?>" data-original="<?php echo esc_attr( $tr_row['original'] ); ?>">
                                <span class="dashicons dashicons-clipboard"></span>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="tmp-translate-save-bar">
        <button id="tmp-translate-save" class="button button-primary button-large">
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
    var lang     = '<?php echo esc_js( $target_lang ); ?>';
    var totalFields = <?php echo intval( $total_fields ); ?>;
    var isDirty  = false;

    function updateProgress() {
        var count = 0;
        $('.tmp-translate-input').each(function() {
            if ($(this).val().trim() !== '') count++;
        });
        $('#tmp-progress-count').text(count);

        $('.tmp-translate-row').each(function() {
            var $input = $(this).find('.tmp-translate-input');
            if ($input.val().trim() !== '') {
                $(this).addClass('tmp-translate-done');
            } else {
                $(this).removeClass('tmp-translate-done');
            }
        });
    }

    $('.tmp-translate-input').on('input change', function() {
        isDirty = true;
        updateProgress();
    });

    $(window).on('beforeunload', function() {
        if (isDirty) {
            return '<?php echo esc_js( __( 'Je hebt niet-opgeslagen vertalingen. Weet je zeker dat je wilt vertrekken?', TMP_TEXT_DOMAIN ) ); ?>';
        }
    });

    $('.tmp-translate-copy-btn').on('click', function() {
        var original = $(this).data('original');
        var $field   = $(this).closest('.tmp-translate-field-wrap').find('.tmp-translate-input');
        $field.val(original).trigger('input').focus();
    });

    $('#tmp-translate-lang-select').on('change', function() {
        if (isDirty && !confirm('<?php echo esc_js( __( 'Je hebt niet-opgeslagen vertalingen. Toch van taal wisselen?', TMP_TEXT_DOMAIN ) ); ?>')) {
            return;
        }
        isDirty = false;
        var newLang = $(this).val();
        window.location.href = '<?php echo esc_js( admin_url( 'admin.php?page=tablemaster-translate&id=' . $table_id . '&lang=' ) ); ?>' + newLang;
    });

    $('#tmp-translate-save').on('click', function() {
        var $btn    = $(this);
        var $status = $('#tmp-translate-status');

        if ($btn.prop('disabled')) return;
        $btn.prop('disabled', true).addClass('updating-message');

        var translations = {};
        $('.tmp-translate-input').each(function() {
            var name = $(this).data('string-name');
            var val  = $(this).val();
            translations[name] = val;
        });

        $.post(ajaxurl, {
            action:       'tablemaster_save_translations',
            nonce:        nonce,
            table_id:     tableId,
            lang:         lang,
            translations: JSON.stringify(translations),
        }, function(res) {
            $btn.prop('disabled', false).removeClass('updating-message');
            if (res.success) {
                isDirty = false;
                $status.removeClass('error').addClass('success').text('Vertalingen opgeslagen!');
                setTimeout(function() { $status.text('').removeClass('success'); }, 3000);
            } else {
                $status.removeClass('success').addClass('error').text(res.data && res.data.message ? res.data.message : 'Fout bij opslaan.');
            }
        }).fail(function() {
            $btn.prop('disabled', false).removeClass('updating-message');
            $status.removeClass('success').addClass('error').text('Fout bij opslaan.');
        });
    });
})(jQuery);
</script>
