<?php
// Snippet ID: 57
// Name: S2-G4-S05 Social API Error Handler
// Scope: global
// Active: True
// Modified: 2026-03-09 22:42:13
// Lines: 160

/**
 * S2-G4-S05 — Social API Error Handler
 * Sprint 2, Gate 4 | GitLab Issue #37
 *
 * Centralized error handling for all social platform API calls.
 * Token scrubbing, graceful degradation, structured error codes.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S2-G4-S05 Social API Error Handler"
 * Scope: global
 * Priority: 10
 * Depends on: Diagnostic Logger (S1.23), Audit Log (S1.22)
 *
 * @package NonSequitur
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Centralized social API error handler.
 * Scrubs tokens from error output, logs structured errors,
 * and returns a safe WP_Error.
 *
 * @param string          $platform Platform name (twitter, linkedin, youtube, medium).
 * @param array|WP_Error  $response API response or WP_Error.
 * @param array           $context  Additional context (action, endpoint, etc.).
 * @return WP_Error Sanitized error object.
 */
function ns_social_api_error( $platform, $response, $context = array() ) {
    $platform = sanitize_key( $platform );

    // Extract error details
    if ( is_wp_error( $response ) ) {
        $error_msg  = $response->get_error_message();
        $error_code = $response->get_error_code();
        $http_code  = 0;
    } else {
        $http_code  = wp_remote_retrieve_response_code( $response );
        $body       = wp_remote_retrieve_body( $response );
        $error_msg  = ns_social_extract_api_error( $platform, $body, $http_code );
        $error_code = 'ns_' . $platform . '_api_error';
    }

    // Scrub any tokens from error message
    $error_msg = ns_social_scrub_tokens( $error_msg );

    // Map to structured error code
    $structured = ns_social_map_error_code( $platform, $http_code, $error_code );

    // Log the error
    if ( function_exists( 'ns_diag_write' ) ) {
        ns_diag_write( 'error', $platform, $structured['message'], array(
            'http_code' => $http_code,
            'action'    => $context['action'] ?? 'unknown',
            'retryable' => $structured['retryable'],
        ) );
    }

    if ( function_exists( 'ns_audit_log' ) ) {
        ns_audit_log( $platform . '_api_error', 'error', $platform, $structured['message'], array(
            'code' => $structured['code'],
        ) );
    }

    return new WP_Error( $structured['code'], $structured['message'], array(
        'status'    => $http_code ?: 500,
        'platform'  => $platform,
        'retryable' => $structured['retryable'],
    ) );
}

/**
 * Extract a human-readable error from platform-specific API response.
 */
function ns_social_extract_api_error( $platform, $body, $http_code ) {
    $data = json_decode( $body, true );

    if ( $platform === 'twitter' ) {
        return $data['detail'] ?? $data['title'] ?? 'Twitter API error (HTTP ' . $http_code . ')';
    }
    if ( $platform === 'linkedin' ) {
        return $data['message'] ?? 'LinkedIn API error (HTTP ' . $http_code . ')';
    }
    if ( $platform === 'youtube' ) {
        return $data['error']['message'] ?? 'YouTube API error (HTTP ' . $http_code . ')';
    }
    if ( $platform === 'medium' ) {
        return $data['errors'][0]['message'] ?? 'Medium API error (HTTP ' . $http_code . ')';
    }

    return 'API error (HTTP ' . $http_code . ')';
}

/**
 * Scrub tokens and secrets from any string.
 * Catches common token patterns and redacts them.
 */
function ns_social_scrub_tokens( $text ) {
    if ( ! is_string( $text ) ) return '';

    // Scrub Bearer tokens
    $text = preg_replace( '/Bearer\s+[A-Za-z0-9\-._~+\/]+=*/i', 'Bearer [REDACTED]', $text );
    // Scrub OAuth tokens (long alphanumeric strings > 20 chars)
    $text = preg_replace( '/[A-Za-z0-9\-._]{30,}/', '[REDACTED]', $text );
    // Scrub anything that looks like a key=value with a long value
    $text = preg_replace( '/(token|secret|key|password|credential)[\s=:]+\S{10,}/i', '$1=[REDACTED]', $text );

    return $text;
}

/**
 * Map HTTP codes and error types to structured error codes.
 */
function ns_social_map_error_code( $platform, $http_code, $error_code ) {
    $prefix = 'ns_' . $platform . '_';

    if ( $http_code === 401 || $http_code === 403 ) {
        return array( 'code' => $prefix . 'auth_error', 'message' => ucfirst( $platform ) . ': Authentication failed. Re-connect the account.', 'retryable' => false );
    }
    if ( $http_code === 429 ) {
        return array( 'code' => $prefix . 'rate_limit', 'message' => ucfirst( $platform ) . ': Rate limit hit. Try again later.', 'retryable' => true );
    }
    if ( $http_code >= 500 ) {
        return array( 'code' => $prefix . 'server_error', 'message' => ucfirst( $platform ) . ': Platform server error. Try again later.', 'retryable' => true );
    }
    if ( $http_code === 400 || $http_code === 422 ) {
        return array( 'code' => $prefix . 'bad_request', 'message' => ucfirst( $platform ) . ': Invalid request.', 'retryable' => false );
    }

    return array( 'code' => $prefix . 'unknown_error', 'message' => ucfirst( $platform ) . ': Unexpected error.', 'retryable' => false );
}

/**
 * Post to multiple platforms with graceful degradation.
 * Failure on one platform does not block others.
 *
 * @param array $platforms Array of platform => callback pairs.
 * @return array Results per platform (data or WP_Error).
 */
function ns_social_multi_publish( $platforms ) {
    $results = array();

    foreach ( $platforms as $platform => $callback ) {
        if ( ! is_callable( $callback ) ) {
            $results[ $platform ] = new WP_Error( 'ns_' . $platform . '_not_callable', 'Publish function not available.' );
            continue;
        }

        $result = call_user_func( $callback );
        $results[ $platform ] = $result;

        if ( is_wp_error( $result ) && function_exists( 'ns_diag_write' ) ) {
            ns_diag_write( 'warning', 'multi_publish', $platform . ' failed, continuing others', array() );
        }
    }

    return $results;
}
