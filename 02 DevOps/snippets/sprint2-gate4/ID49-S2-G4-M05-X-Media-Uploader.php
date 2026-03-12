<?php
// Snippet ID: 49
// Name: S2-G4-M05 X Media Uploader
// Scope: global
// Active: True
// Modified: 2026-03-09 22:42:05
// Lines: 95

/**
 * S2-G4-M05 — X (Twitter) Media Uploader
 * Sprint 2, Gate 4 | GitLab Issue #30
 *
 * Upload images to X media endpoint (still v1.1 API).
 * Note: Media upload uses OAuth 1.0a-style endpoint but accepts Bearer tokens
 * from OAuth 2.0 for simple uploads via the v1.1 media/upload.json endpoint.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S2-G4-M05 X Media Uploader"
 * Scope: global
 * Priority: 10
 * Depends on: S2-G4-M02 X Token Manager
 *
 * @package NonSequitur
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Upload an image to Twitter for use in tweets.
 *
 * @param string $image_url URL of the image to upload.
 * @return string|WP_Error Media ID string or error.
 */
function ns_twitter_upload_media( $image_url ) {
    if ( ! function_exists( 'ns_twitter_get_token' ) ) {
        return new WP_Error( 'ns_twitter_missing_dep', 'Token manager not loaded.' );
    }

    // Download the image locally first
    $image_data = wp_remote_get( $image_url, array( 'timeout' => 30 ) );
    if ( is_wp_error( $image_data ) ) {
        return $image_data;
    }

    $image_body = wp_remote_retrieve_body( $image_data );
    $mime_type   = wp_remote_retrieve_header( $image_data, 'content-type' );

    if ( empty( $image_body ) ) {
        return new WP_Error( 'ns_twitter_no_image', 'Could not download image.' );
    }

    // Validate size (max 5MB for images)
    if ( strlen( $image_body ) > 5 * 1024 * 1024 ) {
        return new WP_Error( 'ns_twitter_image_too_large', 'Image exceeds 5MB limit.' );
    }

    $token = ns_twitter_get_token();
    if ( is_wp_error( $token ) ) {
        return $token;
    }

    // Upload via multipart form
    $boundary = wp_generate_password( 24, false );
    $body     = '';
    $body    .= '--' . $boundary . "\r\n";
    $body    .= 'Content-Disposition: form-data; name="media_data"' . "\r\n\r\n";
    $body    .= base64_encode( $image_body ) . "\r\n";
    $body    .= '--' . $boundary . '--' . "\r\n";

    $resp = wp_remote_post( 'https://upload.twitter.com/1.1/media/upload.json', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'multipart/form-data; boundary=' . $boundary,
        ),
        'body'    => $body,
        'timeout' => 60,
    ) );

    if ( is_wp_error( $resp ) ) {
        if ( function_exists( 'ns_diag_write' ) ) {
            ns_diag_write( 'error', 'twitter', 'Media upload failed', array( 'error' => $resp->get_error_message() ) );
        }
        return $resp;
    }

    $code     = wp_remote_retrieve_response_code( $resp );
    $resp_body = json_decode( wp_remote_retrieve_body( $resp ), true );

    if ( $code !== 200 || empty( $resp_body['media_id_string'] ) ) {
        if ( function_exists( 'ns_diag_write' ) ) {
            ns_diag_write( 'error', 'twitter', 'Media upload rejected', array( 'http_code' => $code ) );
        }
        return new WP_Error( 'ns_twitter_upload_fail', 'Media upload failed (HTTP ' . $code . ').' );
    }

    if ( function_exists( 'ns_diag_write' ) ) {
        ns_diag_write( 'info', 'twitter', 'Media uploaded', array( 'media_id' => $resp_body['media_id_string'] ) );
    }

    return $resp_body['media_id_string'];
}
