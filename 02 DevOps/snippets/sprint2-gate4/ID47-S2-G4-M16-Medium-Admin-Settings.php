<?php
// Snippet ID: 47
// Name: S2-G4-M16 Medium Admin Settings
// Scope: admin
// Active: True
// Modified: 2026-03-09 22:42:02
// Lines: 104

/**
 * S2-G4-M16 — Medium Admin Settings
 * Sprint 2, Gate 4 | GitLab Issue #34
 *
 * Admin page for Medium bearer token entry + author verification.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S2-G4-M16 Medium Admin Settings"
 * Scope: admin
 * Priority: 10
 * Depends on: Token Vault (S1.17), S2-G4-M15 Medium API Client
 *
 * @package NonSequitur
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', 'ns_medium_register_settings_page' );

function ns_medium_register_settings_page() {
    add_submenu_page(
        'edit.php?post_type=ns_feed_item',
        'Medium Settings',
        'Medium',
        'manage_options',
        'ns-medium-settings',
        'ns_medium_render_settings_page'
    );
}

add_action( 'admin_init', 'ns_medium_handle_settings_form' );

function ns_medium_handle_settings_form() {
    if ( ! isset( $_POST['ns_medium_action'] ) || $_POST['ns_medium_action'] !== 'save_token' ) {
        return;
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized.' );
    }
    if ( ! wp_verify_nonce( $_POST['ns_medium_nonce'] ?? '', 'ns_medium_save' ) ) {
        wp_die( 'Security check failed.' );
    }

    $token = trim( $_POST['ns_medium_token'] ?? '' );
    if ( ! empty( $token ) && function_exists( 'ns_vault_store' ) ) {
        $result = ns_vault_store( 'medium_bearer_token', $token );
        if ( is_wp_error( $result ) ) {
            set_transient( 'ns_medium_notice', 'error:' . $result->get_error_message(), 30 );
        } else {
            if ( function_exists( 'ns_audit_log' ) ) {
                ns_audit_log( 'medium_token_stored', 'credential', 'medium_bearer_token', 'Token updated', array() );
            }
            set_transient( 'ns_medium_notice', 'success:Token saved.', 30 );
        }
    }

    wp_safe_redirect( admin_url( 'edit.php?post_type=ns_feed_item&page=ns-medium-settings' ) );
    exit;
}

function ns_medium_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized.' );
    }

    $notice = get_transient( 'ns_medium_notice' );
    if ( $notice ) {
        delete_transient( 'ns_medium_notice' );
        list( $type, $msg ) = explode( ':', $notice, 2 );
        printf( '<div class="notice notice-%s is-dismissible"><p>%s</p></div>', esc_attr( $type ), esc_html( $msg ) );
    }

    $has_token = function_exists( 'ns_vault_has_token' ) ? ns_vault_has_token( 'medium_bearer_token' ) : false;
    $masked    = function_exists( 'ns_vault_masked' ) ? ns_vault_masked( 'medium_bearer_token' ) : '—';

    // Verify author if token exists
    $author_info = '';
    if ( $has_token && function_exists( 'ns_medium_get_user_id' ) ) {
        $uid = ns_medium_get_user_id();
        $author_info = is_wp_error( $uid ) ? 'Verification failed' : 'Verified (ID: ' . substr( $uid, 0, 8 ) . '...)';
    }
    ?>
    <div class="wrap">
        <h1>Medium Integration</h1>
        <p>Enter your Medium Integration Token. Get it from <a href="https://medium.com/me/settings/security" target="_blank">Medium Settings &gt; Security</a>.</p>
        <table class="form-table"><tr>
            <th>Token Status</th>
            <td><?php echo $has_token ? '<span style="color:green;">&#9679; Set</span> <code>' . esc_html( $masked ) . '</code>' : '<span style="color:#999;">&#9675; Not set</span>'; ?>
            <?php if ( $author_info ) : ?><br><em><?php echo esc_html( $author_info ); ?></em><?php endif; ?></td>
        </tr></table>
        <form method="post">
            <?php wp_nonce_field( 'ns_medium_save', 'ns_medium_nonce' ); ?>
            <input type="hidden" name="ns_medium_action" value="save_token">
            <table class="form-table"><tr>
                <th><label for="ns_medium_token">Integration Token</label></th>
                <td><input type="password" id="ns_medium_token" name="ns_medium_token" class="regular-text" autocomplete="off" required></td>
            </tr></table>
            <?php submit_button( 'Save Token' ); ?>
        </form>
    </div>
    <?php
}
