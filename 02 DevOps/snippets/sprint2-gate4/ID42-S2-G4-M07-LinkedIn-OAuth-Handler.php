<?php
// Snippet ID: 42
// Name: S2-G4-M07 LinkedIn OAuth Handler
// Scope: global
// Active: True
// Modified: 2026-03-09 22:41:57
// Lines: 96

/**
 * S2-G4-M07 — LinkedIn OAuth 2.0 Handler
 * Sprint 2, Gate 4 | GitLab Issue #31
 *
 * OAuth 2.0 Authorization Code flow for LinkedIn API.
 * Registers start + callback REST routes.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S2-G4-M07 LinkedIn OAuth Handler"
 * Scope: global
 * Priority: 10
 * Depends on: Token Vault (S1.17), Audit Log (S1.22), Diagnostic Logger (S1.23)
 *
 * @package NonSequitur
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'rest_api_init', 'ns_linkedin_register_oauth_routes' );

function ns_linkedin_register_oauth_routes() {
    register_rest_route( 'ns/v1', '/oauth/linkedin/start', array(
        'methods'             => 'GET',
        'callback'            => 'ns_linkedin_oauth_start',
        'permission_callback' => function () { return current_user_can( 'manage_options' ); },
    ) );
    register_rest_route( 'ns/v1', '/oauth/linkedin/callback', array(
        'methods'             => 'GET',
        'callback'            => 'ns_linkedin_oauth_callback',
        'permission_callback' => '__return_true',
    ) );
}

function ns_linkedin_oauth_start( $request ) {
    $client_id = function_exists( 'ns_vault_retrieve' ) ? ns_vault_retrieve( 'linkedin_client_id' ) : '';
    if ( is_wp_error( $client_id ) || empty( $client_id ) ) {
        return new WP_Error( 'ns_linkedin_no_creds', 'LinkedIn Client ID not configured.', array( 'status' => 500 ) );
    }

    $state = wp_generate_password( 32, false );
    set_transient( 'ns_linkedin_oauth_state_' . $state, true, 600 );

    $auth_url = add_query_arg( array(
        'response_type' => 'code',
        'client_id'     => $client_id,
        'redirect_uri'  => rest_url( 'ns/v1/oauth/linkedin/callback' ),
        'state'         => $state,
        'scope'         => 'openid profile w_member_social',
    ), 'https://www.linkedin.com/oauth/v2/authorization' );

    if ( function_exists( 'ns_audit_log' ) ) {
        ns_audit_log( 'linkedin_oauth_start', 'oauth', 'linkedin', 'OAuth flow started', array() );
    }

    wp_redirect( $auth_url );
    exit;
}

function ns_linkedin_oauth_callback( $request ) {
    $code  = sanitize_text_field( $request->get_param( 'code' ) ?? '' );
    $state = sanitize_text_field( $request->get_param( 'state' ) ?? '' );
    $error = $request->get_param( 'error' );

    if ( $error || empty( $code ) || empty( $state ) ) {
        if ( function_exists( 'ns_diag_write' ) ) {
            ns_diag_write( 'error', 'linkedin', 'OAuth callback error', array( 'error' => $error ?? 'missing params' ) );
        }
        return new WP_Error( 'ns_linkedin_oauth_error', 'OAuth failed.', array( 'status' => 400 ) );
    }

    if ( ! get_transient( 'ns_linkedin_oauth_state_' . $state ) ) {
        if ( function_exists( 'ns_diag_write' ) ) {
            ns_diag_write( 'warning', 'linkedin', 'Invalid OAuth state', array() );
        }
        return new WP_Error( 'ns_linkedin_bad_state', 'Invalid state.', array( 'status' => 403 ) );
    }
    delete_transient( 'ns_linkedin_oauth_state_' . $state );

    if ( ! function_exists( 'ns_linkedin_exchange_code' ) ) {
        return new WP_Error( 'ns_linkedin_missing_dep', 'Token manager not loaded.', array( 'status' => 500 ) );
    }

    $result = ns_linkedin_exchange_code( $code );
    if ( is_wp_error( $result ) ) {
        return $result;
    }

    if ( function_exists( 'ns_audit_log' ) ) {
        ns_audit_log( 'linkedin_oauth_complete', 'oauth', 'linkedin', 'OAuth completed', array() );
    }

    wp_redirect( admin_url( 'edit.php?post_type=ns_feed_item&page=ns-linkedin-settings&connected=1' ) );
    exit;
}
