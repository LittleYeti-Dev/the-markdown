<?php
// Snippet ID: 46
// Name: S2-G4-M14 YouTube Admin Settings
// Scope: admin
// Active: True
// Modified: 2026-03-09 22:42:01
// Lines: 117

/**
 * S2-G4-M14 — YouTube Admin Settings
 * Sprint 2, Gate 4 | GitLab Issue #33
 *
 * Admin page for Google/YouTube API credentials and connection.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S2-G4-M14 YouTube Admin Settings"
 * Scope: admin
 * Priority: 10
 * Depends on: Token Vault (S1.17), S2-G4-M11 YouTube OAuth Handler
 *
 * @package NonSequitur
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', 'ns_youtube_register_settings_page' );

function ns_youtube_register_settings_page() {
    add_submenu_page(
        'edit.php?post_type=ns_feed_item',
        'YouTube Settings',
        'YouTube',
        'manage_options',
        'ns-youtube-settings',
        'ns_youtube_render_settings_page'
    );
}

add_action( 'admin_init', 'ns_youtube_handle_settings_form' );

function ns_youtube_handle_settings_form() {
    if ( ! isset( $_POST['ns_youtube_action'] ) ) return;
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized.' );
    if ( ! wp_verify_nonce( $_POST['ns_youtube_nonce'] ?? '', 'ns_youtube_save' ) ) wp_die( 'Nonce failed.' );

    $action = sanitize_key( $_POST['ns_youtube_action'] );

    if ( $action === 'save_creds' && function_exists( 'ns_vault_store' ) ) {
        $cid = trim( $_POST['ns_youtube_client_id'] ?? '' );
        $cs  = trim( $_POST['ns_youtube_client_secret'] ?? '' );
        if ( ! empty( $cid ) ) ns_vault_store( 'youtube_client_id', $cid );
        if ( ! empty( $cs ) )  ns_vault_store( 'youtube_client_secret', $cs );
        if ( function_exists( 'ns_audit_log' ) ) {
            ns_audit_log( 'youtube_creds_saved', 'credential', 'youtube', 'App credentials updated', array() );
        }
        set_transient( 'ns_youtube_notice', 'success:Credentials saved.', 30 );
    }

    if ( $action === 'disconnect' && function_exists( 'ns_vault_delete' ) ) {
        ns_vault_delete( 'youtube_oauth_token' );
        ns_vault_delete( 'youtube_refresh_token' );
        delete_option( 'ns_youtube_token_expires' );
        delete_transient( 'ns_youtube_channel_info' );
        if ( function_exists( 'ns_audit_log' ) ) {
            ns_audit_log( 'youtube_disconnected', 'oauth', 'youtube', 'Account disconnected', array() );
        }
        set_transient( 'ns_youtube_notice', 'success:Disconnected.', 30 );
    }

    wp_safe_redirect( admin_url( 'edit.php?post_type=ns_feed_item&page=ns-youtube-settings' ) );
    exit;
}

function ns_youtube_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized.' );

    $notice = get_transient( 'ns_youtube_notice' );
    if ( $notice ) { delete_transient( 'ns_youtube_notice' ); list( $t, $m ) = explode( ':', $notice, 2 );
        printf( '<div class="notice notice-%s is-dismissible"><p>%s</p></div>', esc_attr( $t ), esc_html( $m ) ); }
    if ( isset( $_GET['connected'] ) ) {
        echo '<div class="notice notice-success is-dismissible"><p>YouTube connected!</p></div>'; }

    $has_cid   = function_exists( 'ns_vault_has_token' ) && ns_vault_has_token( 'youtube_client_id' );
    $has_token = function_exists( 'ns_vault_has_token' ) && ns_vault_has_token( 'youtube_oauth_token' );
    $expires   = get_option( 'ns_youtube_token_expires', 0 );

    // Show channel info if connected
    $channel_info = '';
    if ( $has_token && function_exists( 'ns_youtube_get_channel' ) ) {
        $ch = ns_youtube_get_channel();
        if ( ! is_wp_error( $ch ) ) {
            $channel_info = $ch['title'] . ' (' . number_format( $ch['subscribers'] ) . ' subs)';
        }
    }
    ?>
    <div class="wrap"><h1>YouTube Integration</h1>
    <p>Create a project in <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a>, enable YouTube Data API v3, create OAuth 2.0 credentials.</p>
    <p>Callback URL: <code><?php echo esc_html( rest_url( 'ns/v1/oauth/youtube/callback' ) ); ?></code></p>
    <h2>App Credentials</h2>
    <form method="post"><?php wp_nonce_field( 'ns_youtube_save', 'ns_youtube_nonce' ); ?>
        <input type="hidden" name="ns_youtube_action" value="save_creds">
        <table class="form-table">
            <tr><th>Client ID</th><td><input type="password" name="ns_youtube_client_id" class="regular-text" autocomplete="off" placeholder="<?php echo $has_cid ? '(stored)' : ''; ?>"></td></tr>
            <tr><th>Client Secret</th><td><input type="password" name="ns_youtube_client_secret" class="regular-text" autocomplete="off"></td></tr>
        </table><?php submit_button( 'Save Credentials' ); ?>
    </form>
    <h2>Connection</h2>
    <table class="form-table"><tr><th>Status</th>
        <td><?php if ( $has_token ) : ?>
            <span style="color:green;">&#9679; Connected</span>
            <?php if ( $channel_info ) : ?><br>Channel: <?php echo esc_html( $channel_info ); ?><?php endif; ?>
        <?php else : ?><span style="color:#999;">&#9675; Not connected</span><?php endif; ?></td>
    </tr></table>
    <?php if ( $has_cid && ! $has_token ) : ?>
        <p><a href="<?php echo esc_url( rest_url( 'ns/v1/oauth/youtube/start' ) ); ?>" class="button button-primary">Connect YouTube</a></p>
    <?php elseif ( $has_token ) : ?>
        <form method="post"><?php wp_nonce_field( 'ns_youtube_save', 'ns_youtube_nonce' ); ?>
            <input type="hidden" name="ns_youtube_action" value="disconnect">
            <?php submit_button( 'Disconnect', 'delete' ); ?>
        </form>
    <?php endif; ?>
    </div><?php
}
