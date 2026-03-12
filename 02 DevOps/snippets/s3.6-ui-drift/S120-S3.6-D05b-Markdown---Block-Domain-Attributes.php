<?php
/**
 * Snippet ID: 120
 * Name: S3.6-D05b Markdown — Block Domain Attributes
 * Scope: front-end
 * Active: True
 * Sprint: 3.6 — UI Drift Fixes
 * Deployed: 2026-03-12
 */

/**
 * S3.6-D05b — Inject data-domain attributes on editorial blocks
 * Reads .md-block-domain text and maps to data-domain values
 * so the per-category CSS in M09b can match and color each block.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_footer', function() {
    if ( ! is_page( 1077 ) ) return;
    ?>
    <script id="md-domain-attrs">
    (function(){
        var map = {
            'ai':'ai','gen ai':'ai','ai / gen ai':'ai',
            'cyber':'cyber','cybersecurity':'cyber',
            'innovation':'innovation',
            'fnw':'fnw','finance':'fnw',
            'space':'space','space / aero':'space','aerospace':'space',
            'digital life':'digital','digital':'digital'
        };
        document.querySelectorAll('.md-block').forEach(function(b){
            var d = b.querySelector('.md-block-domain');
            if(!d) return;
            var t = d.textContent.trim().toLowerCase();
            var key = map[t];
            if(key) b.setAttribute('data-domain', key);
        });
    })();
    </script>
    <?php
}, 99 );