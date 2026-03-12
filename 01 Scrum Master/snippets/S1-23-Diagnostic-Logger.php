<?php
/**
 * S1.23 — Application Diagnostic Logger (Upgrade)
 * Sprint 1, Gate 3 (tail) | GitLab Issue #73
 *
 * UPGRADES the existing ns_log() from Core Utilities (snippet #9)
 * which stores a 200-entry ring buffer in wp_options. This version
 * writes to a proper DB table with severity levels, structured
 * context, stack traces, and an admin viewer.
 *
 * BACKWARDS COMPATIBLE: Existing calls like ns_log('rss-import', 'msg')
 * still work — the old 2-param signature maps to level=info.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S1.23 Diagnostic Logger"
 * Scope: Admin + Front (global)
 * Priority: 2 (loads early — after vault key at 1, before snippets at 10)
 *
 * IMPORTANT: After activating this snippet, REMOVE the ns_log()
 * function from snippet #9 (Core Utilities) to avoid redeclaration.
 * This snippet replaces it entirely.
 *
 * @package NonSequitur
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ==================================================================
   1. CREATE / UPDATE TABLE
   ================================================================== */

add_action( 'init', 'ns_diag_maybe_create_table' );

function ns_diag_maybe_create_table() {
    $installed_version = get_option( 'ns_diag_db_version', '0' );
    $current_version   = '1.0.0';

    if ( $installed_version === $current_version ) {
        return;
    }

    global $wpdb;
    $table   = $wpdb->prefix . 'ns_diagnostic_log';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        log_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        level VARCHAR(10) NOT NULL DEFAULT 'info',
        source VARCHAR(100) NOT NULL DEFAULT '',
        message TEXT NOT NULL,
        context LONGTEXT,
        stack_trace TEXT,
        PRIMARY KEY (id),
        KEY idx_log_time (log_time),
        KEY idx_level (level),
        KEY idx_source (source)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );

    update_option( 'ns_diag_db_version', $current_version, false );

    // Migrate old ring buffer entries to new table
    $old_log = get_option( 'ns_event_log', array() );
    if ( is_array( $old_log ) && ! empty( $old_log ) ) {
        foreach ( $old_log as $entry ) {
            $wpdb->insert( $table, array(
                'log_time' => isset( $entry['time'] ) ? gmdate( 'Y-m-d H:i:s', strtotime( $entry['time'] ) ) : current_time( 'mysql' ),
                'level'    => 'info',
                'source'   => isset( $entry['category'] ) ? sanitize_key( $entry['category'] ) : 'legacy',
                'message'  => isset( $entry['message'] ) ? sanitize_text_field( $entry['message'] ) : '',
                'context'  => wp_json_encode( array( 'migrated' => true ) ),
            ), array( '%s', '%s', '%s', '%s', '%s' ) );
        }
        delete_option( 'ns_event_log' );
    }
}

/* ==================================================================
   2. LOGGING API — ns_diag_write() (internal writer)
   ================================================================== */

/**
 * Internal: write a diagnostic log entry to the DB table.
 *
 * @param string $level   One of: error, warning, info, debug.
 * @param string $source  Component name.
 * @param string $message Human-readable description.
 * @param array  $context Optional structured data stored as JSON.
 */
function ns_diag_write( $level, $source, $message, $context = array() ) {
    $valid_levels = array( 'error', 'warning', 'info', 'debug' );
    $level = in_array( $level, $valid_levels, true ) ? $level : 'info';

    if ( $level === 'debug' && ! defined( 'NS_DEBUG_LOGGING' ) ) {
        return;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'ns_diagnostic_log';

    $trace = '';
    if ( $level === 'error' ) {
        $bt = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 5 );
        $frames = array();
        foreach ( $bt as $i => $frame ) {
            if ( $i === 0 ) continue;
            $file = isset( $frame['file'] ) ? basename( $frame['file'] ) : 'unknown';
            $line = isset( $frame['line'] ) ? $frame['line'] : '?';
            $func = isset( $frame['function'] ) ? $frame['function'] : 'unknown';
            $frames[] = sprintf( '#%d %s:%s %s()', $i, $file, $line, $func );
        }
        $trace = implode( "\n", $frames );
    }

    $wpdb->insert( $table, array(
        'log_time'    => current_time( 'mysql' ),
        'level'       => sanitize_key( $level ),
        'source'      => sanitize_text_field( substr( $source, 0, 100 ) ),
        'message'     => sanitize_text_field( substr( $message, 0, 1000 ) ),
        'context'     => wp_json_encode( $context ),
        'stack_trace' => sanitize_textarea_field( $trace ),
    ), array( '%s', '%s', '%s', '%s', '%s', '%s' ) );

    if ( $level === 'error' ) {
        error_log( '[NS] [' . $source . '] ' . $message );
    }
}

/* ==================================================================
   3. LOG ROTATION — 30-day auto-purge
   ================================================================== */

add_action( 'init', 'ns_diag_schedule_purge' );

function ns_diag_schedule_purge() {
    if ( ! wp_next_scheduled( 'ns_diag_daily_purge' ) ) {
        wp_schedule_event( time(), 'daily', 'ns_diag_daily_purge' );
    }
}

add_action( 'ns_diag_daily_purge', 'ns_diag_run_purge' );

function ns_diag_run_purge() {
    global $wpdb;
    $table  = $wpdb->prefix . 'ns_diagnostic_log';
    $cutoff = gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) );

    $deleted = $wpdb->query(
        $wpdb->prepare( "DELETE FROM $table WHERE log_time < %s", $cutoff )
    );

    if ( $deleted > 0 ) {
        ns_diag_write( 'info', 'log-rotation', "Purged {$deleted} diagnostic entries older than 30 days" );
    }
}

/* ==================================================================
   4. REST API — Health Endpoint
   ================================================================== */

add_action( 'rest_api_init', 'ns_diag_register_rest_routes' );

function ns_diag_register_rest_routes() {
    register_rest_route( 'ns/v1', '/diagnostics/health', array(
        'methods'             => 'GET',
        'callback'            => 'ns_diag_health_endpoint',
        'permission_callback' => function() {
            return current_user_can( 'manage_options' );
        },
    ) );
}

function ns_diag_health_endpoint() {
    global $wpdb;
    $table = $wpdb->prefix . 'ns_diagnostic_log';

    $since_24h = gmdate( 'Y-m-d H:i:s', strtotime( '-24 hours' ) );

    $error_count   = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE level = 'error' AND log_time >= %s", $since_24h
    ) );
    $warning_count = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE level = 'warning' AND log_time >= %s", $since_24h
    ) );
    $last_error    = $wpdb->get_var(
        "SELECT log_time FROM $table WHERE level = 'error' ORDER BY log_time DESC LIMIT 1"
    );
    $total_entries = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );

    $status = 'healthy';
    if ( $error_count > 10 ) {
        $status = 'degraded';
    } elseif ( $error_count > 0 ) {
        $status = 'warning';
    }

    return rest_ensure_response( array(
        'status'         => $status,
        'errors_24h'     => $error_count,
        'warnings_24h'   => $warning_count,
        'last_error'     => $last_error ? $last_error : 'none',
        'total_entries'  => $total_entries,
        'retention_days' => 30,
        'checked_at'     => current_time( 'c' ),
    ) );
}

/* ==================================================================
   5. ADMIN LOG VIEWER
   ================================================================== */

add_action( 'admin_menu', 'ns_diag_register_admin_page' );

function ns_diag_register_admin_page() {
    add_submenu_page(
        'edit.php?post_type=ns_feed_item',
        'Diagnostic Log',
        'Diagnostic Log',
        'manage_options',
        'ns-diagnostic-log',
        'ns_diag_render_admin_page'
    );
}

function ns_diag_render_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized.' );
    }

    global $wpdb;
    $table = $wpdb->prefix . 'ns_diagnostic_log';

    $filter_level  = isset( $_GET['ns_level'] )  ? sanitize_key( $_GET['ns_level'] )         : '';
    $filter_source = isset( $_GET['ns_source'] ) ? sanitize_text_field( $_GET['ns_source'] ) : '';
    $filter_search = isset( $_GET['ns_search'] ) ? sanitize_text_field( $_GET['ns_search'] ) : '';
    $filter_from   = isset( $_GET['ns_from'] )   ? sanitize_text_field( $_GET['ns_from'] )   : '';
    $filter_to     = isset( $_GET['ns_to'] )     ? sanitize_text_field( $_GET['ns_to'] )     : '';
    $paged         = isset( $_GET['paged'] )     ? max( 1, absint( $_GET['paged'] ) )        : 1;
    $per_page      = 50;
    $offset        = ( $paged - 1 ) * $per_page;

    $where  = 'WHERE 1=1';
    $params = array();

    if ( $filter_level ) {
        $where   .= ' AND level = %s';
        $params[] = $filter_level;
    }
    if ( $filter_source ) {
        $where   .= ' AND source = %s';
        $params[] = $filter_source;
    }
    if ( $filter_search ) {
        $where   .= ' AND message LIKE %s';
        $params[] = '%' . $wpdb->esc_like( $filter_search ) . '%';
    }
    if ( $filter_from ) {
        $where   .= ' AND log_time >= %s';
        $params[] = $filter_from . ' 00:00:00';
    }
    if ( $filter_to ) {
        $where   .= ' AND log_time <= %s';
        $params[] = $filter_to . ' 23:59:59';
    }

    $count_sql = "SELECT COUNT(*) FROM $table $where";
    $total     = $params
        ? (int) $wpdb->get_var( $wpdb->prepare( $count_sql, ...$params ) )
        : (int) $wpdb->get_var( $count_sql );

    $data_sql   = "SELECT * FROM $table $where ORDER BY log_time DESC LIMIT %d OFFSET %d";
    $all_params = array_merge( $params, array( $per_page, $offset ) );
    $rows       = $wpdb->get_results( $wpdb->prepare( $data_sql, ...$all_params ) );

    $sources     = $wpdb->get_col( "SELECT DISTINCT source FROM $table ORDER BY source" );
    $total_pages = (int) ceil( $total / $per_page );

    $level_colors = array(
        'error'   => '#dc3232',
        'warning' => '#dba617',
        'info'    => '#0073aa',
        'debug'   => '#999',
    );

    $since_24h    = gmdate( 'Y-m-d H:i:s', strtotime( '-24 hours' ) );
    $errors_24h   = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE level = 'error' AND log_time >= %s", $since_24h
    ) );
    $warnings_24h = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE level = 'warning' AND log_time >= %s", $since_24h
    ) );

    ?>
    <div class="wrap">
        <h1>Diagnostic Log</h1>
        <p>System-level events for troubleshooting. For admin actions, see <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=ns_feed_item&page=ns-audit-log' ) ); ?>">Audit Log</a>.</p>

        <div style="display:flex;gap:20px;margin:15px 0;padding:12px 16px;background:#f8f8f8;border:1px solid #ddd;border-radius:4px;">
            <div>
                <strong style="color:<?php echo $errors_24h > 0 ? '#dc3232' : '#46b450'; ?>;">
                    <?php echo $errors_24h; ?> errors
                </strong>
                <span style="color:#666;font-size:12px;">(24h)</span>
            </div>
            <div>
                <strong style="color:<?php echo $warnings_24h > 0 ? '#dba617' : '#46b450'; ?>;">
                    <?php echo $warnings_24h; ?> warnings
                </strong>
                <span style="color:#666;font-size:12px;">(24h)</span>
            </div>
            <div>
                <strong><?php echo number_format( $total ); ?></strong>
                <span style="color:#666;font-size:12px;">total entries</span>
            </div>
            <div style="margin-left:auto;font-size:12px;color:#666;">
                Retention: 30 days
            </div>
        </div>

        <form method="get" style="display:flex;gap:8px;align-items:center;margin:10px 0;flex-wrap:wrap;">
            <input type="hidden" name="post_type" value="ns_feed_item">
            <input type="hidden" name="page" value="ns-diagnostic-log">
            <select name="ns_level">
                <option value="">All levels</option>
                <?php foreach ( array( 'error', 'warning', 'info', 'debug' ) as $lvl ) : ?>
                    <option value="<?php echo esc_attr( $lvl ); ?>" <?php selected( $filter_level, $lvl ); ?>>
                        <?php echo esc_html( ucfirst( $lvl ) ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="ns_source">
                <option value="">All sources</option>
                <?php foreach ( $sources as $src ) : ?>
                    <option value="<?php echo esc_attr( $src ); ?>" <?php selected( $filter_source, $src ); ?>>
                        <?php echo esc_html( $src ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="ns_from" value="<?php echo esc_attr( $filter_from ); ?>" title="From date">
            <input type="date" name="ns_to" value="<?php echo esc_attr( $filter_to ); ?>" title="To date">
            <input type="text" name="ns_search" value="<?php echo esc_attr( $filter_search ); ?>"
                   placeholder="Search messages..." style="min-width:200px;">
            <input type="submit" class="button" value="Filter">
            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=ns_feed_item&page=ns-diagnostic-log' ) ); ?>" class="button">Reset</a>
        </form>

        <p>Page <?php echo $paged; ?> of <?php echo max( 1, $total_pages ); ?></p>

        <table class="widefat striped" style="table-layout:fixed;">
            <thead>
                <tr>
                    <th style="width:140px;">Time</th>
                    <th style="width:70px;">Level</th>
                    <th style="width:120px;">Source</th>
                    <th>Message</th>
                    <th style="width:200px;">Context</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $rows ) ) : ?>
                    <tr><td colspan="5"><em>No diagnostic entries found.</em></td></tr>
                <?php else : foreach ( $rows as $row ) :
                    $color = isset( $level_colors[ $row->level ] ) ? $level_colors[ $row->level ] : '#666';
                ?>
                    <tr>
                        <td style="white-space:nowrap;font-size:12px;color:#555;">
                            <?php echo esc_html( $row->log_time ); ?>
                        </td>
                        <td>
                            <span style="display:inline-block;padding:2px 8px;border-radius:3px;font-size:11px;font-weight:600;color:#fff;background:<?php echo esc_attr( $color ); ?>;">
                                <?php echo esc_html( strtoupper( $row->level ) ); ?>
                            </span>
                        </td>
                        <td><code style="font-size:11px;"><?php echo esc_html( $row->source ); ?></code></td>
                        <td style="font-size:13px;">
                            <?php echo esc_html( $row->message ); ?>
                            <?php if ( ! empty( $row->stack_trace ) ) : ?>
                                <details style="margin-top:4px;">
                                    <summary style="font-size:11px;color:#0073aa;cursor:pointer;">Stack trace</summary>
                                    <pre style="font-size:10px;margin:4px 0;padding:6px;background:#f5f5f5;overflow-x:auto;"><?php echo esc_html( $row->stack_trace ); ?></pre>
                                </details>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:11px;max-width:200px;overflow:hidden;">
                            <?php if ( ! empty( $row->context ) && $row->context !== 'null' && $row->context !== '[]' ) : ?>
                                <details>
                                    <summary style="cursor:pointer;color:#0073aa;">View</summary>
                                    <pre style="font-size:10px;margin:4px 0;padding:6px;background:#f5f5f5;white-space:pre-wrap;word-break:break-all;"><?php
                                        $decoded = json_decode( $row->context );
                                        echo esc_html( wp_json_encode( $decoded, JSON_PRETTY_PRINT ) );
                                    ?></pre>
                                </details>
                            <?php else : ?>
                                <span style="color:#ccc;">&mdash;</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>

        <?php if ( $total_pages > 1 ) : ?>
            <div class="tablenav" style="margin-top:10px;">
                <div class="tablenav-pages">
                    <?php if ( $paged > 1 ) : ?>
                        <a href="<?php echo esc_url( add_query_arg( 'paged', $paged - 1 ) ); ?>" class="button">&laquo; Prev</a>
                    <?php endif; ?>
                    <span style="padding:0 10px;">Page <?php echo $paged; ?> of <?php echo $total_pages; ?></span>
                    <?php if ( $paged < $total_pages ) : ?>
                        <a href="<?php echo esc_url( add_query_arg( 'paged', $paged + 1 ) ); ?>" class="button">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div style="margin-top:20px;padding:12px 16px;background:#fff;border:1px solid #ddd;border-radius:4px;">
            <h3 style="margin-top:0;">Usage from any snippet</h3>
            <pre style="background:#f5f5f5;padding:10px;font-size:12px;overflow-x:auto;">// Log an error with context
ns_diag_write( 'error', 'rss-import', 'Feed fetch failed', array( 'url' => $url, 'http_status' => 503 ) );

// Log a warning
ns_diag_write( 'warning', 'claude-scoring', 'API response slow', array( 'elapsed_ms' => 4200 ) );

// Log info
ns_diag_write( 'info', 'cron', 'Morning digest sent', array( 'items' => 20 ) );

// Legacy calls still work (routed through Core Utilities ns_log):
// ns_log( 'rss-import', 'Feed fetched successfully' );</pre>
        </div>
    </div>
    <?php
}
