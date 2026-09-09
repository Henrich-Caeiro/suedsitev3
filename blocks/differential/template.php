<?php
$sentences = $attributes['sentences'] ?? [
  ['text' => 'Não somos uma agência de execução.', 'highlight' => false],
  ['text' => 'Somos um parceiro de crescimento', 'highlight' => true],
  ['text' => 'que pensa junto com você.', 'highlight' => false],
  ['text' => 'Cada projeto começa com uma pergunta:', 'highlight' => false],
  ['text' => '"Isso vai gerar resultado?"', 'highlight' => true],
];
$cta_label = $attributes['ctaLabel'] ?? 'Quero um parceiro, não um fornecedor';
$cta_url   = $attributes['ctaUrl']   ?? '#contact';
?>
<section
  <?php echo get_block_wrapper_attributes(['class' => 'sued-differential sued-section sued-noise']); ?>
  id="differential"
>
  <div class="sued-differential__bg-text" aria-hidden="true">SUED</div>

  <div class="sued-container">
    <span class="sued-eyebrow" data-reveal><?php esc_html_e('Diferencial', 'sued-studio'); ?></span>

    <div class="sued-differential__text-block">
      <?php foreach ($sentences as $i => $s) : ?>
        <?php if ($s['highlight']) : ?>
          <span
            class="sued-differential__sentence sued-differential__sentence--highlight"
            data-reveal
            data-reveal-delay="<?php echo $i + 1; ?>"
          ><?php echo esc_html($s['text']); ?></span>
        <?php else : ?>
          <span
            class="sued-differential__sentence"
            data-reveal
            data-reveal-delay="<?php echo $i + 1; ?>"
          ><?php echo esc_html($s['text']); ?></span>
        <?php endif; ?>
        <?php echo $i < count($sentences) - 1 ? ' ' : ''; ?>
      <?php endforeach; ?>
    </div>

    <div class="sued-differential__action" data-reveal data-reveal-delay="<?php echo count($sentences) + 1; ?>">
      <a href="<?php echo esc_url($cta_url); ?>" class="sued-btn sued-btn--primary sued-ripple-btn">
        <?php echo esc_html($cta_label); ?>
      </a>
    </div>
  </div>
</section>
