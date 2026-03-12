<?php
/**
 * G2-M07a Scoring Runner — Non Sequitur
 *
 * Sprint 1, Gate 2 Micro-snippet 07a
 * S1.9 — Claude Feed Scoring cron runner
 * GitLab Issue #16
 *
 * @package NonSequitur
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Hook Claude scoring to the same 4h cron (runs after import)
 */
add_action( 'ns_rss_import_event', 'ns_run_claude_scoring', 20 );

/**
 * Score unscored feed items with Claude
 */
function ns_run_claude_scoring() {
    $api_key = ns_get_api_key( 'claude' );
    if ( empty( $api_key ) ) {
        ns_log( 'claude_scoring', 'No Claude API key configured. Skipping scoring.' );
        return;
    }

    $unscored = get_posts( array(
        'post_type'      => 'ns_feed_item',
        'posts_per_page' => 20,
        'meta_query'     => array(
            array(
                'key'     => 'relevance_score',
                'value'   => '0',
                'compare' => '=',
                'type'    => 'NUMERIC',
            ),
        ),
        'date_query'     => array(
            array( 'after' => '48 hours ago' ),
        ),
        'orderby'        => 'date',
        'order'          => 'DESC',
    ) );

    if ( empty( $unscored ) ) {
        ns_log( 'claude_scoring', 'No unscored items found.' );
        return;
    }

    $scored = 0;
    foreach ( $unscored as $post ) {
        $result = ns_score_single_item( $post, $api_key );
        if ( $result ) {
            $scored++;
        }
        // Rate limit: wp_remote_post timeout provides natural spacing
    }

    ns_log( 'claude_scoring', sprintf( 'Scored %d of %d items.', $scored, count( $unscored ) ) );
}
