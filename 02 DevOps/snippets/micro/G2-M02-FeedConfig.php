<?php
/**
 * G2-M02 Feed Configuration — Non Sequitur
 *
 * Sprint 1, Gate 2 Micro-snippet 02
 * S1.5 — 40 Starter Feeds Configuration (6 domains)
 * GitLab Issue #12
 *
 * @package NonSequitur
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Return the feed configuration array — 40 feeds across 6 domains
 *
 * @return array Feed config arrays with url, source_name, domain_tag
 */
function ns_get_feed_config() {
    return array(
        // ── AI & Machine Learning (7 feeds) ──
        array( 'url' => 'https://feeds.feedburner.com/oreilly/radar',        'source_name' => "O'Reilly Radar",            'domain_tag' => 'ai' ),
        array( 'url' => 'https://openai.com/blog/rss/',                      'source_name' => 'OpenAI Blog',               'domain_tag' => 'ai' ),
        array( 'url' => 'https://blog.google/technology/ai/rss/',            'source_name' => 'Google AI Blog',             'domain_tag' => 'ai' ),
        array( 'url' => 'https://www.deepmind.com/blog/rss.xml',            'source_name' => 'DeepMind Blog',              'domain_tag' => 'ai' ),
        array( 'url' => 'https://www.technologyreview.com/feed/',            'source_name' => 'MIT Tech Review',            'domain_tag' => 'ai' ),
        array( 'url' => 'https://www.anthropic.com/research/rss',            'source_name' => 'Anthropic Research',         'domain_tag' => 'ai' ),
        array( 'url' => 'https://machinelearningmastery.com/feed/',          'source_name' => 'ML Mastery',                 'domain_tag' => 'ai' ),

        // ── Cybersecurity (7 feeds) ──
        array( 'url' => 'https://krebsonsecurity.com/feed/',                 'source_name' => 'Krebs on Security',          'domain_tag' => 'cyber' ),
        array( 'url' => 'https://www.schneier.com/feed/atom/',               'source_name' => 'Schneier on Security',       'domain_tag' => 'cyber' ),
        array( 'url' => 'https://thehackernews.com/feeds/posts/default',     'source_name' => 'The Hacker News',            'domain_tag' => 'cyber' ),
        array( 'url' => 'https://threatpost.com/feed/',                      'source_name' => 'Threatpost',                 'domain_tag' => 'cyber' ),
        array( 'url' => 'https://www.bleepingcomputer.com/feed/',            'source_name' => 'BleepingComputer',           'domain_tag' => 'cyber' ),
        array( 'url' => 'https://www.darkreading.com/rss.xml',              'source_name' => 'Dark Reading',               'domain_tag' => 'cyber' ),
        array( 'url' => 'https://www.cisa.gov/news.xml',                     'source_name' => 'CISA Alerts',                'domain_tag' => 'cyber' ),

        // ── Innovation & Strategy (7 feeds) ──
        array( 'url' => 'https://hbr.org/feed',                             'source_name' => 'Harvard Business Review',    'domain_tag' => 'innovation' ),
        array( 'url' => 'https://feeds.feedburner.com/fastcompany/headlines','source_name' => 'Fast Company',               'domain_tag' => 'innovation' ),
        array( 'url' => 'https://www.wired.com/feed/rss',                   'source_name' => 'WIRED',                      'domain_tag' => 'innovation' ),
        array( 'url' => 'https://a16z.com/feed/',                           'source_name' => 'a16z',                       'domain_tag' => 'innovation' ),
        array( 'url' => 'https://www.strategyand.pwc.com/gx/en/feeds/all.rss','source_name' => 'Strategy&',               'domain_tag' => 'innovation' ),
        array( 'url' => 'https://singularityhub.com/feed/',                  'source_name' => 'Singularity Hub',            'domain_tag' => 'innovation' ),
        array( 'url' => 'https://feeds.feedburner.com/TEDTalks_video',       'source_name' => 'TED Talks',                  'domain_tag' => 'innovation' ),

        // ── Future of National Security & Warfare (7 feeds) ──
        array( 'url' => 'https://warontherocks.com/feed/',                   'source_name' => 'War on the Rocks',           'domain_tag' => 'fnw' ),
        array( 'url' => 'https://www.defenseone.com/rss/',                   'source_name' => 'Defense One',                'domain_tag' => 'fnw' ),
        array( 'url' => 'https://breakingdefense.com/feed/',                 'source_name' => 'Breaking Defense',           'domain_tag' => 'fnw' ),
        array( 'url' => 'https://www.rand.org/blog.xml',                     'source_name' => 'RAND Corporation',           'domain_tag' => 'fnw' ),
        array( 'url' => 'https://www.csis.org/analysis/feed',                'source_name' => 'CSIS Analysis',              'domain_tag' => 'fnw' ),
        array( 'url' => 'https://www.foreignaffairs.com/rss.xml',            'source_name' => 'Foreign Affairs',            'domain_tag' => 'fnw' ),
        array( 'url' => 'https://mwi.westpoint.edu/feed/',                   'source_name' => 'Modern War Institute',       'domain_tag' => 'fnw' ),

        // ── Space & Aerospace (6 feeds) ──
        array( 'url' => 'https://spacenews.com/feed/',                       'source_name' => 'SpaceNews',                  'domain_tag' => 'space' ),
        array( 'url' => 'https://www.nasaspaceflight.com/feed/',             'source_name' => 'NASASpaceflight',            'domain_tag' => 'space' ),
        array( 'url' => 'https://arstechnica.com/space/feed/',               'source_name' => 'Ars Technica Space',         'domain_tag' => 'space' ),
        array( 'url' => 'https://www.space.com/feeds/all',                   'source_name' => 'Space.com',                  'domain_tag' => 'space' ),
        array( 'url' => 'https://blogs.nasa.gov/feed/',                      'source_name' => 'NASA Blogs',                 'domain_tag' => 'space' ),
        array( 'url' => 'https://aviationweek.com/rss.xml',                  'source_name' => 'Aviation Week',              'domain_tag' => 'space' ),

        // ── Digital Transformation (6 feeds) ──
        array( 'url' => 'https://techcrunch.com/feed/',                      'source_name' => 'TechCrunch',                 'domain_tag' => 'digital' ),
        array( 'url' => 'https://www.theverge.com/rss/index.xml',           'source_name' => 'The Verge',                  'domain_tag' => 'digital' ),
        array( 'url' => 'https://stackoverflow.blog/feed/',                  'source_name' => 'Stack Overflow Blog',        'domain_tag' => 'digital' ),
        array( 'url' => 'https://martinfowler.com/feed.atom',               'source_name' => 'Martin Fowler',              'domain_tag' => 'digital' ),
        array( 'url' => 'https://css-tricks.com/feed/',                      'source_name' => 'CSS-Tricks',                 'domain_tag' => 'digital' ),
        array( 'url' => 'https://www.smashingmagazine.com/feed/',           'source_name' => 'Smashing Magazine',          'domain_tag' => 'digital' ),
    );
}
