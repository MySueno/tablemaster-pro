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
                <th><?php esc_html_e( 'Standaard kleurthema', TMP_TEXT_DOMAIN ); ?></th>
                <td>
                    <select name="default_theme">
                        <?php foreach ( array( 'green', 'red', 'blue', 'grey' ) as $t ) : ?>
                            <option value="<?php echo esc_attr( $t ); ?>" <?php selected( $s['default_theme'], $t ); ?>>
                                <?php echo esc_html( ucfirst( $t ) ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
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
                <th><?php esc_html_e( 'Export inschakelen (CSV/Print)', TMP_TEXT_DOMAIN ); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="enable_export" value="1" <?php checked( $s['enable_export'] ); ?>>
                        <?php esc_html_e( 'Exportknoppen tonen op de frontend', TMP_TEXT_DOMAIN ); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Update Server URL', TMP_TEXT_DOMAIN ); ?></th>
                <td>
                    <input type="url" name="update_url" value="<?php echo esc_attr( $s['update_url'] ); ?>" class="regular-text" placeholder="https://uw-replit-domein.replit.app">
                    <p class="description"><?php esc_html_e( 'URL van de Replit update-server. Als dit is ingevuld, controleert WordPress automatisch op nieuwe versies.', TMP_TEXT_DOMAIN ); ?></p>
                </td>
            </tr>
        </table>
        <?php submit_button( __( 'Instellingen opslaan', TMP_TEXT_DOMAIN ) ); ?>
    </form>
    <hr>
    <h2><?php esc_html_e( 'Plugin Info', TMP_TEXT_DOMAIN ); ?></h2>
    <p><?php printf( esc_html__( 'Versie: %s', TMP_TEXT_DOMAIN ), esc_html( TMP_VERSION ) ); ?></p>
    <p><?php printf( esc_html__( 'Database versie: %s', TMP_TEXT_DOMAIN ), esc_html( get_option( 'tablemaster_db_version', 'n/a' ) ) ); ?></p>
    <?php if ( ! empty( $s['update_url'] ) ) : ?>
        <p><?php printf( esc_html__( 'Update server: %s', TMP_TEXT_DOMAIN ), esc_html( $s['update_url'] ) ); ?> ✅</p>
    <?php else : ?>
        <p><?php esc_html_e( 'Update server: niet geconfigureerd', TMP_TEXT_DOMAIN ); ?></p>
    <?php endif; ?>
</div>
