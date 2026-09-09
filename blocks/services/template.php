<?php
$services = $attributes['services'] ?? [
  [
    'icon'  => '<svg width="32" height="32" viewBox="0 0 32 32" fill="none"><rect x="2" y="4" width="20" height="24" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M8 10h8M8 15h6M8 20h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M24 10l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    'title' => 'Sites Institucionais',
    'short' => 'Presença digital que converte visitantes em clientes.',
    'full'  => 'Desenvolvemos sites institucionais com arquitetura estratégica, foco em conversão e experiência de usuário premium. Cada projeto é único e construído para gerar resultado real.',
    'tags'  => ['WordPress', 'Performance', 'SEO', 'CRO'],
  ],
  [
    'icon'  => '<svg width="32" height="32" viewBox="0 0 32 32" fill="none"><path d="M4 8h24M4 16h18M4 24h12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="26" cy="22" r="6" stroke="currentColor" stroke-width="1.5"/><path d="M26 20v4M24 22h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
    'title' => 'Landing Pages',
    'short' => 'Páginas de conversão com propósito e impacto.',
    'full'  => 'Criamos landing pages testadas e otimizadas com copywriting estratégico, design focado no objetivo e integração com ferramentas de análise e automação.',
    'tags'  => ['CRO', 'A/B Testing', 'Copy', 'Analytics'],
  ],
  [
    'icon'  => '<svg width="32" height="32" viewBox="0 0 32 32" fill="none"><circle cx="16" cy="16" r="12" stroke="currentColor" stroke-width="1.5"/><path d="M16 10v6l4 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 6l2 2M22 6l-2 2M10 26l2-2M22 26l-2-2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
    'title' => 'Branding',
    'short' => 'Identidade visual com DNA estratégico.',
    'full'  => 'Construímos marcas que comunicam autoridade antes de mostrar estética. Do naming ao brandbook, cada elemento tem propósito estratégico alinhado ao negócio.',
    'tags'  => ['Naming', 'Identidade', 'Brandbook', 'Estratégia'],
  ],
  [
    'icon'  => '<svg width="32" height="32" viewBox="0 0 32 32" fill="none"><path d="M4 28L12 18l6 4 8-12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><circle cx="26" cy="8" r="3" stroke="currentColor" stroke-width="1.5"/></svg>',
    'title' => 'Mídia Paga',
    'short' => 'Investimento com retorno mensurável e escalável.',
    'full'  => 'Gerenciamos campanhas no Google e Meta com estratégia orientada a dados. Cada centavo investido é rastreado, analisado e otimizado para maximizar o ROI.',
    'tags'  => ['Google Ads', 'Meta Ads', 'ROI', 'Dados'],
  ],
];
?>
<section
  <?php echo get_block_wrapper_attributes(['class' => 'sued-services sued-section']); ?>
  id="services"
>
  <div class="sued-container">
    <div class="sued-services__header">
      <span class="sued-eyebrow" data-reveal><?php esc_html_e('Serviços', 'sued-studio'); ?></span>
      <h2 data-reveal data-reveal-delay="1"><?php esc_html_e('O que fazemos', 'sued-studio'); ?></h2>
    </div>

    <div class="sued-services__grid">
      <?php foreach ($services as $i => $svc) : ?>
      <div
        class="sued-service-card"
        data-reveal
        data-reveal-delay="<?php echo $i + 1; ?>"
        tabindex="0"
      >
        <div class="sued-service-card__inner">
          <div class="sued-service-card__front sued-glass">
            <div class="sued-service-card__icon" aria-hidden="true">
              <?php echo $svc['icon']; ?>
            </div>
            <h3 class="sued-service-card__title"><?php echo esc_html($svc['title']); ?></h3>
            <p class="sued-service-card__short"><?php echo esc_html($svc['short']); ?></p>
          </div>
          <div class="sued-service-card__back sued-glass">
            <h3 class="sued-service-card__title"><?php echo esc_html($svc['title']); ?></h3>
            <p><?php echo esc_html($svc['full']); ?></p>
            <div class="sued-service-card__tags">
              <?php foreach ($svc['tags'] as $tag) : ?>
              <span class="sued-service-card__tag"><?php echo esc_html($tag); ?></span>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
