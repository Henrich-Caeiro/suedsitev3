<?php
$title    = $attributes['title']    ?? 'Pronto para crescer com estratégia?';
$subtitle = $attributes['subtitle'] ?? 'Agende uma conversa e descubra como podemos transformar seu digital em resultado real.';
?>
<section
  <?php echo get_block_wrapper_attributes(['class' => 'sued-cta sued-section']); ?>
  id="contact"
>
  <!-- Accent glow BG -->
  <div class="sued-cta__glow" aria-hidden="true"></div>

  <div class="sued-container sued-text-center">

    <span class="sued-eyebrow" data-reveal><?php esc_html_e('Próximo Passo', 'sued-studio'); ?></span>

    <h2 class="sued-cta__title" data-reveal data-reveal-delay="1">
      <?php echo esc_html($title); ?>
    </h2>

    <p class="sued-cta__subtitle" data-reveal data-reveal-delay="2">
      <?php echo esc_html($subtitle); ?>
    </p>

    <div class="sued-cta__form" data-reveal data-reveal-delay="3">
      <form
        class="sued-contact-form"
        id="sued-contact-form"
        method="POST"
        novalidate
      >
        <?php wp_nonce_field('sued_nonce', 'nonce'); ?>
        <input type="hidden" name="action" value="sued_contact" />
        <input type="hidden" name="source" value="<?php echo esc_url(home_url(isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '/')); ?>" />

        <!-- Honeypot — hidden from humans, visible to bots -->
        <input
          type="text"
          name="website"
          tabindex="-1"
          autocomplete="off"
          style="display:none!important;position:absolute;left:-9999px;"
          aria-hidden="true"
        />

        <input
          type="text"
          name="name"
          id="sued-contact-name"
          placeholder="Seu nome"
          autocomplete="name"
          required
        />
        <input
          type="email"
          name="email"
          id="sued-contact-email"
          placeholder="Seu melhor e-mail"
          autocomplete="email"
          required
        />
        <input
          type="tel"
          name="phone"
          id="sued-contact-phone"
          placeholder="WhatsApp (opcional)"
          autocomplete="tel"
        />

        <button
          type="submit"
          class="sued-btn sued-btn--primary sued-ripple-btn"
          id="sued-contact-submit"
        >
          <span class="sued-btn__label">Enviar mensagem</span>
          <svg width="18" height="18" viewBox="0 0 18 18" fill="none" aria-hidden="true" style="margin-left:0.5rem">
            <path d="M4 9h10M10 5l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span class="sued-btn__spinner" aria-hidden="true" style="display:none">⏳</span>
        </button>

        <p id="sued-contact-msg" class="sued-form-feedback" role="alert" aria-live="polite"></p>
      </form>
    </div>

    <!-- Trust signals -->
    <div class="sued-cta__trust" data-reveal data-reveal-delay="4">
      <span><?php esc_html_e('✓ Sem compromisso', 'sued-studio'); ?></span>
      <span><?php esc_html_e('✓ Resposta em 24h', 'sued-studio'); ?></span>
      <span><?php esc_html_e('✓ Diagnóstico gratuito', 'sued-studio'); ?></span>
    </div>

  </div>
</section>
