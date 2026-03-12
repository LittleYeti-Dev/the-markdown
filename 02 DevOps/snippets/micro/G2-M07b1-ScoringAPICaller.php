<?php
/**
 * G2-M07b1 Score Single Item — Non Sequitur
 *
 * Micro-snippet 07b1 — Orchestrator function
 * S1.9 — GitLab Issue #16
 *
 * @package NonSequitur
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
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

    $safe_title   = ns_sanitize_llm_input( $title );
    $safe_content = ns_sanitize_llm_input( $content );

    $prompt = ns_build_scoring_prompt( $safe_title, esc_html( $source ), esc_html( $domain_tag ), $safe_content );

    $text = ns_call_claude_api( $prompt, $api_key, $post->ID );
    if ( false === $text ) {
        return false;
    }

    $scores = ns_parse_scoring_response( $text, $post->ID );
    if ( false === $scores ) {
        return false;
    }

    return ns_save_score_meta( $post->ID, $scores );
}
