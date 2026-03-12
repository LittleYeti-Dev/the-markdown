<?php
/**
 * G2-M01 Core + Utilities — Non Sequitur
 *
 * Sprint 1, Gate 2 Micro-snippet 01
 * Version constant, API key retrieval, event logging
 *
 * @package NonSequitur
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'NS_RSS_PIPELINE_VERSION' ) ) {
    define( 'NS_RSS_PIPELINE_VERSION', '1.1.0' );
}


/* ==========================================================================
   Utility: API Key Retrieval
   Placeholder until S1.17 Token Vault is built
   ========================================================================== */

/**
 * Get an API key from the token vault (or wp_options fallback)
 *
 * @param string $service Service name (e.g., 'claude')
 * @return string API key or empty string
 */
function ns_get_api_key( $service ) {
    $key = get_option( 'ns_api_key_' . sanitize_key( $service ), '' );
    return sanitize_text_field( $key );
}


/* ==========================================================================
   Utility: Simple Logging
   ========================================================================== */

/**
 * Log Non Sequitur events to wp_options (ring buffer, last 200 entries)
 *
 * @param string $category Log category
 * @param string $message  Log message
 */
function ns_log( $category, $message ) {
    $log = get_option( 'ns_event_log', array() );
    if ( ! is_array( $log ) ) {
        $log = array();
    }

    $log[] = array(
        'time'     => gmdate( 'c' ),
        'category' => sanitize_key( $category ),
        'message'  => sanitize_text_field( substr( $message, 0, 500 ) ),
    );

    // Ring buffer: keep last 200 entries
    if ( count( $log ) > 200 ) {
        $log = array_slice( $log, -200 );
    }

    update_option( 'ns_event_log', $log, false );
}
