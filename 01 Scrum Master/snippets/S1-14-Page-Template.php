<?php
/**
 * S1.14 — Custom Page Template: 7-Block Layout Rendering
 * Sprint 1, Gate 3 | GitLab Issue #21
 *
 * Shortcode [ns_frontpage] renders a 7-block responsive grid.
 * Queries ns_feed_item by block_assignment + promote_status = published.
 * Dark mode, matches design system (Rajdhani / Source Sans Pro).
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S1.14 Page Template — 7-Block Layout"
 * Scope: Run everywhere (front-end)
 * Depends on: S1-G1-Data-Model, S1.13 Promote + Block Assign
 *
 * @package NonSequitur
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ------------------------------------------------------------------
   1. Register [ns_frontpage] Shortcode
   ------------------------------------------------------------------ */

add_shortcode( 'ns_frontpage', 'ns_render_frontpage' );

function ns_render_frontpage( $atts ) {
    $atts = shortcode_atts( array(
        'status' => 'published',
    ), $atts, 'ns_frontpage' );

    // Domain color map
    $domain_colors = array(
        'Politics'      => '#3B82F6',
        'Tech'          => '#06B6D4',
        'Finance'       => '#10B981',
        'Culture'       => '#F59E0B',
        'Science'       => '#8B5CF6',
        'World'         => '#F97316',
        'Uncategorized' => '#6B7280',
    );

    // Query items per block
    $blocks = array();
    for ( $b = 1; $b <= 7; $b++ ) {
        $query = new WP_Query( array(
            'post_type'      => 'ns_feed_item',
            'posts_per_page' => ( $b <= 2 ) ? 1 : 3,
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'   => 'block_assignment',
                    'value' => $b,
                    'type'  => 'NUMERIC',
                ),
                array(
                    'key'   => 'promote_status',
                    'value' => sanitize_text_field( $atts['status'] ),
                ),
            ),
            'orderby'  => 'meta_value_num',
            'meta_key' => 'relevance_score',
            'order'    => 'DESC',
        ) );
        $blocks[ $b ] = $query->posts;
        wp_reset_postdata();
    }

    ob_start();
    ?>
    <div class="ns-frontpage">

        <!-- Masthead -->
        <header class="ns-masthead">
            <div class="ns-masthead-inner">
                <h1 class="ns-title">THE <span>MARKDOWN</span></h1>
                <p class="ns-subtitle">NON SEQUITUR INTELLIGENCE FEED</p>
                <p class="ns-dateline"><?php echo esc_html( strtoupper( gmdate( 'l, F j, Y' ) ) ); ?></p>
            </div>
        </header>

        <!-- 7-Block Grid -->
        <div class="ns-grid">

            <!-- HERO ROW: Block 1 + Block 2 -->
            <div class="ns-hero-row">
                <?php echo ns_render_block( 1, $blocks[1], $domain_colors, 'hero' ); ?>
                <?php echo ns_render_block( 2, $blocks[2], $domain_colors, 'hero' ); ?>
            </div>

            <!-- MID ROW: Blocks 3, 4, 5 -->
            <div class="ns-mid-row">
                <?php echo ns_render_block( 3, $blocks[3], $domain_colors, 'mid' ); ?>
                <?php echo ns_render_block( 4, $blocks[4], $domain_colors, 'mid' ); ?>
                <?php echo ns_render_block( 5, $blocks[5], $domain_colors, 'mid' ); ?>
            </div>

            <!-- BOTTOM ROW: Blocks 6, 7 -->
            <div class="ns-bottom-row">
                <?php echo ns_render_block( 6, $blocks[6], $domain_colors, 'bottom' ); ?>
                <?php echo ns_render_block( 7, $blocks[7], $domain_colors, 'bottom' ); ?>
            </div>

        </div>

        <!-- Footer -->
        <footer class="ns-footer">
            <p>NON SEQUITUR &mdash; AUTOMATED INTELLIGENCE FEED</p>
        </footer>

    </div>
    <?php
    return ob_get_clean();
}

/* ------------------------------------------------------------------
   2. Render a Single Block
   ------------------------------------------------------------------ */

function ns_render_block( $block_num, $items, $domain_colors, $row_type ) {
    $html = '<div class="ns-block ns-block-' . $block_num . ' ns-row-' . $row_type . '">';
    $html .= '<div class="ns-block-label">BLOCK ' . $block_num . '</div>';

    if ( empty( $items ) ) {
        $html .= '<div class="ns-empty">Awaiting content</div>';
    } else {
        foreach ( $items as $index => $post ) {
            $title   = esc_html( get_the_title( $post ) );
            $source  = esc_html( get_post_meta( $post->ID, 'source_name', true ) );
            $score   = intval( get_post_meta( $post->ID, 'relevance_score', true ) );
            $url     = esc_url( get_post_meta( $post->ID, 'source_url', true ) );
            $excerpt = wp_trim_words( $post->post_content, 25, '&hellip;' );
            $imported = get_post_meta( $post->ID, 'imported_date', true );
            $ago     = $imported ? human_time_diff( strtotime( $imported ), current_time( 'timestamp' ) ) . ' ago' : '';

            // Domain
            $domains = wp_get_post_terms( $post->ID, 'content_domain' );
            $domain_name  = ! empty( $domains ) ? $domains[0]->name : 'Uncategorized';
            $domain_color = isset( $domain_colors[ $domain_name ] ) ? $domain_colors[ $domain_name ] : '#6B7280';

            $is_lead = ( $row_type === 'hero' && $index === 0 );

            $html .= '<article class="ns-card' . ( $is_lead ? ' ns-card-lead' : '' ) . '">';
            $html .= '  <div class="ns-card-domain" style="background:' . $domain_color . '20;color:' . $domain_color . ';">' . esc_html( $domain_name ) . '</div>';
            $html .= '  <h3 class="ns-card-title">';
            if ( $url ) {
                $html .= '<a href="' . $url . '" target="_blank" rel="noopener">' . $title . '</a>';
            } else {
                $html .= $title;
            }
            $html .= '</h3>';

            if ( $is_lead && $excerpt ) {
                $html .= '<p class="ns-card-excerpt">' . esc_html( $excerpt ) . '</p>';
            }

            $html .= '  <div class="ns-card-meta">';
            if ( $source ) {
                $html .= '<span class="ns-meta-source">' . $source . '</span>';
            }
            if ( $score ) {
                $html .= '<span class="ns-meta-score" title="Relevance score">' . $score . '/10</span>';
            }
            if ( $ago ) {
                $html .= '<span class="ns-meta-time">' . esc_html( $ago ) . '</span>';
            }
            $html .= '  </div>';
            $html .= '</article>';
        }
    }

    $html .= '</div>';
    return $html;
}

/* ------------------------------------------------------------------
   3. Enqueue Front-End Styles
   ------------------------------------------------------------------ */

add_action( 'wp_head', 'ns_frontpage_styles' );

function ns_frontpage_styles() {
    if ( ! is_page() ) return;

    global $post;
    if ( ! $post || ! has_shortcode( $post->post_content, 'ns_frontpage' ) ) return;
    ?>
    <style>
    /* ===== NON SEQUITUR — 7-BLOCK LAYOUT ===== */
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Source+Sans+3:wght@300;400;600;700&family=JetBrains+Mono:wght@400;500&display=swap');

    .ns-frontpage {
        --bg: #0F1117;
        --bg2: #1A1D23;
        --bg3: #22262E;
        --bg4: #2A2F38;
        --text: #E8ECF1;
        --text2: #9CA3AF;
        --text3: #6B7280;
        --border: #2D3340;
        --blue: #3B82F6;

        background: var(--bg);
        color: var(--text);
        font-family: 'Source Sans 3', 'Source Sans Pro', -apple-system, sans-serif;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 16px 40px;
    }

    /* Masthead */
    .ns-masthead {
        text-align: center;
        padding: 32px 0 24px;
        border-bottom: 1px solid var(--border);
        margin-bottom: 24px;
    }
    .ns-title {
        font-family: 'Rajdhani', sans-serif;
        font-size: clamp(28px, 5vw, 48px);
        font-weight: 700;
        letter-spacing: 8px;
        margin: 0;
        color: var(--text);
    }
    .ns-title span { color: var(--blue); }
    .ns-subtitle {
        font-family: 'JetBrains Mono', monospace;
        font-size: 11px;
        letter-spacing: 4px;
        color: var(--text3);
        margin: 4px 0 0;
    }
    .ns-dateline {
        font-family: 'JetBrains Mono', monospace;
        font-size: 10px;
        letter-spacing: 3px;
        color: var(--text3);
        margin: 6px 0 0;
    }

    /* Grid Rows */
    .ns-hero-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 16px;
    }
    .ns-mid-row {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 16px;
        margin-bottom: 16px;
    }
    .ns-bottom-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 24px;
    }

    /* Block Container */
    .ns-block {
        background: var(--bg2);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 16px;
        min-height: 140px;
    }
    .ns-block-label {
        font-family: 'JetBrains Mono', monospace;
        font-size: 9px;
        letter-spacing: 2px;
        color: var(--text3);
        text-transform: uppercase;
        margin-bottom: 12px;
        padding-bottom: 6px;
        border-bottom: 1px solid var(--border);
    }
    .ns-empty {
        font-size: 13px;
        color: var(--text3);
        font-style: italic;
        text-align: center;
        padding: 20px 0;
    }

    /* Card */
    .ns-card {
        margin-bottom: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(45,51,64,0.5);
    }
    .ns-card:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }
    .ns-card-domain {
        display: inline-block;
        font-family: 'JetBrains Mono', monospace;
        font-size: 9px;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        padding: 2px 8px;
        border-radius: 3px;
        margin-bottom: 6px;
        font-weight: 500;
    }
    .ns-card-title {
        font-family: 'Rajdhani', sans-serif;
        font-size: 16px;
        font-weight: 600;
        line-height: 1.3;
        margin: 0 0 4px;
        letter-spacing: 0.5px;
    }
    .ns-card-lead .ns-card-title {
        font-size: 22px;
        line-height: 1.25;
    }
    .ns-card-title a {
        color: var(--text);
        text-decoration: none;
        transition: color 0.2s;
    }
    .ns-card-title a:hover { color: var(--blue); }
    .ns-card-excerpt {
        font-size: 13px;
        line-height: 1.5;
        color: var(--text2);
        margin: 6px 0 8px;
    }
    .ns-card-meta {
        display: flex;
        gap: 10px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 10px;
        color: var(--text3);
        letter-spacing: 0.5px;
    }
    .ns-meta-score {
        color: var(--blue);
        font-weight: 500;
    }

    /* Footer */
    .ns-footer {
        text-align: center;
        padding: 16px 0;
        border-top: 1px solid var(--border);
        font-family: 'JetBrains Mono', monospace;
        font-size: 10px;
        letter-spacing: 3px;
        color: var(--text3);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .ns-hero-row,
        .ns-mid-row,
        .ns-bottom-row {
            grid-template-columns: 1fr;
        }
    }
    @media (min-width: 769px) and (max-width: 1024px) {
        .ns-mid-row {
            grid-template-columns: 1fr 1fr;
        }
    }
    </style>
    <?php
}
