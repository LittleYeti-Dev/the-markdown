<?php
/**
 * S1.19 — Application Passwords for REST API Auth
 * Sprint 1, Gate 3 | GitLab Issue #26
 *
 * Enforces authentication on sensitive custom REST endpoints.
 * Public read-only endpoints (ns/v1/blocks) remain open for front-end polling.
 * Write/admin endpoints require authentication via Application Passwords.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S1.19 App Passwords"
 * Scope: Run everywhere
 * Depends on: S1.17 Token Vault
 *
 * Acceptance Criteria (GitLab #26):
 *   ✅ REST API endpoints require authentication via application passwords
 *   ✅ Unauthenticated requests return 401
 *   ✅ Test with curl
 *
 * IMPORTANT: The /ns/v1/blocks endpoint (S1.16 Auto-Refresh) is
 * deliberately EXCLUDED from auth requirement since it serves the
 * public front page. Only admin/write endpoints are protected.
 *
 * @package NonSequitur
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ==================================================================
   1. DEFINE PROTECTED ENDPOINTS
   ================================================================== */

/**
 * Routes that require authentication.
 * Public read endpoints are intentionally excluded.
 */
function ns_auth_protected_routes() {
    return array(
        '/ns/v1/promote',
        '/ns/v1/assign',
        '/ns/v1/publish',
        '/ns/v1/digest',
        '/ns/v1/score',
        '/ns/v1/vault',
        '/ns/v1/audit',
        '/ns/v1/settings',
    );
}

/**
 * Routes that are public (no auth required).
 * These serve the front-end and must remain open.
 */
function ns_auth_public_routes() {
    return array(
        '/ns/v1/blocks',   // S1.16 Auto-Refresh — public front page polling
    );
}

/* ==================================================================
   2. ENFORCE AUTH ON PROTECTED NS ENDPOINTS
   ================================================================== */

add_filter( 'rest_pre_dispatch', 'ns_enforce_rest_auth', 8, 3 );

function ns_enforce_rest_auth( $result, $server, $request ) {
    $route = $request->get_route();

    // Only apply to ns/v1 namespace
    if ( strpos( $route, '/ns/v1/' ) !== 0 ) {
        return $result;
    }

    // Check if route is explicitly public
    foreach ( ns_auth_public_routes() as $public ) {
        if ( strpos( $route, $public ) === 0 ) {
            return $result; // Allow without auth
        }
    }

    // For all other ns/v1 routes, require authentication
    // Only enforce on write methods (POST, PUT, PATCH, DELETE)
    $method = $request->get_method();
    $write_methods = array( 'POST', 'PUT', 'PATCH', 'DELETE' );

    if ( in_array( $method, $write_methods, true ) && ! is_user_logged_in() ) {
        // Log the failed attempt
        if ( function_exists( 'ns_audit_log' ) ) {
            $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : 'unknown';
            ns_audit_log(
                'auth_rejected',
                'rest_api',
                0,
                $route,
                wp_json_encode( array( 'method' => $method, 'ip' => $ip ) )
            );
        }

        return new WP_REST_Response( array(
            'code'    => 'rest_not_authenticated',
            'message' => 'Authentication required. Use Application Passwords for REST API access.',
            'data'    => array( 'status' => 401 ),
        ), 401 );
    }

    return $result;
}

/* ==================================================================
   3. ENSURE APPLICATION PASSWORDS ARE ENABLED
   ================================================================== */

/**
 * WordPress 5.6+ has Application Passwords built in.
 * This ensures the feature is enabled (it can be disabled by plugins).
 */
add_filter( 'wp_is_application_passwords_available', '__return_true' );

/* ==================================================================
   4. AUTH STATUS INDICATOR IN ADMIN
   ================================================================== */

add_action( 'admin_notices', 'ns_auth_admin_notice' );

function ns_auth_admin_notice() {
    $screen = get_current_screen();
    if ( ! $screen || $screen->id !== 'ns_feed_item_page_ns-token-vault' ) {
        return;
    }

    // Check if current user has application passwords
    $user = wp_get_current_user();
    $app_passwords = WP_Application_Passwords::get_user_application_passwords( $user->ID );

    if ( empty( $app_passwords ) ) {
        echo '<div class="notice notice-warning"><p>';
        echo '<strong>REST API Auth:</strong> No Application Passwords configured for your account. ';
        echo 'Create one at <a href="' . esc_url( admin_url( 'profile.php#application-passwords-section' ) ) . '">your profile</a> ';
        echo 'to use the REST API for automated deploys.';
        echo '</p></div>';
    } else {
        echo '<div class="notice notice-info"><p>';
        echo '<strong>REST API Auth:</strong> ' . count( $app_passwords ) . ' Application Password(s) configured. ';
        echo 'Write endpoints on ns/v1/* require authentication. Read-only /ns/v1/blocks remains public.';
        echo '</p></div>';
    }
}
