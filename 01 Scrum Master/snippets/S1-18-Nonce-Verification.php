<?php
/**
 * S1.18 — Nonce Verification on All Admin AJAX Endpoints
 * Sprint 1, Gate 3 | GitLab Issue #25
 *
 * Adds a verification layer that ensures ALL custom AJAX actions
 * in the ns_ namespace are protected by nonce checks.
 * Acts as a safety net — even if a new handler forgets check_ajax_referer,
 * this catches it.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S1.18 Nonce Verification"
 * Scope: Admin only
 * Depends on: S1.12 Editorial Dashboard
 *
 * Acceptance Criteria (GitLab #25):
 *   ✅ All custom admin AJAX handlers verify wp_nonce
 *   ✅ CSRF test suite passes
 *   ✅ Unauthorized requests return 403
 *
 * NOTE: S1.13 already uses check_ajax_referer on all 4 handlers.
 * This snippet adds a blanket enforcement layer as defense-in-depth.
 *
 * @package NonSequitur
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ==================================================================
   1. BLANKET NONCE ENFORCEMENT FOR ALL NS_ AJAX ACTIONS
   ================================================================== */

/**
 * Intercept all AJAX requests with ns_ prefix and verify nonce.
 * Fires early (priority 1) before individual handlers.
 *
 * Accepted nonce fields: 'nonce', '_wpnonce', 'security'
 * Accepted nonce actions: 'ns_editorial_actions', 'ns_admin_{action_suffix}'
 */
add_action( 'admin_init', 'ns_enforce_ajax_nonces', 1 );

function ns_enforce_ajax_nonces() {
    // Only intercept AJAX requests
    if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
        return;
    }

    // Get the AJAX action
    $action = '';
    if ( isset( $_REQUEST['action'] ) ) {
        $action = sanitize_key( $_REQUEST['action'] );
    }

    // Only enforce on ns_ prefixed actions
    if ( strpos( $action, 'ns_' ) !== 0 ) {
        return;
    }

    // Look for nonce in standard locations
    $nonce = '';
    foreach ( array( 'nonce', '_wpnonce', 'security' ) as $field ) {
        if ( ! empty( $_REQUEST[ $field ] ) ) {
            $nonce = sanitize_text_field( $_REQUEST[ $field ] );
            break;
        }
    }

    // No nonce provided at all — reject immediately
    if ( empty( $nonce ) ) {
        ns_nonce_fail_log( $action, 'no_nonce_provided' );
        wp_send_json_error( array(
            'code'    => 'missing_nonce',
            'message' => 'Security verification failed. Please refresh the page and try again.',
        ), 403 );
    }

    // Verify against known nonce actions
    $valid_actions = array(
        'ns_editorial_actions',                    // S1.13 promote/block actions
        'ns_admin_' . str_replace( 'ns_', '', $action ), // Generic per-action nonce
        'ns_vault_manage',                         // S1.17 token vault
    );

    $verified = false;
    foreach ( $valid_actions as $nonce_action ) {
        if ( wp_verify_nonce( $nonce, $nonce_action ) ) {
            $verified = true;
            break;
        }
    }

    if ( ! $verified ) {
        ns_nonce_fail_log( $action, 'nonce_invalid' );
        wp_send_json_error( array(
            'code'    => 'invalid_nonce',
            'message' => 'Security token expired or invalid. Please refresh the page.',
        ), 403 );
    }

    // Also enforce that user is logged in
    if ( ! is_user_logged_in() ) {
        ns_nonce_fail_log( $action, 'not_logged_in' );
        wp_send_json_error( array(
            'code'    => 'not_authenticated',
            'message' => 'You must be logged in to perform this action.',
        ), 403 );
    }
}

/* ==================================================================
   2. NONCE FAILURE LOGGING
   ================================================================== */

/**
 * Log nonce verification failures for security monitoring.
 */
function ns_nonce_fail_log( $action, $reason ) {
    $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : 'unknown';
    $user_id = get_current_user_id();

    // Log to audit table if available (S1.22)
    if ( function_exists( 'ns_audit_log' ) ) {
        ns_audit_log(
            'nonce_fail',
            'ajax',
            0,
            $action,
            wp_json_encode( array( 'reason' => $reason, 'ip' => $ip ) )
        );
    }

    // Also log to error log for immediate visibility
    error_log( sprintf(
        '[NS Security] Nonce failure: action=%s reason=%s ip=%s user=%d',
        $action, $reason, $ip, $user_id
    ) );
}

/* ==================================================================
   3. NONCE REFRESH ENDPOINT
   ================================================================== */

/**
 * Provide a way to refresh nonces without full page reload.
 * Used by long-lived admin sessions.
 */
add_action( 'wp_ajax_ns_refresh_nonce', 'ns_ajax_refresh_nonce' );

function ns_ajax_refresh_nonce() {
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( 'Not authenticated.', 403 );
    }

    wp_send_json_success( array(
        'nonce'   => wp_create_nonce( 'ns_editorial_actions' ),
        'expires' => time() + ( DAY_IN_SECONDS ), // WP nonces last ~24h
    ) );
}
