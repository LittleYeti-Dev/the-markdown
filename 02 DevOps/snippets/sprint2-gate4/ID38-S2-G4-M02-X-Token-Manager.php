<?php
// Snippet ID: 38
// Name: S2-G4-M02 X Token Manager
// Scope: global
// Active: True
// Modified: 2026-03-09 22:41:52
// Lines: 131

/**
 * S2-G4-M02 — X (Twitter) Token Manager
 * Sprint 2, Gate 4 | GitLab Issue #30
 *
 * Exchange authorization code for tokens, store/refresh via Token Vault.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S2-G4-M02 X Token Manager"
 * Scope: global
 * Priority: 10
 * Depends on: Token Vault (S1.17), Diagnostic Logger (S1.23)
 *
 * @package NonSequitur
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Exchange authorization code for access + refresh tokens.
 *
 * @param string $code     Authorization code from callback.
 * @param string $verifier PKCE code_verifier.
 * @return bool|WP_Error True on success.
 */
function ns_twitter_exchange_code( $code, $verifier ) {
    $client_id     = ns_vault_retrieve( 'twitter_client_id' );
    $client_secret = ns_vault_retrieve( 'twitter_client_secret' );
    if ( is_wp_error( $client_id ) || is_wp_error( $client_secret ) ) {
        return new WP_Error( 'ns_twitter_no_creds', 'Twitter app credentials missing.' );
    }

    $resp = wp_remote_post( 'https://api.twitter.com/2/oauth2/token', array(
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode( $client_id . ':' . $client_secret ),
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ),
        'body'    => array(
            'code'          => $code,
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => rest_url( 'ns/v1/oauth/twitter/callback' ),
            'code_verifier' => $verifier,
        ),
        'timeout' => 15,
    ) );

    return ns_twitter_process_token_response( $resp );
}

/**
 * Refresh the Twitter access token using the stored refresh token.
 *
 * @return bool|WP_Error True on success.
 */
function ns_twitter_refresh_token() {
    $client_id     = ns_vault_retrieve( 'twitter_client_id' );
    $client_secret = ns_vault_retrieve( 'twitter_client_secret' );
    $refresh       = ns_vault_retrieve( 'twitter_refresh_token' );

    if ( is_wp_error( $client_id ) || is_wp_error( $client_secret ) || is_wp_error( $refresh ) ) {
        return new WP_Error( 'ns_twitter_refresh_fail', 'Missing credentials for token refresh.' );
    }

    $resp = wp_remote_post( 'https://api.twitter.com/2/oauth2/token', array(
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode( $client_id . ':' . $client_secret ),
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ),
        'body'    => array(
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refresh,
        ),
        'timeout' => 15,
    ) );

    return ns_twitter_process_token_response( $resp );
}

/**
 * Process token endpoint response and store tokens in vault.
 */
function ns_twitter_process_token_response( $resp ) {
    if ( is_wp_error( $resp ) ) {
        if ( function_exists( 'ns_diag_write' ) ) {
            ns_diag_write( 'error', 'twitter', 'Token request failed', array( 'error' => $resp->get_error_message() ) );
        }
        return $resp;
    }

    $body = json_decode( wp_remote_retrieve_body( $resp ), true );
    $code = wp_remote_retrieve_response_code( $resp );

    if ( $code !== 200 || empty( $body['access_token'] ) ) {
        if ( function_exists( 'ns_diag_write' ) ) {
            ns_diag_write( 'error', 'twitter', 'Token exchange failed', array( 'http_code' => $code ) );
        }
        return new WP_Error( 'ns_twitter_token_fail', 'Token exchange failed (HTTP ' . $code . ').' );
    }

    ns_vault_store( 'twitter_oauth_token', $body['access_token'] );
    if ( ! empty( $body['refresh_token'] ) ) {
        ns_vault_store( 'twitter_refresh_token', $body['refresh_token'] );
    }

    // Store expiry as a plain option (not sensitive)
    $expires_at = time() + intval( $body['expires_in'] ?? 7200 );
    update_option( 'ns_twitter_token_expires', $expires_at, false );

    if ( function_exists( 'ns_diag_write' ) ) {
        ns_diag_write( 'info', 'twitter', 'Tokens stored', array( 'expires_in' => $body['expires_in'] ?? 7200 ) );
    }

    return true;
}

/**
 * Get a valid Twitter access token, refreshing if needed.
 *
 * @return string|WP_Error Access token or error.
 */
function ns_twitter_get_token() {
    $expires = get_option( 'ns_twitter_token_expires', 0 );
    if ( $expires > 0 && $expires < ( time() + 300 ) ) {
        $refresh = ns_twitter_refresh_token();
        if ( is_wp_error( $refresh ) ) {
            return $refresh;
        }
    }
    return ns_vault_retrieve( 'twitter_oauth_token' );
}
