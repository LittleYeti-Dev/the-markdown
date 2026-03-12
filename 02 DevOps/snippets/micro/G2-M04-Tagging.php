<?php
/**
 * G2-M04 Auto-Domain Tagging — Non Sequitur
 *
 * Sprint 1, Gate 2 Micro-snippet 04
 * S1.6 — Auto-Domain Tagging (keyword rules assign content_domain taxonomy)
 * GitLab Issue #13
 *
 * @package NonSequitur
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Auto-assign content_domain taxonomy term based on domain_tag and keywords
 *
 * @param int    $post_id     Post ID
 * @param string $domain_tag  Primary domain from feed config
 * @param string $title       Article title
 * @param string $content     Article content
 */
function ns_auto_tag_domain( $post_id, $domain_tag, $title, $content ) {
    $text = strtolower( $title . ' ' . $content );

    // Domain tag to taxonomy term mapping
    $tag_to_term = array(
        'ai'         => 'AI & Machine Learning',
        'cyber'      => 'Cybersecurity',
        'innovation' => 'Innovation & Strategy',
        'fnw'        => 'Future of National Security & Warfare',
        'space'      => 'Space & Aerospace',
        'digital'    => 'Digital Transformation',
    );

    // Primary domain from feed config
    $primary_term = isset( $tag_to_term[ $domain_tag ] ) ? $tag_to_term[ $domain_tag ] : '';
    $terms = array();

    if ( ! empty( $primary_term ) ) {
        $terms[] = $primary_term;
    }

    // Cross-domain keyword detection — may add secondary domains
    $keyword_rules = array(
        'ai' => array(
            'artificial intelligence', 'machine learning', 'deep learning', 'neural network',
            'large language model', 'llm', 'generative ai', 'chatgpt', 'claude', 'gpt-4',
            'transformer', 'computer vision', 'natural language processing', 'nlp',
        ),
        'cyber' => array(
            'cybersecurity', 'zero-day', 'ransomware', 'phishing', 'vulnerability',
            'threat actor', 'malware', 'data breach', 'apt', 'exploit', 'cve-',
            'incident response', 'soc ', 'penetration test',
        ),
        'innovation' => array(
            'disruption', 'startup', 'venture capital', 'innovation', 'strategy',
            'digital twin', 'blockchain', 'quantum computing', 'metaverse',
        ),
        'fnw' => array(
            'national security', 'defense', 'warfare', 'military', 'pentagon',
            'nato', 'deterrence', 'autonomous weapons', 'hypersonic', 'indo-pacific',
            'intelligence community', 'dod ', 'joint force',
        ),
        'space' => array(
            'space', 'satellite', 'orbit', 'launch', 'nasa', 'spacex', 'artemis',
            'lunar', 'mars', 'iss', 'starlink', 'aerospace',
        ),
        'digital' => array(
            'cloud computing', 'devops', 'api', 'microservices', 'kubernetes',
            'digital transformation', 'saas', 'platform engineering', 'low-code',
        ),
    );

    foreach ( $keyword_rules as $tag => $keywords ) {
        if ( $tag === $domain_tag ) {
            continue;
        }
        foreach ( $keywords as $keyword ) {
            if ( strpos( $text, $keyword ) !== false ) {
                $terms[] = $tag_to_term[ $tag ];
                break;
            }
        }
    }

    // Assign taxonomy terms
    if ( ! empty( $terms ) ) {
        wp_set_object_terms( $post_id, array_unique( $terms ), 'content_domain' );
    }

    // Also attempt thematic arc auto-tagging
    ns_auto_tag_arc( $post_id, $text );
}

/**
 * Auto-assign thematic_arc taxonomy based on content keywords
 */
function ns_auto_tag_arc( $post_id, $text ) {
    $arc_keywords = array(
        'Convergence'    => array(
            'convergence', 'cross-domain', 'interdisciplinary', 'hybrid',
            'fusion', 'integrated', 'multi-domain', 'combined',
        ),
        'Disruption'     => array(
            'disruption', 'disruptive', 'breakthrough', 'paradigm shift',
            'revolution', 'transform', 'game-changer', 'unprecedented',
        ),
        'Human Element'  => array(
            'human', 'workforce', 'talent', 'ethics', 'bias', 'trust',
            'decision-making', 'cognitive', 'human-machine', 'augmentation',
        ),
    );

    $arcs = array();
    foreach ( $arc_keywords as $arc => $keywords ) {
        foreach ( $keywords as $keyword ) {
            if ( strpos( $text, $keyword ) !== false ) {
                $arcs[] = $arc;
                break;
            }
        }
    }

    if ( ! empty( $arcs ) ) {
        wp_set_object_terms( $post_id, $arcs, 'thematic_arc' );
    }
}
