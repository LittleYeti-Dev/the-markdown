<?php
// Snippet ID: 40
// Name: S2-G4-M12 YouTube Token Manager
// Scope: global
// Active: True
// Modified: 2026-03-09 22:41:54
// Lines: 126

/**
 * S2-G4-M12 — YouTube Token Manager
 * Sprint 2, Gate 4 | GitLab Issue #33
 *
 * Exchange auth code for Google OAuth tokens, store/refresh via Token Vault.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S2-G4-M12 YouTube Token Manager"
 * Scope: global
 * Priority: 10
 * Depends on: Token Vault (S1.17), Diagnostic Logger (S1.23)
 *
 * @package NonSequitur
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Exchange authorization code for Google access + refresh tokens.
 *
 * @param string $code Authorization code from callback.
 * @return bool|WP_Error True on success.
 */
function ns_youtube_exchange_code( $code ) {
    $client_id     = ns_vault_retrieve( 'youtube_client_id' );
    $client_secret = ns_vault_retrieve( 'youtube_client_secret' );
    if ( is_wp_error( $client_id ) || is_wp_error( $client_secret ) ) {
        return new WP_Error( 'ns_youtube_no_creds', 'YouTube app credentials missing.' );
    }

    $resp = wp_remote_post( 'https://oauth2.googleapis.com/token', array(
        'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
        'body'    => array(
            'code'          => $code,
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri'  => rest_url( 'ns/v1/oauth/youtube/callback' ),
            'grant_type'    => 'authorization_code',
        ),
        'timeout' => 15,
    ) );

    return ns_youtube_process_token_response( $resp );
}

/**
 * Refresh the Google/YouTube access token.
 *
 * @return bool|WP_Error True on success.
 */
function ns_youtube_refresh_token() {
    $client_id     = ns_vault_retrieve( 'youtube_client_id' );
    $client_secret = ns_vault_retrieve( 'youtube_client_secret' );
    $refresh       = ns_vault_retrieve( 'youtube_refresh_token' );

    if ( is_wp_error( $client_id ) || is_wp_error( $client_secret ) || is_wp_error( $refresh ) ) {
        return new WP_Error( 'ns_youtube_refresh_fail', 'Missing credentials for refresh.' );
    }

    $resp = wp_remote_post( 'https://oauth2.googleapis.com/token', array(
        'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
        'body'    => array(
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'refresh_token' => $refresh,
            'grant_type'    => 'refresh_token',
        ),
        'timeout' => 15,
    ) );

    return ns_youtube_process_token_response( $resp );
}

/**
 * Process Google token response and store in vault.
 */
function ns_youtube_process_token_response( $resp ) {
    if ( is_wp_error( $resp ) ) {
        if ( function_exists( 'ns_diag_write' ) ) {
            ns_diag_write( 'error', 'youtube', 'Token request failed', array( 'error' => $resp->get_error_message() ) );
        }
        return $resp;
    }

    $body = json_decode( wp_remote_retrieve_body( $resp ), true );
    $code = wp_remote_retrieve_response_code( $resp );

    if ( $code !== 200 || empty( $body['access_token'] ) ) {
        if ( function_exists( 'ns_diag_write' ) ) {
            ns_diag_write( 'error', 'youtube', 'Token exchange failed', array( 'http_code' => $code ) );
        }
        return new WP_Error( 'ns_youtube_token_fail', 'Token exchange failed (HTTP ' . $code . ').' );
    }

    ns_vault_store( 'youtube_oauth_token', $body['access_token'] );
    if ( ! empty( $body['refresh_token'] ) ) {
        ns_vault_store( 'youtube_refresh_token', $body['refresh_token'] );
    }

    $expires_at = time() + intval( $body['expires_in'] ?? 3600 );
    update_option( 'ns_youtube_token_expires', $expires_at, false );

    if ( function_exists( 'ns_diag_write' ) ) {
        ns_diag_write( 'info', 'youtube', 'Tokens stored', array( 'expires_in' => $body['expires_in'] ?? 3600 ) );
    }

    return true;
}

/**
 * Get a valid YouTube access token, refreshing if near expiry.
 *
 * @return string|WP_Error Access token or error.
 */
function ns_youtube_get_token() {
    $expires = get_option( 'ns_youtube_token_expires', 0 );
    if ( $expires > 0 && $expires < ( time() + 300 ) ) {
        $refresh = ns_youtube_refresh_token();
        if ( is_wp_error( $refresh ) ) {
            return $refresh;
        }
    }
    return ns_vault_retrieve( 'youtube_oauth_token' );
}
