<?php
/**
 * G2-M03 RSS Importer — Non Sequitur
 *
 * Sprint 1, Gate 2 Micro-snippet 03
 * S1.4 — Custom RSS Importer (SimplePie-based, maps to ns_feed_item CPT)
 * GitLab Issue #11
 *
 * Uses WordPress built-in SimplePie instead of paid RSS Aggregator Pro.
 * Runs via WP-Cron every 4 hours.
 *
 * @package NonSequitur
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Schedule the RSS import cron if not already scheduled
 */
add_action( 'init', 'ns_schedule_rss_cron' );

function ns_schedule_rss_cron() {
    if ( ! wp_next_scheduled( 'ns_rss_import_event' ) ) {
        wp_schedule_event( time(), 'ns_four_hours', 'ns_rss_import_event' );
    }
    // Also schedule morning digest at 0600 CST (1200 UTC)
    if ( ! wp_next_scheduled( 'ns_morning_digest_event' ) ) {
        $next_0600_cst = strtotime( 'tomorrow 12:00:00 UTC' );
        wp_schedule_event( $next_0600_cst, 'daily', 'ns_morning_digest_event' );
    }
}

/**
 * Register custom 4-hour cron interval
 */
add_filter( 'cron_schedules', 'ns_add_cron_intervals' );

function ns_add_cron_intervals( $schedules ) {
    $schedules['ns_four_hours'] = array(
        'interval' => 14400,
        'display'  => 'Every 4 Hours (Non Sequitur)',
    );
    return $schedules;
}

/**
 * Hook the import to our cron event
 */
add_action( 'ns_rss_import_event', 'ns_run_rss_import' );

/**
 * Main RSS import function — fetches all configured feeds
 */
function ns_run_rss_import() {
    $feeds = ns_get_feed_config();

    if ( empty( $feeds ) ) {
        ns_log( 'rss_import', 'No feeds configured. Aborting.' );
        return;
    }

    $imported = 0;
    $skipped  = 0;
    $errors   = 0;

    foreach ( $feeds as $feed ) {
        $result = ns_import_single_feed( $feed );
        $imported += $result['imported'];
        $skipped  += $result['skipped'];
        $errors   += $result['errors'];
    }

    ns_log( 'rss_import', sprintf(
        'Import complete: %d imported, %d skipped (dedup), %d errors from %d feeds.',
        $imported, $skipped, $errors, count( $feeds )
    ) );
}

/**
 * Import a single RSS feed
 *
 * @param array $feed Feed config array with url, source_name, domain_tag
 * @return array Counts: imported, skipped, errors
 */
function ns_import_single_feed( $feed ) {
    $counts = array( 'imported' => 0, 'skipped' => 0, 'errors' => 0 );

    // S1.8: Validate feed URL before fetching
    $feed_url = esc_url_raw( $feed['url'] );
    if ( empty( $feed_url ) || ! wp_http_validate_url( $feed_url ) ) {
        ns_log( 'rss_sanitize', 'Invalid feed URL rejected: ' . sanitize_text_field( $feed['url'] ) );
        $counts['errors']++;
        return $counts;
    }

    // Fetch with SimplePie (built into WordPress)
    if ( ! function_exists( 'fetch_feed' ) ) {
        require_once ABSPATH . WPINC . '/feed.php';
    }

    $rss = fetch_feed( $feed_url );

    if ( is_wp_error( $rss ) ) {
        ns_log( 'rss_import', 'Feed error for ' . $feed['source_name'] . ': ' . $rss->get_error_message() );
        $counts['errors']++;
        return $counts;
    }

    $max_items = $rss->get_item_quantity( 25 );
    $items     = $rss->get_items( 0, $max_items );

    foreach ( $items as $item ) {
        $article_url   = esc_url_raw( $item->get_permalink() );
        $article_title = sanitize_text_field( $item->get_title() );

        // S1.8: Validate article URL
        if ( empty( $article_url ) || ! wp_http_validate_url( $article_url ) ) {
            $counts['errors']++;
            continue;
        }

        // S1.8: Enforce payload size limit (50KB)
        $raw_content = $item->get_content();
        if ( strlen( $raw_content ) > 51200 ) {
            $raw_content = substr( $raw_content, 0, 51200 );
            ns_log( 'rss_sanitize', 'Content truncated to 50KB: ' . $article_url );
        }

        // S1.8: Strip dangerous content
        $clean_content = ns_sanitize_rss_content( $raw_content );

        // S1.7: Check for duplicates before inserting
        if ( ns_is_duplicate( $article_url, $article_title ) ) {
            $counts['skipped']++;
            continue;
        }

        // Get author name
        $author = $item->get_author();
        $author_name = $author ? sanitize_text_field( $author->get_name() ) : '';

        // Get original publish date
        $pub_date = $item->get_date( 'c' );
        if ( ! $pub_date ) {
            $pub_date = gmdate( 'c' );
        }

        // Create the feed item post
        $post_id = wp_insert_post( array(
            'post_type'    => 'ns_feed_item',
            'post_title'   => $article_title,
            'post_content' => $clean_content,
            'post_status'  => 'publish',
            'post_date'    => get_date_from_gmt( $pub_date ),
        ) );

        if ( is_wp_error( $post_id ) || ! $post_id ) {
            $counts['errors']++;
            continue;
        }

        // Set meta fields
        update_post_meta( $post_id, 'source_url', $article_url );
        update_post_meta( $post_id, 'source_name', sanitize_text_field( $feed['source_name'] ) );
        update_post_meta( $post_id, 'source_author', $author_name );
        update_post_meta( $post_id, 'domain_tag', sanitize_key( $feed['domain_tag'] ) );
        update_post_meta( $post_id, 'publish_date_orig', sanitize_text_field( $pub_date ) );
        update_post_meta( $post_id, 'import_date', gmdate( 'c' ) );
        update_post_meta( $post_id, 'promote_status', 'dev' );
        update_post_meta( $post_id, 'relevance_score', 0 );
        update_post_meta( $post_id, 'arc_score', 0 );
        update_post_meta( $post_id, 'block_assignment', 0 );

        // S1.6: Auto-assign content_domain taxonomy
        ns_auto_tag_domain( $post_id, $feed['domain_tag'], $article_title, $clean_content );

        // Set item_status taxonomy to "New"
        wp_set_object_terms( $post_id, 'New', 'item_status' );

        $counts['imported']++;
    }

    return $counts;
}
