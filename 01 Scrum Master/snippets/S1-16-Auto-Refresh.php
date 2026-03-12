<?php
/**
 * S1.16 — Auto-Refresh: AJAX Polling Every 15 Minutes
 * Sprint 1, Gate 3 | GitLab Issue #23
 *
 * REST API endpoint returns published feed items per block.
 * Front-end JS polls every 15 min, updates block content
 * without full page reload. New items appear in correct blocks.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S1.16 Auto-Refresh"
 * Scope: Run everywhere
 * Depends on: S1-G1-Data-Model, S1.14 Page Template
 *
 * @package NonSequitur
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ------------------------------------------------------------------
   1. Register REST API Endpoint
   ------------------------------------------------------------------ */

add_action( 'rest_api_init', 'ns_register_refresh_endpoint' );

function ns_register_refresh_endpoint() {
    register_rest_route( 'ns/v1', '/blocks', array(
        'methods'             => 'GET',
        'callback'            => 'ns_rest_get_blocks',
        'permission_callback' => '__return_true',
        'args' => array(
            'status' => array(
                'default'           => 'published',
                'sanitize_callback' => 'sanitize_text_field',
            ),
        ),
    ) );
}

function ns_rest_get_blocks( $request ) {
    $status = $request->get_param( 'status' );

    $domain_colors = array(
        'Politics' => '#3B82F6', 'Tech' => '#06B6D4', 'Finance' => '#10B981',
        'Culture'  => '#F59E0B', 'Science' => '#8B5CF6', 'World' => '#F97316',
    );

    $data = array();

    for ( $b = 1; $b <= 7; $b++ ) {
        $query = new WP_Query( array(
            'post_type'      => 'ns_feed_item',
            'posts_per_page' => ( $b <= 2 ) ? 1 : 3,
            'meta_query'     => array(
                'relation' => 'AND',
                array( 'key' => 'block_assignment', 'value' => $b, 'type' => 'NUMERIC' ),
                array( 'key' => 'promote_status', 'value' => $status ),
            ),
            'orderby'  => 'meta_value_num',
            'meta_key' => 'relevance_score',
            'order'    => 'DESC',
        ) );

        $items = array();
        foreach ( $query->posts as $post ) {
            $domains     = wp_get_post_terms( $post->ID, 'content_domain' );
            $domain_name = ! empty( $domains ) ? $domains[0]->name : 'Uncategorized';
            $color       = isset( $domain_colors[ $domain_name ] ) ? $domain_colors[ $domain_name ] : '#6B7280';
            $imported    = get_post_meta( $post->ID, 'imported_date', true );

            $items[] = array(
                'id'       => $post->ID,
                'title'    => get_the_title( $post ),
                'source'   => get_post_meta( $post->ID, 'source_name', true ),
                'url'      => get_post_meta( $post->ID, 'source_url', true ),
                'score'    => (int) get_post_meta( $post->ID, 'relevance_score', true ),
                'excerpt'  => wp_trim_words( $post->post_content, 25, '…' ),
                'domain'   => $domain_name,
                'color'    => $color,
                'ago'      => $imported ? human_time_diff( strtotime( $imported ), current_time( 'timestamp' ) ) . ' ago' : '',
            );
        }
        wp_reset_postdata();

        $data[] = array(
            'block' => $b,
            'items' => $items,
        );
    }

    return rest_ensure_response( array(
        'blocks'    => $data,
        'generated' => current_time( 'c' ),
        'dateline'  => strtoupper( gmdate( 'l, F j, Y' ) ),
    ) );
}

/* ------------------------------------------------------------------
   2. Front-End Polling Script
   ------------------------------------------------------------------ */

add_action( 'wp_footer', 'ns_auto_refresh_script' );

function ns_auto_refresh_script() {
    if ( ! is_page() ) return;

    global $post;
    if ( ! $post || ! has_shortcode( $post->post_content, 'ns_frontpage' ) ) return;
    ?>
    <script>
    (function() {
        var POLL_INTERVAL = 15 * 60 * 1000; // 15 minutes
        var API_URL = '<?php echo esc_js( rest_url( 'ns/v1/blocks' ) ); ?>';
        var refreshTimer = null;
        var lastGenerated = '';

        function renderCard(item, isLead) {
            var cls = 'ns-card' + (isLead ? ' ns-card-lead' : '');
            var link = item.url
                ? '<a href="' + escHtml(item.url) + '" target="_blank" rel="noopener">' + escHtml(item.title) + '</a>'
                : escHtml(item.title);

            var html = '<article class="' + cls + '">';
            html += '<div class="ns-card-domain" style="background:' + item.color + '20;color:' + item.color + ';">' + escHtml(item.domain) + '</div>';
            html += '<h3 class="ns-card-title">' + link + '</h3>';

            if (isLead && item.excerpt) {
                html += '<p class="ns-card-excerpt">' + escHtml(item.excerpt) + '</p>';
            }

            html += '<div class="ns-card-meta">';
            if (item.source) html += '<span class="ns-meta-source">' + escHtml(item.source) + '</span>';
            if (item.score)  html += '<span class="ns-meta-score">' + item.score + '/10</span>';
            if (item.ago)    html += '<span class="ns-meta-time">' + escHtml(item.ago) + '</span>';
            html += '</div>';
            html += '</article>';
            return html;
        }

        function escHtml(str) {
            if (!str) return '';
            var d = document.createElement('div');
            d.textContent = str;
            return d.innerHTML;
        }

        function updateBlocks(data) {
            if (!data || !data.blocks) return;

            // Update dateline
            var dateline = document.querySelector('.ns-dateline');
            if (dateline && data.dateline) {
                dateline.textContent = data.dateline;
            }

            data.blocks.forEach(function(blockData) {
                var blockEl = document.querySelector('.ns-block-' + blockData.block);
                if (!blockEl) return;

                // Determine row type from parent
                var rowType = 'mid';
                if (blockEl.closest('.ns-hero-row')) rowType = 'hero';
                else if (blockEl.closest('.ns-bottom-row')) rowType = 'bottom';

                // Keep the block label
                var label = blockEl.querySelector('.ns-block-label');
                var labelHtml = label ? label.outerHTML : '<div class="ns-block-label">BLOCK ' + blockData.block + '</div>';

                // Build new content
                var content = labelHtml;
                if (blockData.items.length === 0) {
                    content += '<div class="ns-empty">Awaiting content</div>';
                } else {
                    blockData.items.forEach(function(item, idx) {
                        var isLead = (rowType === 'hero' && idx === 0);
                        content += renderCard(item, isLead);
                    });
                }

                // Fade transition
                blockEl.style.opacity = '0.5';
                blockEl.style.transition = 'opacity 0.3s';
                setTimeout(function() {
                    blockEl.innerHTML = content;
                    blockEl.style.opacity = '1';
                }, 300);
            });

            lastGenerated = data.generated || '';
        }

        function poll() {
            fetch(API_URL)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    // Only update if data changed
                    if (data.generated && data.generated !== lastGenerated) {
                        updateBlocks(data);
                    }
                })
                .catch(function(err) {
                    console.warn('[NS Auto-Refresh] Poll failed:', err);
                });
        }

        // Start polling
        function startPolling() {
            if (refreshTimer) clearInterval(refreshTimer);
            refreshTimer = setInterval(poll, POLL_INTERVAL);
            // Also poll immediately on load to populate lastGenerated
            poll();
        }

        // Pause when tab is hidden, resume when visible
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                if (refreshTimer) {
                    clearInterval(refreshTimer);
                    refreshTimer = null;
                }
            } else {
                poll(); // Immediate refresh on return
                startPolling();
            }
        });

        // Initialize
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', startPolling);
        } else {
            startPolling();
        }
    })();
    </script>
    <?php
}
