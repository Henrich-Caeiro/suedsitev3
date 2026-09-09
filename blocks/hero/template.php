<?php
$headline    = $attributes['headline']    ?? 'Estratégia que<br>gera resultado.';
$subheadline = $attributes['subheadline'] ?? 'Não entregamos sites bonitos — entregamos ferramentas que convertem com propósito.';
$cta_label   = $attributes['ctaLabel']   ?? 'Conheça nossa abordagem';
$cta_url     = $attributes['ctaUrl']     ?? '#positioning';
$cta_sec_label = $attributes['ctaSecLabel'] ?? 'Ver nossos projetos';
$cta_sec_url   = $attributes['ctaSecUrl']   ?? '#results';
?>
<section
  <?php echo get_block_wrapper_attributes(['class' => 'sued-hero sued-noise']); ?>
  id="hero"
  aria-label="<?php esc_attr_e('Hero', 'sued-studio'); ?>"
>
  <!-- Global canvas used instead -->

  <!-- Radial accent glow -->
  <div class="sued-hero__glow" aria-hidden="true"></div>

  <div class="sued-container sued-hero__inner">

    <div class="sued-hero__badge" data-reveal>
      <span class="sued-eyebrow"><?php esc_html_e('SUED Studio', 'sued-studio'); ?></span>
    </div>

    <h1 class="sued-hero__headline" data-reveal data-reveal-delay="1">
      <?php echo wp_kses_post(str_replace('\n', '<br>', $headline)); ?>
    </h1>

    <p class="sued-hero__sub" data-reveal data-reveal-delay="2">
      <?php echo esc_html($subheadline); ?>
    </p>

    <div class="sued-hero__actions" data-reveal data-reveal-delay="3">
      <a href="<?php echo esc_url($cta_url); ?>" class="sued-btn sued-btn--primary sued-btn--lg sued-ripple-btn">
        <?php echo esc_html($cta_label); ?>
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
          <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </a>
      <a href="<?php echo esc_url($cta_sec_url); ?>" class="sued-btn sued-btn--outline sued-btn--lg">
        <?php echo esc_html($cta_sec_label); ?>
      </a>
    </div>

    <!-- Scroll indicator -->
    <div class="sued-hero__scroll" aria-hidden="true" data-reveal data-reveal-delay="4">
      <div class="sued-hero__scroll-line"></div>
      <span><?php esc_html_e('Scroll', 'sued-studio'); ?></span>
    </div>

  </div>
</section>
