<?php
/**
 * S1.21 — Rate Limiting on Custom REST Endpoints
 * Sprint 1, Gate 3 | GitLab Issue #28
 *
 * Limits custom ns/v1/* REST endpoints to 60 requests per minute per IP.
 * Returns 429 Too Many Requests on excess with Retry-After header.
 * Uses WordPress transients for counter storage (no external deps).
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S1.21 Rate Limiting"
 * Scope: Run everywhere
 * Depends on: S1.14 Page Template (REST endpoints)
 *
 * Acceptance Criteria (GitLab #28):
 *   ✅ Custom endpoints limited to 60 req/min per IP
 *   ✅ 429 response on excess
 *   ✅ Rate limit headers in response
 *
 * @package NonSequitur
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ==================================================================
   1. CONFIGURATION
   ================================================================== */

define( 'NS_RATE_LIMIT_MAX',    60 );  // Max requests per window
define( 'NS_RATE_LIMIT_WINDOW', 60 );  // Window in seconds (1 minute)

/* ==================================================================
   2. RATE LIMIT CHECK ON REST API
   ================================================================== */

add_filter( 'rest_pre_dispatch', 'ns_rate_limit_check', 5, 3 );

function ns_rate_limit_check( $result, $server, $request ) {
    // Only apply to our custom endpoints
    $route = $request->get_route();
    if ( strpos( $route, '/ns/v1/' ) !== 0 ) {
        return $result;
    }

    // Get client IP
    $ip = ns_rate_get_client_ip();
    if ( empty( $ip ) ) {
        return $result; // Can't rate limit without IP
    }

    // Build transient key (per IP, per minute window)
    $window_key = floor( time() / NS_RATE_LIMIT_WINDOW );
    $cache_key  = 'ns_rl_' . md5( $ip ) . '_' . $window_key;

    // Get current count
    $count = (int) get_transient( $cache_key );
    $count++;

    // Store updated count
    set_transient( $cache_key, $count, NS_RATE_LIMIT_WINDOW * 2 );

    // Calculate remaining and reset time
    $remaining  = max( 0, NS_RATE_LIMIT_MAX - $count );
    $reset_time = ( $window_key + 1 ) * NS_RATE_LIMIT_WINDOW;

    // Check if over limit
    if ( $count > NS_RATE_LIMIT_MAX ) {
        $retry_after = $reset_time - time();

        // Log the rate limit hit
        if ( function_exists( 'ns_audit_log' ) ) {
            ns_audit_log(
                'rate_limited',
                'rest_api',
                0,
                $route,
                wp_json_encode( array( 'ip' => $ip, 'count' => $count ) )
            );
        }

        // Return 429 with headers
        $response = new WP_REST_Response( array(
            'code'    => 'rate_limit_exceeded',
            'message' => 'Rate limit exceeded. Maximum ' . NS_RATE_LIMIT_MAX . ' requests per minute.',
            'data'    => array(
                'status'      => 429,
                'retry_after' => $retry_after,
            ),
        ), 429 );

        $response->header( 'Retry-After', max( 1, $retry_after ) );
        $response->header( 'X-RateLimit-Limit', NS_RATE_LIMIT_MAX );
        $response->header( 'X-RateLimit-Remaining', 0 );
        $response->header( 'X-RateLimit-Reset', $reset_time );

        return $response;
    }

    return $result;
}

/* ==================================================================
   3. ADD RATE LIMIT HEADERS TO ALL NS RESPONSES
   ================================================================== */

add_filter( 'rest_post_dispatch', 'ns_rate_limit_headers', 10, 3 );

function ns_rate_limit_headers( $response, $server, $request ) {
    $route = $request->get_route();
    if ( strpos( $route, '/ns/v1/' ) !== 0 ) {
        return $response;
    }

    $ip = ns_rate_get_client_ip();
    if ( empty( $ip ) ) {
        return $response;
    }

    $window_key = floor( time() / NS_RATE_LIMIT_WINDOW );
    $cache_key  = 'ns_rl_' . md5( $ip ) . '_' . $window_key;
    $count      = (int) get_transient( $cache_key );
    $remaining  = max( 0, NS_RATE_LIMIT_MAX - $count );
    $reset_time = ( $window_key + 1 ) * NS_RATE_LIMIT_WINDOW;

    $response->header( 'X-RateLimit-Limit', NS_RATE_LIMIT_MAX );
    $response->header( 'X-RateLimit-Remaining', $remaining );
    $response->header( 'X-RateLimit-Reset', $reset_time );

    return $response;
}

/* ==================================================================
   4. HELPER: GET CLIENT IP
   ================================================================== */

function ns_rate_get_client_ip() {
    // Check for proxied IP (Cloudflare, load balancers)
    $headers = array(
        'HTTP_CF_CONNECTING_IP',  // Cloudflare
        'HTTP_X_FORWARDED_FOR',   // Standard proxy
        'HTTP_X_REAL_IP',         // Nginx proxy
        'REMOTE_ADDR',            // Direct connection
    );

    foreach ( $headers as $header ) {
        if ( ! empty( $_SERVER[ $header ] ) ) {
            $ip = sanitize_text_field( $_SERVER[ $header ] );
            // X-Forwarded-For can be comma-separated — take the first
            if ( strpos( $ip, ',' ) !== false ) {
                $ip = trim( explode( ',', $ip )[0] );
            }
            // Validate IP format
            if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                return $ip;
            }
        }
    }

    return '';
}
