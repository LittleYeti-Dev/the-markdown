<?php
/**
 * Snippet ID: 108
 * Name: S3.5-W2-M09a Markdown Styles — Layout
 * Scope: front-end
 * Active: True
 * Sprint: 3.6 — UI Drift Fixes
 * Deployed: 2026-03-12
 */

/**
 * S3.5-W2-M09a Markdown Styles — Layout
 * UPDATED: Sprint 3.6 — D-01 full-width breakout, prototype font import
 */
if ( ! defined( 'ABSPATH' ) ) exit;
add_action( 'wp_head', function() {
    if ( ! is_page( 1077 ) ) return;
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;600;700&family=Source+Sans+Pro:wght@400;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style id="md-layout-css">
    /* S3.6 D-01: FULL-WIDTH BREAKOUT */
    body.page-id-1077 .entry-content{max-width:none!important;width:100%!important;padding:0!important;margin:0!important}
    body.page-id-1077 .entry-content>#the-markdown-editorial{max-width:none!important;width:100%!important}
    body.page-id-1077 .site-main{max-width:none!important;padding:0!important}
    body.page-id-1077 .content-area{max-width:none!important;padding:0!important}
    body.page-id-1077 #content{max-width:none!important}
    body.page-id-1077 .entry-header{display:none}
    /* DESIGN TOKENS (matched to prototype) */
    #the-markdown-editorial{
      --bg-primary:#1A1D23;--bg-secondary:#22262E;--bg-card:#1E2229;
      --accent-blue:#3B82F6;--accent-ai:#06B6D4;--accent-cyber:#00FF88;
      --accent-innovation:#FACC15;--accent-fnw:#C084FC;--accent-space:#FF6B35;
      --accent-digital:#14B8A6;--text-primary:#E8ECF1;--text-secondary:#9CA3AF;
      --text-dim:#6B7280;--border-subtle:#2D3340;
      --font-display:'Rajdhani',sans-serif;--font-body:'Source Sans Pro',sans-serif;
      --font-mono:'JetBrains Mono',monospace;
      background:var(--bg-primary)!important;color:var(--text-primary)!important;
      font-family:var(--font-body)!important;font-size:15px;line-height:1.5;padding-bottom:0!important
    }
    .md-wrap{max-width:1400px;margin:0 auto;padding:0 24px;box-sizing:border-box}
    .md-masthead{background:linear-gradient(180deg,#0D0F13 0%,var(--bg-primary) 100%);border-bottom:2px solid var(--accent-blue);padding:20px 0 16px;text-align:center;position:relative}
    .md-masthead::after{content:'';position:absolute;bottom:-2px;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--accent-blue),transparent);box-shadow:0 0 20px rgba(59,130,246,0.15)}
    .md-wordmark{font-family:var(--font-display)!important;font-size:clamp(2.5rem,5vw,3.5rem);font-weight:700;letter-spacing:8px;text-transform:uppercase;color:var(--text-primary);margin:0;line-height:1}
    .md-wordmark .md-accent{color:var(--accent-blue)}
    .md-tagline{font-family:var(--font-mono)!important;font-size:11px;letter-spacing:4px;color:var(--text-dim);text-transform:uppercase;margin:6px 0}
    .md-datetime{font-family:var(--font-mono)!important;font-size:11px;letter-spacing:2px;color:var(--text-secondary);text-transform:uppercase;margin:4px 0}
    .md-updated{font-family:var(--font-mono)!important;font-size:10px;color:var(--text-dim);letter-spacing:2px;text-transform:uppercase}
    .md-edition{background:linear-gradient(90deg,transparent,var(--border-subtle),transparent);text-align:center;padding:10px 0;margin:10px 0 0}
    .md-edition span{font-family:var(--font-mono)!important;font-size:12px;letter-spacing:4px;color:var(--accent-blue);text-transform:uppercase;font-weight:600}
    .md-domainnav{text-align:center;padding:12px 0;border-bottom:1px solid var(--border-subtle)}
    .md-domainnav a{color:var(--text-secondary);text-decoration:none;font-family:var(--font-mono)!important;font-size:11px;letter-spacing:2px;text-transform:uppercase;margin:0 10px;transition:color .2s}
    .md-domainnav a:hover{color:var(--accent-blue)}
    .md-hero{padding:30px 0;text-align:center;border-bottom:1px solid var(--border-subtle)}
    .md-hero-badge{display:inline-block;font-family:var(--font-mono)!important;font-size:11px;letter-spacing:3px;color:var(--accent-blue);text-transform:uppercase;margin-bottom:8px;padding:4px 12px;border:1px solid var(--accent-blue);border-radius:2px}
    .md-hero-headline{font-family:var(--font-display)!important;font-size:clamp(1.5rem,3.5vw,2.4rem);font-weight:700;text-transform:uppercase;color:var(--text-primary);line-height:1.15;margin:12px auto;max-width:900px}
    .md-hero-img{width:100%;max-width:700px;height:380px;background:var(--bg-secondary);border:1px solid var(--border-subtle);display:flex;align-items:center;justify-content:center;color:var(--text-dim);font-family:var(--font-mono)!important;font-size:12px;letter-spacing:2px;text-transform:uppercase;margin:20px auto}
    .md-hero-excerpt{color:var(--text-secondary);font-size:.95rem;line-height:1.6;max-width:750px;margin:12px auto}
    .md-hero-source{font-family:var(--font-mono)!important;font-size:11px;color:var(--text-dim);text-transform:uppercase;letter-spacing:2px}
    .md-statusbar{display:flex;justify-content:center;gap:30px;padding:10px 20px;border-top:1px solid var(--border-subtle);border-bottom:1px solid var(--border-subtle);flex-wrap:wrap}
    .md-statusbar span{font-family:var(--font-mono)!important;font-size:11px;letter-spacing:2px;color:var(--text-secondary);text-transform:uppercase}
    .md-statusbar .md-stat-dot{margin-right:4px}
    .md-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;padding:30px 0}
    @media(max-width:768px){.md-grid{grid-template-columns:1fr}.md-hero-headline{font-size:1.3rem}.md-hero-img{height:200px}.md-statusbar{flex-direction:column;align-items:center;gap:6px}.md-domainnav a{margin:0 5px;font-size:10px}}
    </style>
    <?php
}, 5 );