/**
 * S3.8-HF02 — Sequential Review UI
 * Sprint 3.8, Hotfix 02
 *
 * Full-width overlay for walking through editorial board items
 * one block at a time: review, queue/unqueue, skip, publish.
 *
 * Snippet Title: "S3.8-HF02 Sequential Review UI"
 * Scope: admin | Priority: 10
 * Depends on: S121 (Board Data Model), S123a (Admin UI), S123b (AJAX Handlers)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_footer', function() {
    $screen = get_current_screen();
    if ( ! $screen || $screen->id !== 'toplevel_page_jk-editorial-board' ) return;

    $draft = jk_get_draft_board();
    if ( ! $draft || empty( $draft['blocks'] ) ) return;

    // Build ordered list of populated blocks for JS
    $review_blocks = array();
    $block_labels = array( '00' => 'BOX 00 — LEAD', '01' => 'BOX 01', '02' => 'BOX 02', '03' => 'BOX 03', '04' => 'BOX 04', '05' => 'BOX 05', '06' => 'BOX 06' );
    foreach ( $block_labels as $bid => $label ) {
        if ( empty( $draft['blocks'][ $bid ] ) ) continue;
        $items = $draft['blocks'][ $bid ];
        $block_data = array(
            'block_id' => $bid,
            'label'    => $label,
            'items'    => array(),
        );
        foreach ( $items as $item ) {
            $hero_url = '';
            if ( ! empty( $item['feed_item_id'] ) ) {
                $hero_url = get_post_meta( $item['feed_item_id'], '_jk_hero_image', true );
            }
            $block_data['items'][] = array(
                'feed_item_id' => (int) ( $item['feed_item_id'] ?? 0 ),
                'title'        => $item['title'] ?? '',
                'source'       => $item['source'] ?? '',
                'domain'       => $item['domain'] ?? '',
                'score'        => $item['score'] ?? 0,
                'slot'         => $item['slot'] ?? $bid,
                'hero_image'   => $hero_url,
                'queued'       => ! empty( $item['queued'] ),
                'ingested'     => $item['ingested'] ?? $item['date'] ?? '',
            );
        }
        $review_blocks[] = $block_data;
    }
    if ( empty( $review_blocks ) ) return;
    ?>
    <style>
    #jk-sr-overlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(13,17,23,.95);z-index:100000;overflow-y:auto;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
    #jk-sr-wrap{max-width:720px;margin:40px auto;padding:20px;color:#e6edf3}
    .jk-sr-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px}
    .jk-sr-header h2{margin:0;font-size:18px;color:#e6edf3;font-weight:700}
    .jk-sr-counter{color:#8b949e;font-size:14px}
    .jk-sr-card{background:#1a1a2e;border-radius:8px;padding:24px;margin-bottom:20px}
    .jk-sr-hero{width:100%;max-height:300px;object-fit:cover;border-radius:6px;margin-bottom:16px;border:1px solid #30363d}
    .jk-sr-no-hero{background:#21262d;border:1px dashed #30363d;border-radius:6px;padding:40px;text-align:center;color:#8b949e;margin-bottom:16px}
    .jk-sr-title{font-size:20px;font-weight:700;color:#e6edf3;margin-bottom:12px;line-height:1.3}
    .jk-sr-meta{display:grid;grid-template-columns:auto 1fr;gap:6px 12px;font-size:14px;color:#8b949e;margin-bottom:16px}
    .jk-sr-meta dt{font-weight:600;color:#c9d1d9}
    .jk-sr-meta dd{margin:0}
    .jk-sr-items-list{margin-top:12px;border-top:1px solid #30363d;padding-top:12px}
    .jk-sr-items-list .jk-sr-sub-item{padding:8px 0;border-bottom:1px solid #21262d;font-size:13px;color:#c9d1d9}
    .jk-sr-items-list .jk-sr-sub-item:last-child{border-bottom:0}
    .jk-sr-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px}
    .jk-sr-actions button{padding:10px 20px;border:0;border-radius:6px;font-size:14px;font-weight:600;cursor:pointer;transition:opacity .15s}
    .jk-sr-actions button:hover{opacity:.85}
    .jk-sr-btn-queue{background:#22c55e;color:#fff}
    .jk-sr-btn-unqueue{background:#f97316;color:#fff}
    .jk-sr-btn-skip{background:#374151;color:#e6edf3}
    .jk-sr-btn-back{background:#374151;color:#e6edf3}
    .jk-sr-btn-exit{background:#ef4444;color:#fff}
    .jk-sr-btn-publish{background:#22c55e;color:#fff;font-size:16px;padding:12px 28px}
    .jk-sr-progress{margin-top:20px}
    .jk-sr-progress-bar{background:#21262d;border-radius:4px;height:8px;overflow:hidden}
    .jk-sr-progress-fill{background:#06b6d4;height:100%;transition:width .3s ease}
    .jk-sr-progress-text{color:#8b949e;font-size:13px;margin-top:6px;text-align:center}
    .jk-sr-queued-badge{display:inline-block;background:#22c55e;color:#fff;font-size:11px;font-weight:700;padding:2px 8px;border-radius:10px;margin-left:8px}
    .jk-sr-summary{text-align:center;padding:40px 20px}
    .jk-sr-summary h2{font-size:24px;color:#e6edf3;margin-bottom:16px}
    .jk-sr-summary p{color:#8b949e;font-size:16px;margin-bottom:24px}
    /* Inline confirmation modal */
    .jk-sr-confirm{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.6);z-index:100001;align-items:center;justify-content:center}
    .jk-sr-confirm-box{background:#1a1a2e;border:1px solid #30363d;border-radius:8px;padding:24px;max-width:400px;width:90%;color:#e6edf3}
    .jk-sr-confirm-box h3{margin:0 0 8px;font-size:16px}
    .jk-sr-confirm-box p{margin:0 0 16px;color:#8b949e;font-size:14px}
    .jk-sr-confirm-btns{display:flex;gap:8px}
    .jk-sr-confirm-btns button{flex:1}
    @media(max-width:768px){
        #jk-sr-wrap{margin:10px;padding:10px}
        .jk-sr-card{padding:16px}
        .jk-sr-title{font-size:16px}
        .jk-sr-actions{flex-direction:column}
        .jk-sr-actions button{width:100%}
        .jk-sr-meta{grid-template-columns:1fr;gap:4px}
    }
    </style>

    <div id="jk-sr-overlay">
        <div id="jk-sr-wrap">
            <div id="jk-sr-content"></div>
        </div>
    </div>

    <div class="jk-sr-confirm" id="jk-sr-confirm">
        <div class="jk-sr-confirm-box">
            <h3 id="jk-sr-confirm-title"></h3>
            <p id="jk-sr-confirm-msg"></p>
            <div class="jk-sr-confirm-btns">
                <button id="jk-sr-confirm-yes" class="jk-sr-btn-queue" style="padding:10px 20px;border:0;border-radius:6px;font-size:14px;font-weight:600;cursor:pointer"></button>
                <button onclick="jkSRConfirmClose()" class="jk-sr-btn-skip" style="padding:10px 20px;border:0;border-radius:6px;font-size:14px;font-weight:600;cursor:pointer">Cancel</button>
            </div>
        </div>
    </div>

    <script>
    (function(){
        var blocks = <?php echo wp_json_encode( $review_blocks ); ?>;
        var nonce = document.getElementById('jk-eb-nonce').value;
        var pos = 0;
        var totalBlocks = blocks.length;

        window.jkSROpen = function() {
            pos = 0;
            document.getElementById('jk-sr-overlay').style.display = 'block';
            document.body.style.overflow = 'hidden';
            renderBlock();
        };

        window.jkSRClose = function() {
            document.getElementById('jk-sr-overlay').style.display = 'none';
            document.body.style.overflow = '';
            location.reload();
        };

        window.jkSRConfirmClose = function() {
            document.getElementById('jk-sr-confirm').style.display = 'none';
        };

        function showConfirm(title, msg, btnText, btnClass, cb) {
            var el = document.getElementById('jk-sr-confirm');
            document.getElementById('jk-sr-confirm-title').textContent = title;
            document.getElementById('jk-sr-confirm-msg').textContent = msg;
            var btn = document.getElementById('jk-sr-confirm-yes');
            btn.textContent = btnText;
            btn.className = btnClass + ' jk-sr-btn-queue';
            if (btnClass === 'unqueue') btn.className = 'jk-sr-btn-unqueue';
            else if (btnClass === 'publish') btn.className = 'jk-sr-btn-publish';
            else btn.className = 'jk-sr-btn-queue';
            btn.onclick = function() { jkSRConfirmClose(); cb(); };
            el.style.display = 'flex';
        }

        function renderBlock() {
            var container = document.getElementById('jk-sr-content');
            if (pos >= totalBlocks) { renderSummary(); return; }

            var b = blocks[pos];
            var isQueued = b.items.some(function(it){ return it.queued; });
            var pct = Math.round(((pos + 1) / totalBlocks) * 100);

            var html = '<div class="jk-sr-header">';
            html += '<h2>REVIEWING: ' + esc(b.label);
            if (isQueued) html += '<span class="jk-sr-queued-badge">QUEUED</span>';
            html += '</h2>';
            html += '<span class="jk-sr-counter">' + (pos + 1) + ' / ' + totalBlocks + '</span></div>';

            html += '<div class="jk-sr-card">';
            // Show primary item (first in block)
            var primary = b.items[0];
            if (primary.hero_image) {
                html += '<img class="jk-sr-hero" src="' + esc(primary.hero_image) + '" alt="Hero image">';
            } else {
                html += '<div class="jk-sr-no-hero">No hero image</div>';
            }
            html += '<div class="jk-sr-title">' + esc(primary.title) + '</div>';
            html += '<dl class="jk-sr-meta">';
            html += '<dt>SOURCE</dt><dd>' + esc(primary.source) + '</dd>';
            html += '<dt>DOMAIN</dt><dd>' + esc(primary.domain) + '</dd>';
            html += '<dt>SCORE</dt><dd>' + esc(String(primary.score)) + '</dd>';
            if (primary.ingested) { html += '<dt>INGESTED</dt><dd>' + esc(primary.ingested) + '</dd>'; }
            html += '</dl>';

            // Show additional items in block (Box 01-06 can have up to 3)
            if (b.items.length > 1) {
                html += '<div class="jk-sr-items-list"><strong style="color:#c9d1d9;font-size:13px">Additional items in this block:</strong>';
                for (var i = 1; i < b.items.length; i++) {
                    var sub = b.items[i];
                    html += '<div class="jk-sr-sub-item">[' + esc(sub.slot) + '] ' + esc(sub.title) + ' — ' + esc(sub.source) + ' (Score: ' + sub.score + ')</div>';
                }
                html += '</div>';
            }
            html += '</div>';

            // Action buttons
            html += '<div class="jk-sr-actions">';
            if (isQueued) {
                html += '<button class="jk-sr-btn-unqueue" onclick="jkSRUnqueue()">Remove from Queue</button>';
            } else {
                html += '<button class="jk-sr-btn-queue" onclick="jkSRQueue()">Submit to Queue</button>';
            }
            html += '<button class="jk-sr-btn-skip" onclick="jkSRNext()">Skip &#10132;</button>';
            if (pos > 0) html += '<button class="jk-sr-btn-back" onclick="jkSRBack()">&#10094; Back</button>';
            html += '<button class="jk-sr-btn-exit" onclick="jkSRClose()">Exit</button>';
            html += '</div>';

            // Progress bar
            html += '<div class="jk-sr-progress">';
            html += '<div class="jk-sr-progress-bar"><div class="jk-sr-progress-fill" style="width:' + pct + '%"></div></div>';
            html += '<div class="jk-sr-progress-text">Block ' + (pos + 1) + ' of ' + totalBlocks + ' — Queue: ' + getQueueCount() + ' blocks queued</div>';
            html += '</div>';

            container.innerHTML = html;
        }

        function renderSummary() {
            var container = document.getElementById('jk-sr-content');
            var qc = getQueueCount();
            var html = '<div class="jk-sr-summary">';
            html += '<h2>Review Complete</h2>';
            html += '<p>' + qc + ' block' + (qc !== 1 ? 's' : '') + ' queued for publishing.</p>';
            if (qc > 0) {
                html += '<div class="jk-sr-actions" style="justify-content:center">';
                html += '<button class="jk-sr-btn-publish" onclick="jkSRPublish()">Publish ' + qc + ' Block' + (qc !== 1 ? 's' : '') + ' Now</button>';
                html += '<button class="jk-sr-btn-back" onclick="jkSRBack()">&#10094; Back to Review</button>';
                html += '<button class="jk-sr-btn-exit" onclick="jkSRClose()">Back to Board</button>';
                html += '</div>';
            } else {
                html += '<div class="jk-sr-actions" style="justify-content:center">';
                html += '<button class="jk-sr-btn-back" onclick="jkSRBack()">&#10094; Back to Review</button>';
                html += '<button class="jk-sr-btn-exit" onclick="jkSRClose()">Back to Board</button>';
                html += '</div>';
            }
            html += '</div>';
            container.innerHTML = html;
        }

        window.jkSRQueue = function() {
            var b = blocks[pos];
            var title = b.items[0].title;
            if (title.length > 60) title = title.substring(0, 60) + '...';
            showConfirm('Queue Block', 'Queue "' + title + '" for publishing?', 'Queue', 'queue', function() {
                doQueueAjax(b.block_id, 'queue', function() {
                    markBlockQueued(b.block_id, true);
                    jkSRNext();
                });
            });
        };

        window.jkSRUnqueue = function() {
            var b = blocks[pos];
            var title = b.items[0].title;
            if (title.length > 60) title = title.substring(0, 60) + '...';
            showConfirm('Remove from Queue', 'Remove "' + title + '" from publishing queue?', 'Remove', 'unqueue', function() {
                doQueueAjax(b.block_id, 'unqueue', function() {
                    markBlockQueued(b.block_id, false);
                    renderBlock();
                });
            });
        };

        window.jkSRNext = function() {
            if (pos < totalBlocks) { pos++; renderBlock(); }
        };

        window.jkSRBack = function() {
            if (pos > 0) { pos--; renderBlock(); }
        };

        window.jkSRPublish = function() {
            var qc = getQueueCount();
            showConfirm('Publish Board', 'Publish ' + qc + ' block' + (qc !== 1 ? 's' : '') + ' to The Markdown? This will update the live page.', 'Publish Now', 'publish', function() {
                var x = new XMLHttpRequest();
                x.open('POST', ajaxurl);
                x.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                x.onload = function() {
                    try {
                        var r = JSON.parse(x.responseText);
                        if (r.success) {
                            var msg = r.data.message || r.data;
                            var container = document.getElementById('jk-sr-content');
                            container.innerHTML = '<div class="jk-sr-summary"><h2>Published!</h2><p>' + esc(String(msg)) + '</p><div class="jk-sr-actions" style="justify-content:center"><button class="jk-sr-btn-exit" onclick="jkSRClose()">Back to Board</button></div></div>';
                        } else {
                            var emsg = (r.data && r.data.message) ? r.data.message : (r.data || 'Publish failed');
                            alert(emsg);
                        }
                    } catch(e) { alert('Publish request failed'); }
                };
                x.send('action=jk_editorial_publish&_ajax_nonce=' + nonce);
            });
        };

        function doQueueAjax(blockId, queueAction, onSuccess) {
            var x = new XMLHttpRequest();
            x.open('POST', ajaxurl);
            x.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            x.onload = function() {
                try {
                    var r = JSON.parse(x.responseText);
                    if (r.success) { onSuccess(); }
                    else { alert((r.data && r.data.message) || 'Queue action failed'); }
                } catch(e) { alert('Request failed'); }
            };
            x.send('action=jk_editorial_queue&_ajax_nonce=' + nonce + '&block_id=' + blockId + '&queue_action=' + queueAction);
        }

        function markBlockQueued(blockId, queued) {
            for (var i = 0; i < blocks.length; i++) {
                if (blocks[i].block_id === blockId) {
                    for (var j = 0; j < blocks[i].items.length; j++) {
                        blocks[i].items[j].queued = queued;
                    }
                    break;
                }
            }
        }

        function getQueueCount() {
            var c = 0;
            for (var i = 0; i < blocks.length; i++) {
                if (blocks[i].items.some(function(it){ return it.queued; })) c++;
            }
            return c;
        }

        function esc(s) {
            var d = document.createElement('div');
            d.appendChild(document.createTextNode(s));
            return d.innerHTML;
        }
    })();
    </script>
    <?php
});
