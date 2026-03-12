<?php
// Snippet ID: 58
// Name: S2-G4-M04 X Thread Builder
// Scope: global
// Active: True
// Modified: 2026-03-09 22:42:05
// Lines: 68

/**
 * S2-G4-M04 — X (Twitter) Thread Builder
 * Sprint 2, Gate 4 | GitLab Issue #30
 *
 * Post tweet threads with correct reply chaining.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S2-G4-M04 X Thread Builder"
 * Scope: global
 * Priority: 10
 * Depends on: S2-G4-M03 X Tweet Composer
 *
 * @package NonSequitur
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Post a thread of tweets with reply chaining.
 *
 * @param array $items Array of tweet texts.
 * @return array|WP_Error Array of tweet data or error.
 */
function ns_twitter_post_thread( $items ) {
    if ( ! function_exists( 'ns_twitter_post_tweet' ) ) {
        return new WP_Error( 'ns_twitter_no_dep', 'Tweet composer missing.' );
    }

    if ( empty( $items ) || ! is_array( $items ) ) {
        return new WP_Error( 'ns_twitter_no_items', 'Provide at least one tweet.' );
    }

    // Pre-validate lengths
    foreach ( $items as $idx => $txt ) {
        if ( ns_twitter_count_chars( $txt ) > 280 ) {
            return new WP_Error( 'ns_twitter_long', 'Item ' . ( $idx + 1 ) . ' exceeds limit.' );
        }
    }

    $posted   = array();
    $prev_id  = '';

    foreach ( $items as $idx => $txt ) {
        $res = ns_twitter_post_tweet( $txt, array(), $prev_id );

        if ( is_wp_error( $res ) ) {
            if ( function_exists( 'ns_diag_write' ) ) {
                ns_diag_write( 'error', 'twitter', 'Thread stopped at ' . ( $idx + 1 ), array(
                    'done' => count( $posted ),
                ) );
            }
            return new WP_Error( 'ns_twitter_partial', 'Stopped at item ' . ( $idx + 1 ), array(
                'done' => $posted,
            ) );
        }

        $posted[] = $res;
        $prev_id  = $res['id'];
    }

    if ( function_exists( 'ns_audit_log' ) ) {
        ns_audit_log( 'twitter_thread', 'thread', $posted[0]['id'], count( $posted ) . ' tweets', array() );
    }

    return $posted;
}
