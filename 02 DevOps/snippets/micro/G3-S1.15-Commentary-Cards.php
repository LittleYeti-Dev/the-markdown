<?php
/**
 * S1.15 — Commentary Cards: 3 Embed Styles
 * Sprint 1, Gate 3 | GitLab Issue #22
 *
 * Three commentary card styles for feed items:
 * 1. Tweet Card — social post look with avatar, handle, timestamp
 * 2. Pull Quote — editorial highlight with large type + attribution
 * 3. Inline Embed — compact card for in-flow content
 *
 * Renders via ns_commentary_card() helper used inside S1.14 blocks.
 * Also registers [ns_commentary] shortcode for standalone use.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S1.15 Commentary Cards"
 * Scope: Run everywhere (front-end)
 * Depends on: S1-G1-Data-Model, S1.14 Page Template
 *
 * @package NonSequitur
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ------------------------------------------------------------------
   1. Commentary Card Renderer
   ------------------------------------------------------------------ */

/**
 * Render a commentary card for a feed item.
 *
 * @param int    $post_id   The ns_feed_item post ID.
 * @param string $style     One of: tweet, pullquote, inline.
 * @return string           HTML for the card.
 */
function ns_commentary_card( $post_id, $style = 'inline' ) {
    $post = get_post( $post_id );
    if ( ! $post || $post->post_type !== 'ns_feed_item' ) return '';

    $title     = esc_html( get_the_title( $post ) );
    $source    = esc_html( get_post_meta( $post_id, 'source_name', true ) );
    $url       = esc_url( get_post_meta( $post_id, 'source_url', true ) );
    $score     = intval( get_post_meta( $post_id, 'relevance_score', true ) );
    $excerpt   = wp_trim_words( $post->post_content, 40, '&hellip;' );
    $imported  = get_post_meta( $post_id, 'imported_date', true );
    $ago       = $imported ? human_time_diff( strtotime( $imported ), current_time( 'timestamp' ) ) . ' ago' : '';
    $platform  = '';
    $platforms = wp_get_post_terms( $post_id, 'platform_published' );
    if ( ! empty( $platforms ) ) $platform = strtolower( $platforms[0]->name );

    // Domain
    $domains      = wp_get_post_terms( $post_id, 'content_domain' );
    $domain_name  = ! empty( $domains ) ? $domains[0]->name : 'Uncategorized';
    $domain_colors = array(
        'Politics' => '#3B82F6', 'Tech' => '#06B6D4', 'Finance' => '#10B981',
        'Culture'  => '#F59E0B', 'Science' => '#8B5CF6', 'World' => '#F97316',
    );
    $color = isset( $domain_colors[ $domain_name ] ) ? $domain_colors[ $domain_name ] : '#6B7280';

    $link_open  = $url ? '<a href="' . $url . '" target="_blank" rel="noopener" class="ns-cc-link">' : '<span class="ns-cc-link">';
    $link_close = $url ? '</a>' : '</span>';

    $html = '';

    switch ( $style ) {

        /* ---- TWEET CARD ---- */
        case 'tweet':
            $handle = $source ? '@' . sanitize_title( $source ) : '@source';
            $avatar_letter = $source ? strtoupper( substr( $source, 0, 1 ) ) : 'S';

            $html .= '<div class="ns-cc ns-cc-tweet">';
            $html .= $link_open;
            $html .= '  <div class="ns-cc-tweet-header">';
            $html .= '    <div class="ns-cc-avatar" style="background:' . $color . ';">' . $avatar_letter . '</div>';
            $html .= '    <div class="ns-cc-identity">';
            $html .= '      <span class="ns-cc-name">' . $source . '</span>';
            $html .= '      <span class="ns-cc-handle">' . esc_html( $handle ) . '</span>';
            $html .= '    </div>';
            if ( $platform ) {
                $html .= '    <span class="ns-cc-platform ns-plat-' . $platform . '">' . esc_html( ucfirst( $platform ) ) . '</span>';
            }
            $html .= '  </div>';
            $html .= '  <div class="ns-cc-body">' . esc_html( $excerpt ) . '</div>';
            $html .= '  <div class="ns-cc-tweet-footer">';
            $html .= '    <span class="ns-cc-time">' . esc_html( $ago ) . '</span>';
            if ( $score ) $html .= '    <span class="ns-cc-score">' . $score . '/10</span>';
            $html .= '  </div>';
            $html .= $link_close;
            $html .= '</div>';
            break;

        /* ---- PULL QUOTE ---- */
        case 'pullquote':
            $short = wp_trim_words( $post->post_content, 20, '&hellip;' );
            $html .= '<div class="ns-cc ns-cc-pullquote" style="border-left-color:' . $color . ';">';
            $html .= $link_open;
            $html .= '  <blockquote class="ns-cc-quote">' . esc_html( $short ) . '</blockquote>';
            $html .= '  <cite class="ns-cc-cite">';
            if ( $source ) $html .= $source;
            if ( $domain_name !== 'Uncategorized' ) $html .= ' &mdash; ' . esc_html( $domain_name );
            $html .= '  </cite>';
            $html .= $link_close;
            $html .= '</div>';
            break;

        /* ---- INLINE EMBED (default) ---- */
        case 'inline':
        default:
            $html .= '<div class="ns-cc ns-cc-inline">';
            $html .= $link_open;
            $html .= '  <div class="ns-cc-inline-domain" style="background:' . $color . '20;color:' . $color . ';">' . esc_html( $domain_name ) . '</div>';
            $html .= '  <h4 class="ns-cc-inline-title">' . $title . '</h4>';
            $html .= '  <p class="ns-cc-inline-excerpt">' . esc_html( wp_trim_words( $post->post_content, 18, '&hellip;' ) ) . '</p>';
            $html .= '  <div class="ns-cc-inline-meta">';
            if ( $source ) $html .= '<span>' . $source . '</span>';
            if ( $score ) $html .= '<span class="ns-cc-score">' . $score . '/10</span>';
            if ( $ago )   $html .= '<span>' . esc_html( $ago ) . '</span>';
            $html .= '  </div>';
            $html .= $link_close;
            $html .= '</div>';
            break;
    }

    return $html;
}

/* ------------------------------------------------------------------
   2. [ns_commentary] Shortcode
   ------------------------------------------------------------------ */

add_shortcode( 'ns_commentary', 'ns_commentary_shortcode' );

function ns_commentary_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'id'    => 0,
        'style' => 'inline',
        'block' => 0,
        'count' => 3,
    ), $atts, 'ns_commentary' );

    // If a specific post ID is given
    if ( $atts['id'] ) {
        return ns_commentary_card( absint( $atts['id'] ), $atts['style'] );
    }

    // Otherwise query by block
    $block = absint( $atts['block'] );
    if ( ! $block ) return '';

    $query = new WP_Query( array(
        'post_type'      => 'ns_feed_item',
        'posts_per_page' => absint( $atts['count'] ),
        'meta_query'     => array(
            'relation' => 'AND',
            array( 'key' => 'block_assignment', 'value' => $block, 'type' => 'NUMERIC' ),
            array( 'key' => 'promote_status', 'value' => 'published' ),
        ),
        'orderby'  => 'meta_value_num',
        'meta_key' => 'relevance_score',
        'order'    => 'DESC',
    ) );

    $html = '<div class="ns-cc-group">';
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $html .= ns_commentary_card( get_the_ID(), $atts['style'] );
        }
        wp_reset_postdata();
    } else {
        $html .= '<div class="ns-cc-empty">No commentary items available.</div>';
    }
    $html .= '</div>';

    return $html;
}

/* ------------------------------------------------------------------
   3. Commentary Card Styles (front-end)
   ------------------------------------------------------------------ */

add_action( 'wp_head', 'ns_commentary_card_styles' );

function ns_commentary_card_styles() {
    ?>
    <style>
    /* ===== COMMENTARY CARDS ===== */
    .ns-cc {
        --cc-bg: #1A1D23;
        --cc-bg2: #22262E;
        --cc-border: #2D3340;
        --cc-text: #E8ECF1;
        --cc-text2: #9CA3AF;
        --cc-text3: #6B7280;
        --cc-blue: #3B82F6;
        font-family: 'Source Sans 3', 'Source Sans Pro', -apple-system, sans-serif;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 12px;
        transition: transform 0.15s, box-shadow 0.15s;
    }
    .ns-cc:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }
    .ns-cc-link {
        color: inherit;
        text-decoration: none;
        display: block;
    }
    .ns-cc-score {
        color: var(--cc-blue);
        font-family: 'JetBrains Mono', monospace;
        font-size: 10px;
        font-weight: 500;
    }

    /* ---- TWEET CARD ---- */
    .ns-cc-tweet {
        background: var(--cc-bg);
        border: 1px solid var(--cc-border);
        padding: 16px;
    }
    .ns-cc-tweet-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }
    .ns-cc-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Rajdhani', sans-serif;
        font-weight: 700;
        font-size: 16px;
        color: #fff;
        flex-shrink: 0;
    }
    .ns-cc-identity {
        display: flex;
        flex-direction: column;
        gap: 1px;
        min-width: 0;
    }
    .ns-cc-name {
        font-weight: 600;
        font-size: 14px;
        color: var(--cc-text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .ns-cc-handle {
        font-family: 'JetBrains Mono', monospace;
        font-size: 11px;
        color: var(--cc-text3);
    }
    .ns-cc-platform {
        margin-left: auto;
        font-family: 'JetBrains Mono', monospace;
        font-size: 9px;
        letter-spacing: 1px;
        text-transform: uppercase;
        padding: 2px 6px;
        border-radius: 3px;
        background: rgba(255,255,255,0.06);
        color: var(--cc-text3);
    }
    .ns-cc-body {
        font-size: 14px;
        line-height: 1.55;
        color: var(--cc-text);
        margin-bottom: 10px;
    }
    .ns-cc-tweet-footer {
        display: flex;
        gap: 12px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 10px;
        color: var(--cc-text3);
    }

    /* ---- PULL QUOTE ---- */
    .ns-cc-pullquote {
        background: var(--cc-bg2);
        border-left: 3px solid var(--cc-blue);
        padding: 20px 20px 16px;
    }
    .ns-cc-quote {
        font-family: 'Rajdhani', sans-serif;
        font-size: 20px;
        font-weight: 600;
        line-height: 1.35;
        color: var(--cc-text);
        margin: 0 0 10px;
        quotes: none;
    }
    .ns-cc-cite {
        font-family: 'JetBrains Mono', monospace;
        font-size: 11px;
        color: var(--cc-text3);
        font-style: normal;
        letter-spacing: 0.5px;
    }

    /* ---- INLINE EMBED ---- */
    .ns-cc-inline {
        background: var(--cc-bg);
        border: 1px solid var(--cc-border);
        padding: 12px 14px;
    }
    .ns-cc-inline-domain {
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
    .ns-cc-inline-title {
        font-family: 'Rajdhani', sans-serif;
        font-size: 15px;
        font-weight: 600;
        line-height: 1.3;
        color: var(--cc-text);
        margin: 0 0 4px;
    }
    .ns-cc-inline-excerpt {
        font-size: 12px;
        line-height: 1.5;
        color: var(--cc-text2);
        margin: 0 0 8px;
    }
    .ns-cc-inline-meta {
        display: flex;
        gap: 10px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 10px;
        color: var(--cc-text3);
    }

    /* ---- GROUP WRAPPER ---- */
    .ns-cc-group {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .ns-cc-empty {
        font-size: 13px;
        color: var(--cc-text3);
        font-style: italic;
        text-align: center;
        padding: 20px 0;
    }

    /* ---- RESPONSIVE ---- */
    @media (max-width: 480px) {
        .ns-cc-tweet { padding: 12px; }
        .ns-cc-pullquote { padding: 14px; }
        .ns-cc-quote { font-size: 17px; }
        .ns-cc-avatar { width: 30px; height: 30px; font-size: 13px; }
    }
    </style>
    <?php
}
