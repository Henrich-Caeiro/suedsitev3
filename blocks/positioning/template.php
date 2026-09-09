<?php
$title  = $attributes['title']  ?? 'Parceiro estratégico, não fornecedor.';
$text1  = $attributes['text1']  ?? 'A maioria das agências entrega projetos. Nós entregamos crescimento.';
$text2  = $attributes['text2']  ?? 'Nossa abordagem começa antes do design — com análise, dados e estratégia. O resultado é um ativo que trabalha por você 24h.';
$stat1v = $attributes['stat1Value'] ?? '98%';
$stat1l = $attributes['stat1Label'] ?? 'Taxa de satisfação';
$stat2v = $attributes['stat2Value'] ?? '3×';
$stat2l = $attributes['stat2Label'] ?? 'Crescimento médio';
$stat3v = $attributes['stat3Value'] ?? '8+';
$stat3l = $attributes['stat3Label'] ?? 'Anos de mercado';
?>
<section
  <?php echo get_block_wrapper_attributes(['class' => 'sued-positioning sued-section']); ?>
  id="positioning"
>
  <!-- Global canvas used instead -->

  <div class="sued-container">
    <div class="sued-positioning__grid">

      <div class="sued-positioning__text">
        <span class="sued-eyebrow" data-reveal><?php esc_html_e('Nossa Visão', 'sued-studio'); ?></span>
        <h2 class="sued-positioning__title" data-reveal data-reveal-delay="1">
          <?php echo esc_html($title); ?>
        </h2>
        <div class="sued-divider" data-reveal data-reveal-delay="2"></div>
        <p class="sued-positioning__p1" data-reveal data-reveal-delay="2"><?php echo esc_html($text1); ?></p>
        <p class="sued-positioning__p2" data-reveal data-reveal-delay="3"><?php echo esc_html($text2); ?></p>
      </div>

      <div class="sued-positioning__stats">
        <div class="sued-positioning__stat sued-glass" data-reveal="right">
          <span class="sued-positioning__stat-value"><?php echo esc_html($stat1v); ?></span>
          <span class="sued-positioning__stat-label"><?php echo esc_html($stat1l); ?></span>
        </div>
        <div class="sued-positioning__stat sued-glass" data-reveal="right" data-reveal-delay="1">
          <span class="sued-positioning__stat-value"><?php echo esc_html($stat2v); ?></span>
          <span class="sued-positioning__stat-label"><?php echo esc_html($stat2l); ?></span>
        </div>
        <div class="sued-positioning__stat sued-glass" data-reveal="right" data-reveal-delay="2">
          <span class="sued-positioning__stat-value"><?php echo esc_html($stat3v); ?></span>
          <span class="sued-positioning__stat-label"><?php echo esc_html($stat3l); ?></span>
        </div>
      </div>

    </div>
  </div>
</section>

