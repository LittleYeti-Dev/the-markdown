<?php
/**
 * G2-M07b3 Response Parser — Non Sequitur
 *
 * Micro-snippet 07b3 — Parse Claude JSON response
 * S1.9 — GitLab Issue #16
 *
 * @package NonSequitur
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Parse Claude scoring response into scores array
 *
 * @param string $text    Raw Claude response
 * @param int    $post_id Post ID for logging
 * @return array|false Scores array or false
 */
function ns_parse_scoring_response( $text, $post_id ) {
    if ( ns_detect_response_injection( $text ) ) {
        ns_log( 'prompt_injection', 'Suspicious response for post #' . $post_id );
        return false;
    }

    $scores = json_decode( $text, true );
    if ( ! is_array( $scores ) ) {
        if ( preg_match( '/\{[^}]+\}/', $text, $matches ) ) {
            $scores = json_decode( $matches[0], true );
        }
    }

    if ( empty( $scores ) || ! isset( $scores['relevance_score'] ) ) {
        ns_log( 'claude_scoring', 'Invalid JSON for post #' . $post_id );
        return false;
    }

    return $scores;
}
