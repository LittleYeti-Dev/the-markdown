<?php
/**
 * G2-M05 Dedup Logic — Non Sequitur
 *
 * Sprint 1, Gate 2 Micro-snippet 05
 * S1.7 — Dedup Logic (URL matching + title similarity 90%)
 * GitLab Issue #14
 *
 * @package NonSequitur
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Check if an article is a duplicate
 *
 * @param string $url   Article URL
 * @param string $title Article title
 * @return bool True if duplicate
 */
function ns_is_duplicate( $url, $title ) {
    global $wpdb;

    // 1. Exact URL match
    $url_match = $wpdb->get_var( $wpdb->prepare(
        "SELECT pm.post_id FROM {$wpdb->postmeta} pm
         INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
         WHERE pm.meta_key = 'source_url'
         AND pm.meta_value = %s
         AND p.post_type = 'ns_feed_item'
         AND p.post_status != 'trash'
         LIMIT 1",
        $url
    ) );

    if ( $url_match ) {
        return true;
    }

    // 2. Normalized URL match (strip query params, trailing slashes)
    $normalized = ns_normalize_url( $url );
    $existing_urls = $wpdb->get_col( $wpdb->prepare(
        "SELECT pm.meta_value FROM {$wpdb->postmeta} pm
         INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
         WHERE pm.meta_key = 'source_url'
         AND p.post_type = 'ns_feed_item'
         AND p.post_status != 'trash'
         AND p.post_date > %s
         LIMIT 200",
        gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) )
    ) );

    foreach ( $existing_urls as $existing ) {
        if ( ns_normalize_url( $existing ) === $normalized ) {
            return true;
        }
    }

    // 3. Title similarity check (>90%)
    $title_clean = ns_normalize_title( $title );
    if ( strlen( $title_clean ) < 10 ) {
        return false;
    }

    $recent_titles = $wpdb->get_results( $wpdb->prepare(
        "SELECT ID, post_title FROM {$wpdb->posts}
         WHERE post_type = 'ns_feed_item'
         AND post_status != 'trash'
         AND post_date > %s
         ORDER BY post_date DESC
         LIMIT 500",
        gmdate( 'Y-m-d H:i:s', strtotime( '-7 days' ) )
    ) );

    foreach ( $recent_titles as $existing ) {
        $existing_clean = ns_normalize_title( $existing->post_title );
        similar_text( $title_clean, $existing_clean, $percent );
        if ( $percent >= 90.0 ) {
            ns_log( 'dedup', sprintf(
                'Title similarity %.1f%%: "%s" vs existing post #%d "%s"',
                $percent, $title, $existing->ID, $existing->post_title
            ) );
            return true;
        }
    }

    return false;
}

/**
 * Normalize URL for dedup comparison
 */
function ns_normalize_url( $url ) {
    $parsed = wp_parse_url( $url );
    $host   = isset( $parsed['host'] ) ? strtolower( $parsed['host'] ) : '';
    $path   = isset( $parsed['path'] ) ? rtrim( $parsed['path'], '/' ) : '';
    $host = preg_replace( '/^www\./', '', $host );
    return $host . $path;
}

/**
 * Normalize title for similarity comparison
 */
function ns_normalize_title( $title ) {
    $title = strtolower( $title );
    $title = preg_replace( '/[^a-z0-9\s]/', '', $title );
    $title = preg_replace( '/\s+/', ' ', $title );
    return trim( $title );
}
