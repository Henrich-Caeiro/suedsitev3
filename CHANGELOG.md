# CHANGELOG — SUED Studio WordPress Theme

Histórico completo de implementações, decisões técnicas e correções.
Formato: [Semver](https://semver.org) · Ordenação: mais recente primeiro.

---

## [1.6.1] — 2026-09-10

### Navegação — Links para o Diagnóstico Digital no Menu e Footer

- **`parts/header.html`**:
  - Adicionado link "Diagnóstico" (`/quiz`) na barra de navegação principal do header (desktop e mobile).
  - Atualizadas as rotas relativas das âncoras internas (`/#positioning`, `/#process`, `/#services`, `/#results`), garantindo que o usuário consiga navegar de volta para as seções da página inicial mesmo quando estiver navegando a partir da página do Quiz (`/quiz`).

- **`parts/footer.html`**:
  - Adicionado link "Diagnóstico Digital" (`/quiz`) sob a coluna "Navegação" do rodapé, estruturado com blocos explícitos `wp:navigation-link`.

- **Bump de Versão**:
  - `style.css` atualizado de `1.6.0` para `1.6.1`.
  - `functions.php` atualizado de `1.6.0` para `1.6.1` (`SUED_VERSION`), assegurando atualização imediata dos caches de navegadores.

---

## [1.6.0] — 2026-09-10

### Quiz Interativo — Diagnóstico Digital Gratuito

Nova funcionalidade estratégica desenvolvida de acordo com as diretrizes de `new_feature.md` para qualificação e diagnóstico de maturidade digital de leads.

- **Banco de Dados Próprio (`wp_sued_quiz_leads`)**:
  - Criada automaticamente via `dbDelta` em `inc/quiz-handler.php` com índices em `email`, `status`, `score` e `created_at`.
  - Armazena todas as respostas estruturadas em JSON, pontuação (score), perfil gerado, dados de contato completos, consentimento LGPD, IP e metadados.

- **Endpoint REST Seguro**:
  - Registrado `POST /wp-json/sued/v1/quiz-submit` com verificação de nonce (`wp_rest`), sanitização de campos (`sanitize_text_field`, `sanitize_email`), validação de e-mail e rate limiting via Transients.
  - **Anti-Spam Honeypot**: Campo oculto `sued_hp_check` para neutralização transparente de submissões automatizadas por bots.
  - **Consentimento LGPD**: Validação obrigatória de consentimento do usuário antes da persistência dos dados.

- **Segurança & Correção XSS**:
  - Implementada função `escapeHtml()` em `assets/js/quiz.js` para escapar nome e negócio antes da renderização no DOM, impedindo injeção arbitrária de tags HTML ou scripts maliciosos.

- **Notificações por E-mail**:
  - Disparo automático via `wp_mail` para os 3 e-mails configurados: `henrich.caeiro@gmail.com`, `le_19camargo@hotmail.com` e `heloheloisa.srf@gmail.com`.
  - Template HTML personalizado com a identidade visual da SUED Studio (Dark Navy, ciano `#26AFFF`, dourado `#C8A96E`), resumo das respostas, pontuação, perfil e botões diretos para WhatsApp e painel administrativo.

- **Painel CRM no WP-Admin (`sued-quiz-leads`)**:
  - Menu administrativo integrado em **Leads SUED > Quiz Diagnóstico**.
  - Estilização completa alinhada à identidade visual do site (Dark mode `#0F1923`/`#141F2B`, bordas `#2A3A47`, tipografia limpa).
  - Cards com métricas e contadores em tempo real para status: *Novos* (`⚡`), *Contactados* (`📞`), *Convertidos* (`💎`) e *Desqualificados* (`✖`).
  - Visualização detalhada do lead com histórico das 6 respostas estratégicas, links de contato rápido (mailto e WhatsApp com `wa.me`) e botões de transição de status protegidos por nonce.
  - Suporte a shortcode `[sued_quiz]` para renderização flexível.

- **Gutenberg Block & Templates FSE**:
  - Criado o bloco Gutenberg dinâmico `sued-studio/quiz` (`blocks/quiz/`).
  - Criados os templates de bloco FSE `templates/page-quiz.html` e `templates/page-diagnostico.html` para rotas automáticas `/quiz` e `/diagnostico`.
  - Registrado template de página `quiz` ("Quiz Diagnóstico") em `theme.json` (`customTemplates`) e criado o template PHP clássico `template-quiz.php`.
  - Estilos dedicados em `assets/css/quiz.css` e motor interativo em `assets/js/quiz.js`.

- **Bump de Versão**:
  - `style.css` atualizado de `1.5.3` para `1.6.0`.
  - `functions.php` atualizado de `1.5.2` para `1.6.0` (`SUED_VERSION`), garantindo cache-busting imediato de todos os assets CSS/JS.

---

## [1.5.3] — 2026-05-10

### Services — Grid 2×2 para Desktop

- **`assets/css/blocks.css`**: Grid de serviços alterado de `repeat(auto-fit, minmax(min(100%, 280px), 1fr))` para `repeat(2, 1fr)` fixo — resultado: 4 cards distribuídos homogeneamente em 2 linhas × 2 colunas no desktop.
- **Mobile preservado**: Adicionado override explícito `grid-template-columns: 1fr` no `@media (max-width: 768px)` para garantir coluna única em mobile, já que o `repeat(2, 1fr)` fixo não colapsa automaticamente como o `auto-fit` fazia.

### Footer — Ícones de Redes Sociais

- **`parts/footer.html`**: Adicionada nova coluna "Redes Sociais" no footer com quatro links:
  - **Instagram** → `@sued_studio` (instagram.com/sued_studio)
  - **Facebook** → facebook.com/suedstudio
  - **LinkedIn** → linkedin.com/company/suedstudio
  - **WhatsApp** → mesmo número do botão flutuante existente (`wa.me/5516999943952`)
  - Cada link usa SVG inline + label de texto, estilizados com as novas classes `.sued-social-links` / `.sued-social-link`.

- **`assets/css/global.css`**: Adicionados estilos para os links sociais do footer:
  - `.sued-social-links`: lista vertical com `gap: 0.65rem`.
  - `.sued-social-link`: cor `--sued-muted`, hover com `color: --sued-light` e `translateX(4px)` suave.
  - `.sued-social-link--whatsapp:hover`: cor verde `#25D366` no hover do link de WhatsApp.

### Formulário de Contato — Input restringido

- **`assets/css/blocks.css`**: Adicionado `max-width: 260px` ao `.sued-contact-form input` para evitar que o campo de e-mail se estique excessivamente em viewports largas.

---

## [1.5.2] — 2026-05-08

- **Bump de Versão**: `style.css` e `functions.php` atualizados para `1.5.2`.
- **Header CTA — Link desktop corrigido**: O atributo `url` do bloco `wp:button` não estava definido no JSON do bloco (`parts/header.html`), fazendo o WordPress Site Editor ignorar o `href="#contact"` no desktop. Adicionado `\"url\":\"#contact\"` explicitamente nos atributos do bloco, garantindo que o scroll para o formulário funcione em todos os contextos.
- **Header CTA — Novo estilo Glass Pill**: Botão "Fale Conosco" refatorado visualmente com classe `sued-header-cta`:
  - `border-radius: 100px` — formato pílula perfeito.
  - `background: rgba(255,255,255,0.07)` + `backdrop-filter: blur(12px) saturate(160%)` — efeito vidro fosco sobre o fundo.
  - `border: 1px solid rgba(255,255,255,0.14)` + `inset box-shadow` duplo (catchlight superior + sombra inferior) — simula a espessura do vidro.
  - Hover: tinge de azul accent (`rgba(38,175,255,0.15)`) com glow de `20px` e `translateY(-1px)`.
  - Mobile herda o mesmo estilo, apenas com `font-size` e `padding` reduzidos.

---

## [1.5.1] — 2026-05-07


- **Bump de Versão**: `style.css` e `functions.php` atualizados para `1.5.1`.
- **Header Mobile — CTA visível**: Removido o `display: none` do `.wp-block-buttons` no breakpoint `≤768px`. O botão "Fale Conosco" agora é exibido no mobile.
- **Header Mobile — Layout em 2 linhas**: Container passa a usar `flex-wrap: wrap`. O logo (`.wp-block-image`) ocupa `flex: 1 0 100%` e centraliza na primeira linha; a segunda linha exibe o hamburger de navegação (esquerda) e o CTA (direita) lado a lado.
- **CTA Mobile — Tamanho adequado**: `font-size: 0.75rem` e `padding: 0.5rem 1rem` para proporcionalidade em telas pequenas; `white-space: nowrap` evita quebra de linha no rótulo.
- **CTA Mobile — Link para o formulário**: O botão já aponta para `href="#contact"` (âncora da seção CTA com `id="contact"` em `blocks/cta/template.php`), linkando diretamente ao formulário de captura de leads implementado em `v1.5.0`.

---

## [1.5.0] — 2026-05-07


### Formulário de Contato Funcional — Email + Banco de Dados

Implementação completa do pipeline de captura de leads: o formulário da seção CTA agora dispara um e-mail de notificação para `contato@suedstudio.com.br` e persiste cada submissão no banco de dados do WordPress, acessível pelo painel `wp-admin`.

#### `inc/contact-form.php` — criado (novo arquivo)

- **Tabela `{prefix}sued_leads`**: criada via `dbDelta()` no hook `after_switch_theme` e verificada no `init` (idempotente). Colunas: `id`, `name`, `email`, `phone`, `message`, `source`, `ip_address`, `user_agent`, `status` (enum: `new/contacted/converted/disqualified`), `created_at`.
- **Handler AJAX** (`sued_contact`): registrado em `wp_ajax_sued_contact` + `wp_ajax_nopriv_sued_contact`.
  - Verificação de nonce via `wp_verify_nonce()`.
  - Sanitização completa: `sanitize_text_field`, `sanitize_email`, `sanitize_textarea_field`.
  - Validação: nome mínimo 2 chars, e-mail válido via `is_email()`.
  - **Honeypot anti-spam**: campo `website` oculto; se preenchido, retorna sucesso silencioso sem salvar.
  - Persiste lead na tabela com IP, user-agent e URL de origem.
  - Envia e-mail formatado para `contato@suedstudio.com.br` via `wp_mail()` com `Reply-To` apontando para o lead e link direto para o detalhe no painel.
  - Retorna JSON `{ success, data: { message, lead_id } }`.
- **Página de administração** (`sued-leads`): menu de nível raiz no `wp-admin` (ícone `dashicons-email-alt`, posição 30).
  - Cards de resumo por status com cores semânticas.
  - Tabela de leads com colunas: #, Nome, E-mail, Telefone, Status, Data, Ações.
  - Filtro por status via query string.
  - Visão de detalhe individual com tabela completa e botões de mudança de status (protegidos por nonce).

#### `blocks/cta/template.php` — refatorado

- Removidas variáveis legadas `$cta1_*` / `$cta2_*` (não utilizadas — formulário substituiu os botões em `v1.3.2`).
- Adicionado `wp_nonce_field('sued_nonce', 'nonce')` para proteção CSRF.
- Campo `<input type="hidden" name="action" value="sued_contact">` para o handler AJAX.
- Campo `<input type="hidden" name="source">` com URL da página de origem.
- Campo honeypot oculto via CSS inline (`display:none!important`).
- Campo de telefone `<input type="tel" name="phone">` (opcional).
- Botão com `<span class="sued-btn__label">` e `<span class="sued-btn__spinner">` para troca de estado visual durante o envio.
- `<p id="sued-contact-msg" role="alert" aria-live="polite">` para feedback acessível.

#### `assets/js/main.js` — adicionado handler de formulário

- Captura o `submit` de `#sued-contact-form`, previne o padrão e monta `FormData`.
- **Loading state**: desabilita botão, troca label para "Enviando…", exibe spinner.
- Usa `fetch()` para `POST` em `SUED.ajaxUrl` (fallback: `/wp-admin/admin-ajax.php`).
- Trata resposta: exibe mensagem de sucesso (verde) ou erro (vermelho) em `#sued-contact-msg`; reseta o form em caso de sucesso.
- Restaura estado do botão no `.finally()`.

#### `assets/css/blocks.css` — estilos de feedback

- `.sued-form-feedback`: base invisível (`display:none`), aparece via `:not(:empty)`.
- `.sued-form-feedback--success`: texto verde `#22c55e` + fundo e borda sutis.
- `.sued-form-feedback--error`: texto vermelho `#f87171` + fundo e borda sutis.
- `#sued-contact-submit:disabled`: `opacity: 0.65`, `cursor: not-allowed`, sem `transform`.

#### `functions.php` + `style.css` — Bump de versão

- `SUED_VERSION` atualizada para `1.5.0`.
- `require_once SUED_DIR . '/inc/contact-form.php'` adicionado logo após a definição das constantes.

---

## [1.4.5] — 2026-05-07

- **Bump de Versão**: Atualização do `style.css` para `1.4.5` (patch manual pós-refinamentos de layout).
- **CTA — Padding ajustado**: Espaçamento interno da seção CTA refinado para melhor proporção visual em desktop e mobile. A classe `.sued-cta` recebeu ajuste de `padding-block` para manter consistência com as demais seções.
- **Footer — Layout revisado**: Revisão manual do `parts/footer.html` para corrigir alinhamentos e espaçamentos entre as colunas após as iterações de responsivo.

---

## [1.4.4] — 2026-05-07

- **Bump de Versão**: Patch intermediário durante refinamentos manuais de CSS.
- **Blocos — Transparência de background**: Confirmada a remoção de `background` de todos os blocos principais (`.sued-positioning`, `.sued-process`, `.sued-services`, `.sued-results`, `.sued-differential`, `.sued-cta`) para garantir visibilidade do canvas 3D global em todos os viewports.
- **Header — Entrada animada**: Adicionado `gsap.from(header, { opacity:0, y:-20, duration:1, delay:2.5 })` no `main.js` para a animação de entrada do header (removida dependência do `hero/view.js` que foi deletado na unificação do engine 3D).
- **Scroll reveal — Consolidação**: Removida a exceção `if (el.closest('.sued-hero')) return;` do loop de scroll reveal no `main.js` (o `hero/view.js` foi deletado; o motor unificado `sued-3d-engine.js` gerencia o Hero). O `[data-reveal]` agora funciona uniformemente em todas as seções.

---

## [1.4.3] -- 2026-05-01


- **Bump de Versão**: Atualização para `1.4.3` nos arquivos `style.css` e `functions.php`.
- **Menu Mobile Fullscreen**: Overlay do menu mobile ajustado com `height: 100dvh`, `position: fixed` e z-index altíssimo para garantir que o menu ocupe toda a tela independentemente da rolagem atual.
- **Footer Mobile**: Ajustado o comportamento das colunas do footer no mobile para manter alinhamento à esquerda (`text-align: left`) com espaçamento lateral de segurança (`padding: 0 1rem`), ao invés de centralizar forçadamente.
- **Footer Logo**: Alterado de volta para `wp:html` usando a exata mesma estrutura `figure > a > img` (140px) fornecida. O FSE Block Theme falha ao renderizar src absolutos do tema através do bloco `wp:image` pois procura no banco de dados. Envolver o exato HTML com o bloco `wp:html` contorna a restrição e assegura a exibição do logo no front-end.

## [1.4.2] -- 2026-05-01

- **Bump de Versão**: Atualização do `style.css` e `functions.php` para a versão `1.4.2`.
- **Efeito Floating Menu**: O Header agora diminui de tamanho e adquire bordas arredondadas e efeito blur glassmorphism ao dar scroll (comportamento de 'pílula flutuante' centralizada).
- **Footer Logo**: Retornada a estrutura do logo no footer para o bloco nativo `wp:image` (com tamanho de 140px) para manter paridade exata com a implementação do header.

## [1.4.1] -- 2026-05-01

- **Bump de Versão**: Atualização do `style.css` e `functions.php` para a versão `1.4.1`.
- Este patch assegura que as implementações cruciais da `1.4.0` (fontes self-hosted e correção do logo do footer via `wp:html`) sejam reconhecidas e aplicadas no ambiente WordPress, além de reforçar o versionamento correto no `style.css`.

## [1.4.0] -- 2026-05-01

### Implementações — new_feature.md

Suite de melhorias visuais, tipográficas e de experiência mobile, conforme especificação `new_feature.md`.

#### 1. Tipografia — Hierarquia de Pesos + Self-hosting
- **H1** alterado de `font-weight: 800` (ExtraBold) para `font-weight: 900` (Black) — impacto máximo nos títulos principais.
- **H2** alterado de `font-weight: 700` (Bold) para `font-weight: 800` (ExtraBold) — maior presença nas seções.
- Sincronizado em `global.css` e `theme.json` (elementos `h1` e `h2`).
- **Montserrat self-hosted:** Fontes baixadas do Google Fonts (v31 variable, woff2) e salvas em `assets/fonts/`. Declarações `@font-face` no `global.css` com `font-weight: 400 900` (cobre Regular até Black). Removida dependência de CDN do Google Fonts para Montserrat.
  - `montserrat-latin.woff2` — 35KB (subset latin)
  - `montserrat-latin-ext.woff2` — 67KB (subset latin-ext)
- `functions.php`: `wp_enqueue_style('sued-fonts')` agora carrega apenas Open Sans via Google Fonts CDN.
- `theme.json`: `fontFace` do Montserrat atualizado para `file:./assets/fonts/` com `fontDisplay: swap` e `unicodeRange`.

#### 2. Footer — Logo + Mobile
- **Logo no footer corrigido:** O bloco `wp:image` não renderizava o SVG porque a imagem não estava na media library do WordPress. Substituído por `wp:html` com tag `<img>` direta, classe `.sued-footer-logo` para estilização.
- Adicionada media query `@768px` para centralização de conteúdo, logo e navegação em mobile.
- Espaçamento vertical reduzido (`padding-top: 3rem`, `padding-bottom: 2rem`) para melhor proporção em telas menores.

#### 3. Consistência de Layout — Timeline Mobile
- **≤ 900px:** Adicionado padding nos cards de conteúdo (`.sued-process__step-content`), gap reduzido, header com margin proporcional.
- **≤ 768px:** Timeline com circles menores (`2.5rem`), gap `1rem`, tipografia reduzida, linha vertical reposicionada para alinhamento perfeito com os circles menores.
- Proporcionalidade preservada entre breakpoints — mesma lógica visual com escalas menores.

#### 4. Flip Cards — Independência Mobile (CRÍTICO)
- **CSS:** Hover flip desabilitado em `@768px` (`.sued-service-card:hover .sued-service-card__inner { transform: none }`). Flip agora controlado pela classe `.is-flipped`.
- **JS:** Adicionado handler de `click` com `matchMedia('max-width: 768px')`. Cada card opera independentemente — ao tocar em um card, os demais fecham automaticamente.
- `touchend` com `preventDefault()` para evitar double-trigger em dispositivos touch.
- Cards com `min-height` reduzida em mobile para melhor encaixe.

#### 5. Menu Mobile — Estilização Premium
- WordPress navigation overlay (`.wp-block-navigation__responsive-container.is-menu-open`) estilizado com glassmorphism escuro (`rgba(10,17,24,0.97)` + blur 24px).
- Links centralizados com Montserrat 600, padding generoso (`1.25rem`), separadores sutis entre itens.
- Estado `:hover`/`:active` com cor accent e fundo translúcido.
- Botão de fechar em cor accent com tamanho aumentado (28px).
- Botão hamburger em cor `--sued-light` para visibilidade.

#### 6. Botão WhatsApp — Touch Feedback
- Adicionado estado `:active` com `transform: scale(0.92)` e borda accent para feedback tátil imediato no mobile.
- CSS e HTML já existiam (implementados em v1.3.2).

---

## [1.3.3] -- 2026-05-01

- **Footer — Fundo preto full-bleed:**
  - **Causa:** A classe `.sued-container` estava aplicada diretamente no elemento `<footer>`, limitando o fundo preto à largura máxima do container (1280px). Nas laterais, o fundo translúcido do `body` (radial-gradient) ficava visível.
  - **Correção:** Reestruturado `parts/footer.html` — o `wp:group` externo agora é um wrapper full-width com `backgroundColor: black` (sem `sued-container`), e um `wp:group` interno com `.sued-container` restringe apenas o conteúdo à largura padrão.
  - Adicionado `background: #000 !important` via CSS em `.sued-site-footer` para garantir opacidade total, sobrescrevendo qualquer herança de glassmorphism ou transparência do body.

- **Header — Menu fixo no topo:**
  - **Causa:** O header usava `position: sticky`, que pode quebrar quando qualquer ancestral possui `overflow: hidden` (presente nos blocos `sued-positioning`, `sued-process` e `sued-results`).
  - **Correção:** Alterado para `position: fixed` com `width: 100%` e `left: 0` em `header.css`. O hero já possui `min-height: 100svh` e `padding-block: 8rem 6rem`, acomodando naturalmente o espaço do header fixo.
  - A animação de entrada via GSAP e o cálculo de `--header-opacity` no scroll continuam funcionando normalmente com `fixed`.

- **Responsivo — Mobile:**
  - Header mobile: padding reduzido, logo menor (100px), botão CTA oculto em telas ≤ 768px (a navegação já colapsa em overlay via WordPress `overlayMenu: "mobile"`).
  - Footer: a estrutura de container aninhado garante que as colunas colapsem corretamente em mobile (grid `wp:columns` já é responsivo nativamente).

---

## [1.3.2] -- 2026-04-28

### Melhorias de QA e Design
- **Body:** Confirmado o uso de Montserrat Bold para os títulos de seção; adicionado um botão flutuante discreto para WhatsApp no rodapé.
- **Header/Menu:** Adicionado container estrutural para respeitar o layout global; menu agora inicia totalmente transparente e entra como o último elemento na animação do Hero. Links centralizados apontando para as seções e CTA do menu estilizado com design do tema.
- **Hero:** Confirmado o peso ExtraBold para o `h1`; botões de CTA aumentados (`sued-btn--lg`) para maior destaque.
- **Positioning (Seção 2):** Alinhamento ajustado (`align-items: start`) para parear o texto descritivo do lado direito com o topo do título da esquerda.
- **Services (Seção 3):** Acordeão substituído por um layout inovador de **Flipcards** responsivo ao hover, otimizando o CSS 3D e removendo a dependência de JS.
- **Process (Seção 4):** Adicionada sombra com efeito 3D (`box-shadow` aprofundado) nos numerais (círculos); linha conectora restrita para não ultrapassar o centro das extremidades; os passos foram alinhados verticalmente ao centro de seus respectivos cards de conteúdo.
- **CTA/Contato (Seção 5):** Botões substituídos por um **formulário de contato curto** com design glassmorphism elegante e integrado ao tema.
- **Footer:** Adicionado a classe de container `.sued-container` para restringir a largura ao padrão de layout.

---

### Corrigido -- Hero Invisível apos Refatoracao 3D

Foi identificado um blackout visual no Hero apos a implementacao do motor 3D unificado, onde tanto o efeito quanto o conteudo da secao ficavam invisiveis.

#### Causa e Correcao
- **Efeito 3D tapado**: A classe `.sued-hero` em `blocks/hero/style.css` possuia `background: var(--sued-dark)`, o que estava agindo como uma parede opaca sobre o novo canvas global (`z-index: -1`). Foi alterado para `background: transparent`.
- **Conteudo travado em opacity 0**: O `assets/js/main.js` continha uma excecao `if (el.closest('.sued-hero')) return;` herdada da versao antiga, que impedia o GSAP ScrollTrigger de revelar o conteudo do Hero (pois antes isso era responsabilidade do `hero/view.js`). Removendo a excecao, o motor unificado do `main.js` passou a animar a entrada do conteudo perfeitamente na carga inicial.

---



### Refatoracao Arquitetural -- Unified Continuous 3D Engine

Realizada uma mudanca dramatica na arquitetura de renderizacao 3D do site para criar uma experiencia totalmente imersiva e contínua, sem quebras entre as secoes.

#### Otimizacao e Engine Global
- **Remocao de Canvases Locais:** Os renderizadores independentes (`view.js`) dos blocos `hero`, `positioning` e `process` foram deletados.
- **`sued-3d-engine.js`**: Criado um motor unificado que gerencia um unico `<canvas>` fixo (`.sued-global-canvas`) injetado no fundo da pagina.
- **Performance**: Reduziu o overhead de multiplos contextos WebGL. Agora, 2500 particulas sao renderizadas em uma unica draw call via `BufferGeometry` e um `ShaderMaterial` customizado.

#### Morphing Responsivo ao Scroll
- As particulas agora fazem uma *transicao matematica e fluida (morphing)* entre diferentes formas geometricas de acordo com a progressao do scroll, controlada via GSAP ScrollTrigger:
  - **Estado 0 (Hero):** Chaos Sphere (particulas flutuando organicamente no espaco).
  - **Estado 1 (Positioning):** Convergence Core (as particulas se reorganizam para formar um Icosaedro central e aneis orbitais).
  - **Estado 2 (Process):** Energy Flow / DNA Helix (a malha se transforma em uma dupla helice fluida, simbolizando o processo de desenvolvimento e energia).
  - **Estado 3 (Results/Resto do Site):** Data Grid (as particulas se estruturam em uma matriz de grade rigorosa 3D, simbolizando dados estruturados e estabilidade).
- Os efeitos funcionam perfeitamente em ordem reversa ao rolar para cima.
- A iluminacao e cor do fundo (`body`) foram unificadas em um `radial-gradient` escuro luxuoso que abraça toda a navegacao, com todos os blocos alterados para `background: transparent`.

---



### Novo Ponto Focal -- Secondary Accent (#C8A96E)

A cor dourada/champagne `#C8A96E` foi introduzida como *secondary accent* em alinhamento com a revisao do PRD, adicionando um elemento extra de luxo e quebra visual sem competir com o azul primario.

#### Alteracoes CSS
- `theme.json`: adicionada paleta `accent-secondary`.
- `global.css`: definida `--sued-accent-secondary`.
- **Eyebrow Label**: A cor do texto e as sombras associadas as labels `sued-eyebrow` agora utilizam a cor dourada, criando um detalhe inicial luxuoso em cada secao.
- **Service Cards (Hover)**: Os SVGs dos icones agora utilizam `stroke="currentColor"`. No `:hover`, a borda do icon container, as sombras e a cor do icone transitam simultaneamente para `#C8A96E`.
- **Result Stats**: Os prefixos/sufixos (`+` e `%`) nos numeros da secao de Resultados agora utilizam a cor dourada, isolando o numero de forma mais dramatica.
- **Differential Highlights**: A sublinha (`border-bottom`) sobre o fundo `#0F1923` na secao de Diferencial agora e dourada.

#### Alteracoes Three.js
- **Hero**: Introduzidos 10% de particulas na cor `#C8A96E`, adicionando um polvilhado sutil de luz quente ao lado dos tons azuis e brancos.
- **Positioning**: A esfera core central (`darkCore` -> `goldCore`) da malha de convergencia agora utiliza `#C8A96E`, transformando o coracao estrutural em uma "joia".
- **Process**: Os "pulsos de energia" (`pulseMesh`) que viajam ao longo das curvas Bezier agora sao renderizados em `#C8A96E`, destacando o fluxo de execucao contra os arcos azuis de fundo.

---



### Refinamento Visual -- Particulas Circulares (Esferas)

O WebGL por padrao renderiza `Points` como quadrados. Para garantir um acabamento mais organico e premium, implementamos uma geracao dinamica de textura circular para todas as particulas do site, substituindo os quadrados default.

#### Implementacao (`view.js` de todas as secoes)
- Criacao de uma helper function interna em cada `view.js` que usa um canvas 2D offscreen (64x64) para desenhar um circulo branco solido perfeito.
- Aplicacao dessa textura via `THREE.CanvasTexture` diretamente no parametro `map` do `THREE.PointsMaterial`.
- Devido ao `transparent: true`, as bordas do quadrado sao descartadas (alpha blend perfeito) resultando em particulas 100% circulares sem necessidade de texturas PNG externas (mantendo zero requests de rede adicionais).

**Afetou as seguintes secoes:**
1. **Hero**: O field de 2000 particulas agora e composto por circulos (brancos, azuis e os recentes dark `#0F1923`).
2. **Positioning**: Os 42 vertices da `Convergence Mesh` agora sao pequenos circulos definidos ao inves de pixels/quadrados.
3. **Process**: As 60 micro-particulas flutuantes atraidas pela gravidade do mouse agora sao circulares.

---



### Refinamento -- #0F1923 como elemento visual ativo

A cor `#0F1923` agora aparece como elemento decorativo **visivel** (nao apenas em sombras). Usada em backgrounds de icones, underlines, particulas 3D e formas geometricas.

#### CSS -- Elementos visiveis
- **Eyebrow**: dot decorativo `#0F1923` com borda azul apos o texto
- **Highlight underline**: fundo da area de sublinhado agora e `#0F1923` solido (ao inves de azul translucido) + borda inferior azul
- **Divider**: barra sombra `#0F1923` posicionada abaixo da linha accent
- **Service card icons**: background `#0F1923` com `border-radius: 10px` e borda azul sutil. No hover, glow e inset shadow
- **Service tags**: background solido `#0F1923` ao inves de azul translucido + inset shadow
- **Toggle button**: background `#0F1923` ao inves de transparente
- **Process step numbers**: background `#0F1923` com inset shadow (ao inves de `rgba(38,175,255,0.06)`)
- **Result stat bars**: track `#0F1923` com borda `#2A3A47` (ao inves de branco 10%)
- **Differential bg-text**: `#0F1923` solido (ao inves de branco 2%) + text-shadow
- **Differential highlights**: underline `#0F1923` + `border-bottom` azul + text-shadow

#### Three.js -- Particulas e geometria
- **Hero particles**: 20% das particulas agora sao `#0F1923` — criam profundidade entre as luminosas
- **Positioning Convergence Mesh**: adicionado anel sombra (`#0F1923`, r=3.4, tube=0.02) que contra-rota o anel accent. Adicionada esfera central `#0F1923` (r=0.5) que aparece a partir de 20% da convergencia

---



### Refinamento -- Detalhes sutis em #0F1923

Adicionados micro-detalhes visuais em `rgba(15,25,35,...)` por todo o tema para criar profundidade, textura e sofisticacao sem chamar atencao. Cada detalhe e quase imperceptivel individualmente mas o efeito cumulativo eleva significativamente a percepcao de qualidade premium.

#### `assets/css/global.css`
- **Tokens**: adicionados `--sued-dark-deep: #0a1118` e `--sued-dark-soft: #141f2b` como variacoes
- **Tipografia**: `text-shadow` em `#0F1923` em todos os headings (h1 mais pronunciado com dupla sombra)
- **Secoes**: `::after` separator gradient entre secoes (fadeado nas bordas, visivel so no centro)
- **Eyebrow**: `text-shadow` glow azul sutil + `box-shadow` na barra decorativa
- **Botao primary**: `inset 0 1px 0 rgba(255,255,255,0.15)` (top edge catch-light) + sombra escura na base
- **Botao outline**: fundo `rgba(15,25,35,0.3)` ao inves de transparente + inset shadow + borda mais sutil
- **Divider**: gradiente de azul para transparente + dual shadow (glow + dark base)
- **Glass cards**: gradiente diagonal (145deg) de `#2A3A47` para `#141f2b`, borda-top diferenciada, triple box-shadow (drop + inset top light + inset bottom dark)
- **Vignette**: `body::after` com `radial-gradient` fixo (escurece bordas da viewport com `mix-blend-mode: multiply`)

#### `assets/css/header.css`
- Borda inferior com `rgba(42,58,71,0.15)` (mais sutil)
- `box-shadow` dual: sombra externa + inset catch-light
- Blur aumentado de 20px para 24px

#### `blocks/hero/style.css`
- `::after` gradient 120px na base do hero (transicao suave para proxima secao)
- Headline: `text-shadow` dual (sombra definida + halo difuso)
- Subheadline: `text-shadow` sutil para profundidade

#### `assets/css/blocks.css`
- **Positioning stats**: `border-left` que aparece no hover com azul + `inset shadow`
- **Service cards**: `::after` catch-light na borda inferior + sombras em camadas `#0F1923`
- **Result numbers**: `text-shadow` para profundidade nos contadores
- **Differential**: background com gradiente vertical (top/bottom mais escuros que o centro)

---



### Adicionado -- "Energy Circuit" na secao Como Trabalhamos

#### Conceito
- Diferente das secoes anteriores (que usam canvas como background), este efeito se sobrepoe diretamente aos elementos DOM da timeline
- Le as posicoes reais dos `.sued-process__step-num` no DOM e desenha circuitos de energia conectando-os
- Representa visualmente o fluxo de trabalho: dados e energia fluindo de etapa em etapa

#### `blocks/process/view.js` -- criado (novo arquivo)
- **Canvas overlay**: inserido dinamicamente via JS dentro de `.sued-process__timeline`
- **Camera ortografica**: mapeada 1:1 com coordenadas de pixel do DOM
- **Arcos de conexao**: curvas Bezier quadraticas entre os nodes, deslocadas para a direita criando arcos organicos
- **Pulsos de energia**: esferas luminosas que viajam ao longo dos arcos quando o step seguinte ativa. Brilho e escala oscilam com seno para efeito pulsante
- **Halos rotativos**: aneis que aparecem ao redor de cada node ativo, expandem no momento da ativacao e depois "respiram"
- **Micro-particulas flutuantes**: 60 particulas com movimento browniano + campo gravitacional do mouse (raio 150px, forca 0.8)
- **MutationObserver**: observa mudancas de classe `.is-active` nos steps para disparar animacoes sem polling
- **Resize handler**: recalcula posicoes dos nodes e reconstroi arcos

#### `assets/css/blocks.css`
- `.sued-process__canvas`: overlay absoluto com `pointer-events:none; opacity:1!important`
- Z-index hierarchy: canvas=0, line=1, step=2, step-num=3 (conteudo sempre acima do efeito)
- `.sued-process` recebeu `position:relative; overflow:hidden`

#### `functions.php`
- Enqueue de `blocks/process/view.js` com dep `['three-js']`

#### Verificacao
- Console: `[SUED Process] Energy Circuit initialized: 4 nodes, 3 arcs`

---



### Adicionado -- "Convergence Mesh" na secao de Posicionamento

#### Conceito narrativo
- **Hero** = caos (particulas dispersas = potencial bruto)
- **Posicionamento** = estrutura emergente (mesh wireframe = estrategia)
- A transicao representa visualmente a proposta de valor: "do caos para a estrategia"

#### `blocks/positioning/view.js` -- criado (novo arquivo)
- Efeito Three.js totalmente diferente do Hero: **Convergence Mesh**
- 42 vertices (IcosahedronGeometry detail=1) comecam dispersos em uma esfera de raio 4-10
- Conforme o scroll, convergem para a forma geometrica estruturada (icosaedro)
- **Convergencia controlada por ScrollTrigger** (scrub 1.5, de `top 80%` ate `center center`)
- Wireframe azul (#26AFFF) faz fade-in a partir de 30% da convergencia
- Vertices pulsam com animacao "breathing" organica na segunda metade da convergencia
- **Core glow** central pulsa quando a forma se completa
- **Anel orbital** aparece quando convergencia > 70%
- Mouse parallax local (limitado a area da secao, nao do documento inteiro)
- Retry mechanism (20x 250ms) igual ao Hero

#### `blocks/positioning/template.php`
- Substituido `<div class="sued-positioning__bg">` (dots CSS estaticos) por `<canvas class="sued-positioning__canvas">`
- Canvas posicionado absolutamente atras do conteudo

#### `assets/css/blocks.css`
- Adicionado `.sued-positioning__canvas` com `position:absolute; pointer-events:none; opacity:1!important`
- `.sued-positioning` recebeu `overflow:hidden; min-height:90vh` para garantir area de scroll trigger
- Removido `.sued-positioning__bg` (substituido pelo canvas)

#### `functions.php`
- Adicionado enqueue de `blocks/positioning/view.js` com deps `['three-js', 'gsap', 'gsap-scroll-trigger']`

#### Verificacao
- Console deve mostrar: `[SUED Positioning] Three.js Convergence Mesh initialized: 42 vertices`

---



### Corrigido -- Three.js CDN 404

#### Causa raiz
- A URL `https://cdn.jsdelivr.net/npm/three@0.165.0/build/three.min.js` retornava **HTTP 404**
- Three.js removeu o build UMD (`three.min.js`) a partir do r160. A partir dessa versao, so existem `three.module.js` (ESM) e `three.cjs` (CommonJS) -- nenhum dos dois expoe `window.THREE` como global
- O `<script>` era adicionado ao HTML com src invalido, o browser ignorava silenciosamente, e `typeof THREE` permanecia `'undefined'` para sempre

#### Correcao
- Baixado Three.js **r160** (ultimo a ter UMD) localmente em `assets/js/vendor/three.min.js` (670KB)
- Trocado o enqueue de CDN para arquivo local: `SUED_URI . '/assets/js/vendor/three.min.js'`
- Elimina completamente a dependencia de CDN externo -- o Three.js agora e servido pelo proprio WordPress

#### Verificacao
- `curl -sI` confirmou: r165 = 404, r160 = 200, r159 = 200
- No console do browser, deve aparecer: `[SUED Hero] Three.js initialized: 2000 particles, 3 rings`

---



### Corrigido -- Three.js nao renderizando no frontend

#### `blocks/hero/view.js` -- reescrito com retry
- **Causa raiz**: o script executava antes do `THREE` global estar disponivel (CDN load race condition) e antes do canvas ter dimensoes computadas (`offsetWidth/offsetHeight = 0`). O `renderer.setSize(0, 0)` criava um canvas de 0x0 pixels -- invisivel
- **Correcao**: adicionado mecanismo de retry (ate 20 tentativas, 250ms cada = 5s total) que aguarda tanto `typeof THREE !== 'undefined'` quanto `section.offsetWidth > 0`
- Adicionado `console.log` de confirmacao quando Three.js inicializa com sucesso (contagem de particulas e aneis)
- Corrigida a matematica de interpolacao de cores das particulas -- estava produzindo valores errados (R fixo em 0.96 ao inves de interpolar entre #F5F2EC e #26AFFF)
- Tubos dos aneis aumentados (0.008 -> 0.012, etc.) para maior visibilidade
- Adicionado `powerPreference: 'high-performance'` ao WebGLRenderer

#### `blocks/hero/style.css` -- canvas com visibilidade forcada
- Adicionado `display: block` (canvas e inline por padrao, pode ter gap fantasma)
- Adicionado `pointer-events: none` (canvas nao deve capturar cliques sobre o conteudo)
- Adicionado `opacity: 1 !important` para sobrescrever qualquer regra `[data-reveal]` herdada

#### `assets/js/main.js` -- conflito GSAP/CSS resolvido
- **Causa**: `main.js` e `hero/view.js` ambos tentavam animar os mesmos elementos `[data-reveal]` dentro do Hero, causando conflito de timelines GSAP
- **Correcao**: adicionado `if (el.closest('.sued-hero')) return;` no loop de scroll reveal, excluindo filhos do Hero (gerenciados exclusivamente por `view.js`)
- Convertido de ES6 para ES5 (`const` -> `var`, arrow functions -> `function`, spread -> `Object.assign`)

---

## [1.0.4] — 2026-04-26

### Corrigido — Editor sem suporte ao bloco + conteúdo invisível no frontend

#### `assets/js/blocks-editor.js` — criado (novo arquivo)
- **Causa**: blocos dinâmicos registrados apenas em PHP via `register_block_type()` não aparecem no registry JS do Gutenberg em todos os contextos (especialmente Site Editor)
- WordPress passa metadados de blocos PHP para o JS automaticamente, mas o mecanismo pode falhar sem um `editorScript` explícito
- **Correção**: criado `blocks-editor.js` em JS puro (sem build step) que chama `wp.blocks.registerBlockType()` para cada um dos 7 blocos customizados, com `save: () => null` (bloco dinâmico/server-side) e preview de placeholder no editor
- Enfileirado via `add_action('enqueue_block_editor_assets', ...)` com deps `['wp-blocks', 'wp-element']` — hook específico para assets do editor que garante que `wp.blocks` esteja disponível

#### `assets/js/main.js` — `gsap.from()` → `gsap.to()`
- **Causa raiz**: `[data-reveal]` no CSS define `opacity: 0` como estado inicial. `gsap.from(el, {opacity: 0})` captura o estado atual do elemento como estado **destino** antes de aplicar o `from`. Como CSS já dizia `opacity: 0`, GSAP capturava `opacity: 0` como destino e animava de `0` para `0` — elemento ficava permanentemente invisível
- **Correção**: trocado para `gsap.to(el, {opacity: 1, y: 0})` — parte do estado oculto definido pelo CSS e anima até o estado visível

#### `blocks/hero/view.js` — reescrito
- **Mesma causa**: `gsap.timeline().from()` no hero tinha o mesmo problema de capturar `opacity: 0` como destino
- **Correção adicional**: convertido de ES6 (`const`, `let`, arrow functions, template literals) para ES5 para máxima compatibilidade com WordPress e browsers legados
- **Correção**: `gsap.set(selectors, {opacity: 0})` estabelece estado inicial explícito via GSAP (não CSS), depois `gsap.to()` anima até `opacity: 1`
- Removidos comentários com caracteres especiais UTF-8 (`──`) que causavam problema de encoding (CRLF + multibyte)

#### `functions.php` — `enqueue_block_editor_assets`
- Adicionado hook `enqueue_block_editor_assets` que enfileira `blocks-editor.js` exclusivamente no editor (não no frontend)

#### `style.css` + `functions.php`
- Versão bumped: `1.0.3` → `1.0.4`
- `SUED_VERSION` constant sincronizada

---



### Corrigido — "Site não tem suporte para os blocos adicionados"

#### Causa 1 — `require_once` em nível de arquivo (`functions.php` linha 110)
- `require_once SUED_DIR . '/inc/patterns.php'` estava fora de qualquer hook WordPress
- Executava antes do `init`, antes dos blocos estarem registrados
- `register_block_pattern()` dentro de `patterns.php` rodava sem que os tipos de bloco existissem
- **Correção**: `require_once` eliminado; todo o conteúdo de patterns.php foi migrado para dentro de `add_action('init', ...)` no próprio `functions.php`, em ordem explícita: blocos → categoria → padrões

#### Causa 2 — Múltiplos hooks `init` sem ordem garantida
- Blocos, categoria de padrões e padrões estavam em 3 callbacks `init` separados
- O WordPress não garante a ordem de execução entre múltiplos callbacks na mesma prioridade
- Um padrão que referencia `sued-studio/hero` podia ser registrado antes do tipo de bloco existir
- **Correção**: consolidado em um único `add_action('init', ..., 10)` com ordem explícita interna

#### Causa 3 — `has_block('sued-studio/hero')` não detecta blocos em templates FSE
- `has_block()` lê `get_the_content()` — funciona apenas para conteúdo de posts/páginas
- Blocos em `templates/index.html` (FSE) não passam por `get_the_content()`, logo `has_block()` sempre retornava `false`
- O `hero/view.js` (Three.js) nunca era enfileirado
- **Correção**: `view.js` sempre enfileirado via `wp_enqueue_scripts` (sem condicional), com dependência explícita `['three-js']`

---



### Corrigido — Block validation e carregamento de scripts

#### `parts/header.html` — reescrito completamente
- **Causa**: `"style":{"position":{"type":"sticky"},"zIndex":100}` não são atributos válidos do `core/group`. O WordPress re-serializa o bloco e o HTML salvo não bate → `Block validation failed for core/group`
- **Causa adicional**: HTML cru (`<div class="sued-container sued-flex-between">`) misturado com markup de blocos — inválido em templates FSE
- **Correção**: reescrito usando apenas markup de blocos válidos (`wp:group` com `tagName:"header"` e layout flex). O comportamento sticky foi movido inteiramente para CSS via classe `.sued-site-header` no `header.css`

#### `parts/footer.html` — reescrito completamente
- **Causa**: mesmos problemas — raw HTML (`<div style="display:grid...">`), PHP (`<?php echo date('Y') ?>`) dentro de arquivo HTML estático FSE, e bloco `wp:paragraph` com atributos não reconhecidos (`spacing.marginTop`)
- **Correção**: reescrito com `wp:columns`/`wp:column` para o grid de 3 colunas; copyright com ano fixo (2025); todos os estilos como atributos válidos de bloco

#### `functions.php` — Three.js UMD e categoria de bloco
- **Causa**: Three.js estava sendo carregado como ES Module (`three.module.min.js`) — não expõe o global `THREE`. O `view.js` usa `typeof THREE` como guard mas ele sempre seria `undefined`, desabilitando a cena 3D silenciosamente
- **Correção**: trocado para a build UMD `three.min.js` que expõe `window.THREE`
- **Causa adicional**: categoria `sued-studio` registrada apenas para padrões (`register_block_pattern_category`), mas não para blocos no inserter do editor
- **Correção**: adicionado filtro `block_categories_all` que insere a categoria no início da lista do inserter
- **Causa adicional**: `viewScript: "file:./view.js"` no `block.json` não tem como declarar dependência de script CDN externo, então o `view.js` podia executar antes do `THREE` estar disponível
- **Correção**: `viewScript` removido do `block.json`; o `hero/view.js` agora é enfileirado manualmente via `wp_enqueue_scripts` com `has_block('sued-studio/hero')` e `['three-js']` como dependência explícita

#### `blocks/hero/block.json`
- **Causa**: `"editorScript": "file:./editor.js"` referenciava um stub vazio, causando erros silenciosos no editor Gutenberg
- **Correção**: `editorScript` removido do schema

---



### Corrigido
- **`theme.json`** — 3 avisos de validação de schema resolvidos:
  - `"version"` alterado de `2` para `3` (requisito do schema atual do WordPress 6.5+)
  - `"spacingScale": { "steps": 0 }` removido — `steps` violava o mínimo de `1` exigido pelo schema; a propriedade era desnecessária pois os tamanhos são definidos manualmente via `spacingSizes`
  - `"defaultSpacingSize": true` removido — propriedade não existe no schema oficial do `theme.json`

---

## [1.0.0] — 2026-04-26

### Criado — Estrutura Base do Tema

Implementação inicial completa do tema WordPress FSE (Full Site Editing) para a SUED Studio, seguindo o PRD institucional.

#### Arquivos Raiz

- **`style.css`** — Cabeçalho obrigatório do WordPress com metadados do tema (nome, versão, URI, textdomain)
- **`functions.php`** — Bootstrap principal do tema:
  - `after_setup_theme`: suporte a `wp-block-styles`, `editor-styles`, `responsive-embeds`, `custom-logo`, `html5`
  - `wp_enqueue_scripts`: enqueue de Google Fonts (Montserrat + Open Sans), `global.css`, `header.css`, `blocks.css`, Three.js v0.165 (CDN, defer), GSAP v3.12.5 + ScrollTrigger (CDN, defer), `main.js`
  - `init`: registro automático de todos os 7 blocos customizados via loop + `register_block_type()`
  - `after_setup_theme`: registro do menu `primary`
  - Remoção de scripts de emoji desnecessários

#### `theme.json` — Sistema de Design (FSE)

Tokens de design completos integrados ao Site Editor do WordPress:

- **Layout**: `contentSize: 800px`, `wideSize: 1280px`
- **Paleta de cores** (8 tokens):
  - `dark` → `#0F1923` (fundo principal)
  - `light` → `#F5F2EC` (texto principal)
  - `accent` → `#26AFFF` (azul destaque — usado com parcimônia)
  - `mid-dark` → `#2A3A47` (fundos de seções alternadas)
  - `muted` → `#7A8B95` (texto secundário)
  - `off-white` → `#E8E5DF`
  - `white`, `black`
- **Gradientes**: `dark-to-mid` (linear 135°), `accent-glow` (radial)
- **Tipografia**:
  - Família heading: Montserrat (weights 400–900 via Google Fonts woff2)
  - Família corpo: Open Sans (weights 300–800 via Google Fonts woff2)
  - 11 tamanhos de fonte com `fluid: true` (de `xs: 0.75rem` a `hero: clamp(3rem, 8vw, 7rem)`)
- **Espaçamento**: 13 tokens manuais (de `1: 0.25rem` a `32: 8rem`), `margin/padding/blockGap: true`
- **Bordas, sombras** (soft, accent-glow, strong) e `custom.transition`
- **Estilos globais**: background `#0F1923`, texto `#F5F2EC`, font-size base, line-height 1.7
- **Estilos de elementos**: h1–h3 com Montserrat, links em accent, botões com padding/radius/cor definidos
- **Template Parts**: `header` (area: header), `footer` (area: footer)
- **Custom Templates**: `blank`, `landing` (ambos para `page`)

---

### Criado — Templates e Parts FSE

- **`templates/index.html`** — Template principal: chama `header` part → `main` com `post-content` → `footer` part
- **`parts/header.html`** — Header sticky com glassmorphism: logo (site-logo block) + navigation block + botão CTA "Fale Conosco"
- **`parts/footer.html`** — Footer 3 colunas: marca + tagline | navegação | contato; linha divisória; copyright

---

### Criado — Assets CSS

#### `assets/css/global.css`
Sistema de design global com:
- **Tokens CSS** (`--sued-dark`, `--sued-accent`, `--sued-font-heading`, `--sued-ease`, `--sued-section-py`, `--sued-gutter`, etc.)
- **Reset** completo (box-sizing, scroll-behavior, font-smoothing)
- **Tipografia base** com `clamp()` responsivo
- **Utilitários**: `.sued-container`, `.sued-section`, `.sued-grid-2/4`, `.sued-flex-center/between`, `.sued-text-center`
- **Componente `.sued-eyebrow`**: label com linha decorativa azul antes do texto
- **Componente `.sued-btn`**: primary (azul + sombra glow no hover), outline (borda sutil), tamanho `--lg`; efeito ripple via `<span class="ripple">` injetado por JS
- **`.sued-highlight`** e **`.sued-highlight-underline`**: destaque em azul com gradiente de fundo
- **`.sued-divider`**: barra azul 48×3px
- **`[data-reveal]`**: animação CSS pura de scroll reveal (opacity + translateY/X); suporte a `data-reveal="left|right"` e `data-reveal-delay="1–5"` — **reforçado por GSAP em `main.js`**
- **`.sued-glass`**: glassmorphism (backdrop-filter blur 16px, borda sutil, fundo semi-transparente)
- **`.sued-noise::before`**: overlay SVG fractalNoise via data URI (textura de granulado)
- Scrollbar customizada, seleção de texto, focus-visible e media queries responsivas

#### `assets/css/header.css`
- Header sticky com `background` dinâmico via CSS custom property `--header-opacity` (atualizada por JS no scroll)
- `backdrop-filter: blur(20px)` com `border-bottom` sutil
- Estilos de override para `wp-block-navigation` links (Montserrat, muted → light no hover)

#### `assets/css/blocks.css`
Estilos de todos os 6 blocos não-hero:

- **Positioning**: grid 2 colunas, stats glassmorphism com hover `translateX`, dot-grid background via `radial-gradient`
- **Process**: timeline vertical com linha `::before` animada por GSAP scrub; steps com número circular (borda azul → preenchido no estado `is-active`)
- **Services**: grid auto-fill, cards glassmorphism com hover `translateY(-6px)` + gradient overlay; acordeão com `max-height` transition; tags pill com borda accent
- **Results**: grid de métricas, números em Montserrat 800 com `clamp(3rem, 6vw, 5rem)`, barra de progresso com transition `width 1.2s`; grid decorativa de linhas
- **Differential**: background text "SUED" em `opacity: 0.02`; texto em Montserrat 600, `clamp(1.5rem, 3.5vw, 2.75rem)`; highlight com gradiente de fundo azul
- **CTA**: radial glow centralizado, ações centralizadas, trust signals em linha

#### `assets/css/editor.css`
Override do editor Gutenberg para manter fundo escuro (`#0F1923`) consistente com o tema.

---

### Criado — Assets JavaScript

#### `assets/js/main.js`
Arquivo principal JS (IIFE, `'use strict'`):

1. **GSAP ScrollTrigger** — scroll reveal genérico: lê `[data-reveal]` e `data-reveal-delay`, anima com `gsap.from()` + `scrollTrigger: { start: 'top 88%', once: true }`. Suporte a direção `left`, `right`, padrão (`y`)
2. **Process timeline** — `gsap.to('.sued-process__line-fill', { height: '100%', scrub: 1 })` entre `top 70%` e `bottom 30%`; cada step recebe classe `is-active` via `ScrollTrigger.create`
3. **Result bars** — `IntersectionObserver` adiciona `is-visible` nas stats, disparando a transition `width` das barras via CSS
4. **Header scroll state** — `ScrollTrigger` atualiza `--header-opacity` via `window.addEventListener('scroll')` para transição gradual de transparência
5. **Contadores animados** — `IntersectionObserver` (threshold 0.4) com `setInterval` (16ms steps); suporte a `data-target`, `data-decimals`
6. **Acordeão de serviços** — fecha todos os cards abertos antes de abrir o clicado; acessibilidade com `aria-expanded` e `aria-hidden`; suporte a teclado (`Enter`, `Space`)
7. **Ripple effect** — injeta `<span class="ripple">` posicionado relativamente ao clique; remove-se após `animationend`

#### `blocks/hero/view.js`
Script do bloco Hero (carregado via `block.json` `viewScript`):

- **Three.js WebGLRenderer** — canvas overlay, `alpha: true`, pixelRatio limitado a 1.5 para performance
- **Partículas** (2000 desktop / 800 mobile) — posição aleatória em espaço 3D 20×12×10; cores interpoladas entre `#F5F2EC` e `#26AFFF` via `vertexColors`
- **3 anéis toroidais** — `TorusGeometry` em radii 1.5, 2.8 e 4.2; rotação independente por frame
- **Mouse parallax** — `document.mousemove` → interpolação suave (`lerp` manual) em `camera.position.x/y` e `particles.rotation`
- **`ResizeObserver`** — recalcula tamanho e aspect ratio da câmera ao redimensionar
- **GSAP entrance** — timeline sequencial animando badge → headline → sub → actions → scroll indicator
- **Cleanup** — expõe `section._heroCleanup()` para cancelar RAF e dispor o renderer

---

### Criado — 7 Blocos Customizados Gutenberg

Cada bloco contém: `block.json`, `template.php` (render server-side), `style.css`.
O bloco Hero tem adicionalmente `view.js` (Three.js) e `editor.js` (stub).

| Bloco | Namespace | Experiência |
|---|---|---|
| **Hero** | `sued-studio/hero` | Canvas Three.js fullscreen, partículas, anéis, parallax cursor, GSAP entrance |
| **Positioning** | `sued-studio/positioning` | Grid texto + 3 stat cards glassmorphism com hover; dot-grid BG |
| **Process** | `sued-studio/process` | Timeline vertical, linha scrub GSAP, steps com estado `is-active` |
| **Services** | `sued-studio/services` | 4 cards com ícone SVG inline, acordeão animado, tags pill |
| **Results** | `sued-studio/results` | 4 métricas com contador JS, barra de progresso CSS, grid decorativa |
| **Differential** | `sued-studio/differential` | Texto em Montserrat grande, palavras-chave com highlight azul, texto BG "SUED" |
| **CTA** | `sued-studio/cta` | Radial glow, 2 CTAs, 3 trust signals, ripple effect |

**Atributos dos blocos**: todos os textos (headline, subheadline, labels de CTAs, dados de métricas, etapas, serviços) são configuráveis via atributos no editor, com valores padrão em português definidos em `block.json` ou `template.php`.

---

### Criado — Padrões de Bloco

- **`inc/patterns.php`** — Registra a categoria `sued-studio` e o padrão `sued-studio/home-page` com todos os 7 blocos em sequência, pronto para inserir em qualquer página via "Adicionar Padrão"

---

### Decisões Técnicas Relevantes

| Decisão | Justificativa |
|---|---|
| Three.js via CDN (defer) | Evita aumentar o bundle do tema; defer garante que não bloqueia o parser |
| GSAP + ScrollTrigger via CDN | Mesma razão; versão pinada (3.12.5) para estabilidade |
| Render server-side (`template.php`) | SEO-friendly; conteúdo indexável sem JS; compatível com cache de página |
| `[data-reveal]` + GSAP | CSS puro é fallback; GSAP enriquece sem quebrar se falhar |
| `pixelRatio` limitado a 1.5 | Dispositivos com DPR alto (3x) renderizariam 9× mais pixels — impacto crítico em mobile |
| Glassmorphism com `backdrop-filter` | Efeito premium sem imagens; degradação graciosa em navegadores que não suportam |
| Contador via `IntersectionObserver` | Evita contar antes do elemento estar visível; `once: true` previne re-animação |
| `spacingSizes` manual sem `spacingScale` | Controle total sobre os tokens de espaçamento; `spacingScale` geraria tokens não utilizados |

---

### Stack

| Tecnologia | Versão | Papel |
|---|---|---|
| WordPress | 6.3+ (FSE) | CMS + Site Editor |
| PHP | 8.1+ | Render de blocos server-side |
| Three.js | 0.165.0 | Cena 3D do Hero |
| GSAP | 3.12.5 | Animações scroll, entrance, timeline |
| ScrollTrigger | 3.12.5 (plugin GSAP) | Gatilhos baseados em scroll |
| Montserrat | — | Tipografia de títulos (Google Fonts) |
| Open Sans | — | Tipografia de corpo (Google Fonts) |
