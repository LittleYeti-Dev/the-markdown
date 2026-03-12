<?php
// Snippet ID: 48
// Name: S2-G4-M03 X Tweet Composer
// Scope: global
// Active: True
// Modified: 2026-03-09 22:42:04
// Lines: 97

/**
 * S2-G4-M03 — X (Twitter) Tweet Composer
 * Sprint 2, Gate 4 | GitLab Issue #30
 *
 * Compose and post a single tweet via X API v2.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S2-G4-M03 X Tweet Composer"
 * Scope: global
 * Priority: 10
 * Depends on: S2-G4-M02 X Token Manager
 *
 * @package NonSequitur
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Post a single tweet.
 *
 * @param string $text      Tweet text (max 280 chars).
 * @param array  $media_ids Optional media IDs from upload.
 * @param string $reply_to  Optional tweet ID to reply to.
 * @return array|WP_Error Tweet data or error.
 */
function ns_twitter_post_tweet( $text, $media_ids = array(), $reply_to = '' ) {
    if ( ! function_exists( 'ns_twitter_get_token' ) ) {
        return new WP_Error( 'ns_twitter_missing_dep', 'Token manager not loaded.' );
    }

    // Validate character count (URLs count as 23 chars per t.co)
    $effective_len = ns_twitter_count_chars( $text );
    if ( $effective_len > 280 ) {
        return new WP_Error( 'ns_twitter_too_long', 'Tweet exceeds 280 characters (' . $effective_len . ').' );
    }

    $token = ns_twitter_get_token();
    if ( is_wp_error( $token ) ) {
        return $token;
    }

    $payload = array( 'text' => $text );

    if ( ! empty( $media_ids ) ) {
        $payload['media'] = array( 'media_ids' => array_values( $media_ids ) );
    }
    if ( ! empty( $reply_to ) ) {
        $payload['reply'] = array( 'in_reply_to_tweet_id' => sanitize_text_field( $reply_to ) );
    }

    $resp = wp_remote_post( 'https://api.twitter.com/2/tweets', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
        ),
        'body'    => wp_json_encode( $payload ),
        'timeout' => 15,
    ) );

    if ( is_wp_error( $resp ) ) {
        if ( function_exists( 'ns_diag_write' ) ) {
            ns_diag_write( 'error', 'twitter', 'Tweet post failed', array( 'error' => $resp->get_error_message() ) );
        }
        return $resp;
    }

    $code = wp_remote_retrieve_response_code( $resp );
    $body = json_decode( wp_remote_retrieve_body( $resp ), true );

    if ( $code !== 201 || empty( $body['data']['id'] ) ) {
        if ( function_exists( 'ns_diag_write' ) ) {
            ns_diag_write( 'error', 'twitter', 'Tweet creation failed', array( 'http_code' => $code ) );
        }
        return new WP_Error( 'ns_twitter_tweet_fail', 'Tweet failed (HTTP ' . $code . ').' );
    }

    if ( function_exists( 'ns_audit_log' ) ) {
        ns_audit_log( 'twitter_tweet_posted', 'tweet', $body['data']['id'], substr( $text, 0, 50 ), array() );
    }

    return $body['data'];
}

/**
 * Count effective tweet length (URLs = 23 chars via t.co).
 *
 * @param string $text Tweet text.
 * @return int Effective character count.
 */
function ns_twitter_count_chars( $text ) {
    // Replace URLs with 23-char placeholder
    $url_pattern = '#https?://[^\s]+#i';
    $replaced    = preg_replace( $url_pattern, str_repeat( 'x', 23 ), $text );
    return mb_strlen( $replaced );
}
