<?php
/**
 * G2-M07b4 Score Meta Saver — Non Sequitur
 *
 * Micro-snippet 07b4 — Save scores to post meta
 * S1.9 — GitLab Issue #16
 *
 * @package NonSequitur
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Save parsed scores to post meta and taxonomies
 *
 * @param int   $post_id Post ID
 * @param array $scores  Parsed scores array
 * @return bool true
 */
function ns_save_score_meta( $post_id, $scores ) {
    update_post_meta( $post_id, 'relevance_score', absint( $scores['relevance_score'] ) );
    update_post_meta( $post_id, 'arc_score', absint( $scores['arc_score'] ?? 0 ) );
    update_post_meta( $post_id, 'claude_scored_at', gmdate( 'c' ) );

    if ( ! empty( $scores['summary'] ) ) {
        update_post_meta( $post_id, 'claude_summary', wp_kses(
            $scores['summary'],
            array( 'p' => array(), 'br' => array(), 'strong' => array(), 'em' => array() )
        ) );
    }

    if ( ! empty( $scores['primary_arc'] ) && $scores['primary_arc'] !== 'None' ) {
        $valid_arcs = array( 'Convergence', 'Disruption', 'Human Element' );
        if ( in_array( $scores['primary_arc'], $valid_arcs, true ) ) {
            wp_set_object_terms( $post_id, $scores['primary_arc'], 'thematic_arc', true );
        }
    }

    wp_set_object_terms( $post_id, 'Scored', 'item_status' );
    return true;
}
