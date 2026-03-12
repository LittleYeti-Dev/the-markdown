<?php
/**
 * S1.20 — Claude API Key Rotation Procedure
 * Sprint 1, Gate 3 | GitLab Issue #27
 *
 * Admin page with documented rotation procedure.
 * One-click re-encryption of all vault tokens when key is rotated.
 * Verifies old key before re-encrypting. Logs all rotation events.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S1.20 Key Rotation"
 * Scope: Admin only
 * Depends on: S1.17 Token Vault
 *
 * Acceptance Criteria (GitLab #27):
 *   ✅ Documented rotation procedure
 *   ✅ Key stored in token vault
 *   ✅ Rotation tested without downtime
 *   ✅ Old key revoked
 *
 * @package NonSequitur
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ==================================================================
   1. ADMIN PAGE
   ================================================================== */

add_action( 'admin_menu', 'ns_keyrot_register_page' );

function ns_keyrot_register_page() {
    add_submenu_page(
        'edit.php?post_type=ns_feed_item',
        'Key Rotation',
        'Key Rotation',
        'manage_options',
        'ns-key-rotation',
        'ns_keyrot_render_page'
    );
}

/* ==================================================================
   2. HANDLE ROTATION FORM
   ================================================================== */

add_action( 'admin_init', 'ns_keyrot_handle_form' );

function ns_keyrot_handle_form() {
    if ( ! isset( $_POST['ns_keyrot_action'] ) ) return;
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized.' );
    if ( ! wp_verify_nonce( $_POST['ns_keyrot_nonce'] ?? '', 'ns_key_rotation' ) ) wp_die( 'Security check failed.' );

    $action = sanitize_key( $_POST['ns_keyrot_action'] );

    if ( $action === 'rotate' ) {
        $old_key = sanitize_text_field( $_POST['ns_old_key'] ?? '' );

        if ( empty( $old_key ) ) {
            add_settings_error( 'ns_keyrot', 'empty_key', 'Old key is required.', 'error' );
            return;
        }

        // Validate old key format
        if ( strlen( $old_key ) !== 64 || ! ctype_xdigit( $old_key ) ) {
            add_settings_error( 'ns_keyrot', 'bad_format', 'Old key must be 64 hex characters.', 'error' );
            return;
        }

        // Check that ns_vault_rotate_key exists (S1.17 must be active)
        if ( ! function_exists( 'ns_vault_rotate_key' ) ) {
            add_settings_error( 'ns_keyrot', 'no_vault', 'Token Vault (S1.17) must be active.', 'error' );
            return;
        }

        // Perform rotation
        $results = ns_vault_rotate_key( $old_key );

        // Log to audit
        if ( function_exists( 'ns_audit_log' ) ) {
            ns_audit_log(
                'key_rotation',
                'vault',
                0,
                'all_tokens',
                wp_json_encode( $results )
            );
        }

        // Check results
        $errors = array_filter( $results, function( $v ) {
            return strpos( $v, 'error' ) === 0;
        } );

        if ( ! empty( $errors ) ) {
            add_settings_error( 'ns_keyrot', 'partial_fail',
                'Rotation completed with errors: ' . implode( ', ', array_keys( $errors ) ), 'warning' );
        } else {
            $rotated = array_filter( $results, function( $v ) { return $v === 'rotated'; } );
            add_settings_error( 'ns_keyrot', 'success',
                count( $rotated ) . ' token(s) re-encrypted successfully.', 'success' );
        }

        set_transient( 'ns_keyrot_results', $results, 60 );
    }

    if ( $action === 'verify' ) {
        // Verify current vault key is working
        $valid = ns_vault_validate_key();
        if ( is_wp_error( $valid ) ) {
            add_settings_error( 'ns_keyrot', 'key_invalid', $valid->get_error_message(), 'error' );
        } else {
            add_settings_error( 'ns_keyrot', 'key_valid', 'Current vault key is valid and functional.', 'success' );
        }
    }

    set_transient( 'ns_keyrot_notices', get_settings_errors( 'ns_keyrot' ), 30 );
    wp_safe_redirect( admin_url( 'edit.php?post_type=ns_feed_item&page=ns-key-rotation&updated=1' ) );
    exit;
}

/* ==================================================================
   3. RENDER PAGE
   ================================================================== */

function ns_keyrot_render_page() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized.' );

    // Show notices
    $notices = get_transient( 'ns_keyrot_notices' );
    if ( $notices ) {
        delete_transient( 'ns_keyrot_notices' );
        foreach ( $notices as $n ) {
            printf( '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                esc_attr( $n['type'] ), esc_html( $n['message'] ) );
        }
    }

    // Show rotation results if available
    $results = get_transient( 'ns_keyrot_results' );
    if ( $results ) {
        delete_transient( 'ns_keyrot_results' );
    }

    $key_valid = function_exists( 'ns_vault_validate_key' ) ? ns_vault_validate_key() : false;
    ?>
    <div class="wrap">
        <h1>Key Rotation Procedure</h1>

        <div style="max-width:800px;">
            <h2>Current Vault Status</h2>
            <?php if ( $key_valid === true ) : ?>
                <div class="notice notice-success"><p>Vault key is configured and valid.</p></div>
            <?php elseif ( is_wp_error( $key_valid ) ) : ?>
                <div class="notice notice-error"><p><?php echo esc_html( $key_valid->get_error_message() ); ?></p></div>
            <?php else : ?>
                <div class="notice notice-warning"><p>Token Vault (S1.17) is not active.</p></div>
            <?php endif; ?>

            <form method="post" style="margin:15px 0;">
                <?php wp_nonce_field( 'ns_key_rotation', 'ns_keyrot_nonce' ); ?>
                <input type="hidden" name="ns_keyrot_action" value="verify">
                <button type="submit" class="button">Verify Current Key</button>
            </form>

            <hr>

            <h2>Rotation Procedure</h2>
            <div style="background:#f0f0f1;padding:15px;border-left:4px solid #2271b1;margin:15px 0;">
                <p><strong>Step-by-step key rotation (zero downtime):</strong></p>
                <ol style="margin-left:20px;">
                    <li>Generate a new key: <code>php -r "echo bin2hex(random_bytes(32));"</code></li>
                    <li>Update the <strong>NS Vault Key</strong> snippet (ID: 31) with the new key value</li>
                    <li>Return here and enter the <strong>old key</strong> below</li>
                    <li>Click <strong>Rotate</strong> — all tokens will be re-encrypted with the new key</li>
                    <li>Verify the Token Vault page still shows all tokens as "Set"</li>
                    <li>The old key is now revoked (no longer usable)</li>
                </ol>
                <p><strong>Important:</strong> Update the vault key snippet <em>before</em> running rotation.
                The rotation function decrypts with the old key and re-encrypts with the current (new) key.</p>
            </div>

            <h2>Execute Rotation</h2>
            <form method="post">
                <?php wp_nonce_field( 'ns_key_rotation', 'ns_keyrot_nonce' ); ?>
                <input type="hidden" name="ns_keyrot_action" value="rotate">
                <table class="form-table">
                    <tr>
                        <th><label for="ns_old_key">Previous (old) key</label></th>
                        <td>
                            <input type="password" id="ns_old_key" name="ns_old_key"
                                   style="width:500px;font-family:monospace;" placeholder="64-character hex key"
                                   autocomplete="off" required>
                            <p class="description">The key that was in the vault key snippet <em>before</em> you updated it.</p>
                        </td>
                    </tr>
                </table>
                <p>
                    <button type="submit" class="button button-primary"
                            onclick="return confirm('This will re-encrypt all stored tokens. Continue?');">
                        Rotate All Tokens
                    </button>
                </p>
            </form>

            <?php if ( $results ) : ?>
                <h3>Rotation Results</h3>
                <table class="widefat striped" style="max-width:500px;">
                    <thead><tr><th>Service</th><th>Result</th></tr></thead>
                    <tbody>
                    <?php foreach ( $results as $svc => $status ) : ?>
                        <tr>
                            <td><code><?php echo esc_html( $svc ); ?></code></td>
                            <td>
                                <?php if ( $status === 'rotated' ) : ?>
                                    <span style="color:green;">✅ Rotated</span>
                                <?php elseif ( $status === 'skipped (empty)' ) : ?>
                                    <span style="color:#999;">⊘ Skipped</span>
                                <?php else : ?>
                                    <span style="color:red;">❌ <?php echo esc_html( $status ); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <hr>

            <h2>Rotation History</h2>
            <?php
            if ( function_exists( 'ns_audit_log' ) ) {
                global $wpdb;
                $table = $wpdb->prefix . 'ns_audit_log';
                $rotations = $wpdb->get_results( $wpdb->prepare(
                    "SELECT * FROM $table WHERE action = 'key_rotation' ORDER BY log_time DESC LIMIT 10"
                ) );
                if ( $rotations ) {
                    echo '<table class="widefat striped" style="max-width:600px;"><thead><tr><th>Date</th><th>User</th><th>Details</th></tr></thead><tbody>';
                    foreach ( $rotations as $r ) {
                        printf( '<tr><td>%s</td><td>%s</td><td style="font-size:11px;">%s</td></tr>',
                            esc_html( $r->log_time ),
                            esc_html( $r->user_login ),
                            esc_html( $r->details )
                        );
                    }
                    echo '</tbody></table>';
                } else {
                    echo '<p><em>No rotations recorded yet.</em></p>';
                }
            } else {
                echo '<p><em>Audit logging (S1.22) not active — rotation history unavailable.</em></p>';
            }
            ?>
        </div>
    </div>
    <?php
}
