<?php
// Snippet ID: 52
// Name: S2-G4-M15 Medium API Client
// Scope: global
// Active: True
// Modified: 2026-03-09 22:42:08
// Lines: 112

/**
 * S2-G4-M15 — Medium API Client
 * Sprint 2, Gate 4 | GitLab Issue #34
 *
 * Bearer token auth + draft creation for Medium publishing.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S2-G4-M15 Medium API Client"
 * Scope: global
 * Priority: 10
 * Depends on: Token Vault (S1.17), Audit Log (S1.22), Diagnostic Logger (S1.23)
 *
 * Acceptance Criteria (GitLab #34):
 *   - Bearer token stored in Token Vault
 *   - Draft creation works (title, content, tags)
 *   - Content formats correctly (HTML)
 *   - Audit + diagnostic logging
 *
 * @package NonSequitur
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get the authenticated Medium user ID.
 *
 * @return string|WP_Error User ID or error.
 */
function ns_medium_get_user_id() {
    $token = function_exists( 'ns_vault_retrieve' ) ? ns_vault_retrieve( 'medium_bearer_token' ) : '';
    if ( is_wp_error( $token ) || empty( $token ) ) {
        return new WP_Error( 'ns_medium_no_token', 'Medium bearer token not configured.' );
    }

    $resp = wp_remote_get( 'https://api.medium.com/v1/me', array(
        'headers' => array( 'Authorization' => 'Bearer ' . $token ),
        'timeout' => 15,
    ) );

    if ( is_wp_error( $resp ) ) {
        return $resp;
    }

    $body = json_decode( wp_remote_retrieve_body( $resp ), true );
    if ( empty( $body['data']['id'] ) ) {
        return new WP_Error( 'ns_medium_no_user', 'Could not resolve Medium user ID.' );
    }

    return $body['data']['id'];
}

/**
 * Create a draft post on Medium.
 *
 * @param string $title   Post title.
 * @param string $content Post content (HTML).
 * @param array  $tags    Optional tags (max 5).
 * @return array|WP_Error Medium API response data or error.
 */
function ns_medium_create_draft( $title, $content, $tags = array() ) {
    $user_id = ns_medium_get_user_id();
    if ( is_wp_error( $user_id ) ) {
        if ( function_exists( 'ns_diag_write' ) ) {
            ns_diag_write( 'error', 'medium', 'Failed to get user ID', array() );
        }
        return $user_id;
    }

    $token = ns_vault_retrieve( 'medium_bearer_token' );
    $resp  = wp_remote_post( 'https://api.medium.com/v1/users/' . $user_id . '/posts', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
        ),
        'body'    => wp_json_encode( array(
            'title'         => sanitize_text_field( $title ),
            'contentFormat' => 'html',
            'content'       => $content,
            'tags'          => array_slice( array_map( 'sanitize_text_field', $tags ), 0, 5 ),
            'publishStatus' => 'draft',
        ) ),
        'timeout' => 30,
    ) );

    if ( is_wp_error( $resp ) ) {
        if ( function_exists( 'ns_diag_write' ) ) {
            ns_diag_write( 'error', 'medium', 'API request failed', array( 'error' => $resp->get_error_message() ) );
        }
        return $resp;
    }

    $code = wp_remote_retrieve_response_code( $resp );
    $body = json_decode( wp_remote_retrieve_body( $resp ), true );

    if ( $code !== 201 || empty( $body['data']['id'] ) ) {
        if ( function_exists( 'ns_diag_write' ) ) {
            ns_diag_write( 'error', 'medium', 'Draft creation failed', array( 'http_code' => $code ) );
        }
        return new WP_Error( 'ns_medium_post_fail', 'Medium draft creation failed (HTTP ' . $code . ').' );
    }

    if ( function_exists( 'ns_audit_log' ) ) {
        ns_audit_log( 'medium_draft_created', 'post', $body['data']['id'], $title, array( 'url' => $body['data']['url'] ?? '' ) );
    }
    if ( function_exists( 'ns_diag_write' ) ) {
        ns_diag_write( 'info', 'medium', 'Draft created', array( 'post_id' => $body['data']['id'] ) );
    }

    return $body['data'];
}
