<?php
/**
 * SUED Studio Theme — functions.php
 */
defined('ABSPATH') || exit;

define('SUED_VERSION', '1.6.1');
define('SUED_DIR',     get_template_directory());
define('SUED_URI',     get_template_directory_uri());

/* ─── Contact Form (leads DB + AJAX + admin page) ───────────── */
require_once SUED_DIR . '/inc/contact-form.php';

/* ─── Interactive Quiz (DB + REST API + Admin CRM) ──────────── */
require_once SUED_DIR . '/inc/quiz-handler.php';

/* ─── Theme Setup ────────────────────────────────────────────── */
add_action('after_setup_theme', function () {
    add_theme_support('wp-block-styles');
    add_theme_support('editor-styles');
    add_theme_support('responsive-embeds');
    add_theme_support('custom-logo');
    add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','style','script']);
    add_editor_style('assets/css/editor.css');
    load_theme_textdomain('sued-studio', SUED_DIR . '/languages');
});

/* ─── Block Category (Inserter) ─────────────────────────────── */
add_filter('block_categories_all', function (array $cats): array {
    array_unshift($cats, [
        'slug'  => 'sued-studio',
        'title' => __('SUED Studio', 'sued-studio'),
        'icon'  => null,
    ]);
    return $cats;
});

/* ─── Custom Blocks + Patterns (single init hook, ordered) ───── */
add_action('init', function () {

    // 1. Register block types first
    $blocks = ['hero', 'positioning', 'process', 'services', 'results', 'differential', 'cta', 'quiz'];
    foreach ($blocks as $block) {
        $dir = SUED_DIR . "/blocks/{$block}";
        if (file_exists("{$dir}/block.json")) {
            register_block_type($dir);
        }
    }

    // 2. Register pattern category
    register_block_pattern_category('sued-studio', [
        'label' => __('SUED Studio', 'sued-studio'),
    ]);

    // 3. Register patterns (after blocks are known)
    register_block_pattern('sued-studio/home-page', [
        'title'       => __('Home Page — Full', 'sued-studio'),
        'description' => __('Complete SUED Studio home page with all sections.', 'sued-studio'),
        'categories'  => ['sued-studio'],
        'content'     => '<!-- wp:sued-studio/hero {"align":"full"} /-->
<!-- wp:sued-studio/positioning {"align":"full"} /-->
<!-- wp:sued-studio/process {"align":"full"} /-->
<!-- wp:sued-studio/services {"align":"full"} /-->
<!-- wp:sued-studio/results {"align":"full"} /-->
<!-- wp:sued-studio/differential {"align":"full"} /-->
<!-- wp:sued-studio/cta {"align":"full"} /-->',
    ]);

}, 10);

/* ─── Enqueue Frontend Assets ────────────────────────────────── */
add_action('wp_enqueue_scripts', function () {

    // Google Fonts — Open Sans only (Montserrat is self-hosted via @font-face in global.css)
    wp_enqueue_style(
        'sued-fonts',
        'https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700&display=swap',
        [],
        null
    );

    // CSS
    wp_enqueue_style('sued-global',  SUED_URI . '/assets/css/global.css',  ['sued-fonts'], SUED_VERSION);
    wp_enqueue_style('sued-header',  SUED_URI . '/assets/css/header.css',  ['sued-global'], SUED_VERSION);
    wp_enqueue_style('sued-blocks',  SUED_URI . '/assets/css/blocks.css',  ['sued-global'], SUED_VERSION);

    // Three.js r160 — local UMD build, exposes global THREE
    // (r165 removed UMD build; CDN returned 404)
    wp_enqueue_script(
        'three-js',
        SUED_URI . '/assets/js/vendor/three.min.js',
        [],
        '0.160.0',
        ['in_footer' => true]
    );

    // GSAP + ScrollTrigger
    wp_enqueue_script('gsap',
        'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js',
        [], '3.12.5', ['strategy' => 'defer', 'in_footer' => true]
    );
    wp_enqueue_script('gsap-scroll-trigger',
        'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js',
        ['gsap'], '3.12.5', ['strategy' => 'defer', 'in_footer' => true]
    );

    // Global 3D Engine — Morphing continuous canvas
    wp_enqueue_script(
        'sued-3d-engine',
        SUED_URI . '/assets/js/sued-3d-engine.js',
        ['three-js', 'gsap', 'gsap-scroll-trigger'],
        SUED_VERSION,
        ['in_footer' => true]
    );

    // Main JS
    wp_enqueue_script(
        'sued-main',
        SUED_URI . '/assets/js/main.js',
        ['gsap', 'gsap-scroll-trigger'],
        SUED_VERSION,
        ['strategy' => 'defer', 'in_footer' => true]
    );

    wp_localize_script('sued-main', 'SUED', [
        'themeUri' => SUED_URI,
        'ajaxUrl'  => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('sued_nonce'),
    ]);
});

/* --- Block Editor JS registration -------------------------------- */
add_action('enqueue_block_editor_assets', function () {
    wp_enqueue_script(
        'sued-blocks-editor',
        SUED_URI . '/assets/js/blocks-editor.js',
        ['wp-blocks', 'wp-element'],
        SUED_VERSION,
        true
    );
});

/* --- Navigation Menus -------------------------------------------- */
add_action('after_setup_theme', function () {
    register_nav_menus(['primary' => __('Primary Navigation', 'sued-studio')]);
});

/* ─── Remove Emoji Scripts ───────────────────────────────────── */
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
