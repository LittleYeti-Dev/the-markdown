<?php
// Snippet ID: 51
// Name: S2-G4-M13 YouTube Community Posts
// Scope: global
// Active: True
// Modified: 2026-03-09 22:42:07
// Lines: 130

/**
 * S2-G4-M13 — YouTube Community Posts
 * Sprint 2, Gate 4 | GitLab Issue #33
 *
 * Create text/image community posts + channel verification.
 * Note: YouTube Community Posts API has limited availability.
 * Uses Activities API (bulletin type) as primary method.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S2-G4-M13 YouTube Community Posts"
 * Scope: global
 * Priority: 10
 * Depends on: S2-G4-M12 YouTube Token Manager
 *
 * @package NonSequitur
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get the authenticated YouTube channel info.
 *
 * @return array|WP_Error Channel data or error.
 */
function ns_youtube_get_channel() {
    $cached = get_transient( 'ns_youtube_channel_info' );
    if ( $cached ) return $cached;

    $token = function_exists( 'ns_youtube_get_token' ) ? ns_youtube_get_token() : '';
    if ( is_wp_error( $token ) ) return $token;

    $resp = wp_remote_get( add_query_arg( array(
        'part' => 'snippet,statistics',
        'mine' => 'true',
    ), 'https://www.googleapis.com/youtube/v3/channels' ), array(
        'headers' => array( 'Authorization' => 'Bearer ' . $token ),
        'timeout' => 15,
    ) );

    if ( is_wp_error( $resp ) ) return $resp;

    $body = json_decode( wp_remote_retrieve_body( $resp ), true );
    if ( empty( $body['items'][0] ) ) {
        return new WP_Error( 'ns_youtube_no_channel', 'No YouTube channel found for this account.' );
    }

    $channel = array(
        'id'          => $body['items'][0]['id'],
        'title'       => $body['items'][0]['snippet']['title'] ?? '',
        'subscribers' => $body['items'][0]['statistics']['subscriberCount'] ?? 0,
    );

    set_transient( 'ns_youtube_channel_info', $channel, HOUR_IN_SECONDS );
    return $channel;
}

/**
 * Create a community post (bulletin) on YouTube.
 *
 * Uses the Activities API insert with bulletin snippet.
 * Note: This endpoint may require channel to have community tab enabled.
 *
 * @param string $text      Post text content.
 * @param string $image_url Optional image URL (not directly supported via API).
 * @return array|WP_Error Activity data or error.
 */
function ns_youtube_create_community_post( $text, $image_url = '' ) {
    $token = function_exists( 'ns_youtube_get_token' ) ? ns_youtube_get_token() : '';
    if ( is_wp_error( $token ) ) return $token;

    $channel = ns_youtube_get_channel();
    if ( is_wp_error( $channel ) ) return $channel;

    // YouTube Activities.insert with bulletin type
    $payload = array(
        'snippet' => array(
            'channelId'   => $channel['id'],
            'description' => $text,
            'type'        => 'bulletin',
        ),
        'contentDetails' => array(
            'bulletin' => array(
                'resourceId' => array(
                    'kind'      => 'youtube#channel',
                    'channelId' => $channel['id'],
                ),
            ),
        ),
    );

    $resp = wp_remote_post( add_query_arg( 'part', 'snippet,contentDetails',
        'https://www.googleapis.com/youtube/v3/activities'
    ), array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
        ),
        'body'    => wp_json_encode( $payload ),
        'timeout' => 30,
    ) );

    if ( is_wp_error( $resp ) ) {
        if ( function_exists( 'ns_diag_write' ) ) {
            ns_diag_write( 'error', 'youtube', 'Community post failed', array( 'error' => $resp->get_error_message() ) );
        }
        return $resp;
    }

    $code = wp_remote_retrieve_response_code( $resp );
    $body = json_decode( wp_remote_retrieve_body( $resp ), true );

    if ( $code !== 200 && $code !== 201 ) {
        if ( function_exists( 'ns_diag_write' ) ) {
            ns_diag_write( 'error', 'youtube', 'Community post rejected', array(
                'http_code' => $code,
                'reason'    => $body['error']['message'] ?? 'unknown',
            ) );
        }
        return new WP_Error( 'ns_youtube_post_fail', 'YouTube post failed (HTTP ' . $code . ').' );
    }

    $activity_id = $body['id'] ?? 'unknown';
    if ( function_exists( 'ns_audit_log' ) ) {
        ns_audit_log( 'youtube_post_created', 'post', $activity_id, substr( $text, 0, 50 ), array() );
    }

    return array( 'id' => $activity_id, 'status' => 'published' );
}
