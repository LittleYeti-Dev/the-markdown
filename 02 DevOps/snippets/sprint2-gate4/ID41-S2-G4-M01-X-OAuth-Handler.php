<?php
// Snippet ID: 41
// Name: S2-G4-M01 X OAuth Handler
// Scope: global
// Active: True
// Modified: 2026-03-09 22:41:56
// Lines: 110

/**
 * S2-G4-M01 — X (Twitter) OAuth 2.0 PKCE Handler
 * Sprint 2, Gate 4 | GitLab Issue #30
 *
 * Registers OAuth 2.0 authorization + callback REST routes for X API v2.
 * Uses PKCE (Proof Key for Code Exchange) — no client secret in auth URL.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S2-G4-M01 X OAuth Handler"
 * Scope: global
 * Priority: 10
 * Depends on: Token Vault (S1.17), Audit Log (S1.22), Diagnostic Logger (S1.23)
 *
 * @package NonSequitur
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'rest_api_init', 'ns_twitter_register_oauth_routes' );

function ns_twitter_register_oauth_routes() {
    register_rest_route( 'ns/v1', '/oauth/twitter/start', array(
        'methods'             => 'GET',
        'callback'            => 'ns_twitter_oauth_start',
        'permission_callback' => function () { return current_user_can( 'manage_options' ); },
    ) );
    register_rest_route( 'ns/v1', '/oauth/twitter/callback', array(
        'methods'             => 'GET',
        'callback'            => 'ns_twitter_oauth_callback',
        'permission_callback' => '__return_true',
    ) );
}

function ns_twitter_oauth_start( $request ) {
    $client_id = function_exists( 'ns_vault_retrieve' ) ? ns_vault_retrieve( 'twitter_client_id' ) : '';
    if ( is_wp_error( $client_id ) || empty( $client_id ) ) {
        return new WP_Error( 'ns_twitter_no_creds', 'Twitter Client ID not configured.', array( 'status' => 500 ) );
    }

    // PKCE: generate code_verifier + code_challenge
    $verifier  = wp_generate_password( 64, false );
    $challenge = rtrim( strtr( base64_encode( hash( 'sha256', $verifier, true ) ), '+/', '-_' ), '=' );
    $state     = wp_generate_password( 32, false );

    set_transient( 'ns_twitter_oauth_verifier_' . $state, $verifier, 600 );
    set_transient( 'ns_twitter_oauth_state_' . $state, true, 600 );

    $callback_url = rest_url( 'ns/v1/oauth/twitter/callback' );
    $scopes       = 'tweet.read tweet.write users.read offline.access';

    $auth_url = add_query_arg( array(
        'response_type'         => 'code',
        'client_id'             => $client_id,
        'redirect_uri'          => $callback_url,
        'scope'                 => $scopes,
        'state'                 => $state,
        'code_challenge'        => $challenge,
        'code_challenge_method' => 'S256',
    ), 'https://twitter.com/i/oauth2/authorize' );

    if ( function_exists( 'ns_audit_log' ) ) {
        ns_audit_log( 'twitter_oauth_start', 'oauth', 'twitter', 'OAuth flow started', array() );
    }

    wp_redirect( $auth_url );
    exit;
}

function ns_twitter_oauth_callback( $request ) {
    $code  = sanitize_text_field( $request->get_param( 'code' ) ?? '' );
    $state = sanitize_text_field( $request->get_param( 'state' ) ?? '' );
    $error = $request->get_param( 'error' );

    if ( $error || empty( $code ) || empty( $state ) ) {
        if ( function_exists( 'ns_diag_write' ) ) {
            ns_diag_write( 'error', 'twitter', 'OAuth callback error', array( 'error' => $error ?? 'missing params' ) );
        }
        return new WP_Error( 'ns_twitter_oauth_error', 'OAuth authorization failed.', array( 'status' => 400 ) );
    }

    // Validate state
    if ( ! get_transient( 'ns_twitter_oauth_state_' . $state ) ) {
        if ( function_exists( 'ns_diag_write' ) ) {
            ns_diag_write( 'warning', 'twitter', 'Invalid OAuth state parameter', array() );
        }
        return new WP_Error( 'ns_twitter_bad_state', 'Invalid state parameter.', array( 'status' => 403 ) );
    }

    $verifier = get_transient( 'ns_twitter_oauth_verifier_' . $state );
    delete_transient( 'ns_twitter_oauth_state_' . $state );
    delete_transient( 'ns_twitter_oauth_verifier_' . $state );

    if ( ! function_exists( 'ns_twitter_exchange_code' ) ) {
        return new WP_Error( 'ns_twitter_missing_dep', 'Token manager not loaded.', array( 'status' => 500 ) );
    }

    $result = ns_twitter_exchange_code( $code, $verifier );
    if ( is_wp_error( $result ) ) {
        return $result;
    }

    if ( function_exists( 'ns_audit_log' ) ) {
        ns_audit_log( 'twitter_oauth_complete', 'oauth', 'twitter', 'OAuth flow completed', array() );
    }

    wp_redirect( admin_url( 'edit.php?post_type=ns_feed_item&page=ns-twitter-settings&connected=1' ) );
    exit;
}
