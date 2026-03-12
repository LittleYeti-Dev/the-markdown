<?php
/**
 * G2-M07b5 Prompt Builder — Non Sequitur
 *
 * Micro-snippet 07b5 — Build scoring prompt template
 * S1.9 — GitLab Issue #16
 *
 * @package NonSequitur
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Build the scoring prompt for Claude
 *
 * @param string $title   Sanitized article title
 * @param string $source  Source name
 * @param string $domain  Domain tag
 * @param string $content Sanitized content excerpt
 * @return string Complete prompt
 */
function ns_build_scoring_prompt( $title, $source, $domain, $content ) {
    $tpl  = 'You are a content relevance scorer for Non Sequitur, ';
    $tpl .= 'a curated intelligence feed covering AI, cybersecurity, ';
    $tpl .= 'innovation, national security, space, and digital transformation.';
    $tpl .= "\n\n" . 'Score this article on two dimensions (1-10 each):' . "\n\n";
    $tpl .= '1. RELEVANCE SCORE: How relevant is this to professionals ';
    $tpl .= 'interested in the intersection of technology, security, ';
    $tpl .= 'and strategy? Consider: novelty, depth of analysis, ';
    $tpl .= 'actionable insights, credibility of source.' . "\n\n";
    $tpl .= '2. ARC SCORE: How well does this fit one of our three ';
    $tpl .= 'thematic arcs?' . "\n";
    $tpl .= '   - Convergence: Where multiple domains intersect' . "\n";
    $tpl .= '   - Disruption: Breakthrough technologies or paradigm shifts' . "\n";
    $tpl .= '   - Human Element: Ethics, workforce impact, human-machine teaming';
    $tpl .= "\n\n" . 'Article:' . "\n";
    $tpl .= '- Title: %s' . "\n" . '- Source: %s' . "\n";
    $tpl .= '- Domain: %s' . "\n" . '- Content excerpt: %s' . "\n\n";
    $tpl .= 'Respond in EXACTLY this JSON format, nothing else:' . "\n";
    $tpl .= '{"relevance_score": N, "arc_score": N, ';
    $tpl .= '"summary": "2-3 sentence summary", ';
    $tpl .= '"primary_arc": "Convergence|Disruption|Human Element|None"}';

    return sprintf( $tpl, $title, $source, $domain, $content );
}
