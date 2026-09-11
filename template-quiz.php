<?php
/**
 * Template Name: Quiz Diagnóstico
 * Template Post Type: page
 *
 * @package SUED_Studio
 */

defined('ABSPATH') || exit;

// Enqueue styles & scripts
wp_enqueue_style('sued-quiz-css', SUED_URI . '/assets/css/quiz.css', [], SUED_VERSION);
wp_enqueue_script('sued-quiz-js',  SUED_URI . '/assets/js/quiz.js', [], SUED_VERSION, ['in_footer' => true]);

wp_localize_script('sued-quiz-js', 'SUED_QUIZ_CONFIG', [
    'restUrl'     => esc_url_raw(rest_url('sued/v1/quiz-submit')),
    'nonce'       => wp_create_nonce('wp_rest'),
    'calendlyUrl' => 'https://calendly.com/suedstudio/30min',
    'whatsappNum' => '+5516999943952',
    'agencyName'  => 'SUED Studio',
    'siteUrl'     => home_url('/'),
]);
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php esc_html_e('Diagnóstico Digital Gratuito — SUED Studio', 'sued-studio'); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class('sued-quiz-page'); ?>>
<?php wp_body_open(); ?>

<div class="sued-quiz-wrapper">
  <div class="sued-quiz-glow" aria-hidden="true"></div>
  
  <div class="sued-quiz-container">
    <div class="sued-quiz-header">
      <a href="<?php echo esc_url(home_url('/')); ?>" class="sued-quiz-logo">
        SUED<span>.</span>Studio
      </a>
      <div class="sued-quiz-badge"><?php esc_html_e('Diagnóstico Gratuito', 'sued-studio'); ?></div>
    </div>

    <div id="sued-quiz-app">
      <noscript>
        <div style="text-align:center; padding: 40px; color: #F5F2EC;">
          <p>O diagnóstico interativo requer JavaScript habilitado no seu navegador.</p>
        </div>
      </noscript>
    </div>
  </div>
</div>

<?php wp_footer(); ?>
</body>
</html>
