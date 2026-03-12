<?php
/**
 * Snippet ID: 109
 * Name: S3.5-W2-M09b Markdown Styles — Cards
 * Scope: front-end
 * Active: True
 * Sprint: 3.6 — UI Drift Fixes
 * Deployed: 2026-03-12
 */

/**
 * S3.5-W2-M09b Markdown Styles — Cards
 * UPDATED: Sprint 3.6 — D-05 per-category accent colors, prototype font integration
 *
 * Card, commentary, PUSH button, and block styles.
 * Uses design-token CSS variables from M09a.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_head', function() {
    if ( ! is_page( 1077 ) ) return;
    ?>
    <style id="md-cards-css">
    /* BASE BLOCK STYLES (default accent = blue) */
    .md-block{background:var(--bg-card,#1E2229);border:1px solid var(--border-subtle,#2D3340);padding:20px;transition:border-color .2s}
    .md-block:hover{border-color:#444}
    .md-block-header{display:flex;justify-content:space-between;align-items:baseline;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid var(--border-subtle,#2D3340)}
    .md-block-num{font-size:.85rem;font-weight:700;color:var(--block-accent,var(--accent-blue,#3B82F6));font-family:var(--font-mono,monospace);margin-right:8px}
    .md-block-domain{font-size:.7rem;letter-spacing:.2em;color:var(--block-accent,var(--accent-blue,#3B82F6));text-transform:uppercase;font-weight:600;font-family:var(--font-mono,monospace)}
    .md-block-section{font-size:.6rem;letter-spacing:.2em;color:var(--text-dim,#6B7280);text-transform:uppercase;font-family:var(--font-mono,monospace)}

    /* D-05: PER-CATEGORY ACCENT COLORS */
    .md-block[data-domain="ai"]{--block-accent:var(--accent-ai,#06B6D4)}
    .md-block[data-domain="cyber"]{--block-accent:var(--accent-cyber,#00FF88)}
    .md-block[data-domain="innovation"]{--block-accent:var(--accent-innovation,#FACC15)}
    .md-block[data-domain="fnw"]{--block-accent:var(--accent-fnw,#C084FC)}
    .md-block[data-domain="space"]{--block-accent:var(--accent-space,#FF6B35)}
    .md-block[data-domain="digital"]{--block-accent:var(--accent-digital,#14B8A6)}

    /* Fallback: class-based selectors for blocks without data attributes */
    .md-block-ai{--block-accent:var(--accent-ai,#06B6D4)}
    .md-block-cyber{--block-accent:var(--accent-cyber,#00FF88)}
    .md-block-innovation{--block-accent:var(--accent-innovation,#FACC15)}
    .md-block-fnw{--block-accent:var(--accent-fnw,#C084FC)}
    .md-block-space{--block-accent:var(--accent-space,#FF6B35)}
    .md-block-digital{--block-accent:var(--accent-digital,#14B8A6)}

    /* Block 00 (lead story) uses primary blue */
    .md-block-lead{--block-accent:var(--accent-blue,#3B82F6)}

    /* PUSH BUTTONS — inherit block accent */
    .md-push{display:flex;gap:6px;flex-wrap:wrap;margin:8px 0}
    .md-push a{font-size:.55rem;letter-spacing:.12em;color:var(--block-accent,var(--accent-blue,#3B82F6));text-transform:uppercase;text-decoration:none;padding:3px 8px;border:1px solid var(--block-accent,var(--accent-blue,#3B82F6));border-radius:2px;font-family:var(--font-mono,monospace);transition:background .2s,color .2s}
    .md-push a:hover{background:var(--block-accent,var(--accent-blue,#3B82F6));color:var(--bg-primary,#1A1D23)}

    /* CARD CONTENT */
    .md-card-title{font-size:1rem;font-weight:700;color:var(--text-primary,#E8ECF1);line-height:1.3;margin:0 0 8px;font-family:var(--font-body,'Source Sans Pro',sans-serif)}
    .md-card-excerpt{font-size:.8rem;color:var(--text-secondary,#9CA3AF);line-height:1.5;margin:0 0 8px;font-family:var(--font-body,'Source Sans Pro',sans-serif)}
    .md-card-source{font-size:.65rem;color:var(--text-dim,#6B7280);text-transform:uppercase;letter-spacing:.1em;font-family:var(--font-mono,monospace)}
    .md-card-sep{border:none;border-top:1px solid var(--border-subtle,#2D3340);margin:12px 0}
    .md-block .md-card-item{margin-bottom:14px}
    .md-block .md-card-item:last-child{margin-bottom:0}
    .md-card-dots{text-align:center;color:var(--border-subtle,#2D3340);letter-spacing:4px;font-size:.8rem;margin:10px 0}

    /* COMMENTARY */
    .md-commentary{background:var(--bg-secondary,#22262E);border:1px solid var(--border-subtle,#2D3340);border-radius:8px;padding:16px;margin:14px 0}
    .md-commentary-header{display:flex;align-items:center;gap:10px;margin-bottom:10px}
    .md-commentary-avatar{width:36px;height:36px;border-radius:50%;background:var(--bg-primary,#1A1D23);display:flex;align-items:center;justify-content:center;color:var(--block-accent,var(--accent-blue,#3B82F6));font-weight:700;font-size:.75rem;flex-shrink:0;font-family:var(--font-mono,monospace)}
    .md-commentary-name{font-size:.8rem;font-weight:600;color:var(--text-primary,#E8ECF1);font-family:var(--font-body,'Source Sans Pro',sans-serif)}
    .md-commentary-handle{font-size:.65rem;color:var(--text-dim,#6B7280);font-family:var(--font-mono,monospace)}
    .md-commentary-icon{margin-left:auto;color:var(--text-dim,#6B7280);font-size:.8rem}
    .md-commentary-text{font-size:.8rem;color:var(--text-secondary,#9CA3AF);line-height:1.5;font-style:italic;margin-bottom:10px;font-family:var(--font-body,'Source Sans Pro',sans-serif)}
    .md-commentary-footer{display:flex;justify-content:space-between;align-items:center;font-size:.6rem;color:var(--text-dim,#6B7280);font-family:var(--font-mono,monospace)}
    .md-commentary-footer a{color:var(--block-accent,var(--accent-blue,#3B82F6));text-decoration:none;letter-spacing:.1em;text-transform:uppercase;font-weight:600}

    /* EMPTY STATE */
    .md-empty{color:var(--text-dim,#6B7280);font-style:italic;font-size:.8rem;text-align:center;padding:20px 0;font-family:var(--font-body,'Source Sans Pro',sans-serif)}
    </style>
    <?php
}, 5 );