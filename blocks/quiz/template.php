<?php
/**
 * Dynamic block template for sued-studio/quiz
 *
 * @package SUED_Studio
 */

defined('ABSPATH') || exit;

// Enqueue quiz CSS and JS
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
<div <?php echo get_block_wrapper_attributes(['class' => 'sued-quiz-section']); ?>>
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
</div>
