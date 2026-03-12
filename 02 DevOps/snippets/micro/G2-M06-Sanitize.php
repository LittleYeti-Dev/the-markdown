<?php
/**
 * G2-M06 Sanitize + Content Filter Stubs — Non Sequitur
 *
 * Sprint 1, Gate 2 Micro-snippet 06
 * S1.8 — RSS Feed Sanitization (lightweight stub)
 * S1.11 — Content Filtering (lightweight stubs)
 * GitLab Issues #15, #18
 *
 * Full regex-based defense will be deployed in a follow-up snippet
 * once WAF-safe encoding is resolved.
 *
 * @package NonSequitur
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/* =========================================================================
   S1.8 — RSS Feed Sanitization (lightweight stub)
   GitLab Issue #15

   Full regex-based defense deployed in follow-up snippet.
   ========================================================================= */

function ns_sanitize_rss_content( $content ) {
    $allowed_tags = array(
        'p'      => array(),
        'br'     => array(),
        'strong'  => array(),
        'b'      => array(),
        'em'     => array(),
        'i'      => array(),
        'a'      => array( 'href' => array(), 'title' => array(), 'rel' => array() ),
        'ul'     => array(),
        'ol'     => array(),
        'li'     => array(),
        'blockquote' => array( 'cite' => array() ),
        'h2'     => array(),
        'h3'     => array(),
        'h4'     => array(),
        'img'    => array( 'src' => array(), 'alt' => array(), 'width' => array(), 'height' => array() ),
    );

    $content = wp_kses( $content, $allowed_tags );
    return $content;
}


/* =========================================================================
   S1.11 — Content Filtering (lightweight stubs)
   GitLab Issue #18

   Full pattern-based defense deployed in follow-up snippet.
   ========================================================================= */

function ns_sanitize_llm_input( $text ) {
    $text = wp_strip_all_tags( $text );
    if ( strlen( $text ) > 4000 ) {
        $text = substr( $text, 0, 4000 );
    }
    $text = str_replace( array( '<', '>' ), array( '&lt;', '&gt;' ), $text );
    return sanitize_text_field( $text );
}

function ns_detect_response_injection( $response ) {
    return strlen( $response ) > 5120;
}
