<?php
// Snippet ID: 54
// Name: S2-G4-S02 OAuth Callback Admin
// Scope: admin
// Active: True
// Modified: 2026-03-09 22:42:11
// Lines: 111

/**
 * S2-G4-S02 — OAuth Callback Admin
 * Sprint 2, Gate 4 | GitLab Issue #35
 *
 * Admin page to manage whitelisted OAuth callback URLs.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S2-G4-S02 OAuth Callback Admin"
 * Scope: admin
 * Priority: 10
 * Depends on: S2-G4-S01 OAuth Callback Validator
 *
 * @package NonSequitur
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', 'ns_oauth_callback_admin_page' );

function ns_oauth_callback_admin_page() {
    add_submenu_page(
        'edit.php?post_type=ns_feed_item',
        'OAuth Callbacks',
        'OAuth Callbacks',
        'manage_options',
        'ns-oauth-callbacks',
        'ns_oauth_render_callback_admin'
    );
}

add_action( 'admin_init', 'ns_oauth_handle_callback_form' );

function ns_oauth_handle_callback_form() {
    if ( ! isset( $_POST['ns_oauth_cb_action'] ) ) return;
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized.' );
    if ( ! wp_verify_nonce( $_POST['ns_oauth_cb_nonce'] ?? '', 'ns_oauth_cb_save' ) ) wp_die( 'Nonce failed.' );

    $action = sanitize_key( $_POST['ns_oauth_cb_action'] );
    $list   = get_option( 'ns_oauth_callback_whitelist', array() );
    if ( ! is_array( $list ) ) $list = array();

    if ( $action === 'add' ) {
        $url = esc_url_raw( trim( $_POST['ns_oauth_cb_url'] ?? '' ) );
        if ( ! empty( $url ) && strpos( $url, 'https://' ) === 0 ) {
            $list[] = $url;
            $list   = array_unique( $list );
            update_option( 'ns_oauth_callback_whitelist', $list, false );
            if ( function_exists( 'ns_audit_log' ) ) {
                ns_audit_log( 'oauth_callback_added', 'security', 'oauth', 'Callback added', array( 'url' => $url ) );
            }
        }
    }

    if ( $action === 'remove' ) {
        $idx = intval( $_POST['ns_oauth_cb_index'] ?? -1 );
        if ( isset( $list[ $idx ] ) ) {
            $removed = $list[ $idx ];
            unset( $list[ $idx ] );
            $list = array_values( $list );
            update_option( 'ns_oauth_callback_whitelist', $list, false );
            if ( function_exists( 'ns_audit_log' ) ) {
                ns_audit_log( 'oauth_callback_removed', 'security', 'oauth', 'Callback removed', array( 'url' => $removed ) );
            }
        }
    }

    wp_safe_redirect( admin_url( 'edit.php?post_type=ns_feed_item&page=ns-oauth-callbacks' ) );
    exit;
}

function ns_oauth_render_callback_admin() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized.' );

    $registered = function_exists( 'ns_oauth_get_registered_callbacks' ) ? ns_oauth_get_registered_callbacks() : array();
    $custom     = get_option( 'ns_oauth_callback_whitelist', array() );
    if ( ! is_array( $custom ) ) $custom = array();
    ?>
    <div class="wrap"><h1>OAuth Callback Whitelist</h1>
    <p>Only registered callback URLs are accepted by OAuth handlers. Unregistered callbacks return HTTP 403.</p>
    <h2>Registered Callbacks</h2>
    <table class="widefat striped" style="max-width:700px;">
        <thead><tr><th>URL</th><th>Source</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ( $registered as $url ) :
            $is_custom = in_array( $url, $custom, true );
            $idx       = $is_custom ? array_search( $url, $custom, true ) : -1;
        ?>
            <tr>
                <td><code style="font-size:12px;"><?php echo esc_html( $url ); ?></code></td>
                <td><?php echo $is_custom ? 'Custom' : 'Built-in'; ?></td>
                <td><?php if ( $is_custom ) : ?>
                    <form method="post" style="display:inline;"><?php wp_nonce_field( 'ns_oauth_cb_save', 'ns_oauth_cb_nonce' ); ?>
                        <input type="hidden" name="ns_oauth_cb_action" value="remove">
                        <input type="hidden" name="ns_oauth_cb_index" value="<?php echo esc_attr( $idx ); ?>">
                        <button type="submit" class="button button-small" style="color:#a00;">Remove</button>
                    </form>
                <?php else : ?>—<?php endif; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <h2 style="margin-top:20px;">Add Custom Callback</h2>
    <form method="post"><?php wp_nonce_field( 'ns_oauth_cb_save', 'ns_oauth_cb_nonce' ); ?>
        <input type="hidden" name="ns_oauth_cb_action" value="add">
        <input type="url" name="ns_oauth_cb_url" placeholder="https://..." class="regular-text" required pattern="https://.*">
        <?php submit_button( 'Add URL', 'secondary', 'submit', false ); ?>
    </form>
    </div><?php
}
