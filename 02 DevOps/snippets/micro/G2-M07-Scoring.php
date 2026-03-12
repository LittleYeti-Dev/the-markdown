<?php
/**
 * G2-M07 Claude Feed Scoring — Non Sequitur
 *
 * Sprint 1, Gate 2 Micro-snippet 07
 * S1.9 — Claude Feed Scoring (4h cron, relevance 1-10)
 * GitLab Issue #16
 *
 * Scores unscored ns_feed_item posts via Claude API.
 * Uses token vault (S1.17) for API key storage.
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
        sleep( 1 );
    }

    ns_log( 'claude_scoring', sprintf( 'Scored %d of %d items.', $scored, count( $unscored ) ) );
}

/**
 * Score a single feed item via Claude API
 *
 * @param WP_Post $post    The feed item post
 * @param string  $api_key Claude API key
 * @return bool Success
 */
function ns_score_single_item( $post, $api_key ) {
    $title       = $post->post_title;
    $content     = wp_trim_words( wp_strip_all_tags( $post->post_content ), 500 );
    $source      = get_post_meta( $post->ID, 'source_name', true );
    $domain_tag  = get_post_meta( $post->ID, 'domain_tag', true );

    // S1.11: Sanitize content before sending to LLM
    $safe_title   = ns_sanitize_llm_input( $title );
    $safe_content = ns_sanitize_llm_input( $content );

    $prompt = sprintf(
        'You are a content relevance scorer for "Non Sequitur", a curated intelligence feed covering AI, cybersecurity, innovation, national security, space, and digital transformation.

Score this article on two dimensions (1-10 each):

1. RELEVANCE SCORE: How relevant is this to professionals interested in the intersection of technology, security, and strategy? Consider: novelty, depth of analysis, actionable insights, credibility of source.

2. ARC SCORE: How well does this fit one of our three thematic arcs?
   - Convergence: Where multiple domains intersect (AI+security, space+defense, etc.)
   - Disruption: Breakthrough technologies or paradigm shifts
   - Human Element: Ethics, workforce impact, human-machine teaming

Article:
- Title: %s
- Source: %s
- Domain: %s
- Content excerpt: %s

Respond in EXACTLY this JSON format, nothing else:
{"relevance_score": N, "arc_score": N, "summary": "2-3 sentence summary", "primary_arc": "Convergence|Disruption|Human Element|None"}',
        $safe_title,
        esc_html( $source ),
        esc_html( $domain_tag ),
        $safe_content
    );

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
        ns_log( 'claude_scoring', 'API error for post #' . $post->ID . ': ' . $response->get_error_message() );
        return false;
    }

    $code = wp_remote_retrieve_response_code( $response );
    if ( $code !== 200 ) {
        ns_log( 'claude_scoring', 'API returned ' . $code . ' for post #' . $post->ID );
        return false;
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );
    if ( empty( $body['content'][0]['text'] ) ) {
        ns_log( 'claude_scoring', 'Empty response for post #' . $post->ID );
        return false;
    }

    $text = $body['content'][0]['text'];

    // S1.11: Validate response
    if ( ns_detect_response_injection( $text ) ) {
        ns_log( 'prompt_injection', 'Suspicious Claude response for post #' . $post->ID . '. Discarding.' );
        return false;
    }

    $scores = json_decode( $text, true );
    if ( ! is_array( $scores ) ) {
        if ( preg_match( '/\{[^}]+\}/', $text, $matches ) ) {
            $scores = json_decode( $matches[0], true );
        }
    }

    if ( empty( $scores ) || ! isset( $scores['relevance_score'] ) ) {
        ns_log( 'claude_scoring', 'Invalid JSON response for post #' . $post->ID );
        return false;
    }

    update_post_meta( $post->ID, 'relevance_score', absint( $scores['relevance_score'] ) );
    update_post_meta( $post->ID, 'arc_score', absint( $scores['arc_score'] ?? 0 ) );
    update_post_meta( $post->ID, 'claude_scored_at', gmdate( 'c' ) );

    if ( ! empty( $scores['summary'] ) ) {
        update_post_meta( $post->ID, 'claude_summary', wp_kses(
            $scores['summary'],
            array( 'p' => array(), 'br' => array(), 'strong' => array(), 'em' => array() )
        ) );
    }

    if ( ! empty( $scores['primary_arc'] ) && $scores['primary_arc'] !== 'None' ) {
        $valid_arcs = array( 'Convergence', 'Disruption', 'Human Element' );
        if ( in_array( $scores['primary_arc'], $valid_arcs, true ) ) {
            wp_set_object_terms( $post->ID, $scores['primary_arc'], 'thematic_arc', true );
        }
    }

    wp_set_object_terms( $post->ID, 'Scored', 'item_status' );

    return true;
}
