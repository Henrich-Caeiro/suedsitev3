<?php
$steps = $attributes['steps'] ?? [
  ['number' => '01', 'title' => 'Entendimento', 'desc' => 'Mergulhamos fundo no seu negócio, público e mercado. Sem atalhos.'],
  ['number' => '02', 'title' => 'Estratégia',   'desc' => 'Desenhamos o caminho com dados, não feeling. Cada decisão tem razão de ser.'],
  ['number' => '03', 'title' => 'Execução',     'desc' => 'Desenvolvemos com precisão técnica e atenção ao detalhe que gera resultado.'],
  ['number' => '04', 'title' => 'Crescimento',  'desc' => 'Monitoramos, otimizamos e escalamos. O trabalho não termina no lançamento.'],
];
?>
<section
  <?php echo get_block_wrapper_attributes(['class' => 'sued-process sued-section']); ?>
  id="process"
>
  <div class="sued-container">
    <div class="sued-text-center sued-process__header">
      <span class="sued-eyebrow" data-reveal><?php esc_html_e('Método', 'sued-studio'); ?></span>
      <h2 data-reveal data-reveal-delay="1"><?php esc_html_e('Como trabalhamos', 'sued-studio'); ?></h2>
      <p class="sued-process__subtitle" data-reveal data-reveal-delay="2">
        <?php esc_html_e('Um processo que elimina incerteza e gera resultados previsíveis.', 'sued-studio'); ?>
      </p>
    </div>

    <div class="sued-process__timeline">
      <div class="sued-process__line" aria-hidden="true">
        <div class="sued-process__line-fill"></div>
      </div>

      <?php foreach ($steps as $i => $step) :
        $delay = $i + 1;
      ?>
      <div class="sued-process__step" data-step="<?php echo $i; ?>" data-reveal data-reveal-delay="<?php echo $delay; ?>">
        <div class="sued-process__step-num"><?php echo esc_html($step['number']); ?></div>
        <div class="sued-process__step-content sued-glass">
          <h3 class="sued-process__step-title"><?php echo esc_html($step['title']); ?></h3>
          <p class="sued-process__step-desc"><?php echo esc_html($step['desc']); ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
