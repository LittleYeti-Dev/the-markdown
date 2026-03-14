/**
 * S3.8-W2-02a — Admin Approval UI
 * Sprint 3.8, Wave 2 + HF02 (Queue badges + Start Review button)
 *
 * Renders the Editorial Board admin page with draft board display,
 * candidate pool, topic interests, and per-item share icons.
 * HF02: Adds queue status badges, queue-aware Publish button, Start Review button.
 *
 * Snippet Title: "S123a S3.8-W2-02a Admin Approval UI"
 * Scope: admin | Priority: 10
 * Depends on: S121 (Board Data Model), S122 (Draft Builder)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', function() {
    add_menu_page( 'Editorial Board', 'Editorial Board', 'manage_options', 'jk-editorial-board', 'jk_render_editorial_board_page', 'dashicons-layout', 3 );
} );



function jk_get_share_urls( $title, $block_id ) {
    $url = 'https://justin-kuiper.com/the-markdown/#box-' . $block_id;
    $text = $title . ' — The Markdown';
    return array(
        'x'         => 'https://twitter.com/intent/tweet?url=' . urlencode( $url ) . '&text=' . urlencode( $text ),
        'linkedin'  => 'https://www.linkedin.com/shareArticle?mini=true&url=' . urlencode( $url ) . '&title=' . urlencode( $text ),
        'instagram' => 'https://www.instagram.com/',
    );
}

function jk_render_editorial_board_page() {
    if ( ! function_exists( 'jk_get_draft_board' ) ) { echo '<div class="wrap"><h1>Error</h1><p>Board Data Model (S121) not active.</p></div>'; return; }
    $draft = jk_get_draft_board();
    $pub   = jk_get_published_board();
    $meta  = jk_board_meta();
    $nonce = wp_create_nonce( 'jk_editorial_actions' );
    $queue_count = $meta['queue_count'] ?? 0;

    echo '<style>.jk-eb{max-width:1100px;margin:20px auto;font-family:-apple-system,sans-serif}
        .jk-eb h1{color:#1d2327;margin-bottom:10px}
        .jk-eb .eb-actions{margin:15px 0;display:flex;gap:8px;flex-wrap:wrap;align-items:center}
        .jk-eb .eb-block{background:#fff;border:1px solid #c3c4c7;border-radius:4px;margin:12px 0;padding:12px;position:relative}
        .jk-eb .eb-block h3{margin:0 0 8px;font-size:15px;color:#1d2327}
        .jk-eb .eb-item{display:flex;justify-content:space-between;align-items:center;padding:8px;border-bottom:1px solid #f0f0f0;gap:10px}
        .jk-eb .eb-item:last-child{border-bottom:0}
        .jk-eb .eb-slot{font-weight:700;color:#2271b1;min-width:35px}
        .jk-eb .eb-info{flex:1;min-width:0}
        .jk-eb .eb-title{font-weight:600;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .jk-eb .eb-meta{color:#787c82;font-size:12px}
        .jk-eb .eb-hero-ok{color:#00a32a}.jk-eb .eb-hero-no{color:#d63638}
        .jk-eb .eb-btns{display:flex;gap:4px;flex-shrink:0;align-items:center}
        .jk-eb .eb-btns select{font-size:12px;padding:2px}
        .jk-eb .eb-btns button{font-size:12px;padding:4px 8px;cursor:pointer}
        .jk-eb .eb-share a{text-decoration:none;font-size:13px;margin-right:4px}
        .jk-eb .eb-pool{background:#f6f7f7;border:1px solid #c3c4c7;border-radius:4px;padding:12px;margin:12px 0}
        .jk-eb .eb-rejected{background:#fcf0f1;border:1px solid #d63638;border-radius:4px;padding:12px;margin:12px 0}
        .jk-eb .eb-topics{background:#f0f6fc;border:1px solid #2271b1;border-radius:4px;padding:12px;margin:12px 0}
        .jk-eb .eb-status{background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:12px;margin:12px 0}
        .jk-eb .eb-queued-badge{position:absolute;top:8px;right:12px;background:#22c55e;color:#fff;font-size:11px;font-weight:700;padding:2px 8px;border-radius:10px}
        .jk-eb .eb-block-queued{border-left:4px solid #22c55e}
        @media(max-width:768px){.jk-eb .eb-item{flex-direction:column;align-items:flex-start}.jk-eb .eb-btns{margin-top:6px;width:100%}.jk-eb .eb-btns button,.jk-eb .eb-btns select{font-size:14px;padding:8px 12px}}
        #jk-eb-modal-bg{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:99999;align-items:center;justify-content:center}
        #jk-eb-modal{background:#fff;border-radius:8px;padding:24px;max-width:420px;width:90%;box-shadow:0 4px 20px rgba(0,0,0,.3)}
        #jk-eb-modal h3{margin:0 0 8px;font-size:16px}
        #jk-eb-modal p{margin:0 0 16px;color:#50575e;font-size:14px}
        .eb-modal-btns{display:flex;gap:8px;flex-wrap:wrap}
        .eb-modal-btns button{padding:8px 16px;border:0;border-radius:4px;cursor:pointer;font-size:14px;font-weight:600}</style>';
    echo '<div class="wrap jk-eb">';
    echo '<h1>Editorial Board</h1>';
    if ( $draft ) echo '<p>Draft generated: <strong>' . esc_html( $draft['generated'] ?? 'unknown' ) . '</strong> | Items scored: ' . (int)($draft['items_considered'] ?? 0) . ' | Trigger: ' . esc_html( $draft['trigger'] ?? '?' ) . '</p>';
    else echo '<p>No draft board. Click Refresh Draft to generate one.</p>';

    // HF02: Action buttons with queue-aware Publish and Start Review
    echo '<div class="eb-actions">';
    echo '<button class="button button-primary" onclick="jkEB(\'refresh\')" id="jk-eb-refresh">Refresh Draft</button>';
    $pub_disabled = $queue_count === 0 ? ' disabled style="background:#a7aaad;border-color:#a7aaad;cursor:not-allowed"' : ' style="background:#22c55e;border-color:#1ea34b"';
    echo '<button class="button button-primary" onclick="jkEBPublishQueued()" id="jk-eb-publish"' . $pub_disabled . '>Publish Board (' . (int) $queue_count . ' queued)</button>';
    if ( $draft && ! empty( $draft['blocks'] ) ) {
        echo '<button class="button button-primary" onclick="jkSROpen()" style="background:#2271b1;border-color:#135e96">Start Review</button>';
    }
    echo '<button class="button" onclick="jkEB(\'clear\')" id="jk-eb-clear">Clear Board</button>';
    echo '</div>';

    $all_slots = array( '00','010','011','012','020','021','022','030','031','032','040','041','042','050','051','052','060','061','062' );

    if ( $draft && ! empty( $draft['blocks'] ) ) {
        $labels = array( '00' => 'BOX 00 — LEAD', '01' => 'BOX 01', '02' => 'BOX 02', '03' => 'BOX 03', '04' => 'BOX 04', '05' => 'BOX 05', '06' => 'BOX 06' );
        foreach ( $labels as $bid => $label ) {
            $items = $draft['blocks'][ $bid ] ?? array();
            // HF02: Check if block is queued
            $block_queued = false;
            foreach ( $items as $it ) {
                if ( ! empty( $it['queued'] ) ) { $block_queued = true; break; }
            }
            $block_class = $block_queued ? ' eb-block-queued' : '';
            $block_style = ( $bid === '00' ) ? 'border:2px solid #2271b1;background:#f0f6fc' : '';
            echo '<div class="eb-block' . $block_class . '" style="' . $block_style . '"><h3>' . esc_html( $label ) . '</h3>';
            if ( $block_queued ) echo '<span class="eb-queued-badge">QUEUED</span>';
            if ( empty( $items ) ) echo '<p style="color:#787c82">No items</p>';
            foreach ( $items as $it ) {
                $hi = ! empty( $it['hero_image'] ) ? '<span class="eb-hero-ok">&#9989;</span>' : '<span class="eb-hero-no">&#9888;</span>';
                $sh = jk_get_share_urls( $it['title'] ?? '', $bid );
                echo '<div class="eb-item" data-slot="' . esc_attr( $it['slot'] ?? '' ) . '" data-fid="' . (int)($it['feed_item_id'] ?? 0) . '">';
                echo '<span class="eb-slot">[' . esc_html( $it['slot'] ?? $bid ) . ']</span>';
                echo '<div class="eb-info"><span class="eb-title">' . esc_html( $it['title'] ?? '' ) . '</span>';
                echo '<span class="eb-meta">' . esc_html( $it['source'] ?? '' ) . ' | Score: ' . esc_html( $it['score'] ?? 0 ) . ' | Hero: ' . $hi . '</span>';
                echo '<span class="eb-meta">' . esc_html( $it['reason'] ?? '' ) . '</span>';
                // HF01-B: Hero preview thumbnail for Box 00
                if ( $bid === '00' ) {
                    $hero_url = get_post_meta( $it['feed_item_id'], '_jk_hero_image', true );
                    if ( $hero_url ) echo '<div style="margin:4px 0"><img src="' . esc_url( $hero_url ) . '" style="max-width:200px;max-height:80px;border-radius:4px;border:1px solid #c3c4c7" alt="Hero preview"></div>';
                }
                echo '</div>';
                echo '<div class="eb-btns">';
                echo '<select onchange="jkEB(\'move\',this)" data-fid="' . (int)$it['feed_item_id'] . '"><option value="">Move...</option>';
                foreach ( $all_slots as $s ) echo '<option value="' . $s . '">' . $s . '</option>';
                echo '</select>';
                echo '<button onclick="jkEB(\'reject\',this)" data-slot="' . esc_attr($it['slot'] ?? '') . '" data-fid="' . (int)$it['feed_item_id'] . '" style="background:#d63638;color:#fff;border:0;border-radius:3px">&#10005;</button>';
                // HF01-B: Promote to Lead button (only for non-00 items)
                if ( $bid !== '00' ) {
                    echo '<button onclick="jkEB(\'move\',this)" data-fid="' . (int)$it['feed_item_id'] . '" data-to-slot="00" style="background:#2271b1;color:#fff;border:0;border-radius:3px;font-weight:700" title="Promote to lead story">&#9733; Lead</button>';
                }
                echo '<span class="eb-share">';
                echo '<a href="' . esc_url( $sh['x'] ) . '" target="_blank" title="Share on X">&#120143;</a>';
                echo '<a href="' . esc_url( $sh['linkedin'] ) . '" target="_blank" title="LinkedIn">in</a>';
                echo '<a href="' . esc_url( $sh['instagram'] ) . '" target="_blank" title="Instagram">&#128247;</a>';
                echo '</span></div></div>';
            }
            echo '</div>';
        }

        $pool = $draft['candidate_pool'] ?? array();
        echo '<div class="eb-pool"><h3>CANDIDATE POOL (' . count( $pool ) . ' items)</h3>';
        foreach ( $pool as $cp ) {
            echo '<div class="eb-item"><div class="eb-info"><span class="eb-title">' . esc_html( $cp['title'] ?? '' ) . '</span>';
            echo '<span class="eb-meta">' . esc_html( $cp['source'] ?? '' ) . ' | Score: ' . esc_html( $cp['score'] ?? 0 ) . '</span></div>';
            echo '<div class="eb-btns">';
            // HF01-B: Set as Lead button for pool items
            echo '<button onclick="jkEB(\'swap\',this)" data-fid="' . (int)$cp['feed_item_id'] . '" data-to-slot="00" style="background:#2271b1;color:#fff;border:0;border-radius:3px;font-weight:700" title="Set as lead story">&#9733; Lead</button>';
            echo '<select onchange="jkEB(\'swap\',this)" data-fid="' . (int)$cp['feed_item_id'] . '"><option value="">Add to slot...</option>';
            foreach ( $all_slots as $s ) echo '<option value="' . $s . '">' . $s . '</option>';
            echo '</select></div></div>';
        }
        echo '</div>';
    }

    echo '<div class="eb-rejected" id="jk-eb-rejected"><h3>REJECTED THIS SESSION</h3><p id="jk-eb-rej-list" style="color:#787c82">None yet</p></div>';

    $topics = get_option( 'jk_editorial_topic_interests', array() );
    if ( ! is_array( $topics ) ) $topics = array();
    echo '<div class="eb-topics"><h3>TOPIC INTERESTS</h3>';
    foreach ( $topics as $i => $t ) {
        echo '<div class="eb-item"><div class="eb-info"><strong>' . esc_html( $t['label'] ?? '' ) . '</strong> [' . esc_html( $t['weight'] ?? 1.0 ) . 'x] — ' . esc_html( implode( ', ', (array)($t['keywords'] ?? array()) ) ) . '</div>';
        echo '<div class="eb-btns"><button onclick="jkEB(\'rmtopic\',' . $i . ')" style="color:#d63638">Remove</button></div></div>';
    }
    echo '<div style="margin-top:8px"><input id="jk-ti-label" placeholder="Label" style="width:120px"> <input id="jk-ti-kw" placeholder="Keywords (comma-sep)" style="width:200px"> <input id="jk-ti-wt" type="number" min="1" max="2" step="0.1" value="1.3" style="width:60px"> <button class="button" onclick="jkEB(\'addtopic\')">+ Add</button></div></div>';

    if ( $pub ) {
        echo '<div class="eb-status"><h3>PUBLISHED BOARD STATUS</h3>';
        echo '<p>Published: ' . esc_html( $meta['published_at'] ?? 'never' ) . ' | Stale: ' . ( $meta['is_stale'] ? 'Yes &#9888;' : 'No &#9989;' ) . '</p></div>';
    }

    echo '<input type="hidden" id="jk-eb-nonce" value="' . esc_attr( $nonce ) . '">';
    // Conflict resolution modal (non-blocking)
    echo '<div id="jk-eb-modal-bg"><div id="jk-eb-modal">';
    echo '<h3>Slot Occupied</h3>';
    echo '<p id="jk-eb-modal-msg"></p>';
    echo '<div class="eb-modal-btns">';
    echo '<button onclick="jkEBResolve(\'swap\')" style="background:#2271b1;color:#fff">&#8644; Swap Positions</button>';
    echo '<button onclick="jkEBResolve(\'replace\')" style="background:#d63638;color:#fff">&#8681; Replace (to pool)</button>';
    echo '<button onclick="jkEBResolve(\'cancel\')" style="background:#dcdcde;color:#1d2327">Cancel</button>';
    echo '</div></div></div>';
    echo '</div>';
    ?>
    <script>
    var _jkPendingEl=null;
    function jkEBResolve(choice){
        document.getElementById('jk-eb-modal-bg').style.display='none';
        if(!_jkPendingEl||choice==='cancel'){if(_jkPendingEl&&_jkPendingEl.tagName==='SELECT')_jkPendingEl.value='';_jkPendingEl=null;return;}
        jkEB('move',_jkPendingEl,choice);
        _jkPendingEl=null;
    }
    // HF02: Queue-aware publish with inline confirmation
    function jkEBPublishQueued(){
        var btn=document.getElementById('jk-eb-publish');
        if(btn.disabled)return;
        var modal=document.getElementById('jk-eb-modal-bg');
        var msg=document.getElementById('jk-eb-modal-msg');
        var box=document.getElementById('jk-eb-modal');
        box.innerHTML='<h3>Publish Queued Items</h3><p>Publish all queued blocks to The Markdown? This will update the live page.</p><div class="eb-modal-btns"><button onclick="jkEB(\'publish\');document.getElementById(\'jk-eb-modal-bg\').style.display=\'none\'" style="background:#22c55e;color:#fff">Publish Now</button><button onclick="document.getElementById(\'jk-eb-modal-bg\').style.display=\'none\'" style="background:#dcdcde;color:#1d2327">Cancel</button></div>';
        modal.style.display='flex';
    }
    function jkEB(action, el, mode) {
        var n = document.getElementById('jk-eb-nonce').value;
        var d = 'action=jk_editorial_' + action + '&_ajax_nonce=' + n;
        var toSlot = '';
        if (action==='reject') { d += '&slot=' + el.dataset.slot + '&feed_item_id=' + el.dataset.fid; }
        if (action==='move') {
            toSlot = el.dataset.toSlot || el.value;
            if(!toSlot)return;
            d += '&feed_item_id=' + el.dataset.fid + '&to_slot=' + toSlot;
            if(mode) d += '&mode=' + mode;
        }
        if (action==='swap') { d += '&feed_item_id=' + el.dataset.fid + '&to_slot=' + (el.dataset.toSlot || el.value); if(!(el.dataset.toSlot || el.value))return; }
        if (action==='rmtopic') { d += '&index=' + el; }
        if (action==='addtopic') {
            var l=document.getElementById('jk-ti-label').value,k=document.getElementById('jk-ti-kw').value,w=document.getElementById('jk-ti-wt').value;
            if(!l||!k){alert('Label and keywords required');return;}
            d+='&label='+encodeURIComponent(l)+'&keywords='+encodeURIComponent(k)+'&weight='+w;
        }
        var x=new XMLHttpRequest();x.open('POST',ajaxurl);
        x.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        x.onload=function(){
            try{
                var r=JSON.parse(x.responseText);
                if(r.success){ location.reload(); return; }
                if(r.data && r.data.code==='slot_occupied'){
                    _jkPendingEl=el;
                    var t=r.data.occupant_title||'an item';
                    // Restore conflict modal content
                    var box=document.getElementById('jk-eb-modal');
                    box.innerHTML='<h3>Slot Occupied</h3><p id="jk-eb-modal-msg">Slot '+toSlot+' has: "'+t+'"</p><div class="eb-modal-btns"><button onclick="jkEBResolve(\'swap\')" style="background:#2271b1;color:#fff">&#8644; Swap Positions</button><button onclick="jkEBResolve(\'replace\')" style="background:#d63638;color:#fff">&#8681; Replace (to pool)</button><button onclick="jkEBResolve(\'cancel\')" style="background:#dcdcde;color:#1d2327">Cancel</button></div>';
                    document.getElementById('jk-eb-modal-bg').style.display='flex';
                    return;
                }
                if(r.data && r.data.code==='empty_queue'){
                    alert(r.data.message || 'No items queued. Use Start Review to queue items first.');
                    return;
                }
                alert(r.data && r.data.message ? r.data.message : (r.data||'Error'));
            }catch(e){alert('Request failed');}
        };
        x.send(d);
    }
    </script>
    <?php
}
