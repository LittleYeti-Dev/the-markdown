<?php
/**
 * G2-M09 Admin Page — Non Sequitur
 *
 * Sprint 1, Gate 2 Micro-snippet 09
 * Admin: Manual Import Trigger + Claude Scoring + Log Viewer
 *
 * @package NonSequitur
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_menu', 'ns_add_pipeline_admin_page' );

function ns_add_pipeline_admin_page() {
    add_submenu_page(
        'edit.php?post_type=ns_feed_item',
        'RSS Pipeline',
        'RSS Pipeline',
        'manage_options',
        'ns-rss-pipeline',
        'ns_render_pipeline_page'
    );
}

function ns_render_pipeline_page() {
    // Handle manual import trigger
    if ( isset( $_POST['ns_manual_import'] ) && check_admin_referer( 'ns_manual_import_action' ) ) {
        ns_run_rss_import();
        echo '<div class="notice notice-success"><p>RSS import completed. Check log below.</p></div>';
    }

    // Handle manual scoring trigger
    if ( isset( $_POST['ns_manual_score'] ) && check_admin_referer( 'ns_manual_score_action' ) ) {
        ns_run_claude_scoring();
        echo '<div class="notice notice-success"><p>Claude scoring completed. Check log below.</p></div>';
    }

    // Handle API key save
    if ( isset( $_POST['ns_save_api_key'] ) && check_admin_referer( 'ns_save_api_key_action' ) ) {
        $key = sanitize_text_field( $_POST['ns_claude_api_key'] ?? '' );
        if ( ! empty( $key ) ) {
            update_option( 'ns_api_key_claude', $key );
            echo '<div class="notice notice-success"><p>Claude API key saved.</p></div>';
        }
    }

    $log = get_option( 'ns_event_log', array() );
    $feeds = ns_get_feed_config();
    $api_key = ns_get_api_key( 'claude' );
    $next_import = wp_next_scheduled( 'ns_rss_import_event' );
    $next_digest = wp_next_scheduled( 'ns_morning_digest_event' );
    ?>
    <div class="wrap" style="max-width:900px;">
        <h1 style="display:flex;align-items:center;gap:10px;">
            <span class="dashicons dashicons-rss" style="font-size:28px;color:#f0883e;"></span>
            Non Sequitur — RSS Pipeline
        </h1>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:15px;margin:20px 0;">
            <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:15px;">
                <h3 style="margin-top:0;">Feeds</h3>
                <p style="font-size:28px;font-weight:700;margin:0;"><?php echo count( $feeds ); ?></p>
                <p style="color:#646970;">Configured feeds</p>
            </div>
            <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:15px;">
                <h3 style="margin-top:0;">Next Import</h3>
                <p style="font-size:14px;font-weight:500;margin:0;">
                    <?php echo $next_import ? esc_html( gmdate( 'M j, H:i UTC', $next_import ) ) : 'Not scheduled'; ?>
                </p>
                <p style="color:#646970;">Every 4 hours</p>
            </div>
            <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:15px;">
                <h3 style="margin-top:0;">Next Digest</h3>
                <p style="font-size:14px;font-weight:500;margin:0;">
                    <?php echo $next_digest ? esc_html( gmdate( 'M j, H:i UTC', $next_digest ) ) : 'Not scheduled'; ?>
                </p>
                <p style="color:#646970;">Daily 0600 CST</p>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:15px;margin:20px 0;">
            <form method="post">
                <?php wp_nonce_field( 'ns_manual_import_action' ); ?>
                <button type="submit" name="ns_manual_import" class="button button-primary" style="width:100%;">
                    Run Import Now
                </button>
            </form>
            <form method="post">
                <?php wp_nonce_field( 'ns_manual_score_action' ); ?>
                <button type="submit" name="ns_manual_score" class="button button-secondary" style="width:100%;">
                    Run Claude Scoring
                </button>
            </form>
            <form method="post">
                <?php wp_nonce_field( 'ns_save_api_key_action' ); ?>
                <input type="password" name="ns_claude_api_key"
                       value="<?php echo esc_attr( $api_key ); ?>"
                       placeholder="Claude API Key"
                       style="width:calc(100% - 80px);">
                <button type="submit" name="ns_save_api_key" class="button" style="width:70px;">Save</button>
            </form>
        </div>

        <h2>Event Log (last 50)</h2>
        <table class="widefat striped" style="font-size:13px;">
            <thead>
                <tr>
                    <th style="width:160px;">Time</th>
                    <th style="width:120px;">Category</th>
                    <th>Message</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $recent_log = array_reverse( array_slice( $log, -50 ) );
                if ( empty( $recent_log ) ) {
                    echo '<tr><td colspan="3">No log entries yet.</td></tr>';
                }
                foreach ( $recent_log as $entry ) {
                    printf(
                        '<tr><td><code>%s</code></td><td><strong>%s</strong></td><td>%s</td></tr>',
                        esc_html( $entry['time'] ?? '' ),
                        esc_html( $entry['category'] ?? '' ),
                        esc_html( $entry['message'] ?? '' )
                    );
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}
