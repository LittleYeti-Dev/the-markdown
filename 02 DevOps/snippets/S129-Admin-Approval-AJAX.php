/* S123b S3.8-W2-02b Admin Approval AJAX Handlers
 * Depends: S121 (Board Data Model), S122 (Draft Builder), S125 (Preference Logger)
 * Handles: reject, move, swap, publish, refresh, clear, topic interests
 * HF02: Added jk_queue_slot action, modified publish for queue-only behavior
 */
if ( ! defined( 'ABSPATH' ) ) return;

// === Security check helper ===
function jk_eb_check_auth() {
    check_ajax_referer( 'jk_editorial_actions' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Unauthorized' );
    }
}

// === HF02: QUEUE/UNQUEUE a block for publishing ===
add_action( 'wp_ajax_jk_editorial_queue', function() {
    jk_eb_check_auth();
    $block_id     = sanitize_text_field( $_POST['block_id'] ?? '' );
    $queue_action = sanitize_text_field( $_POST['queue_action'] ?? 'queue' );

    if ( ! preg_match( '/^\d{2}$/', $block_id ) ) {
        wp_send_json_error( array( 'code' => 'invalid_block', 'message' => 'Invalid block ID format' ) );
    }

    $queued = ( $queue_action === 'queue' );
    $result = jk_queue_block( $block_id, $queued );

    if ( $result && ! is_wp_error( $result ) ) {
        wp_send_json_success( array(
            'block_id'    => $block_id,
            'queued'      => $queued,
            'message'     => $queued ? 'Added to publishing queue' : 'Removed from publishing queue',
            'queue_count' => jk_get_queue_count(),
        ) );
    } else {
        wp_send_json_error( array( 'code' => 'queue_failed', 'message' => 'Block is empty or save failed' ) );
    }
});

// === REJECT: remove item from slot, auto-fill from candidate pool ===
add_action( 'wp_ajax_jk_editorial_reject', function() {
    jk_eb_check_auth();
    $slot = sanitize_text_field( $_POST['slot'] ?? '' );
    $fid  = intval( $_POST['feed_item_id'] ?? 0 );
    if ( ! $slot || ! $fid ) wp_send_json_error( 'Missing slot or feed_item_id' );

    $board = jk_get_draft_board();
    if ( ! $board || empty( $board['blocks'] ) ) wp_send_json_error( 'No draft board' );

    $block_id = substr( $slot, 0, 2 );
    $found = false;
    if ( isset( $board['blocks'][ $block_id ] ) ) {
        foreach ( $board['blocks'][ $block_id ] as $i => $item ) {
            if ( (int) $item['feed_item_id'] === $fid ) {
                $removed = $item;
                unset( $board['blocks'][ $block_id ][ $i ] );
                $board['blocks'][ $block_id ] = array_values( $board['blocks'][ $block_id ] );
                $found = true;
                break;
            }
        }
    }
    if ( ! $found ) wp_send_json_error( 'Item not found in slot' );

    // Add to rejected list
    if ( ! isset( $board['rejected'] ) ) $board['rejected'] = array();
    $board['rejected'][] = array(
        'feed_item_id' => $fid,
        'was_slot'     => $slot,
        'signal'       => 'rejected',
        'title'        => $removed['title'] ?? '',
    );

    // Auto-fill from candidate pool
    if ( ! empty( $board['candidate_pool'] ) ) {
        $replacement = array_shift( $board['candidate_pool'] );
        $pos = count( $board['blocks'][ $block_id ] );
        $replacement['slot'] = $block_id . $pos;
        $board['blocks'][ $block_id ][] = $replacement;
    }

    jk_save_draft_board( $board );
    wp_send_json_success( 'Rejected and replaced' );
});

// === MOVE: move item to a different slot ===
add_action( 'wp_ajax_jk_editorial_move', function() {
    jk_eb_check_auth();
    $fid     = intval( $_POST['feed_item_id'] ?? 0 );
    $to_slot = sanitize_text_field( $_POST['to_slot'] ?? '' );
    if ( ! $fid || ! $to_slot ) wp_send_json_error( 'Missing params' );

    $board = jk_get_draft_board();
    if ( ! $board ) wp_send_json_error( 'No draft board' );

    // Find and remove item from current position
    $moving_item = null;
    foreach ( $board['blocks'] as $bid => &$items ) {
        foreach ( $items as $i => $item ) {
            if ( (int) $item['feed_item_id'] === $fid ) {
                $moving_item = $item;
                unset( $items[ $i ] );
                $items = array_values( $items );
                break 2;
            }
        }
    }
    unset( $items );
    if ( ! $moving_item ) wp_send_json_error( 'Item not found on board' );

    // Insert into target block
    $to_block = substr( $to_slot, 0, 2 );
    if ( ! isset( $board['blocks'][ $to_block ] ) ) {
        $board['blocks'][ $to_block ] = array();
    }
    $max = ( $to_block === '00' ) ? 1 : 3;
    $mode = sanitize_text_field( $_POST['mode'] ?? '' );

    if ( count( $board['blocks'][ $to_block ] ) >= $max ) {
        // Target block is full — need mode to resolve
        if ( ! $mode ) {
            // Return conflict info so frontend can ask user
            $occupant = null;
            foreach ( $board['blocks'][ $to_block ] as $occ ) {
                if ( isset( $occ['slot'] ) && $occ['slot'] === $to_slot ) {
                    $occupant = $occ;
                    break;
                }
            }
            // If exact slot not found, use last item in block
            if ( ! $occupant && ! empty( $board['blocks'][ $to_block ] ) ) {
                $occupant = end( $board['blocks'][ $to_block ] );
            }
            wp_send_json_error( array(
                'code'    => 'slot_occupied',
                'message' => 'Target slot is occupied',
                'occupant_title' => $occupant['title'] ?? 'Unknown',
                'occupant_fid'   => (int)( $occupant['feed_item_id'] ?? 0 ),
                'occupant_slot'  => $occupant['slot'] ?? $to_slot,
            ) );
        }

        if ( $mode === 'swap' ) {
            // Board-to-board swap: find occupant at target, swap positions
            $from_slot = $moving_item['slot'];
            $from_block = substr( $from_slot, 0, 2 );
            $swapped = false;
            foreach ( $board['blocks'][ $to_block ] as &$occ ) {
                if ( isset( $occ['slot'] ) && $occ['slot'] === $to_slot ) {
                    // Swap slots
                    $occ['slot'] = $from_slot;
                    $moving_item['slot'] = $to_slot;
                    // Move occupant to source block
                    $board['blocks'][ $from_block ][] = $occ;
                    // Remove occupant from target block
                    $board['blocks'][ $to_block ] = array_values( array_filter(
                        $board['blocks'][ $to_block ],
                        function( $it ) use ( $occ ) { return (int)$it['feed_item_id'] !== (int)$occ['feed_item_id']; }
                    ) );
                    $swapped = true;
                    break;
                }
            }
            unset( $occ );
            if ( ! $swapped ) wp_send_json_error( 'Swap target not found' );
            $board['blocks'][ $to_block ][] = $moving_item;
            jk_save_draft_board( $board );
            wp_send_json_success( 'Items swapped' );
            return;
        }

        if ( $mode === 'replace' ) {
            // Displace occupant to candidate pool, insert mover
            foreach ( $board['blocks'][ $to_block ] as $idx => $occ ) {
                if ( isset( $occ['slot'] ) && $occ['slot'] === $to_slot ) {
                    unset( $occ['slot'] );
                    $board['candidate_pool'][] = $occ;
                    unset( $board['blocks'][ $to_block ][ $idx ] );
                    $board['blocks'][ $to_block ] = array_values( $board['blocks'][ $to_block ] );
                    break;
                }
            }
            // Fall through to insert below
        }
    }

    $pos = count( $board['blocks'][ $to_block ] );
    $moving_item['slot'] = $to_block . $pos;
    $board['blocks'][ $to_block ][] = $moving_item;

    jk_save_draft_board( $board );
    wp_send_json_success( $mode === 'replace' ? 'Item replaced' : 'Item moved' );
});

// === SWAP: replace a board slot with a candidate pool item ===
add_action( 'wp_ajax_jk_editorial_swap', function() {
    jk_eb_check_auth();
    $fid     = intval( $_POST['feed_item_id'] ?? 0 );
    $to_slot = sanitize_text_field( $_POST['to_slot'] ?? '' );
    if ( ! $fid || ! $to_slot ) wp_send_json_error( 'Missing params' );

    $board = jk_get_draft_board();
    if ( ! $board ) wp_send_json_error( 'No draft board' );

    // Find candidate in pool
    $candidate = null;
    $pool_idx  = null;
    foreach ( $board['candidate_pool'] as $ci => $c ) {
        if ( (int) $c['feed_item_id'] === $fid ) {
            $candidate = $c;
            $pool_idx  = $ci;
            break;
        }
    }
    if ( ! $candidate ) wp_send_json_error( 'Candidate not found in pool' );

    // Insert candidate into target slot, push displaced item to pool
    $to_block = substr( $to_slot, 0, 2 );
    if ( ! isset( $board['blocks'][ $to_block ] ) ) {
        $board['blocks'][ $to_block ] = array();
    }

    // If block is full, remove last item to pool
    $max = ( $to_block === '00' ) ? 1 : 3;
    if ( count( $board['blocks'][ $to_block ] ) >= $max ) {
        $displaced = array_pop( $board['blocks'][ $to_block ] );
        $board['candidate_pool'][] = $displaced;
    }

    // Remove candidate from pool and add to block
    unset( $board['candidate_pool'][ $pool_idx ] );
    $board['candidate_pool'] = array_values( $board['candidate_pool'] );
    $pos = count( $board['blocks'][ $to_block ] );
    $candidate['slot'] = $to_block . $pos;
    $board['blocks'][ $to_block ][] = $candidate;

    jk_save_draft_board( $board );
    wp_send_json_success( 'Candidate swapped in' );
});

// === PUBLISH: validate + publish + log preferences (HF02: queue-aware) ===
add_action( 'wp_ajax_jk_editorial_publish', function() {
    jk_eb_check_auth();
    $draft = jk_get_draft_board();
    if ( ! $draft ) wp_send_json_error( 'No draft to publish' );

    // HF02: Check queue has items
    $queue_count = jk_get_queue_count();
    if ( $queue_count === 0 ) {
        wp_send_json_error( array(
            'code'    => 'empty_queue',
            'message' => 'No items in publishing queue. Review and queue items first.',
        ) );
    }

    // Box 00 hero check (only if Box 00 is queued)
    $queued = jk_get_queued_slots();
    if ( isset( $queued['00'] ) && ! empty( $queued['00'] ) ) {
        $lead = $queued['00'][0];
        $hero = get_post_meta( $lead['feed_item_id'], '_jk_hero_image', true );
        if ( empty( $hero ) ) {
            wp_send_json_error( 'Box 00 lead story must have a hero image' );
        }
    }

    // Capture pre-publish state for preference logging
    $prev_published = jk_get_published_board();
    $result = jk_publish_board();
    if ( is_wp_error( $result ) ) {
        wp_send_json_error( $result->get_error_message() );
    }

    // Log preferences if logger exists
    if ( function_exists( 'jk_log_editorial_preferences' ) ) {
        jk_log_editorial_preferences( $draft, jk_get_published_board() );
    }

    wp_send_json_success( array(
        'message'         => sprintf( '%d items published to The Markdown', $result ),
        'published_count' => $result,
    ) );
});

// === REFRESH: re-run draft builder ===
add_action( 'wp_ajax_jk_editorial_refresh', function() {
    jk_eb_check_auth();
    if ( function_exists( 'jk_build_editorial_draft' ) ) {
        jk_build_editorial_draft( 'manual' );
        wp_send_json_success( 'Draft refreshed' );
    } else {
        wp_send_json_error( 'Draft builder (S122) not available' );
    }
});

// === CLEAR: clear published board ===
add_action( 'wp_ajax_jk_editorial_clear', function() {
    jk_eb_check_auth();
    if ( function_exists( 'jk_clear_board' ) ) {
        jk_clear_board();
        wp_send_json_success( 'Board cleared — page will use live query' );
    } else {
        wp_send_json_error( 'Board model (S121) not available' );
    }
});

// === ADD TOPIC INTEREST ===
add_action( 'wp_ajax_jk_editorial_addtopic', function() {
    jk_eb_check_auth();
    $label = sanitize_text_field( $_POST['label'] ?? '' );
    $kw    = sanitize_text_field( $_POST['keywords'] ?? '' );
    $wt    = floatval( $_POST['weight'] ?? 1.2 );
    if ( ! $label || ! $kw ) wp_send_json_error( 'Label and keywords required' );
    $wt = max( 1.0, min( 2.0, $wt ) );

    $topics = get_option( 'jk_editorial_topic_interests', array() );
    $topics[] = array(
        'label'    => $label,
        'keywords' => array_map( 'trim', explode( ',', $kw ) ),
        'weight'   => $wt,
    );
    update_option( 'jk_editorial_topic_interests', $topics );
    wp_send_json_success( 'Topic added' );
});

// === REMOVE TOPIC INTEREST ===
add_action( 'wp_ajax_jk_editorial_rmtopic', function() {
    jk_eb_check_auth();
    $idx = intval( $_POST['index'] ?? -1 );
    $topics = get_option( 'jk_editorial_topic_interests', array() );
    if ( ! isset( $topics[ $idx ] ) ) wp_send_json_error( 'Invalid index' );
    unset( $topics[ $idx ] );
    update_option( 'jk_editorial_topic_interests', array_values( $topics ) );
    wp_send_json_success( 'Topic removed' );
});
