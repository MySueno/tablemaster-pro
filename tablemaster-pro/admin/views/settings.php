<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( esc_html__( 'Geen toegang.', TMP_TEXT_DOMAIN ) );

settings_errors( 'tablemaster' );
$s = TableMaster_Settings::get();
?>
<div class="wrap tmp-wrap">
    <h1><?php esc_html_e( 'TableMaster Pro — Instellingen', TMP_TEXT_DOMAIN ); ?></h1>
    <form method="post">
        <?php wp_nonce_field( 'tablemaster_save_settings', 'tablemaster_settings_nonce' ); ?>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e( 'Standaard items per pagina', TMP_TEXT_DOMAIN ); ?></th>
                <td>
                    <select name="default_per_page">
                        <?php foreach ( array( 5, 10, 25, 50, 100 ) as $v ) : ?>
                            <option value="<?php echo esc_attr( $v ); ?>" <?php selected( $s['default_per_page'], $v ); ?>>
                                <?php echo esc_html( $v ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Tabel border-radius (px)', TMP_TEXT_DOMAIN ); ?></th>
                <td>
                    <input type="number" name="border_radius" value="<?php echo esc_attr( $s['border_radius'] ); ?>" min="0" max="50" step="1" style="width:80px;"> px
                    <p class="description"><?php esc_html_e( 'Hoekafronding van alle tabellen. Geldt direct voor alle tabellen op de frontend.', TMP_TEXT_DOMAIN ); ?></p>
                    <div id="tmp-radius-preview" style="margin-top:10px;width:280px;height:60px;border:2px solid #2e7d32;background:#f1f8e9;transition:border-radius .2s;border-radius:<?php echo intval( $s['border_radius'] ); ?>px;display:flex;align-items:center;justify-content:center;color:#555;font-size:13px;">
                        <?php esc_html_e( 'Voorbeeld border-radius', TMP_TEXT_DOMAIN ); ?>
                    </div>
                    <script>
                    jQuery(function($){
                        $('input[name="border_radius"]').on('input change', function(){
                            $('#tmp-radius-preview').css('border-radius', $(this).val() + 'px');
                        });
                    });
                    </script>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Licentiecode', TMP_TEXT_DOMAIN ); ?></th>
                <td>
                    <input type="text" name="license_key" value="<?php echo esc_attr( $s['license_key'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Voer uw licentiecode in', TMP_TEXT_DOMAIN ); ?>">
                    <p class="description"><?php esc_html_e( 'Voer uw licentiecode in om automatische updates te activeren.', TMP_TEXT_DOMAIN ); ?></p>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Data verwijderen bij deïnstallatie', TMP_TEXT_DOMAIN ); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="delete_data_on_uninstall" value="1" <?php checked( $s['delete_data_on_uninstall'], '1' ); ?>>
                        <?php esc_html_e( 'Alle tabellen en gegevens permanent verwijderen wanneer de plugin wordt verwijderd', TMP_TEXT_DOMAIN ); ?>
                    </label>
                    <p class="description" style="color:#d63638;"><?php esc_html_e( 'Let op: als deze optie is uitgeschakeld (standaard), blijven al uw tabellen en data bewaard in de database — ook na het verwijderen en opnieuw installeren van de plugin.', TMP_TEXT_DOMAIN ); ?></p>
                </td>
            </tr>
        </table>
        <?php submit_button( __( 'Instellingen opslaan', TMP_TEXT_DOMAIN ) ); ?>
    </form>

    <hr>
    <h2><?php esc_html_e( 'Plugin-informatie', TMP_TEXT_DOMAIN ); ?></h2>
    <p><?php printf( esc_html__( 'Versie: %s', TMP_TEXT_DOMAIN ), esc_html( TMP_VERSION ) ); ?></p>
    <p><?php printf( esc_html__( 'Database versie: %s', TMP_TEXT_DOMAIN ), esc_html( get_option( 'tablemaster_db_version', 'n/a' ) ) ); ?></p>

    <?php
        $saved_license   = TableMaster_Settings::get( 'license_key' );
        $active_url      = TableMaster_Settings::get_update_url();
        $cached          = get_transient( 'tmp_update_check' );
        $cached_version  = ( is_object( $cached ) && ! empty( $cached->version ) ) ? $cached->version : null;
        $last_error      = get_transient( 'tmp_update_error' );
        $last_check_ts   = (int) get_option( 'tmp_last_successful_check', 0 );
        $last_check_str  = $last_check_ts
            ? sprintf(
                /* translators: %s: human-readable time-ago string */
                __( '%s geleden', TMP_TEXT_DOMAIN ),
                human_time_diff( $last_check_ts, current_time( 'timestamp' ) )
            )
            : __( 'nog geen succesvolle check', TMP_TEXT_DOMAIN );

        $masked_license = '';
        if ( ! empty( $saved_license ) ) {
            $len = strlen( $saved_license );
            if ( $len <= 4 ) {
                $masked_license = str_repeat( '•', $len );
            } else {
                $masked_license = substr( $saved_license, 0, 2 ) . str_repeat( '•', max( 1, $len - 4 ) ) . substr( $saved_license, -2 );
            }
        }
    ?>

    <hr>
    <h2><?php esc_html_e( 'Updates & verbinding', TMP_TEXT_DOMAIN ); ?></h2>

    <table class="form-table" role="presentation">
        <tbody>
            <tr>
                <th scope="row"><?php esc_html_e( 'Licentie', TMP_TEXT_DOMAIN ); ?></th>
                <td>
                    <?php if ( ! empty( $saved_license ) ) : ?>
                        <code><?php echo esc_html( $masked_license ); ?></code>
                        <span style="color:#2e7d32; margin-left:6px;"><?php esc_html_e( 'actief', TMP_TEXT_DOMAIN ); ?> ✅</span>
                    <?php else : ?>
                        <span style="color:#d63638;"><?php esc_html_e( 'geen licentiecode ingevuld — automatische updates zijn uitgeschakeld', TMP_TEXT_DOMAIN ); ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e( 'Update-server', TMP_TEXT_DOMAIN ); ?></th>
                <td><code><?php echo esc_html( $active_url ); ?></code></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e( 'Geïnstalleerde versie', TMP_TEXT_DOMAIN ); ?></th>
                <td><code><?php echo esc_html( TMP_VERSION ); ?></code></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e( 'Laatst bekend op server', TMP_TEXT_DOMAIN ); ?></th>
                <td>
                    <?php if ( $cached_version ) : ?>
                        <code><?php echo esc_html( $cached_version ); ?></code>
                        <?php if ( version_compare( $cached_version, TMP_VERSION, '>' ) ) : ?>
                            <span style="color:#d63638; margin-left:6px;"><?php esc_html_e( 'update beschikbaar', TMP_TEXT_DOMAIN ); ?></span>
                        <?php else : ?>
                            <span style="color:#2e7d32; margin-left:6px;"><?php esc_html_e( 'up-to-date', TMP_TEXT_DOMAIN ); ?> ✅</span>
                        <?php endif; ?>
                    <?php else : ?>
                        <em><?php esc_html_e( 'cache leeg — bij volgende controle wordt verse info opgehaald', TMP_TEXT_DOMAIN ); ?></em>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e( 'Laatste succesvolle controle', TMP_TEXT_DOMAIN ); ?></th>
                <td><?php echo esc_html( $last_check_str ); ?></td>
            </tr>
            <?php if ( ! empty( $last_error ) ) : ?>
            <tr>
                <th scope="row"><?php esc_html_e( 'Laatste foutmelding', TMP_TEXT_DOMAIN ); ?></th>
                <td><span style="color:#d63638;"><?php echo esc_html( $last_error ); ?></span></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div style="margin-top:14px;">
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block; margin-right:8px;">
            <input type="hidden" name="action" value="tmp_force_update_check">
            <?php wp_nonce_field( 'tmp_force_update_check' ); ?>
            <button type="submit" class="button button-primary"><?php esc_html_e( 'Nu controleren op updates', TMP_TEXT_DOMAIN ); ?></button>
        </form>

        <button type="button" class="button" id="tmp-test-connection-btn"><?php esc_html_e( 'Verbinding testen', TMP_TEXT_DOMAIN ); ?></button>
        <span id="tmp-test-connection-result" style="margin-left:10px;"></span>
    </div>

    <p class="description" style="margin-top:14px; max-width:760px;">
        <?php esc_html_e( 'Update niet zichtbaar? Klik op "Nu controleren op updates". De cache vernieuwt automatisch elk uur, en bij een netwerkfout wordt na 5 minuten opnieuw geprobeerd. Bij het openen van de WordPress Plugins- of Updates-pagina wordt automatisch een verse controle gedaan (max 1× per 5 minuten per gebruiker).', TMP_TEXT_DOMAIN ); ?>
    </p>

    <script>
    jQuery(function($){
        var $btn    = $('#tmp-test-connection-btn');
        var $result = $('#tmp-test-connection-result');
        $btn.on('click', function(){
            $btn.prop('disabled', true);
            $result.html('<span style="color:#666;">' + <?php echo wp_json_encode( __( 'Bezig met testen…', TMP_TEXT_DOMAIN ) ); ?> + '</span>');
            $.post(ajaxurl, {
                action: 'tmp_test_connection',
                nonce:  <?php echo wp_json_encode( wp_create_nonce( 'tmp_test_connection' ) ); ?>
            }).done(function(resp){
                if ( resp && resp.success ) {
                    var d = resp.data || {};
                    var color = (d.http_status === 200 && d.remote_version) ? '#2e7d32' : '#d63638';
                    var msg = 'HTTP ' + (d.http_status || '?') + ' · ' +
                              (<?php echo wp_json_encode( __( 'versie', TMP_TEXT_DOMAIN ) ); ?>) + ': ' +
                              (d.remote_version || '?') + ' · ' +
                              (d.elapsed_ms || '?') + ' ms';
                    $result.html('<span style="color:' + color + ';">' + $('<div>').text(msg).html() + '</span>');
                } else {
                    var err = (resp && resp.data && resp.data.message) ? resp.data.message : <?php echo wp_json_encode( __( 'Onbekende fout', TMP_TEXT_DOMAIN ) ); ?>;
                    $result.html('<span style="color:#d63638;">' + $('<div>').text(err).html() + '</span>');
                }
            }).fail(function(xhr){
                $result.html('<span style="color:#d63638;">HTTP ' + xhr.status + '</span>');
            }).always(function(){
                $btn.prop('disabled', false);
            });
        });
    });
    </script>
</div>
