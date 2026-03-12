<?php
/**
 * S1.13 — Promote Button + Block Assignment
 * Sprint 1, Gate 3 | GitLab Issue #20
 *
 * One-click promote from DEV to READY status,
 * block assignment dropdown (7 blocks),
 * bulk action support for multi-select.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S1.13 Promote + Block Assign"
 * Scope: Admin only
 * Depends on: S1-G1-Data-Model, S1.12 Editorial Dashboard
 *
 * @package NonSequitur
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ------------------------------------------------------------------
   1. AJAX: Promote Single Item (DEV → READY)
   ------------------------------------------------------------------ */

add_action( 'wp_ajax_ns_promote_item', 'ns_ajax_promote_item' );

function ns_ajax_promote_item() {
    check_ajax_referer( 'ns_editorial_actions', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Permission denied.' );
    }

    $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
    if ( ! $post_id || get_post_type( $post_id ) !== 'ns_feed_item' ) {
        wp_send_json_error( 'Invalid feed item.' );
    }

    $current = get_post_meta( $post_id, 'promote_status', true );

    // Promotion path: dev → ready → published
    $next = array( 'dev' => 'ready', 'ready' => 'published' );
    if ( ! isset( $next[ $current ] ) ) {
        wp_send_json_error( 'Item cannot be promoted from status: ' . $current );
    }

    $new_status = $next[ $current ];
    update_post_meta( $post_id, 'promote_status', $new_status );
    update_post_meta( $post_id, 'promoted_by', get_current_user_id() );
    update_post_meta( $post_id, 'promoted_at', gmdate( 'c' ) );

    wp_send_json_success( array(
        'post_id'    => $post_id,
        'old_status' => $current,
        'new_status' => $new_status,
        'promoted_by' => get_current_user_id(),
        'promoted_at' => current_time( 'M j, g:ia' ),
    ) );
}

/* ------------------------------------------------------------------
   2. AJAX: Assign Block
   ------------------------------------------------------------------ */

add_action( 'wp_ajax_ns_assign_block', 'ns_ajax_assign_block' );

function ns_ajax_assign_block() {
    check_ajax_referer( 'ns_editorial_actions', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Permission denied.' );
    }

    $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
    $block   = isset( $_POST['block'] )   ? absint( $_POST['block'] )   : 0;

    if ( ! $post_id || get_post_type( $post_id ) !== 'ns_feed_item' ) {
        wp_send_json_error( 'Invalid feed item.' );
    }
    if ( $block > 7 ) {
        wp_send_json_error( 'Invalid block number.' );
    }

    update_post_meta( $post_id, 'block_assignment', $block );

    wp_send_json_success( array(
        'post_id' => $post_id,
        'block'   => $block,
    ) );
}

/* ------------------------------------------------------------------
   3. AJAX: Bulk Promote
   ------------------------------------------------------------------ */

add_action( 'wp_ajax_ns_bulk_promote', 'ns_ajax_bulk_promote' );

function ns_ajax_bulk_promote() {
    check_ajax_referer( 'ns_editorial_actions', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Permission denied.' );
    }

    $post_ids = isset( $_POST['post_ids'] ) ? array_map( 'absint', (array) $_POST['post_ids'] ) : array();
    $post_ids = array_filter( $post_ids );

    if ( empty( $post_ids ) ) {
        wp_send_json_error( 'No items selected.' );
    }

    $next = array( 'dev' => 'ready', 'ready' => 'published' );
    $results = array();

    foreach ( $post_ids as $pid ) {
        if ( get_post_type( $pid ) !== 'ns_feed_item' ) continue;

        $current = get_post_meta( $pid, 'promote_status', true ) ?: 'dev';
        if ( isset( $next[ $current ] ) ) {
            update_post_meta( $pid, 'promote_status', $next[ $current ] );
            update_post_meta( $pid, 'promoted_by', get_current_user_id() );
            update_post_meta( $pid, 'promoted_at', gmdate( 'c' ) );
            $results[] = array( 'post_id' => $pid, 'new_status' => $next[ $current ] );
        }
    }

    wp_send_json_success( array( 'promoted' => count( $results ), 'items' => $results ) );
}

/* ------------------------------------------------------------------
   4. AJAX: Bulk Assign Block
   ------------------------------------------------------------------ */

add_action( 'wp_ajax_ns_bulk_assign_block', 'ns_ajax_bulk_assign_block' );

function ns_ajax_bulk_assign_block() {
    check_ajax_referer( 'ns_editorial_actions', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Permission denied.' );
    }

    $post_ids = isset( $_POST['post_ids'] ) ? array_map( 'absint', (array) $_POST['post_ids'] ) : array();
    $block    = isset( $_POST['block'] )    ? absint( $_POST['block'] )                         : 0;
    $post_ids = array_filter( $post_ids );

    if ( empty( $post_ids ) ) {
        wp_send_json_error( 'No items selected.' );
    }
    if ( $block > 7 ) {
        wp_send_json_error( 'Invalid block number.' );
    }

    $count = 0;
    foreach ( $post_ids as $pid ) {
        if ( get_post_type( $pid ) !== 'ns_feed_item' ) continue;
        update_post_meta( $pid, 'block_assignment', $block );
        $count++;
    }

    wp_send_json_success( array( 'assigned' => $count, 'block' => $block ) );
}

/* ------------------------------------------------------------------
   5. Enqueue Action Bar JS on Editorial Dashboard
   ------------------------------------------------------------------ */

add_action( 'admin_footer', 'ns_editorial_action_bar_js' );

function ns_editorial_action_bar_js() {
    $screen = get_current_screen();
    if ( ! $screen || $screen->id !== 'ns_feed_item_page_ns-editorial-dashboard' ) {
        return;
    }

    $nonce = wp_create_nonce( 'ns_editorial_actions' );
    ?>
    <div id="ns-action-bar" style="display:none;position:sticky;bottom:0;left:0;right:0;background:#1d2327;color:#fff;padding:10px 20px;z-index:999;border-top:2px solid #2271b1;display:none;align-items:center;gap:12px;flex-wrap:wrap;">
        <span id="ns-selected-count" style="font-weight:600;font-size:13px;min-width:80px;">0 selected</span>

        <button id="ns-bulk-promote" class="button" style="background:#2271b1;color:#fff;border:none;font-weight:600;">
            Promote Selected
        </button>

        <select id="ns-bulk-block-select" style="min-width:120px;">
            <option value="">Assign Block...</option>
            <option value="0">Unassign</option>
            <option value="1">Block 1</option>
            <option value="2">Block 2</option>
            <option value="3">Block 3</option>
            <option value="4">Block 4</option>
            <option value="5">Block 5</option>
            <option value="6">Block 6</option>
            <option value="7">Block 7</option>
        </select>
        <button id="ns-bulk-block-apply" class="button" style="background:#135e21;color:#fff;border:none;font-weight:600;">
            Apply Block
        </button>

        <button id="ns-clear-selection" class="button" style="margin-left:auto;background:transparent;color:#a7aaad;border:1px solid #3c434a;">
            Clear
        </button>
    </div>

    <script>
    (function() {
        var nonce = '<?php echo esc_js( $nonce ); ?>';
        var ajaxurl = '<?php echo esc_js( admin_url( "admin-ajax.php" ) ); ?>';
        var bar = document.getElementById('ns-action-bar');
        var countEl = document.getElementById('ns-selected-count');

        function getChecked() {
            return Array.from(document.querySelectorAll('.ns-item-check:checked')).map(function(cb) {
                return parseInt(cb.value);
            });
        }

        function updateBar() {
            var ids = getChecked();
            if (ids.length > 0) {
                bar.style.display = 'flex';
                countEl.textContent = ids.length + ' selected';
            } else {
                bar.style.display = 'none';
            }
        }

        // Listen for checkbox changes
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('ns-item-check') || e.target.id === 'ns-select-all') {
                updateBar();
            }
        });

        // Inline promote buttons (added per-row)
        document.querySelectorAll('tr[data-post-id]').forEach(function(row) {
            var pid = row.dataset.postId;
            var statusCell = row.querySelector('td:nth-child(7)');
            var blockCell = row.querySelector('td:nth-child(6)');

            if (statusCell) {
                var statusText = statusCell.textContent.trim().toLowerCase();
                if (statusText === 'dev' || statusText === 'ready') {
                    var btn = document.createElement('button');
                    btn.className = 'button button-small';
                    btn.style.cssText = 'margin-left:6px;font-size:10px;padding:0 6px;line-height:20px;';
                    btn.textContent = statusText === 'dev' ? 'Promote' : 'Publish';
                    btn.onclick = function() {
                        btn.disabled = true;
                        btn.textContent = '...';
                        fetch(ajaxurl, {
                            method: 'POST',
                            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                            body: 'action=ns_promote_item&nonce=' + nonce + '&post_id=' + pid
                        })
                        .then(function(r) { return r.json(); })
                        .then(function(d) {
                            if (d.success) {
                                location.reload();
                            } else {
                                btn.textContent = 'Error';
                                alert(d.data || 'Promote failed');
                            }
                        });
                    };
                    statusCell.appendChild(btn);
                }
            }

            // Inline block dropdown
            if (blockCell) {
                var sel = document.createElement('select');
                sel.style.cssText = 'font-size:11px;padding:1px 4px;margin-left:4px;max-width:70px;';
                sel.innerHTML = '<option value="">B...</option><option value="0">--</option>';
                for (var b = 1; b <= 7; b++) sel.innerHTML += '<option value="' + b + '">B' + b + '</option>';
                sel.onchange = function() {
                    if (sel.value === '') return;
                    fetch(ajaxurl, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'action=ns_assign_block&nonce=' + nonce + '&post_id=' + pid + '&block=' + sel.value
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(d) {
                        if (d.success) location.reload();
                        else alert(d.data || 'Block assign failed');
                    });
                };
                blockCell.appendChild(sel);
            }
        });

        // Bulk promote
        document.getElementById('ns-bulk-promote').onclick = function() {
            var ids = getChecked();
            if (!ids.length) return;
            this.disabled = true;
            this.textContent = 'Promoting...';
            fetch(ajaxurl, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=ns_bulk_promote&nonce=' + nonce + '&post_ids[]=' + ids.join('&post_ids[]=')
            })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.success) location.reload();
                else alert(d.data || 'Bulk promote failed');
            });
        };

        // Bulk block assign
        document.getElementById('ns-bulk-block-apply').onclick = function() {
            var ids = getChecked();
            var block = document.getElementById('ns-bulk-block-select').value;
            if (!ids.length || block === '') return;
            this.disabled = true;
            this.textContent = 'Assigning...';
            fetch(ajaxurl, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=ns_bulk_assign_block&nonce=' + nonce + '&block=' + block + '&post_ids[]=' + ids.join('&post_ids[]=')
            })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.success) location.reload();
                else alert(d.data || 'Block assign failed');
            });
        };

        // Clear selection
        document.getElementById('ns-clear-selection').onclick = function() {
            document.querySelectorAll('.ns-item-check, #ns-select-all').forEach(function(cb) {
                cb.checked = false;
            });
            updateBar();
        };
    })();
    </script>
    <?php
}
