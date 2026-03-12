<?php
/**
 * Snippet ID: 119
 * Name: S3.6-D07 Markdown — Hide WP Widgets
 * Scope: front-end
 * Active: True
 * Sprint: 3.6 — UI Drift Fixes
 * Deployed: 2026-03-12
 */

/**
 * S3.6-D07 — Hide WordPress Share/Like Widgets on The Markdown
 * Hides Jetpack sharing buttons and like widgets that break the editorial layout.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_head', function() {
    if ( ! is_page( 1077 ) ) return;
    ?>
    <style id="md-hide-wp-widgets">
    body.page-id-1077 .sharedaddy,
    body.page-id-1077 .sd-sharing-enabled,
    body.page-id-1077 .sd-like-enabled,
    body.page-id-1077 .jetpack-likes-widget-wrapper,
    body.page-id-1077 .sd-block,
    body.page-id-1077 .jp-relatedposts,
    body.page-id-1077 .wpl-likebox,
    body.page-id-1077 #jp-post-flair,
    body.page-id-1077 .post-likes-widget-placeholder{
        display:none!important
    }
    </style>
    <?php
}, 5 );