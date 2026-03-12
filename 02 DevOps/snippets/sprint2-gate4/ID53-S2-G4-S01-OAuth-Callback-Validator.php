<?php
// Snippet ID: 53
// Name: S2-G4-S01 OAuth Callback Validator
// Scope: global
// Active: True
// Modified: 2026-03-09 22:42:10
// Lines: 107

/**
 * S2-G4-S01 — OAuth Callback URL Validator
 * Sprint 2, Gate 4 | GitLab Issue #35
 *
 * Central callback URL whitelist. All OAuth handlers must validate
 * callback URLs against this whitelist before processing.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S2-G4-S01 OAuth Callback Validator"
 * Scope: global
 * Priority: 10
 * Depends on: Audit Log (S1.22), Diagnostic Logger (S1.23)
 *
 * @package NonSequitur
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get the list of registered OAuth callback URLs.
 *
 * @return array List of allowed callback URL patterns.
 */
function ns_oauth_get_registered_callbacks() {
    $defaults = array(
        rest_url( 'ns/v1/oauth/twitter/callback' ),
        rest_url( 'ns/v1/oauth/linkedin/callback' ),
        rest_url( 'ns/v1/oauth/youtube/callback' ),
    );
    $custom = get_option( 'ns_oauth_callback_whitelist', array() );
    if ( ! is_array( $custom ) ) $custom = array();
    return array_unique( array_merge( $defaults, $custom ) );
}

/**
 * Validate that a callback URL is in the registered whitelist.
 *
 * @param string $url The callback URL to validate.
 * @return bool True if valid, false if rejected.
 */
function ns_oauth_validate_callback( $url ) {
    if ( empty( $url ) ) {
        ns_oauth_log_rejection( $url, 'empty URL' );
        return false;
    }

    // Must be HTTPS
    if ( strpos( $url, 'https://' ) !== 0 ) {
        ns_oauth_log_rejection( $url, 'not HTTPS' );
        return false;
    }

    $allowed = ns_oauth_get_registered_callbacks();

    // Normalize URLs for comparison (strip trailing slashes)
    $normalized = untrailingslashit( $url );
    foreach ( $allowed as $registered ) {
        if ( untrailingslashit( $registered ) === $normalized ) {
            return true;
        }
    }

    ns_oauth_log_rejection( $url, 'not in whitelist' );
    return false;
}

/**
 * Log a rejected callback attempt.
 */
function ns_oauth_log_rejection( $url, $reason ) {
    if ( function_exists( 'ns_audit_log' ) ) {
        ns_audit_log( 'oauth_callback_rejected', 'security', 'oauth', 'Callback rejected: ' . $reason, array(
            'url' => substr( $url, 0, 200 ),
        ) );
    }
    if ( function_exists( 'ns_diag_write' ) ) {
        ns_diag_write( 'warning', 'oauth', 'Callback URL rejected', array(
            'url'    => substr( $url, 0, 200 ),
            'reason' => $reason,
        ) );
    }
}

/**
 * Hook into REST pre-dispatch to validate OAuth callback requests.
 * Checks that incoming OAuth callbacks match registered redirect_uri values.
 */
add_filter( 'rest_pre_dispatch', 'ns_oauth_enforce_callback_validation', 5, 3 );

function ns_oauth_enforce_callback_validation( $result, $server, $request ) {
    $route = $request->get_route();

    // Only check our OAuth callback routes
    if ( strpos( $route, '/ns/v1/oauth/' ) === false || strpos( $route, '/callback' ) === false ) {
        return $result;
    }

    // The callback URL is the current request URL — validate it's registered
    $current_url = rest_url( ltrim( $route, '/' ) );
    if ( ! ns_oauth_validate_callback( $current_url ) ) {
        return new WP_Error( 'ns_oauth_invalid_callback', 'Unregistered callback URL.', array( 'status' => 403 ) );
    }

    return $result;
}
