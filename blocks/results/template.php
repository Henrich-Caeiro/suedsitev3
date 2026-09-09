<?php
$metrics = $attributes['metrics'] ?? [
  ['value' => '150', 'suffix' => '+', 'label' => 'Projetos entregues', 'prefix' => ''],
  ['value' => '98',  'suffix' => '%', 'label' => 'Clientes satisfeitos', 'prefix' => ''],
  ['value' => '3.2', 'suffix' => '×', 'label' => 'ROI médio em mídia paga', 'prefix' => ''],
  ['value' => '47',  'suffix' => '%', 'label' => 'Aumento médio em conversão', 'prefix' => ''],
];
?>
<section
  <?php echo get_block_wrapper_attributes(['class' => 'sued-results sued-section']); ?>
  id="results"
  style="background: var(--sued-mid);"
>
  <div class="sued-container">
    <div class="sued-text-center sued-results__header">
      <span class="sued-eyebrow" data-reveal><?php esc_html_e('Resultados', 'sued-studio'); ?></span>
      <h2 data-reveal data-reveal-delay="1"><?php esc_html_e('Números que provam a estratégia', 'sued-studio'); ?></h2>
    </div>

    <div class="sued-results__grid">
      <?php foreach ($metrics as $i => $m) : ?>
      <div class="sued-result-stat" data-reveal data-reveal-delay="<?php echo $i + 1; ?>">
        <div class="sued-result-stat__number">
          <span class="sued-result-stat__prefix"><?php echo esc_html($m['prefix']); ?></span>
          <span
            class="sued-result-stat__value sued-counter"
            data-target="<?php echo esc_attr($m['value']); ?>"
            data-decimals="<?php echo str_contains($m['value'], '.') ? '1' : '0'; ?>"
          >0</span>
          <span class="sued-result-stat__suffix"><?php echo esc_html($m['suffix']); ?></span>
        </div>
        <p class="sued-result-stat__label"><?php echo esc_html($m['label']); ?></p>
        <div class="sued-result-stat__bar" aria-hidden="true">
          <div class="sued-result-stat__bar-fill"></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Decorative grid -->
  <div class="sued-results__grid-deco" aria-hidden="true"></div>
</section>
