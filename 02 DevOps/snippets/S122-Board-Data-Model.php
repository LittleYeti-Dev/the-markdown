/**
 * S3.8-W1-01 — Board Data Model + Helper Functions
 * Sprint 3.8, Wave 1 + HF02 (Publishing Queue)
 *
 * Manages editorial board state via wp_options. Two layers:
 * draft (AI proposal) and published (Yeti-approved).
 * HF02 adds queue helpers for sequential review workflow.
 *
 * Snippet Title: "S3.8-W1-01 Board Data Model"
 * Scope: global | Priority: 10 | Depends on: none
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function jk_get_draft_board() {
    $b = get_option( 'jk_editorial_draft', null );
    return is_array( $b ) ? $b : null;
}

function jk_get_published_board() {
    $b = get_option( 'jk_editorial_published', null );
    return is_array( $b ) ? $b : null;
}

function jk_save_draft_board( $board ) {
    if ( ! is_array( $board ) || empty( $board['blocks'] ) ) {
        return new WP_Error( 'invalid_board', 'Board must contain a blocks array.' );
    }
    if ( empty( $board['generated'] ) ) $board['generated'] = current_time( 'c' );
    $board['version']      = isset( $board['version'] ) ? (int) $board['version'] : 1;
    $board['published_at'] = null;
    update_option( 'jk_editorial_draft', $board, false );
    return true;
}

function jk_publish_board() {
    $draft = jk_get_draft_board();
    if ( ! $draft ) return new WP_Error( 'no_draft', 'No draft board to publish.' );

    // HF02: Queue-aware publishing — only publish queued items
    $queued = jk_get_queued_slots();
    if ( empty( $queued ) ) {
        return new WP_Error( 'empty_queue', 'No items in publishing queue. Review and queue items first.' );
    }

    // Validate queued items: Box 00 lead must have hero_image if queued
    if ( isset( $queued['00'] ) && ! empty( $queued['00'] ) ) {
        $lead = $queued['00'][0];
        if ( empty( $lead['hero_image'] ) ) {
            return new WP_Error( 'no_hero', 'Box 00 lead story must have a hero_image.' );
        }
    }

    // Merge queued slots into existing published board (preserve un-queued slots)
    $published = jk_get_published_board();
    if ( ! is_array( $published ) ) {
        $published = array( 'blocks' => array() );
    }
    if ( ! isset( $published['blocks'] ) ) {
        $published['blocks'] = array();
    }

    foreach ( $queued as $block_id => $items ) {
        // Strip queue flags before saving to published
        $clean_items = array();
        foreach ( $items as $item ) {
            unset( $item['queued'] );
            $clean_items[] = $item;
        }
        $published['blocks'][ $block_id ] = $clean_items;
    }

    // Copy metadata from draft
    $published['generated']    = $draft['generated'] ?? current_time( 'c' );
    $published['published_at'] = current_time( 'c' );
    $published['version']      = ( $draft['version'] ?? 0 ) + 1;

    update_option( 'jk_editorial_published', $published, false );
    $count = count( $queued );

    // Clear queue flags on draft
    jk_clear_queue_flags();

    // Fire preference logger hook
    do_action( 'jk_board_published', $published );

    return $count;
}

// === HF02: Queue Helpers ===

/**
 * Toggle queue status for all items in a block.
 * @param string $block_id Block ID (e.g., '00', '01')
 * @param bool $queued True to queue, false to unqueue
 * @return bool|WP_Error
 */
function jk_queue_block( $block_id, $queued = true ) {
    $board = jk_get_draft_board();
    if ( ! $board || ! isset( $board['blocks'][ $block_id ] ) ) {
        return false;
    }
    if ( empty( $board['blocks'][ $block_id ] ) ) {
        return false;
    }
    foreach ( $board['blocks'][ $block_id ] as &$item ) {
        if ( ! empty( $item['feed_item_id'] ) ) {
            $item['queued'] = (bool) $queued;
        }
    }
    unset( $item );
    return jk_save_draft_board( $board );
}

/**
 * Get all queued blocks from draft board.
 * Returns only blocks where at least one item is queued.
 * @return array Associative array of block_id => items (queued only)
 */
function jk_get_queued_slots() {
    $board = jk_get_draft_board();
    if ( ! $board || empty( $board['blocks'] ) ) return array();

    $queued = array();
    foreach ( $board['blocks'] as $block_id => $items ) {
        $block_queued = array();
        foreach ( $items as $item ) {
            if ( ! empty( $item['feed_item_id'] ) && ! empty( $item['queued'] ) ) {
                $block_queued[] = $item;
            }
        }
        if ( ! empty( $block_queued ) ) {
            $queued[ $block_id ] = $block_queued;
        }
    }
    return $queued;
}

/**
 * Count total queued items across all blocks.
 * @return int
 */
function jk_get_queue_count() {
    $queued = jk_get_queued_slots();
    $count = 0;
    foreach ( $queued as $items ) {
        $count += count( $items );
    }
    return $count;
}

/**
 * Clear all queue flags on the draft board.
 */
function jk_clear_queue_flags() {
    $board = jk_get_draft_board();
    if ( ! $board || empty( $board['blocks'] ) ) return true;

    foreach ( $board['blocks'] as &$items ) {
        foreach ( $items as &$item ) {
            unset( $item['queued'] );
        }
        unset( $item );
    }
    unset( $items );
    return jk_save_draft_board( $board );
}

// === End HF02 Queue Helpers ===

function jk_get_slot( $block_id ) {
    $board = jk_get_published_board();
    if ( ! $board || ! isset( $board['blocks'][ $block_id ] ) ) return null;
    return $board['blocks'][ $block_id ];
}

function jk_get_block_items( $block_id ) {
    return jk_get_slot( $block_id ) ?: array();
}

function jk_board_is_stale( $hours = 24 ) {
    $board = jk_get_published_board();
    if ( ! $board || empty( $board['published_at'] ) ) return true;
    return ( current_time( 'timestamp' ) - strtotime( $board['published_at'] ) ) > ( $hours * 3600 );
}

function jk_clear_board() {
    delete_option( 'jk_editorial_published' );
    return true;
}

function jk_board_meta() {
    $d = jk_get_draft_board();
    $p = jk_get_published_board();
    return array(
        'has_draft'       => ! empty( $d ),
        'has_published'   => ! empty( $p ),
        'draft_generated' => $d['generated'] ?? null,
        'published_at'    => $p['published_at'] ?? null,
        'version'         => $p['version'] ?? null,
        'is_stale'        => jk_board_is_stale(),
        'draft_differs'   => ( $d && $p ) ? ( $d['generated'] !== ( $p['generated'] ?? '' ) ) : false,
        'queue_count'     => jk_get_queue_count(),
    );
}
