<?php
/**
 * S1.17 — Token Vault: Encrypted wp_options Storage
 * Sprint 1, Gate 3 | GitLab Issue #24
 *
 * AES-256-CBC encrypted storage for all platform API tokens.
 * Encryption key lives in wp-config.php (never in DB).
 * Tokens are never visible in admin UI — masked display only.
 * Provides helper API for other snippets to store/retrieve credentials.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S1.17 Token Vault"
 * Scope: Admin only
 * Depends on: Sprint 0 complete, NS_VAULT_KEY defined in wp-config.php
 *
 * SETUP REQUIRED (Yeti — one-time in WP admin):
 *   Add to wp-config.php (above "That's all, stop editing!"):
 *   define( 'NS_VAULT_KEY', 'your-64-char-hex-key-here' );
 *
 *   Generate a key via: bin2hex( random_bytes( 32 ) )
 *   Or in terminal: php -r "echo bin2hex(random_bytes(32));"
 *
 * Acceptance Criteria (from GitLab #24):
 *   ✅ Encrypted storage for all platform tokens
 *   ✅ Encryption key in wp-config.php (not in DB)
 *   ✅ AES-256 minimum
 *   ✅ Tokens not visible in admin UI
 *
 * @package NonSequitur
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ==================================================================
   0. CONSTANTS & VALIDATION
   ================================================================== */

/**
 * Option name prefix for all vault entries.
 * Stored as: ns_vault_{service_name} in wp_options.
 */
define( 'NS_VAULT_PREFIX', 'ns_vault_' );

/**
 * Supported cipher — AES-256-CBC.
 */
define( 'NS_VAULT_CIPHER', 'aes-256-cbc' );

/**
 * List of allowed service keys that can be stored in the vault.
 * Prevents arbitrary option creation.
 */
function ns_vault_allowed_services() {
    return array(
        'claude_api_key',
        'twitter_oauth_token',
        'twitter_oauth_secret',
        'twitter_client_id',
        'twitter_client_secret',
        'linkedin_access_token',
        'linkedin_client_id',
        'linkedin_client_secret',
        'instagram_access_token',
        'instagram_app_secret',
        'youtube_api_key',
        'youtube_oauth_token',
        'medium_bearer_token',
        'rss_aggregator_license',
    );
}

/* ==================================================================
   1. CORE ENCRYPTION / DECRYPTION
   ================================================================== */

/**
 * Validate that the vault key is configured and correct length.
 *
 * @return bool|WP_Error True if valid, WP_Error otherwise.
 */
function ns_vault_validate_key() {
    if ( ! defined( 'NS_VAULT_KEY' ) || empty( NS_VAULT_KEY ) ) {
        return new WP_Error(
            'ns_vault_no_key',
            'NS_VAULT_KEY is not defined in wp-config.php. Token vault is disabled.'
        );
    }

    $key_bytes = @hex2bin( NS_VAULT_KEY );
    if ( $key_bytes === false || strlen( $key_bytes ) !== 32 ) {
        return new WP_Error(
            'ns_vault_bad_key',
            'NS_VAULT_KEY must be exactly 64 hex characters (32 bytes). Current length: ' . strlen( NS_VAULT_KEY )
        );
    }

    return true;
}

/**
 * Encrypt a plaintext value using AES-256-CBC.
 *
 * @param string $plaintext The value to encrypt.
 * @return string|WP_Error Base64-encoded "iv:ciphertext:hmac" or WP_Error.
 */
function ns_vault_encrypt( $plaintext ) {
    $valid = ns_vault_validate_key();
    if ( is_wp_error( $valid ) ) {
        return $valid;
    }

    $key = hex2bin( NS_VAULT_KEY );

    // Generate random IV
    $iv_length = openssl_cipher_iv_length( NS_VAULT_CIPHER );
    $iv = openssl_random_pseudo_bytes( $iv_length );

    // Encrypt
    $ciphertext = openssl_encrypt( $plaintext, NS_VAULT_CIPHER, $key, OPENSSL_RAW_DATA, $iv );
    if ( $ciphertext === false ) {
        return new WP_Error( 'ns_vault_encrypt_fail', 'Encryption failed.' );
    }

    // HMAC for integrity verification (encrypt-then-MAC)
    $hmac = hash_hmac( 'sha256', $iv . $ciphertext, $key, true );

    // Pack as base64: iv:ciphertext:hmac
    $packed = base64_encode( $iv ) . ':' . base64_encode( $ciphertext ) . ':' . base64_encode( $hmac );

    return $packed;
}

/**
 * Decrypt a vault-encrypted value.
 *
 * @param string $packed Base64-encoded "iv:ciphertext:hmac" string.
 * @return string|WP_Error Decrypted plaintext or WP_Error.
 */
function ns_vault_decrypt( $packed ) {
    $valid = ns_vault_validate_key();
    if ( is_wp_error( $valid ) ) {
        return $valid;
    }

    $key = hex2bin( NS_VAULT_KEY );

    // Unpack
    $parts = explode( ':', $packed );
    if ( count( $parts ) !== 3 ) {
        return new WP_Error( 'ns_vault_bad_format', 'Invalid vault data format.' );
    }

    $iv         = base64_decode( $parts[0], true );
    $ciphertext = base64_decode( $parts[1], true );
    $hmac       = base64_decode( $parts[2], true );

    if ( $iv === false || $ciphertext === false || $hmac === false ) {
        return new WP_Error( 'ns_vault_decode_fail', 'Base64 decode failed.' );
    }

    // Verify HMAC first (timing-safe comparison)
    $expected_hmac = hash_hmac( 'sha256', $iv . $ciphertext, $key, true );
    if ( ! hash_equals( $expected_hmac, $hmac ) ) {
        return new WP_Error( 'ns_vault_hmac_fail', 'HMAC verification failed. Data may be tampered.' );
    }

    // Decrypt
    $plaintext = openssl_decrypt( $ciphertext, NS_VAULT_CIPHER, $key, OPENSSL_RAW_DATA, $iv );
    if ( $plaintext === false ) {
        return new WP_Error( 'ns_vault_decrypt_fail', 'Decryption failed.' );
    }

    return $plaintext;
}

/* ==================================================================
   2. STORE / RETRIEVE / DELETE API
   ================================================================== */

/**
 * Store a token in the vault.
 *
 * @param string $service Service key (must be in allowed list).
 * @param string $token   Plaintext token value.
 * @return bool|WP_Error True on success, WP_Error on failure.
 */
function ns_vault_store( $service, $token ) {
    if ( ! in_array( $service, ns_vault_allowed_services(), true ) ) {
        return new WP_Error( 'ns_vault_invalid_service', 'Service "' . esc_html( $service ) . '" is not in the allowed list.' );
    }

    if ( empty( $token ) ) {
        return new WP_Error( 'ns_vault_empty_token', 'Token value cannot be empty.' );
    }

    $encrypted = ns_vault_encrypt( $token );
    if ( is_wp_error( $encrypted ) ) {
        return $encrypted;
    }

    $option_name = NS_VAULT_PREFIX . sanitize_key( $service );
    $updated = update_option( $option_name, $encrypted, false ); // autoload = false

    // Log the store event (no token value in log)
    ns_vault_log( 'store', $service );

    return true;
}

/**
 * Retrieve a decrypted token from the vault.
 *
 * @param string $service Service key.
 * @return string|WP_Error Decrypted token or WP_Error.
 */
function ns_vault_retrieve( $service ) {
    if ( ! in_array( $service, ns_vault_allowed_services(), true ) ) {
        return new WP_Error( 'ns_vault_invalid_service', 'Service "' . esc_html( $service ) . '" is not in the allowed list.' );
    }

    $option_name = NS_VAULT_PREFIX . sanitize_key( $service );
    $encrypted   = get_option( $option_name, '' );

    if ( empty( $encrypted ) ) {
        return new WP_Error( 'ns_vault_not_found', 'No token stored for service: ' . esc_html( $service ) );
    }

    return ns_vault_decrypt( $encrypted );
}

/**
 * Delete a token from the vault.
 *
 * @param string $service Service key.
 * @return bool True on success.
 */
function ns_vault_delete( $service ) {
    if ( ! in_array( $service, ns_vault_allowed_services(), true ) ) {
        return false;
    }

    $option_name = NS_VAULT_PREFIX . sanitize_key( $service );
    $deleted = delete_option( $option_name );

    if ( $deleted ) {
        ns_vault_log( 'delete', $service );
    }

    return $deleted;
}

/**
 * Check if a service has a stored token.
 *
 * @param string $service Service key.
 * @return bool
 */
function ns_vault_has_token( $service ) {
    $option_name = NS_VAULT_PREFIX . sanitize_key( $service );
    return get_option( $option_name, '' ) !== '';
}

/**
 * Get masked version of a token (for admin display).
 * Shows first 4 and last 4 characters only.
 *
 * @param string $service Service key.
 * @return string Masked token or status message.
 */
function ns_vault_masked( $service ) {
    $token = ns_vault_retrieve( $service );
    if ( is_wp_error( $token ) ) {
        return '— not set —';
    }

    $len = strlen( $token );
    if ( $len <= 8 ) {
        return str_repeat( '•', $len );
    }

    return substr( $token, 0, 4 ) . str_repeat( '•', min( $len - 8, 20 ) ) . substr( $token, -4 );
}

/* ==================================================================
   3. AUDIT LOGGING
   ================================================================== */

/**
 * Log vault operations. Prepares for S1.22 audit logging integration.
 * Never logs actual token values.
 *
 * @param string $action  Action performed (store, delete, rotate).
 * @param string $service Service key.
 */
function ns_vault_log( $action, $service ) {
    $log_entry = array(
        'timestamp' => current_time( 'mysql' ),
        'user_id'   => get_current_user_id(),
        'action'    => sanitize_key( $action ),
        'service'   => sanitize_key( $service ),
        'ip'        => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : 'cli',
    );

    // Store recent log entries in a rolling option (max 100)
    $log = get_option( 'ns_vault_audit_log', array() );
    array_unshift( $log, $log_entry );
    $log = array_slice( $log, 0, 100 );
    update_option( 'ns_vault_audit_log', $log, false );
}

/* ==================================================================
   4. ADMIN UI — TOKEN MANAGEMENT PAGE
   ================================================================== */

add_action( 'admin_menu', 'ns_vault_register_admin_page' );

function ns_vault_register_admin_page() {
    add_submenu_page(
        'edit.php?post_type=ns_feed_item',
        'Token Vault',
        'Token Vault',
        'manage_options',  // Admins only
        'ns-token-vault',
        'ns_vault_render_admin_page'
    );
}

/**
 * Handle form submissions for storing/deleting tokens.
 */
add_action( 'admin_init', 'ns_vault_handle_form' );

function ns_vault_handle_form() {
    if ( ! isset( $_POST['ns_vault_action'] ) ) {
        return;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized.' );
    }

    // Verify nonce
    if ( ! isset( $_POST['ns_vault_nonce'] ) || ! wp_verify_nonce( $_POST['ns_vault_nonce'], 'ns_vault_manage' ) ) {
        wp_die( 'Security check failed.' );
    }

    $action  = sanitize_key( $_POST['ns_vault_action'] );
    $service = isset( $_POST['ns_vault_service'] ) ? sanitize_key( $_POST['ns_vault_service'] ) : '';

    if ( $action === 'store' && ! empty( $service ) ) {
        $token = isset( $_POST['ns_vault_token'] ) ? trim( $_POST['ns_vault_token'] ) : '';
        if ( ! empty( $token ) ) {
            $result = ns_vault_store( $service, $token );
            if ( is_wp_error( $result ) ) {
                add_settings_error( 'ns_vault', 'store_error', $result->get_error_message(), 'error' );
            } else {
                add_settings_error( 'ns_vault', 'store_success', 'Token for "' . esc_html( $service ) . '" stored securely.', 'success' );
            }
        }
    }

    if ( $action === 'delete' && ! empty( $service ) ) {
        ns_vault_delete( $service );
        add_settings_error( 'ns_vault', 'delete_success', 'Token for "' . esc_html( $service ) . '" deleted.', 'success' );
    }

    // Redirect to prevent form resubmission
    set_transient( 'ns_vault_notices', get_settings_errors( 'ns_vault' ), 30 );
    wp_safe_redirect( admin_url( 'edit.php?post_type=ns_feed_item&page=ns-token-vault&updated=1' ) );
    exit;
}

/**
 * Render the Token Vault admin page.
 * Tokens are NEVER displayed in plaintext — masked only.
 */
function ns_vault_render_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized.' );
    }

    // Show any stored notices
    $notices = get_transient( 'ns_vault_notices' );
    if ( $notices ) {
        delete_transient( 'ns_vault_notices' );
        foreach ( $notices as $notice ) {
            printf(
                '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                esc_attr( $notice['type'] ),
                esc_html( $notice['message'] )
            );
        }
    }

    // Vault key status
    $key_valid = ns_vault_validate_key();
    $key_ok    = ( $key_valid === true );

    $services = ns_vault_allowed_services();

    ?>
    <div class="wrap">
        <h1>🔐 Token Vault</h1>
        <p>AES-256-CBC encrypted credential storage. Tokens are never displayed in plaintext.</p>

        <?php if ( ! $key_ok ) : ?>
            <div class="notice notice-error">
                <p><strong>Vault Disabled:</strong>
                <?php echo esc_html( $key_valid->get_error_message() ); ?></p>
                <p>Add to <code>wp-config.php</code>:<br>
                <code>define( 'NS_VAULT_KEY', '<?php echo esc_html( bin2hex( random_bytes( 32 ) ) ); ?>' );</code></p>
            </div>
        <?php else : ?>
            <div class="notice notice-success"><p>Vault key is configured and valid (AES-256-CBC).</p></div>
        <?php endif; ?>

        <h2>Stored Credentials</h2>
        <table class="widefat striped" style="max-width:800px;">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Status</th>
                    <th>Masked Value</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $services as $svc ) :
                    $has   = ns_vault_has_token( $svc );
                    $label = ucwords( str_replace( '_', ' ', $svc ) );
                ?>
                <tr>
                    <td><strong><?php echo esc_html( $label ); ?></strong><br>
                        <code style="font-size:11px;color:#666;"><?php echo esc_html( $svc ); ?></code></td>
                    <td><?php echo $has ? '<span style="color:green;">● Set</span>' : '<span style="color:#999;">○ Empty</span>'; ?></td>
                    <td><code><?php echo esc_html( ns_vault_masked( $svc ) ); ?></code></td>
                    <td>
                        <?php if ( $key_ok ) : ?>
                        <form method="post" style="display:inline;">
                            <?php wp_nonce_field( 'ns_vault_manage', 'ns_vault_nonce' ); ?>
                            <input type="hidden" name="ns_vault_service" value="<?php echo esc_attr( $svc ); ?>">
                            <input type="hidden" name="ns_vault_action" value="store">
                            <input type="password" name="ns_vault_token" placeholder="Paste token…"
                                   style="width:200px;" autocomplete="off" required>
                            <button type="submit" class="button button-small">Save</button>
                        </form>
                        <?php if ( $has ) : ?>
                        <form method="post" style="display:inline;margin-left:5px;"
                              onsubmit="return confirm('Delete this token?');">
                            <?php wp_nonce_field( 'ns_vault_manage', 'ns_vault_nonce' ); ?>
                            <input type="hidden" name="ns_vault_service" value="<?php echo esc_attr( $svc ); ?>">
                            <input type="hidden" name="ns_vault_action" value="delete">
                            <button type="submit" class="button button-small" style="color:#a00;">Delete</button>
                        </form>
                        <?php endif; ?>
                        <?php else : ?>
                            <em>Vault disabled</em>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2 style="margin-top:30px;">Audit Log (Last 20)</h2>
        <table class="widefat striped" style="max-width:800px;">
            <thead>
                <tr><th>Time</th><th>User</th><th>Action</th><th>Service</th><th>IP</th></tr>
            </thead>
            <tbody>
                <?php
                $log = get_option( 'ns_vault_audit_log', array() );
                $log = array_slice( $log, 0, 20 );
                if ( empty( $log ) ) :
                ?>
                    <tr><td colspan="5"><em>No vault activity recorded yet.</em></td></tr>
                <?php else :
                    foreach ( $log as $entry ) :
                        $user = get_userdata( $entry['user_id'] );
                        $username = $user ? $user->user_login : '#' . $entry['user_id'];
                ?>
                    <tr>
                        <td><?php echo esc_html( $entry['timestamp'] ); ?></td>
                        <td><?php echo esc_html( $username ); ?></td>
                        <td><?php echo esc_html( strtoupper( $entry['action'] ) ); ?></td>
                        <td><code><?php echo esc_html( $entry['service'] ); ?></code></td>
                        <td><?php echo esc_html( $entry['ip'] ); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>

        <h2 style="margin-top:30px;">Usage for Other Snippets</h2>
        <p>Retrieve a token in any snippet:</p>
        <pre style="background:#f0f0f0;padding:10px;max-width:600px;"><code>$api_key = ns_vault_retrieve( 'claude_api_key' );
if ( is_wp_error( $api_key ) ) {
    // Handle missing/invalid token
    error_log( $api_key->get_error_message() );
} else {
    // Use $api_key safely
}</code></pre>
    </div>
    <?php
}

/* ==================================================================
   5. KEY ROTATION HELPER (for S1.20)
   ================================================================== */

/**
 * Re-encrypt all stored tokens with a new key.
 * Called during key rotation procedure.
 * Must be run AFTER updating NS_VAULT_KEY in wp-config.php.
 *
 * @param string $old_key_hex Previous 64-char hex key.
 * @return array Results per service.
 */
function ns_vault_rotate_key( $old_key_hex ) {
    if ( ! current_user_can( 'manage_options' ) ) {
        return array( 'error' => 'Unauthorized' );
    }

    $old_key = @hex2bin( $old_key_hex );
    if ( $old_key === false || strlen( $old_key ) !== 32 ) {
        return array( 'error' => 'Invalid old key format' );
    }

    $results = array();

    foreach ( ns_vault_allowed_services() as $service ) {
        $option_name = NS_VAULT_PREFIX . sanitize_key( $service );
        $encrypted   = get_option( $option_name, '' );

        if ( empty( $encrypted ) ) {
            $results[ $service ] = 'skipped (empty)';
            continue;
        }

        // Decrypt with old key
        $parts = explode( ':', $encrypted );
        if ( count( $parts ) !== 3 ) {
            $results[ $service ] = 'error (bad format)';
            continue;
        }

        $iv         = base64_decode( $parts[0], true );
        $ciphertext = base64_decode( $parts[1], true );
        $hmac       = base64_decode( $parts[2], true );

        // Verify HMAC with old key
        $expected = hash_hmac( 'sha256', $iv . $ciphertext, $old_key, true );
        if ( ! hash_equals( $expected, $hmac ) ) {
            $results[ $service ] = 'error (HMAC failed with old key)';
            continue;
        }

        // Decrypt with old key
        $plaintext = openssl_decrypt( $ciphertext, NS_VAULT_CIPHER, $old_key, OPENSSL_RAW_DATA, $iv );
        if ( $plaintext === false ) {
            $results[ $service ] = 'error (decrypt failed)';
            continue;
        }

        // Re-encrypt with new key (uses current NS_VAULT_KEY)
        $new_encrypted = ns_vault_encrypt( $plaintext );
        if ( is_wp_error( $new_encrypted ) ) {
            $results[ $service ] = 'error (' . $new_encrypted->get_error_message() . ')';
            continue;
        }

        update_option( $option_name, $new_encrypted, false );
        ns_vault_log( 'rotate', $service );
        $results[ $service ] = 'rotated';
    }

    return $results;
}

/* ==================================================================
   6. SECURITY HARDENING
   ================================================================== */

/**
 * Block direct access to vault options via REST API.
 * Prevents token exfiltration through default WP REST endpoints.
 */
add_filter( 'rest_pre_dispatch', 'ns_vault_block_rest_option_access', 10, 3 );

function ns_vault_block_rest_option_access( $result, $server, $request ) {
    $route = $request->get_route();

    // Block any REST request trying to read vault options
    if ( strpos( $route, '/wp/v2/settings' ) !== false ||
         strpos( $route, 'ns_vault_' ) !== false ) {
        // Allow only if explicitly requesting our own endpoints
        if ( strpos( $route, '/ns/v1/' ) === false ) {
            // Check if any vault option names appear in request params
            $params = $request->get_params();
            $param_string = wp_json_encode( $params );
            if ( strpos( $param_string, 'ns_vault_' ) !== false ) {
                return new WP_Error(
                    'ns_vault_rest_blocked',
                    'Vault data cannot be accessed via REST API.',
                    array( 'status' => 403 )
                );
            }
        }
    }

    return $result;
}

/**
 * Remove vault options from any export/debug output.
 */
add_filter( 'option_ns_vault_claude_api_key', 'ns_vault_redact_on_read' );
add_filter( 'option_ns_vault_twitter_oauth_token', 'ns_vault_redact_on_read' );

function ns_vault_redact_on_read( $value ) {
    // Only redact in non-admin contexts or if accessed outside our functions
    $bt = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 5 );
    foreach ( $bt as $frame ) {
        if ( isset( $frame['function'] ) && strpos( $frame['function'], 'ns_vault_' ) === 0 ) {
            return $value; // Our own code — allow
        }
    }
    return '[REDACTED]';
}
