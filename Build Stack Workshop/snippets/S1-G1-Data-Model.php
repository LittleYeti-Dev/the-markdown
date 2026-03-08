<?php
/**
 * S1-G1 Data Model — The Markdown / Non Sequitur
 *
 * Sprint 1, Gate 1: Data Model Registration
 * Tasks: S1.1 (CPT), S1.2 (Taxonomies), S1.3 (Input Validation)
 *
 * Deploy via: WordPress.com Code Snippets plugin
 * Snippet Title: "S1-G1 Data Model"
 * Scope: Run everywhere
 *
 * @package NonSequitur
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ==========================================================================
   S1.1 — Register ns_feed_item Custom Post Type
   GitLab Issue #8
   ========================================================================== */

add_action( 'init', 'ns_register_feed_item_cpt' );

function ns_register_feed_item_cpt() {
    $labels = array(
        'name'                  => 'Feed Items',
        'singular_name'         => 'Feed Item',
        'menu_name'             => 'Non Sequitur',
        'name_admin_bar'        => 'Feed Item',
        'add_new'               => 'Add New',
        'add_new_item'          => 'Add New Feed Item',
        'new_item'              => 'New Feed Item',
        'edit_item'             => 'Edit Feed Item',
        'view_item'             => 'View Feed Item',
        'all_items'             => 'All Feed Items',
        'search_items'          => 'Search Feed Items',
        'not_found'             => 'No feed items found.',
        'not_found_in_trash'    => 'No feed items found in Trash.',
        'filter_items_list'     => 'Filter feed items',
        'items_list_navigation' => 'Feed items navigation',
        'items_list'            => 'Feed items list',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => true,
        'rest_base'          => 'feed-items',
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'feed-item', 'with_front' => false ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-rss',
        'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
    );

    register_post_type( 'ns_feed_item', $args );
}


/* ==========================================================================
   S1.1 — Register 15 Meta Fields with Sanitization (S1.3 integrated)
   GitLab Issues #8, #10

   Meta fields:
   1.  source_url          — Original article URL
   2.  source_name         — Feed/publication name
   3.  source_author       — Original author name
   4.  domain_tag          — Primary content domain (redundant with taxonomy for fast query)
   5.  relevance_score     — Claude AI score 1-10
   6.  arc_score           — Thematic arc alignment 1-10
   7.  block_assignment    — Which of the 7 page blocks (0 = unassigned)
   8.  publish_date_orig   — Original publication date (ISO 8601)
   9.  import_date         — When RSS Aggregator pulled it (ISO 8601)
   10. social_post_urls    — JSON array of platform post URLs after publishing
   11. claude_summary      — AI-generated summary text
   12. claude_scored_at    — Timestamp of last Claude scoring (ISO 8601)
   13. promote_status      — Editorial workflow: dev|ready|published|archived
   14. promoted_by         — WP user ID who promoted
   15. promoted_at         — Timestamp of promotion (ISO 8601)
   ========================================================================== */

add_action( 'init', 'ns_register_feed_item_meta' );

function ns_register_feed_item_meta() {

    $meta_fields = array(

        // 1. source_url — validated as URL
        'source_url' => array(
            'type'              => 'string',
            'description'       => 'Original article URL',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'esc_url_raw',
        ),

        // 2. source_name — plain text
        'source_name' => array(
            'type'              => 'string',
            'description'       => 'Feed/publication name',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'sanitize_text_field',
        ),

        // 3. source_author — plain text
        'source_author' => array(
            'type'              => 'string',
            'description'       => 'Original author name',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'sanitize_text_field',
        ),

        // 4. domain_tag — restricted to known domains
        'domain_tag' => array(
            'type'              => 'string',
            'description'       => 'Primary content domain slug',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'ns_sanitize_domain_tag',
        ),

        // 5. relevance_score — integer 0-10
        'relevance_score' => array(
            'type'              => 'integer',
            'description'       => 'Claude AI relevance score 1-10',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'ns_sanitize_score',
        ),

        // 6. arc_score — integer 0-10
        'arc_score' => array(
            'type'              => 'integer',
            'description'       => 'Thematic arc alignment score 1-10',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'ns_sanitize_score',
        ),

        // 7. block_assignment — integer 0-7
        'block_assignment' => array(
            'type'              => 'integer',
            'description'       => 'Page block position 0-7 (0 = unassigned)',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'ns_sanitize_block_assignment',
        ),

        // 8. publish_date_orig — ISO 8601 datetime
        'publish_date_orig' => array(
            'type'              => 'string',
            'description'       => 'Original publication date (ISO 8601)',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'ns_sanitize_iso_date',
        ),

        // 9. import_date — ISO 8601 datetime
        'import_date' => array(
            'type'              => 'string',
            'description'       => 'RSS import timestamp (ISO 8601)',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'ns_sanitize_iso_date',
        ),

        // 10. social_post_urls — JSON array
        'social_post_urls' => array(
            'type'              => 'string',
            'description'       => 'JSON array of social media post URLs',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'ns_sanitize_json_urls',
        ),

        // 11. claude_summary — sanitized HTML (limited tags)
        'claude_summary' => array(
            'type'              => 'string',
            'description'       => 'AI-generated summary text',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'ns_sanitize_summary',
        ),

        // 12. claude_scored_at — ISO 8601 datetime
        'claude_scored_at' => array(
            'type'              => 'string',
            'description'       => 'Timestamp of last Claude scoring (ISO 8601)',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'ns_sanitize_iso_date',
        ),

        // 13. promote_status — restricted enum
        'promote_status' => array(
            'type'              => 'string',
            'description'       => 'Editorial workflow status: dev|ready|published|archived',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'ns_sanitize_promote_status',
            'default'           => 'dev',
        ),

        // 14. promoted_by — WP user ID
        'promoted_by' => array(
            'type'              => 'integer',
            'description'       => 'WP user ID who promoted this item',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'absint',
        ),

        // 15. promoted_at — ISO 8601 datetime
        'promoted_at' => array(
            'type'              => 'string',
            'description'       => 'Timestamp of promotion (ISO 8601)',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'ns_sanitize_iso_date',
        ),
    );

    foreach ( $meta_fields as $key => $args ) {
        register_post_meta( 'ns_feed_item', $key, $args );
    }
}


/* ==========================================================================
   S1.3 — Custom Sanitization Callbacks
   GitLab Issue #10 — Input Validation
   ========================================================================== */

/**
 * Sanitize domain_tag to known slugs only
 */
function ns_sanitize_domain_tag( $value ) {
    $allowed = array( 'ai', 'cyber', 'innovation', 'fnw', 'space', 'digital' );
    $value = sanitize_key( $value );
    return in_array( $value, $allowed, true ) ? $value : '';
}

/**
 * Sanitize score to integer 0-10
 */
function ns_sanitize_score( $value ) {
    $value = absint( $value );
    return min( $value, 10 );
}

/**
 * Sanitize block assignment to integer 0-7
 */
function ns_sanitize_block_assignment( $value ) {
    $value = absint( $value );
    return min( $value, 7 );
}

/**
 * Sanitize ISO 8601 date string
 */
function ns_sanitize_iso_date( $value ) {
    $value = sanitize_text_field( $value );
    // Validate ISO 8601 format
    if ( empty( $value ) ) {
        return '';
    }
    $timestamp = strtotime( $value );
    if ( false === $timestamp ) {
        return '';
    }
    return gmdate( 'c', $timestamp );
}

/**
 * Sanitize JSON array of URLs
 */
function ns_sanitize_json_urls( $value ) {
    if ( empty( $value ) ) {
        return '[]';
    }
    $decoded = json_decode( $value, true );
    if ( ! is_array( $decoded ) ) {
        return '[]';
    }
    $clean = array();
    foreach ( $decoded as $url ) {
        $sanitized = esc_url_raw( $url );
        if ( ! empty( $sanitized ) ) {
            $clean[] = $sanitized;
        }
    }
    return wp_json_encode( $clean );
}

/**
 * Sanitize AI summary — allow only safe HTML
 */
function ns_sanitize_summary( $value ) {
    return wp_kses( $value, array(
        'p'      => array(),
        'br'     => array(),
        'strong' => array(),
        'em'     => array(),
        'a'      => array( 'href' => array(), 'title' => array(), 'rel' => array() ),
        'ul'     => array(),
        'ol'     => array(),
        'li'     => array(),
    ) );
}

/**
 * Sanitize promote_status to allowed enum values
 */
function ns_sanitize_promote_status( $value ) {
    $allowed = array( 'dev', 'ready', 'published', 'archived' );
    $value = sanitize_key( $value );
    return in_array( $value, $allowed, true ) ? $value : 'dev';
}


/* ==========================================================================
   S1.2 — Register 4 Custom Taxonomies
   GitLab Issue #9
   ========================================================================== */

add_action( 'init', 'ns_register_taxonomies' );

function ns_register_taxonomies() {

    // 1. content_domain — 6 knowledge domains
    register_taxonomy( 'content_domain', 'ns_feed_item', array(
        'labels' => array(
            'name'          => 'Content Domains',
            'singular_name' => 'Content Domain',
            'search_items'  => 'Search Domains',
            'all_items'     => 'All Domains',
            'edit_item'     => 'Edit Domain',
            'update_item'   => 'Update Domain',
            'add_new_item'  => 'Add New Domain',
            'new_item_name' => 'New Domain Name',
            'menu_name'     => 'Domains',
        ),
        'hierarchical'      => true,
        'public'            => true,
        'show_in_rest'      => true,
        'rest_base'         => 'content-domains',
        'show_admin_column' => true,
        'rewrite'           => array( 'slug' => 'domain' ),
    ) );

    // 2. thematic_arc — 3 storytelling arcs
    register_taxonomy( 'thematic_arc', 'ns_feed_item', array(
        'labels' => array(
            'name'          => 'Thematic Arcs',
            'singular_name' => 'Thematic Arc',
            'search_items'  => 'Search Arcs',
            'all_items'     => 'All Arcs',
            'edit_item'     => 'Edit Arc',
            'update_item'   => 'Update Arc',
            'add_new_item'  => 'Add New Arc',
            'new_item_name' => 'New Arc Name',
            'menu_name'     => 'Arcs',
        ),
        'hierarchical'      => true,
        'public'            => true,
        'show_in_rest'      => true,
        'rest_base'         => 'thematic-arcs',
        'show_admin_column' => true,
        'rewrite'           => array( 'slug' => 'arc' ),
    ) );

    // 3. item_status — 7 workflow statuses
    register_taxonomy( 'item_status', 'ns_feed_item', array(
        'labels' => array(
            'name'          => 'Item Statuses',
            'singular_name' => 'Item Status',
            'search_items'  => 'Search Statuses',
            'all_items'     => 'All Statuses',
            'edit_item'     => 'Edit Status',
            'update_item'   => 'Update Status',
            'add_new_item'  => 'Add New Status',
            'new_item_name' => 'New Status Name',
            'menu_name'     => 'Status',
        ),
        'hierarchical'      => true,
        'public'            => false,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'rest_base'         => 'item-statuses',
        'show_admin_column' => true,
    ) );

    // 4. platform_published — 6 target platforms
    register_taxonomy( 'platform_published', 'ns_feed_item', array(
        'labels' => array(
            'name'          => 'Platforms Published',
            'singular_name' => 'Platform',
            'search_items'  => 'Search Platforms',
            'all_items'     => 'All Platforms',
            'edit_item'     => 'Edit Platform',
            'update_item'   => 'Update Platform',
            'add_new_item'  => 'Add New Platform',
            'new_item_name' => 'New Platform Name',
            'menu_name'     => 'Platforms',
        ),
        'hierarchical'      => false,
        'public'            => false,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'rest_base'         => 'platforms-published',
        'show_admin_column' => true,
    ) );
}


/* ==========================================================================
   S1.2 — Insert Default Taxonomy Terms (runs once)
   ========================================================================== */

add_action( 'init', 'ns_insert_default_terms', 20 );

function ns_insert_default_terms() {
    // Only run once — check option flag
    if ( get_option( 'ns_default_terms_inserted' ) ) {
        return;
    }

    // content_domain — 6 domains
    $domains = array(
        'AI & Machine Learning',
        'Cybersecurity',
        'Innovation & Strategy',
        'Future of National Security & Warfare',
        'Space & Aerospace',
        'Digital Transformation',
    );
    foreach ( $domains as $term ) {
        if ( ! term_exists( $term, 'content_domain' ) ) {
            wp_insert_term( $term, 'content_domain' );
        }
    }

    // thematic_arc — 3 arcs
    $arcs = array(
        'Convergence',
        'Disruption',
        'Human Element',
    );
    foreach ( $arcs as $term ) {
        if ( ! term_exists( $term, 'thematic_arc' ) ) {
            wp_insert_term( $term, 'thematic_arc' );
        }
    }

    // item_status — 7 workflow statuses
    $statuses = array(
        'New',
        'Scored',
        'Promoted',
        'Block Assigned',
        'Published',
        'Archived',
        'Rejected',
    );
    foreach ( $statuses as $term ) {
        if ( ! term_exists( $term, 'item_status' ) ) {
            wp_insert_term( $term, 'item_status' );
        }
    }

    // platform_published — 6 platforms
    $platforms = array(
        'WordPress',
        'X (Twitter)',
        'LinkedIn',
        'Instagram',
        'YouTube',
        'Medium',
    );
    foreach ( $platforms as $term ) {
        if ( ! term_exists( $term, 'platform_published' ) ) {
            wp_insert_term( $term, 'platform_published' );
        }
    }

    update_option( 'ns_default_terms_inserted', true );
}


/* ==========================================================================
   S1.1 — Admin Columns for Feed Items List Table
   ========================================================================== */

add_filter( 'manage_ns_feed_item_posts_columns', 'ns_feed_item_columns' );

function ns_feed_item_columns( $columns ) {
    $new_columns = array();
    $new_columns['cb']              = $columns['cb'];
    $new_columns['title']           = $columns['title'];
    $new_columns['source_name']     = 'Source';
    $new_columns['domain_tag']      = 'Domain';
    $new_columns['relevance_score'] = 'Score';
    $new_columns['block_assignment'] = 'Block';
    $new_columns['promote_status']  = 'Status';
    $new_columns['date']            = $columns['date'];
    return $new_columns;
}

add_action( 'manage_ns_feed_item_posts_custom_column', 'ns_feed_item_column_content', 10, 2 );

function ns_feed_item_column_content( $column, $post_id ) {
    switch ( $column ) {
        case 'source_name':
            // S1.3: escape on output
            echo esc_html( get_post_meta( $post_id, 'source_name', true ) );
            break;

        case 'domain_tag':
            echo esc_html( get_post_meta( $post_id, 'domain_tag', true ) );
            break;

        case 'relevance_score':
            $score = (int) get_post_meta( $post_id, 'relevance_score', true );
            $color = $score >= 7 ? '#3fb950' : ( $score >= 4 ? '#d29922' : '#8b949e' );
            printf( '<strong style="color:%s">%d</strong>/10', esc_attr( $color ), $score );
            break;

        case 'block_assignment':
            $block = (int) get_post_meta( $post_id, 'block_assignment', true );
            echo $block > 0 ? esc_html( 'B' . $block ) : '<span style="color:#8b949e">—</span>';
            break;

        case 'promote_status':
            $status = get_post_meta( $post_id, 'promote_status', true );
            $badges = array(
                'dev'       => 'background:#21262d;color:#8b949e',
                'ready'     => 'background:rgba(210,153,34,0.2);color:#d29922',
                'published' => 'background:rgba(63,185,80,0.2);color:#3fb950',
                'archived'  => 'background:rgba(139,148,158,0.1);color:#8b949e',
            );
            $style = isset( $badges[ $status ] ) ? $badges[ $status ] : $badges['dev'];
            printf(
                '<span style="padding:2px 8px;border-radius:12px;font-size:11px;font-weight:600;%s">%s</span>',
                esc_attr( $style ),
                esc_html( strtoupper( $status ?: 'DEV' ) )
            );
            break;
    }
}


/* ==========================================================================
   S1.1 — Make Admin Columns Sortable
   ========================================================================== */

add_filter( 'manage_edit-ns_feed_item_sortable_columns', 'ns_feed_item_sortable_columns' );

function ns_feed_item_sortable_columns( $columns ) {
    $columns['relevance_score']  = 'relevance_score';
    $columns['block_assignment'] = 'block_assignment';
    $columns['promote_status']   = 'promote_status';
    $columns['source_name']      = 'source_name';
    return $columns;
}

add_action( 'pre_get_posts', 'ns_feed_item_orderby' );

function ns_feed_item_orderby( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() ) {
        return;
    }
    if ( 'ns_feed_item' !== $query->get( 'post_type' ) ) {
        return;
    }

    $orderby = $query->get( 'orderby' );
    $meta_sorts = array( 'relevance_score', 'block_assignment', 'promote_status', 'source_name' );

    if ( in_array( $orderby, $meta_sorts, true ) ) {
        $query->set( 'meta_key', $orderby );
        if ( in_array( $orderby, array( 'relevance_score', 'block_assignment' ), true ) ) {
            $query->set( 'orderby', 'meta_value_num' );
        } else {
            $query->set( 'orderby', 'meta_value' );
        }
    }
}


/* ==========================================================================
   S1.3 — Output Escaping Helper (used throughout front-end templates)
   ========================================================================== */

/**
 * Safely get and escape a feed item meta field for display
 *
 * @param int    $post_id  Post ID
 * @param string $key      Meta key
 * @param string $context  'text', 'url', 'html', 'attr', 'int'
 * @return string|int Escaped value
 */
function ns_get_meta_escaped( $post_id, $key, $context = 'text' ) {
    $value = get_post_meta( $post_id, $key, true );

    switch ( $context ) {
        case 'url':
            return esc_url( $value );
        case 'html':
            return wp_kses_post( $value );
        case 'attr':
            return esc_attr( $value );
        case 'int':
            return absint( $value );
        case 'text':
        default:
            return esc_html( $value );
    }
}


/* ==========================================================================
   Version & Diagnostics
   ========================================================================== */

if ( ! defined( 'NS_DATA_MODEL_VERSION' ) ) {
    define( 'NS_DATA_MODEL_VERSION', '1.0.0' );
}
