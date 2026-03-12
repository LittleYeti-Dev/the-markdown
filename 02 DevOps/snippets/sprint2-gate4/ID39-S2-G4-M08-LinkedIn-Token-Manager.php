<?php
// Snippet ID: 39
// Name: S2-G4-M08 LinkedIn Token Manager
// Scope: global
// Active: True
// Modified: 2026-03-09 22:41:53
// Lines: 126

/**
 * S2-G4-M08 — LinkedIn Token Manager
 * Sprint 2, Gate 4 | GitLab Issue #31
 *
 * Exchange auth code for tokens, store/refresh via Token Vault.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S2-G4-M08 LinkedIn Token Manager"
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
 * @param string $code Authorization code from callback.
 * @return bool|WP_Error True on success.
 */
function ns_linkedin_exchange_code( $code ) {
    $client_id     = ns_vault_retrieve( 'linkedin_client_id' );
    $client_secret = ns_vault_retrieve( 'linkedin_client_secret' );
    if ( is_wp_error( $client_id ) || is_wp_error( $client_secret ) ) {
        return new WP_Error( 'ns_linkedin_no_creds', 'LinkedIn app credentials missing.' );
    }

    $resp = wp_remote_post( 'https://www.linkedin.com/oauth/v2/accessToken', array(
        'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
        'body'    => array(
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => rest_url( 'ns/v1/oauth/linkedin/callback' ),
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
        ),
        'timeout' => 15,
    ) );

    return ns_linkedin_process_token_response( $resp );
}

/**
 * Refresh the LinkedIn access token.
 *
 * @return bool|WP_Error True on success.
 */
function ns_linkedin_refresh_token() {
    $client_id     = ns_vault_retrieve( 'linkedin_client_id' );
    $client_secret = ns_vault_retrieve( 'linkedin_client_secret' );
    $refresh       = ns_vault_retrieve( 'linkedin_refresh_token' );

    if ( is_wp_error( $client_id ) || is_wp_error( $client_secret ) || is_wp_error( $refresh ) ) {
        return new WP_Error( 'ns_linkedin_refresh_fail', 'Missing credentials for refresh.' );
    }

    $resp = wp_remote_post( 'https://www.linkedin.com/oauth/v2/accessToken', array(
        'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
        'body'    => array(
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refresh,
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
        ),
        'timeout' => 15,
    ) );

    return ns_linkedin_process_token_response( $resp );
}

/**
 * Process token response and store in vault.
 */
function ns_linkedin_process_token_response( $resp ) {
    if ( is_wp_error( $resp ) ) {
        if ( function_exists( 'ns_diag_write' ) ) {
            ns_diag_write( 'error', 'linkedin', 'Token request failed', array( 'error' => $resp->get_error_message() ) );
        }
        return $resp;
    }

    $body = json_decode( wp_remote_retrieve_body( $resp ), true );
    $code = wp_remote_retrieve_response_code( $resp );

    if ( $code !== 200 || empty( $body['access_token'] ) ) {
        if ( function_exists( 'ns_diag_write' ) ) {
            ns_diag_write( 'error', 'linkedin', 'Token exchange failed', array( 'http_code' => $code ) );
        }
        return new WP_Error( 'ns_linkedin_token_fail', 'Token exchange failed (HTTP ' . $code . ').' );
    }

    ns_vault_store( 'linkedin_access_token', $body['access_token'] );
    if ( ! empty( $body['refresh_token'] ) ) {
        ns_vault_store( 'linkedin_refresh_token', $body['refresh_token'] );
    }

    $expires_at = time() + intval( $body['expires_in'] ?? 5184000 );
    update_option( 'ns_linkedin_token_expires', $expires_at, false );

    if ( function_exists( 'ns_diag_write' ) ) {
        ns_diag_write( 'info', 'linkedin', 'Tokens stored', array( 'expires_in' => $body['expires_in'] ?? 5184000 ) );
    }

    return true;
}

/**
 * Get a valid LinkedIn access token, refreshing if near expiry.
 *
 * @return string|WP_Error Access token or error.
 */
function ns_linkedin_get_token() {
    $expires = get_option( 'ns_linkedin_token_expires', 0 );
    if ( $expires > 0 && $expires < ( time() + 86400 ) ) {
        $refresh = ns_linkedin_refresh_token();
        if ( is_wp_error( $refresh ) ) {
            return $refresh;
        }
    }
    return ns_vault_retrieve( 'linkedin_access_token' );
}
