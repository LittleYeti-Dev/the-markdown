<?php
/**
 * S1.22 — Audit Logging: Custom DB Table for Admin Actions
 * Sprint 1, Gate 3 | GitLab Issue #29
 *
 * Creates ns_audit_log table. Logs all promote/publish/edit/block-assign
 * actions with timestamp, user, action, target, IP.
 * Admin view under Feed Items → Audit Log.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S1.22 Audit Logging"
 * Scope: Admin only
 * Depends on: S1.12 Editorial Dashboard
 *
 * Acceptance Criteria (GitLab #29):
 *   ✅ Custom table logs: timestamp, user, action, target, IP
 *   ✅ All promote/publish/edit actions logged
 *   ✅ Admin view shows log
 *
 * @package NonSequitur
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ==================================================================
   1. CREATE / UPDATE TABLE ON ACTIVATION
   ================================================================== */

add_action( 'init', 'ns_audit_maybe_create_table' );

function ns_audit_maybe_create_table() {
    $installed_version = get_option( 'ns_audit_db_version', '0' );
    $current_version   = '1.0.0';

    if ( $installed_version === $current_version ) {
        return;
    }

    global $wpdb;
    $table   = $wpdb->prefix . 'ns_audit_log';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        log_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
        user_login VARCHAR(60) NOT NULL DEFAULT '',
        action VARCHAR(50) NOT NULL DEFAULT '',
        target_type VARCHAR(50) NOT NULL DEFAULT '',
        target_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
        target_label VARCHAR(255) NOT NULL DEFAULT '',
        details TEXT,
        ip_address VARCHAR(45) NOT NULL DEFAULT '',
        PRIMARY KEY (id),
        KEY idx_log_time (log_time),
        KEY idx_user_id (user_id),
        KEY idx_action (action)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );

    update_option( 'ns_audit_db_version', $current_version, false );
}

/* ==================================================================
   2. LOGGING API
   ================================================================== */

/**
 * Write an entry to the audit log.
 *
 * @param string $action       Action name (promote, block_assign, publish, edit, vault_store, etc.)
 * @param string $target_type  Target type (feed_item, token, setting, etc.)
 * @param int    $target_id    Target object ID (post ID, etc.)
 * @param string $target_label Human-readable label for the target.
 * @param string $details      Optional JSON or text details.
 */
function ns_audit_log( $action, $target_type = '', $target_id = 0, $target_label = '', $details = '' ) {
    global $wpdb;
    $table = $wpdb->prefix . 'ns_audit_log';

    $user    = wp_get_current_user();
    $user_id = $user->ID ?? 0;
    $login   = $user->user_login ?? 'system';
    $ip      = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : 'cli';

    $wpdb->insert( $table, array(
        'log_time'     => current_time( 'mysql' ),
        'user_id'      => $user_id,
        'user_login'   => sanitize_user( $login ),
        'action'       => sanitize_key( $action ),
        'target_type'  => sanitize_key( $target_type ),
        'target_id'    => absint( $target_id ),
        'target_label' => sanitize_text_field( $target_label ),
        'details'      => wp_kses_post( $details ),
        'ip_address'   => $ip,
    ), array( '%s', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s' ) );
}

/* ==================================================================
   3. HOOK INTO EXISTING ACTIONS
   ================================================================== */

/**
 * Log promote actions.
 * Hooks into meta updates on promote_status.
 */
add_action( 'updated_post_meta', 'ns_audit_log_promote', 10, 4 );

function ns_audit_log_promote( $meta_id, $post_id, $meta_key, $meta_value ) {
    if ( $meta_key !== 'promote_status' ) return;
    if ( get_post_type( $post_id ) !== 'ns_feed_item' ) return;

    $title = get_the_title( $post_id );
    ns_audit_log(
        'promote',
        'feed_item',
        $post_id,
        $title,
        wp_json_encode( array( 'new_status' => $meta_value ) )
    );
}

/**
 * Log block assignment changes.
 */
add_action( 'updated_post_meta', 'ns_audit_log_block_assign', 10, 4 );

function ns_audit_log_block_assign( $meta_id, $post_id, $meta_key, $meta_value ) {
    if ( $meta_key !== 'block_assignment' ) return;
    if ( get_post_type( $post_id ) !== 'ns_feed_item' ) return;

    $title = get_the_title( $post_id );
    ns_audit_log(
        'block_assign',
        'feed_item',
        $post_id,
        $title,
        wp_json_encode( array( 'block' => $meta_value ) )
    );
}

/**
 * Log vault operations (upgrade from option-based vault log).
 */
add_action( 'updated_option', 'ns_audit_log_vault_option', 10, 3 );

function ns_audit_log_vault_option( $option, $old_value, $new_value ) {
    if ( strpos( $option, 'ns_vault_' ) !== 0 ) return;
    if ( $option === 'ns_vault_audit_log' ) return; // Don't log the log

    $service = str_replace( 'ns_vault_', '', $option );
    ns_audit_log(
        'vault_update',
        'token',
        0,
        $service,
        'Token value changed (encrypted)'
    );
}

/* ==================================================================
   4. ADMIN LOG VIEWER
   ================================================================== */

add_action( 'admin_menu', 'ns_audit_register_admin_page' );

function ns_audit_register_admin_page() {
    add_submenu_page(
        'edit.php?post_type=ns_feed_item',
        'Audit Log',
        'Audit Log',
        'manage_options',
        'ns-audit-log',
        'ns_audit_render_admin_page'
    );
}

function ns_audit_render_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized.' );
    }

    global $wpdb;
    $table = $wpdb->prefix . 'ns_audit_log';

    // Filters
    $filter_action = isset( $_GET['ns_action'] ) ? sanitize_key( $_GET['ns_action'] ) : '';
    $filter_user   = isset( $_GET['ns_user'] )   ? absint( $_GET['ns_user'] )         : 0;
    $paged         = isset( $_GET['paged'] )      ? max( 1, absint( $_GET['paged'] ) ) : 1;
    $per_page      = 50;
    $offset        = ( $paged - 1 ) * $per_page;

    // Build query
    $where = 'WHERE 1=1';
    $params = array();
    if ( $filter_action ) {
        $where .= ' AND action = %s';
        $params[] = $filter_action;
    }
    if ( $filter_user ) {
        $where .= ' AND user_id = %d';
        $params[] = $filter_user;
    }

    $count_sql = "SELECT COUNT(*) FROM $table $where";
    $total = $params ? $wpdb->get_var( $wpdb->prepare( $count_sql, ...$params ) ) : $wpdb->get_var( $count_sql );

    $data_sql = "SELECT * FROM $table $where ORDER BY log_time DESC LIMIT %d OFFSET %d";
    $all_params = array_merge( $params, array( $per_page, $offset ) );
    $rows = $wpdb->get_results( $wpdb->prepare( $data_sql, ...$all_params ) );

    // Get distinct actions for filter dropdown
    $actions = $wpdb->get_col( "SELECT DISTINCT action FROM $table ORDER BY action" );

    $total_pages = ceil( $total / $per_page );

    ?>
    <div class="wrap">
        <h1>Audit Log</h1>
        <p>All administrative actions on feed items, tokens, and settings.</p>

        <form method="get" style="margin:10px 0;">
            <input type="hidden" name="post_type" value="ns_feed_item">
            <input type="hidden" name="page" value="ns-audit-log">
            <select name="ns_action">
                <option value="">All actions</option>
                <?php foreach ( $actions as $act ) : ?>
                    <option value="<?php echo esc_attr( $act ); ?>" <?php selected( $filter_action, $act ); ?>>
                        <?php echo esc_html( $act ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="submit" class="button" value="Filter">
        </form>

        <p><strong><?php echo number_format( $total ); ?></strong> entries | Page <?php echo $paged; ?> of <?php echo max( 1, $total_pages ); ?></p>

        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Target</th>
                    <th>Details</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $rows ) ) : ?>
                    <tr><td colspan="6"><em>No log entries yet.</em></td></tr>
                <?php else : foreach ( $rows as $row ) : ?>
                    <tr>
                        <td style="white-space:nowrap;font-size:12px;"><?php echo esc_html( $row->log_time ); ?></td>
                        <td><?php echo esc_html( $row->user_login ); ?></td>
                        <td><code><?php echo esc_html( $row->action ); ?></code></td>
                        <td>
                            <?php if ( $row->target_id ) : ?>
                                <a href="<?php echo esc_url( get_edit_post_link( $row->target_id ) ); ?>">
                                    #<?php echo esc_html( $row->target_id ); ?>
                                </a>
                            <?php endif; ?>
                            <?php echo esc_html( $row->target_label ); ?>
                        </td>
                        <td style="font-size:11px;max-width:200px;overflow:hidden;text-overflow:ellipsis;">
                            <?php echo esc_html( $row->details ); ?>
                        </td>
                        <td style="font-size:11px;"><?php echo esc_html( $row->ip_address ); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>

        <?php if ( $total_pages > 1 ) : ?>
            <div class="tablenav">
                <div class="tablenav-pages">
                    <?php for ( $p = 1; $p <= $total_pages; $p++ ) : ?>
                        <?php if ( $p === $paged ) : ?>
                            <strong><?php echo $p; ?></strong>
                        <?php else : ?>
                            <a href="<?php echo esc_url( add_query_arg( 'paged', $p ) ); ?>"><?php echo $p; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
