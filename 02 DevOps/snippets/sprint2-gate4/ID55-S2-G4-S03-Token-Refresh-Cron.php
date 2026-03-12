<?php
// Snippet ID: 55
// Name: S2-G4-S03 Token Refresh Cron
// Scope: global
// Active: True
// Modified: 2026-03-09 22:42:11
// Lines: 108

/**
 * S2-G4-S03 — Token Refresh Cron
 * Sprint 2, Gate 4 | GitLab Issue #36
 *
 * Daily WP-Cron job to check all OAuth tokens for expiration
 * and auto-refresh tokens that support refresh_token flow.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S2-G4-S03 Token Refresh Cron"
 * Scope: global
 * Priority: 10
 * Depends on: Token Vault (S1.17), S2-G4-M02, M08, M12 Token Managers
 *
 * @package NonSequitur
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Register custom cron schedule (twice daily)
add_filter( 'cron_schedules', 'ns_token_refresh_schedule' );
function ns_token_refresh_schedule( $schedules ) {
    $schedules['ns_twice_daily'] = array( 'interval' => 43200, 'display' => 'Twice Daily (NS)' );
    return $schedules;
}

// Schedule the cron event
add_action( 'init', 'ns_token_refresh_schedule_event' );
function ns_token_refresh_schedule_event() {
    if ( ! wp_next_scheduled( 'ns_token_refresh_check' ) ) {
        wp_schedule_event( time(), 'ns_twice_daily', 'ns_token_refresh_check' );
    }
}

// The cron callback
add_action( 'ns_token_refresh_check', 'ns_token_refresh_run' );

function ns_token_refresh_run() {
    $platforms = array(
        'twitter'  => array(
            'expires_option' => 'ns_twitter_token_expires',
            'refresh_func'   => 'ns_twitter_refresh_token',
            'buffer'         => 3600,  // refresh 1h before expiry
        ),
        'linkedin' => array(
            'expires_option' => 'ns_linkedin_token_expires',
            'refresh_func'   => 'ns_linkedin_refresh_token',
            'buffer'         => 86400, // refresh 1 day before expiry
        ),
        'youtube'  => array(
            'expires_option' => 'ns_youtube_token_expires',
            'refresh_func'   => 'ns_youtube_refresh_token',
            'buffer'         => 3600,  // refresh 1h before expiry
        ),
    );

    $failures = array();

    foreach ( $platforms as $name => $config ) {
        $expires = get_option( $config['expires_option'], 0 );

        // Skip if no expiry set (not connected)
        if ( empty( $expires ) ) continue;

        // Check if token is near expiry
        if ( $expires > ( time() + $config['buffer'] ) ) {
            if ( function_exists( 'ns_diag_write' ) ) {
                ns_diag_write( 'debug', 'token_refresh', $name . ' token still valid', array(
                    'expires_in' => $expires - time(),
                ) );
            }
            continue;
        }

        // Attempt refresh
        if ( ! function_exists( $config['refresh_func'] ) ) {
            $failures[] = $name . ': refresh function not available';
            continue;
        }

        $result = call_user_func( $config['refresh_func'] );

        if ( is_wp_error( $result ) ) {
            $failures[] = $name . ': ' . $result->get_error_message();
            if ( function_exists( 'ns_diag_write' ) ) {
                ns_diag_write( 'error', 'token_refresh', $name . ' refresh failed', array(
                    'error' => $result->get_error_message(),
                ) );
            }
            if ( function_exists( 'ns_audit_log' ) ) {
                ns_audit_log( 'token_refresh_failed', 'security', $name, 'Auto-refresh failed', array() );
            }
        } else {
            if ( function_exists( 'ns_diag_write' ) ) {
                ns_diag_write( 'info', 'token_refresh', $name . ' token refreshed', array() );
            }
            if ( function_exists( 'ns_audit_log' ) ) {
                ns_audit_log( 'token_refreshed', 'security', $name, 'Token auto-refreshed', array() );
            }
        }
    }

    // Send failure alerts
    if ( ! empty( $failures ) && function_exists( 'ns_token_refresh_alert' ) ) {
        ns_token_refresh_alert( $failures );
    }
}
