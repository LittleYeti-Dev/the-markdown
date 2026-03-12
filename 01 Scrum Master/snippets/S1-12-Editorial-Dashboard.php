<?php
/**
 * S1.12 — Editorial Dashboard
 * Sprint 1, Gate 3 | GitLab Issue #19
 *
 * Custom admin page: feed items list with domain/score/status filters,
 * color-coded relevance, sortable columns.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S1.12 Editorial Dashboard"
 * Scope: Admin only
 * Depends on: S1-G1-Data-Model (CPT + meta + taxonomies)
 *
 * @package NonSequitur
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ------------------------------------------------------------------
   1. Register Admin Submenu Page
   ------------------------------------------------------------------ */

add_action( 'admin_menu', 'ns_register_editorial_dashboard' );

function ns_register_editorial_dashboard() {
    add_submenu_page(
        'edit.php?post_type=ns_feed_item',
        'Editorial Dashboard',
        'Editorial Dashboard',
        'edit_posts',
        'ns-editorial-dashboard',
        'ns_render_editorial_dashboard'
    );
}

/* ------------------------------------------------------------------
   2. Render Dashboard Page
   ------------------------------------------------------------------ */

function ns_render_editorial_dashboard() {
    // Current filter values
    $filter_domain  = isset( $_GET['ns_domain'] )  ? sanitize_key( $_GET['ns_domain'] )   : '';
    $filter_status  = isset( $_GET['ns_status'] )  ? sanitize_key( $_GET['ns_status'] )   : '';
    $filter_score   = isset( $_GET['ns_score'] )   ? sanitize_key( $_GET['ns_score'] )    : '';
    $filter_block   = isset( $_GET['ns_block'] )   ? absint( $_GET['ns_block'] )          : -1;
    $orderby        = isset( $_GET['ns_orderby'] ) ? sanitize_key( $_GET['ns_orderby'] )  : 'relevance_score';
    $order          = isset( $_GET['ns_order'] )   ? strtoupper( sanitize_key( $_GET['ns_order'] ) ) : 'DESC';
    $paged          = isset( $_GET['paged'] )      ? max( 1, absint( $_GET['paged'] ) )   : 1;
    $per_page       = 25;

    if ( ! in_array( $order, array( 'ASC', 'DESC' ), true ) ) {
        $order = 'DESC';
    }

    // Build query
    $meta_query = array();
    if ( $filter_domain !== '' ) {
        $meta_query[] = array( 'key' => 'domain_tag', 'value' => $filter_domain );
    }
    if ( $filter_status !== '' ) {
        $meta_query[] = array( 'key' => 'promote_status', 'value' => $filter_status );
    }
    if ( $filter_score !== '' ) {
        $ranges = array(
            'high'   => array( 'key' => 'relevance_score', 'value' => 7, 'compare' => '>=', 'type' => 'NUMERIC' ),
            'medium' => array( 'key' => 'relevance_score', 'value' => array( 4, 6 ), 'compare' => 'BETWEEN', 'type' => 'NUMERIC' ),
            'low'    => array( 'key' => 'relevance_score', 'value' => 4, 'compare' => '<', 'type' => 'NUMERIC' ),
        );
        if ( isset( $ranges[ $filter_score ] ) ) {
            $meta_query[] = $ranges[ $filter_score ];
        }
    }
    if ( $filter_block >= 0 ) {
        $meta_query[] = array( 'key' => 'block_assignment', 'value' => $filter_block, 'type' => 'NUMERIC' );
    }

    $args = array(
        'post_type'      => 'ns_feed_item',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $paged,
        'meta_key'       => $orderby,
        'orderby'        => in_array( $orderby, array( 'relevance_score', 'arc_score', 'block_assignment' ), true )
                            ? 'meta_value_num' : 'meta_value',
        'order'          => $order,
    );
    if ( ! empty( $meta_query ) ) {
        $args['meta_query'] = $meta_query;
    }

    $query = new WP_Query( $args );

    // Stats
    $total_items  = $query->found_posts;
    $total_pages  = $query->max_num_pages;
    $count_dev    = ns_count_by_status( 'dev' );
    $count_ready  = ns_count_by_status( 'ready' );
    $count_pub    = ns_count_by_status( 'published' );

    // Domain colors
    $domain_colors = array(
        'ai'         => '#06B6D4',
        'cyber'      => '#00FF88',
        'innovation' => '#FACC15',
        'fnw'        => '#C084FC',
        'space'      => '#FF6B35',
        'digital'    => '#14B8A6',
    );

    $base_url = admin_url( 'edit.php?post_type=ns_feed_item&page=ns-editorial-dashboard' );

    ?>
    <div class="wrap">
        <h1 style="font-size:24px;font-weight:600;margin-bottom:4px;">
            Editorial Dashboard
        </h1>
        <p class="description" style="margin-bottom:16px;">
            Non Sequitur feed pipeline &mdash; filter, score, and promote items to page blocks.
        </p>

        <!-- Status Counters -->
        <div style="display:flex;gap:12px;margin-bottom:16px;">
            <?php
            $counters = array(
                array( 'label' => 'Total',     'count' => $total_items, 'bg' => '#2563EB', 'color' => '#fff' ),
                array( 'label' => 'DEV',       'count' => $count_dev,   'bg' => '#21262d', 'color' => '#8b949e' ),
                array( 'label' => 'READY',     'count' => $count_ready, 'bg' => 'rgba(210,153,34,0.2)', 'color' => '#d29922' ),
                array( 'label' => 'PUBLISHED', 'count' => $count_pub,   'bg' => 'rgba(63,185,80,0.2)', 'color' => '#3fb950' ),
            );
            foreach ( $counters as $c ) :
            ?>
                <div style="padding:8px 16px;border-radius:6px;background:<?php echo esc_attr( $c['bg'] ); ?>;color:<?php echo esc_attr( $c['color'] ); ?>;font-weight:600;font-size:13px;">
                    <?php echo esc_html( $c['count'] ); ?> <?php echo esc_html( $c['label'] ); ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Filters -->
        <form method="get" action="<?php echo esc_url( $base_url ); ?>" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:16px;padding:12px;background:#f0f0f1;border-radius:6px;">
            <input type="hidden" name="post_type" value="ns_feed_item">
            <input type="hidden" name="page" value="ns-editorial-dashboard">

            <label style="font-size:12px;font-weight:600;">Domain:</label>
            <select name="ns_domain" style="min-width:120px;">
                <option value="">All</option>
                <?php foreach ( array( 'ai', 'cyber', 'innovation', 'fnw', 'space', 'digital' ) as $d ) : ?>
                    <option value="<?php echo esc_attr( $d ); ?>" <?php selected( $filter_domain, $d ); ?>>
                        <?php echo esc_html( strtoupper( $d ) ); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label style="font-size:12px;font-weight:600;">Status:</label>
            <select name="ns_status" style="min-width:110px;">
                <option value="">All</option>
                <?php foreach ( array( 'dev', 'ready', 'published', 'archived' ) as $s ) : ?>
                    <option value="<?php echo esc_attr( $s ); ?>" <?php selected( $filter_status, $s ); ?>>
                        <?php echo esc_html( strtoupper( $s ) ); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label style="font-size:12px;font-weight:600;">Score:</label>
            <select name="ns_score" style="min-width:100px;">
                <option value="">All</option>
                <option value="high" <?php selected( $filter_score, 'high' ); ?>>High (7-10)</option>
                <option value="medium" <?php selected( $filter_score, 'medium' ); ?>>Med (4-6)</option>
                <option value="low" <?php selected( $filter_score, 'low' ); ?>>Low (0-3)</option>
            </select>

            <label style="font-size:12px;font-weight:600;">Block:</label>
            <select name="ns_block" style="min-width:100px;">
                <option value="-1">All</option>
                <option value="0" <?php selected( $filter_block, 0 ); ?>>Unassigned</option>
                <?php for ( $b = 1; $b <= 7; $b++ ) : ?>
                    <option value="<?php echo $b; ?>" <?php selected( $filter_block, $b ); ?>>
                        Block <?php echo $b; ?>
                    </option>
                <?php endfor; ?>
            </select>

            <button type="submit" class="button button-primary" style="margin-left:4px;">Filter</button>
            <a href="<?php echo esc_url( $base_url ); ?>" class="button" style="margin-left:2px;">Reset</a>
        </form>

        <!-- Results Table -->
        <table class="widefat striped" style="border-collapse:collapse;">
            <thead>
                <tr>
                    <th style="width:3%;padding:8px;"><input type="checkbox" id="ns-select-all"></th>
                    <th style="width:30%;padding:8px;"><?php echo ns_sortable_header( 'Title', 'title', $orderby, $order, $base_url, $filter_domain, $filter_status, $filter_score, $filter_block ); ?></th>
                    <th style="width:12%;padding:8px;">Source</th>
                    <th style="width:10%;padding:8px;">Domain</th>
                    <th style="width:8%;padding:8px;text-align:center;"><?php echo ns_sortable_header( 'Score', 'relevance_score', $orderby, $order, $base_url, $filter_domain, $filter_status, $filter_score, $filter_block ); ?></th>
                    <th style="width:8%;padding:8px;text-align:center;"><?php echo ns_sortable_header( 'Block', 'block_assignment', $orderby, $order, $base_url, $filter_domain, $filter_status, $filter_score, $filter_block ); ?></th>
                    <th style="width:10%;padding:8px;"><?php echo ns_sortable_header( 'Status', 'promote_status', $orderby, $order, $base_url, $filter_domain, $filter_status, $filter_score, $filter_block ); ?></th>
                    <th style="width:12%;padding:8px;">Imported</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post();
                    $pid   = get_the_ID();
                    $score = absint( get_post_meta( $pid, 'relevance_score', true ) );
                    $block = absint( get_post_meta( $pid, 'block_assignment', true ) );
                    $status = get_post_meta( $pid, 'promote_status', true ) ?: 'dev';
                    $domain = get_post_meta( $pid, 'domain_tag', true );
                    $source = get_post_meta( $pid, 'source_name', true );
                    $source_url = get_post_meta( $pid, 'source_url', true );
                    $import = get_post_meta( $pid, 'import_date', true );

                    // Score color
                    $score_color = $score >= 7 ? '#3fb950' : ( $score >= 4 ? '#d29922' : '#8b949e' );

                    // Domain color
                    $d_color = isset( $domain_colors[ $domain ] ) ? $domain_colors[ $domain ] : '#8b949e';

                    // Status badge
                    $status_styles = array(
                        'dev'       => 'background:#f0f0f1;color:#646970;',
                        'ready'     => 'background:#fcf0cf;color:#996800;',
                        'published' => 'background:#d4edda;color:#135e21;',
                        'archived'  => 'background:#f0f0f1;color:#a7aaad;',
                    );
                    $badge_style = isset( $status_styles[ $status ] ) ? $status_styles[ $status ] : $status_styles['dev'];
                ?>
                <tr data-post-id="<?php echo esc_attr( $pid ); ?>">
                    <td style="padding:8px;"><input type="checkbox" class="ns-item-check" value="<?php echo esc_attr( $pid ); ?>"></td>
                    <td style="padding:8px;">
                        <a href="<?php echo esc_url( get_edit_post_link( $pid ) ); ?>" style="font-weight:600;color:#2271b1;">
                            <?php echo esc_html( get_the_title() ); ?>
                        </a>
                        <?php if ( $source_url ) : ?>
                            <br><a href="<?php echo esc_url( $source_url ); ?>" target="_blank" rel="noopener" style="font-size:11px;color:#8c8f94;">
                                <?php echo esc_html( wp_trim_words( $source_url, 8, '...' ) ); ?> &#8599;
                            </a>
                        <?php endif; ?>
                    </td>
                    <td style="padding:8px;font-size:13px;"><?php echo esc_html( $source ); ?></td>
                    <td style="padding:8px;">
                        <span style="display:inline-block;padding:2px 8px;border-radius:3px;font-size:11px;font-weight:600;letter-spacing:0.5px;background:<?php echo esc_attr( $d_color ); ?>22;color:<?php echo esc_attr( $d_color ); ?>;border:1px solid <?php echo esc_attr( $d_color ); ?>44;">
                            <?php echo esc_html( strtoupper( $domain ?: '—' ) ); ?>
                        </span>
                    </td>
                    <td style="padding:8px;text-align:center;">
                        <strong style="color:<?php echo esc_attr( $score_color ); ?>;font-size:14px;">
                            <?php echo esc_html( $score ); ?>
                        </strong>
                        <span style="color:#a7aaad;font-size:11px;">/10</span>
                    </td>
                    <td style="padding:8px;text-align:center;font-weight:600;">
                        <?php echo $block > 0 ? esc_html( 'B' . $block ) : '<span style="color:#a7aaad;">—</span>'; ?>
                    </td>
                    <td style="padding:8px;">
                        <span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;<?php echo esc_attr( $badge_style ); ?>">
                            <?php echo esc_html( strtoupper( $status ) ); ?>
                        </span>
                    </td>
                    <td style="padding:8px;font-size:12px;color:#8c8f94;">
                        <?php
                        if ( $import ) {
                            echo esc_html( date_i18n( 'M j, g:ia', strtotime( $import ) ) );
                        } else {
                            echo esc_html( get_the_date( 'M j, g:ia' ) );
                        }
                        ?>
                    </td>
                </tr>
                <?php endwhile; else : ?>
                <tr><td colspan="8" style="padding:20px;text-align:center;color:#646970;">No feed items match the current filters.</td></tr>
                <?php endif; wp_reset_postdata(); ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ( $total_pages > 1 ) : ?>
        <div style="margin-top:12px;display:flex;justify-content:space-between;align-items:center;">
            <span style="font-size:13px;color:#646970;">
                Page <?php echo esc_html( $paged ); ?> of <?php echo esc_html( $total_pages ); ?>
                (<?php echo esc_html( $total_items ); ?> items)
            </span>
            <div>
                <?php if ( $paged > 1 ) : ?>
                    <a class="button" href="<?php echo esc_url( add_query_arg( 'paged', $paged - 1 ) ); ?>">&laquo; Prev</a>
                <?php endif; ?>
                <?php if ( $paged < $total_pages ) : ?>
                    <a class="button" href="<?php echo esc_url( add_query_arg( 'paged', $paged + 1 ) ); ?>">Next &raquo;</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- .wrap -->

    <script>
    document.getElementById('ns-select-all').addEventListener('change', function() {
        document.querySelectorAll('.ns-item-check').forEach(function(cb) {
            cb.checked = this.checked;
        }.bind(this));
    });
    </script>
    <?php
}

/* ------------------------------------------------------------------
   3. Helper: Count by promote_status
   ------------------------------------------------------------------ */

function ns_count_by_status( $status ) {
    global $wpdb;
    return (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p
         INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
         WHERE p.post_type = 'ns_feed_item'
         AND p.post_status = 'publish'
         AND pm.meta_key = 'promote_status'
         AND pm.meta_value = %s",
        $status
    ) );
}

/* ------------------------------------------------------------------
   4. Helper: Sortable Column Header
   ------------------------------------------------------------------ */

function ns_sortable_header( $label, $field, $current_orderby, $current_order, $base_url, $domain, $status, $score, $block ) {
    $new_order = ( $current_orderby === $field && $current_order === 'DESC' ) ? 'ASC' : 'DESC';
    $arrow = '';
    if ( $current_orderby === $field ) {
        $arrow = $current_order === 'DESC' ? ' &#9660;' : ' &#9650;';
    }
    $url = add_query_arg( array(
        'ns_orderby' => $field,
        'ns_order'   => $new_order,
        'ns_domain'  => $domain,
        'ns_status'  => $status,
        'ns_score'   => $score,
        'ns_block'   => $block,
    ), $base_url );
    return '<a href="' . esc_url( $url ) . '" style="color:#2271b1;text-decoration:none;font-weight:600;">' . esc_html( $label ) . $arrow . '</a>';
}
