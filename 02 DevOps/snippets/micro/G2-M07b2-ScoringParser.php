<?php
/**
 * G2-M07b2 Claude API Caller — Non Sequitur
 *
 * Micro-snippet 07b2 — HTTP request to Claude
 * S1.9 — GitLab Issue #16
 *
 * @package NonSequitur
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Call Claude API with a prompt
 *
 * @param string $prompt  The scoring prompt
 * @param string $api_key Claude API key
 * @param int    $post_id Post ID for logging
 * @return string|false Response text or false on error
 */
function ns_call_claude_api( $prompt, $api_key, $post_id ) {
    $response = wp_remote_post( 'https://api.anthropic.com/v1/messages', array(
        'timeout' => 30,
        'headers' => array(
            'Content-Type'      => 'application/json',
            'x-api-key'        => $api_key,
            'anthropic-version' => '2023-06-01',
        ),
        'body' => wp_json_encode( array(
            'model'      => 'claude-sonnet-4-5-20250514',
            'max_tokens' => 300,
            'messages'   => array(
                array( 'role' => 'user', 'content' => $prompt ),
            ),
        ) ),
    ) );

    if ( is_wp_error( $response ) ) {
        ns_log( 'claude_scoring', 'API error for post #' . $post_id . ': ' . $response->get_error_message() );
        return false;
    }

    $code = wp_remote_retrieve_response_code( $response );
    if ( $code !== 200 ) {
        ns_log( 'claude_scoring', 'API returned ' . $code . ' for post #' . $post_id );
        return false;
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );
    if ( empty( $body['content'][0]['text'] ) ) {
        ns_log( 'claude_scoring', 'Empty response for post #' . $post_id );
        return false;
    }

    return $body['content'][0]['text'];
}
