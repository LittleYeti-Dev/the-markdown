<?php
// Snippet ID: 44
// Name: S2-G4-M06 X Admin Settings
// Scope: admin
// Active: True
// Modified: 2026-03-09 22:42:00
// Lines: 105

/**
 * S2-G4-M06 — X (Twitter) Admin Settings
 * Sprint 2, Gate 4 | GitLab Issue #30
 *
 * Admin page for X app credentials and OAuth connection management.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S2-G4-M06 X Admin Settings"
 * Scope: admin
 * Priority: 10
 * Depends on: Token Vault (S1.17), S2-G4-M01 X OAuth Handler
 *
 * @package NonSequitur
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', 'ns_twitter_register_settings_page' );

function ns_twitter_register_settings_page() {
    add_submenu_page(
        'edit.php?post_type=ns_feed_item',
        'X (Twitter) Settings',
        'X (Twitter)',
        'manage_options',
        'ns-twitter-settings',
        'ns_twitter_render_settings_page'
    );
}

add_action( 'admin_init', 'ns_twitter_handle_settings_form' );

function ns_twitter_handle_settings_form() {
    if ( ! isset( $_POST['ns_twitter_action'] ) ) return;
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized.' );
    if ( ! wp_verify_nonce( $_POST['ns_twitter_nonce'] ?? '', 'ns_twitter_save' ) ) wp_die( 'Nonce failed.' );

    $action = sanitize_key( $_POST['ns_twitter_action'] );

    if ( $action === 'save_creds' && function_exists( 'ns_vault_store' ) ) {
        $cid = trim( $_POST['ns_twitter_client_id'] ?? '' );
        $cs  = trim( $_POST['ns_twitter_client_secret'] ?? '' );
        if ( ! empty( $cid ) ) ns_vault_store( 'twitter_client_id', $cid );
        if ( ! empty( $cs ) )  ns_vault_store( 'twitter_client_secret', $cs );
        if ( function_exists( 'ns_audit_log' ) ) {
            ns_audit_log( 'twitter_creds_saved', 'credential', 'twitter', 'App credentials updated', array() );
        }
        set_transient( 'ns_twitter_notice', 'success:Credentials saved.', 30 );
    }

    if ( $action === 'disconnect' && function_exists( 'ns_vault_delete' ) ) {
        ns_vault_delete( 'twitter_oauth_token' );
        ns_vault_delete( 'twitter_refresh_token' );
        delete_option( 'ns_twitter_token_expires' );
        if ( function_exists( 'ns_audit_log' ) ) {
            ns_audit_log( 'twitter_disconnected', 'oauth', 'twitter', 'Account disconnected', array() );
        }
        set_transient( 'ns_twitter_notice', 'success:Disconnected.', 30 );
    }

    wp_safe_redirect( admin_url( 'edit.php?post_type=ns_feed_item&page=ns-twitter-settings' ) );
    exit;
}

function ns_twitter_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized.' );

    $notice = get_transient( 'ns_twitter_notice' );
    if ( $notice ) { delete_transient( 'ns_twitter_notice' ); list( $t, $m ) = explode( ':', $notice, 2 );
        printf( '<div class="notice notice-%s is-dismissible"><p>%s</p></div>', esc_attr( $t ), esc_html( $m ) ); }
    if ( isset( $_GET['connected'] ) ) {
        echo '<div class="notice notice-success is-dismissible"><p>X account connected!</p></div>'; }

    $has_cid   = function_exists( 'ns_vault_has_token' ) && ns_vault_has_token( 'twitter_client_id' );
    $has_token = function_exists( 'ns_vault_has_token' ) && ns_vault_has_token( 'twitter_oauth_token' );
    $expires   = get_option( 'ns_twitter_token_expires', 0 );
    ?>
    <div class="wrap"><h1>X (Twitter) Integration</h1>
    <h2>App Credentials</h2>
    <form method="post"><?php wp_nonce_field( 'ns_twitter_save', 'ns_twitter_nonce' ); ?>
        <input type="hidden" name="ns_twitter_action" value="save_creds">
        <table class="form-table">
            <tr><th>Client ID</th><td><input type="password" name="ns_twitter_client_id" class="regular-text" autocomplete="off" placeholder="<?php echo $has_cid ? '(stored)' : ''; ?>"></td></tr>
            <tr><th>Client Secret</th><td><input type="password" name="ns_twitter_client_secret" class="regular-text" autocomplete="off"></td></tr>
        </table><?php submit_button( 'Save Credentials' ); ?>
    </form>
    <h2>Connection Status</h2>
    <table class="form-table"><tr><th>Status</th>
        <td><?php if ( $has_token ) : ?>
            <span style="color:green;">&#9679; Connected</span>
            <?php if ( $expires ) : ?><br>Token expires: <?php echo esc_html( date( 'Y-m-d H:i', $expires ) ); ?><?php endif; ?>
        <?php else : ?><span style="color:#999;">&#9675; Not connected</span><?php endif; ?></td>
    </tr></table>
    <?php if ( $has_cid && ! $has_token ) : ?>
        <p><a href="<?php echo esc_url( rest_url( 'ns/v1/oauth/twitter/start' ) ); ?>" class="button button-primary">Connect X Account</a></p>
    <?php elseif ( $has_token ) : ?>
        <form method="post"><?php wp_nonce_field( 'ns_twitter_save', 'ns_twitter_nonce' ); ?>
            <input type="hidden" name="ns_twitter_action" value="disconnect">
            <?php submit_button( 'Disconnect', 'delete' ); ?>
        </form>
    <?php endif; ?>
    </div><?php
}
