<?php
// Snippet ID: 56
// Name: S2-G4-S04 Token Refresh Alerter
// Scope: global
// Active: True
// Modified: 2026-03-09 22:42:12
// Lines: 47

/**
 * S2-G4-S04 — Token Refresh Alerter
 * Sprint 2, Gate 4 | GitLab Issue #36
 *
 * Email admin on token refresh failure.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S2-G4-S04 Token Refresh Alerter"
 * Scope: global
 * Priority: 10
 * Depends on: S2-G4-S03 Token Refresh Cron
 *
 * @package NonSequitur
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Send email alert to admin about token refresh failures.
 *
 * @param array $failures List of failure messages.
 */
function ns_token_refresh_alert( $failures ) {
    if ( empty( $failures ) ) return;

    $admin_email = get_option( 'admin_email' );
    $site_name   = get_bloginfo( 'name' );

    $subject = '[' . $site_name . '] Social Token Refresh Failed';

    $body  = "The following social media token refreshes failed:\n\n";
    foreach ( $failures as $failure ) {
        $body .= "  - " . $failure . "\n";
    }
    $body .= "\nPlease reconnect the affected platforms in the WordPress admin.\n";
    $body .= admin_url( 'edit.php?post_type=ns_feed_item' ) . "\n";

    wp_mail( $admin_email, $subject, $body );

    if ( function_exists( 'ns_diag_write' ) ) {
        ns_diag_write( 'info', 'token_refresh', 'Alert email sent', array(
            'failures' => count( $failures ),
        ) );
    }
}
