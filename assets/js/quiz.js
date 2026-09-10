/**
 * SUED Studio — Interactive Quiz Engine
 *
 * Handles quiz progression, user input validation, XSS escaping,
 * LGPD compliance, honeypot anti-spam, and secure REST API transmission.
 */

(function () {
  'use strict';

  // Config object fallback if not set by wp_localize_script
  const CONFIG = window.SUED_QUIZ_CONFIG || {
    restUrl: '/wp-json/sued/v1/quiz-submit',
    nonce: '',
    calendlyUrl: 'https://calendly.com/suedstudio/diagnostico',
    whatsappNum: '5516999999999',
    agencyName: 'SUED Studio',
    siteUrl: '/'
  };

  /**
   * Helper: Escape HTML entities to prevent XSS attacks.
   */
  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // Quiz Questions Definition
  const perguntas = [
    {
      id: "setor",
      eyebrow: "Sobre seu negócio",
      texto: "Qual é o setor do seu negócio?",
      hint: "Isso nos ajuda a montar um diagnóstico mais preciso para a sua realidade.",
      tipo: "opcao",
      opcoes: [
        { emoji: "💈", label: "Beleza & Estética", sub: "Barbearia, salão, clínica estética", valor: "beleza", pontos: 2 },
        { emoji: "🚗", label: "Automotivo", sub: "Oficina, loja de peças, concessionária", valor: "auto", pontos: 2 },
        { emoji: "🏥", label: "Saúde & Bem-estar", sub: "Clínica, consultório, academia", valor: "saude", pontos: 2 },
        { emoji: "🍽️", label: "Alimentação", sub: "Restaurante, lanchonete, delivery", valor: "food", pontos: 2 },
        { emoji: "🏪", label: "Comércio local", sub: "Loja física, varejo geral", valor: "comercio", pontos: 2 },
        { emoji: "📦", label: "Outro setor", sub: "Serviços, tecnologia, outros", valor: "outro", pontos: 1 }
      ]
    },
    {
      id: "presenca",
      eyebrow: "Presença digital",
      texto: "Como está sua presença digital hoje?",
      hint: "Seja honesto — isso determina qual nível de intervenção você precisa.",
      tipo: "opcao",
      opcoes: [
        { emoji: "🚫", label: "Praticamente zero", sub: "Sem site, redes paradas ou sem perfil", valor: "zero", pontos: 4 },
        { emoji: "😬", label: "Existe, mas é fraca", sub: "Perfil criado mas sem estratégia", valor: "fraca", pontos: 3 },
        { emoji: "📊", label: "Funciona, mas sem resultado", sub: "Ativo mas não gera clientes", valor: "media", pontos: 2 },
        { emoji: "📈", label: "Relativamente bem", sub: "Gera leads, mas quero escalar", valor: "boa", pontos: 1 }
      ]
    },
    {
      id: "trafego",
      eyebrow: "Investimento em mídia",
      texto: "Você já investe em tráfego pago (Google ou Meta Ads)?",
      hint: "Anúncios online — impulsionamentos ou campanhas estruturadas.",
      tipo: "opcao",
      opcoes: [
        { emoji: "❌", label: "Nunca investi", sub: "Ainda não usei anúncios pagos", valor: "nunca", pontos: 4 },
        { emoji: "🎲", label: "Já impulsionei posts", sub: "Sem estratégia definida, resultado fraco", valor: "impulso", pontos: 3 },
        { emoji: "⚙️", label: "Tenho campanhas ativas", sub: "Mas não sei se está funcionando", valor: "ativo_sem_dado", pontos: 2 },
        { emoji: "🎯", label: "Invisto e acompanho métricas", sub: "Tenho controle dos resultados", valor: "ativo_com_dado", pontos: 1 }
      ]
    },
    {
      id: "objetivo",
      eyebrow: "Seu principal objetivo",
      texto: "O que você mais precisa resolver nos próximos 90 dias?",
      hint: "Escolha o que dói mais agora.",
      tipo: "opcao",
      opcoes: [
        { emoji: "👥", label: "Atrair mais clientes", sub: "Preciso de mais volume de novos clientes", valor: "aquisicao", pontos: 3 },
        { emoji: "🔁", label: "Fidelizar quem já comprou", sub: "Perco clientes sem conseguir manter", valor: "retencao", pontos: 2 },
        { emoji: "🌐", label: "Ter presença profissional", sub: "Preciso de site e redes organizados", valor: "presenca", pontos: 2 },
        { emoji: "📣", label: "Melhorar minha reputação", sub: "Quero ser referência no meu setor", valor: "autoridade", pontos: 2 }
      ]
    },
    {
      id: "budget",
      eyebrow: "Investimento disponível",
      texto: "Qual o investimento mensal que você consegue destinar para marketing?",
      hint: "Sem julgamentos — precisamos disso para recomendar a estratégia certa.",
      tipo: "opcao",
      opcoes: [
        { emoji: "🌱", label: "Até R$ 500/mês", sub: "Estou começando ou com budget limitado", valor: "ate500", pontos: 1 },
        { emoji: "🚀", label: "R$ 500 a R$ 1.500/mês", sub: "Consigo investir com consistência", valor: "500a1500", pontos: 2 },
        { emoji: "💼", label: "R$ 1.500 a R$ 3.000/mês", sub: "Quero crescimento mais acelerado", valor: "1500a3k", pontos: 3 },
        { emoji: "🏆", label: "Acima de R$ 3.000/mês", sub: "Escala é o objetivo principal", valor: "acima3k", pontos: 4 }
      ]
    },
    {
      id: "dor",
      eyebrow: "Maior frustração",
      texto: "Qual é a sua maior frustração com marketing hoje?",
      hint: "Isso nos ajuda a entender onde estão os maiores pontos de melhoria.",
      tipo: "opcao",
      opcoes: [
        { emoji: "💸", label: "Já gastei dinheiro sem resultado", sub: "Tentei e não funcionou", valor: "dinheiro_perdido", pontos: 4 },
        { emoji: "😵", label: "Não sei por onde começar", sub: "Muita informação, pouca direção", valor: "sem_direcao", pontos: 3 },
        { emoji: "⏰", label: "Não tenho tempo para cuidar disso", sub: "A operação consome tudo", valor: "sem_tempo", pontos: 3 },
        { emoji: "🤷", label: "Não sei se está funcionando", sub: "Faço coisas mas sem dados claros", valor: "sem_dados", pontos: 3 }
      ]
    },
    {
      id: "nome",
      eyebrow: "Quase lá",
      texto: "Como podemos te chamar?",
      hint: "Seu primeiro nome é suficiente.",
      tipo: "texto",
      placeholder: "Seu nome completo ou primeiro nome"
    },
    {
      id: "negocio",
      eyebrow: "Sobre seu negócio",
      texto: "Qual o nome da sua empresa ou negócio?",
      hint: "Vamos gerar recomendações personalizadas para sua marca.",
      tipo: "texto",
      placeholder: "Nome do negócio"
    },
    {
      id: "contato",
      eyebrow: "Finalizar Diagnóstico",
      texto: "Para onde enviamos seu relatório completo?",
      hint: "Seus dados estão protegidos pela LGPD. Não enviamos spam.",
      tipo: "contato"
    }
  ];

  /**
   * Diagnostic calculation based on answers
   */
  function calcularPerfil(respostas, pontuacao) {
    const presenca = respostas.presenca?.valor;
    const trafego = respostas.trafego?.valor;

    if (presenca === "zero" || presenca === "fraca") {
      return {
        perfil: "Fundação Urgente",
        titulo: "Seu negócio está invisível online",
        subtitulo: "A boa notícia: você tem o maior potencial de crescimento. A base precisa ser construída agora — antes de qualquer anúncio.",
        nivel: "crítico",
        diagnostico: [
          { tipo: "alerta", titulo: "Presença digital", texto: "Seu negócio praticamente não existe para quem busca online. Isso custa clientes todo dia." },
          { tipo: "alerta", titulo: "Tráfego pago", texto: "Investir em anúncios agora seria queimar dinheiro — a base precisa existir primeiro." },
          { tipo: "destaque", titulo: "Potencial real", texto: "Negócios locais que partem do zero e estruturam do jeito certo costumam dobrar de volume em 6 meses." },
          { tipo: "neutro", titulo: "Próximo passo ideal", texto: "Estruturar presença + Google Meu Negócio + 1 canal de conversão antes de qualquer campanha paga." }
        ],
        passos: [
          { n: "01", texto: "<strong>Diagnóstico completo</strong> — 30 minutos para mapear o que falta e o que atacar primeiro." },
          { n: "02", texto: "<strong>Fundação digital</strong> — perfil, landing page de alta conversão e presença local estruturada." },
          { n: "03", texto: "<strong>Primeiros anúncios</strong> — só depois da base pronta, com estratégia e ROI previsível." }
        ]
      };
    }

    if (trafego === "nunca" || trafego === "impulso") {
      return {
        perfil: "Pronto para Decolar",
        titulo: "Você tem presença, mas ainda não gera clientes de forma previsível",
        subtitulo: "A estrutura básica existe. Falta a estratégia de aquisição que transforma visualizações em faturamento real.",
        nivel: "oportunidade",
        diagnostico: [
          { tipo: "destaque", titulo: "Presença digital", texto: "Você já existe online — isso é um ponto de partida valioso em relação aos concorrentes." },
          { tipo: "alerta", titulo: "Aquisição de clientes", texto: "Sem tráfego pago estruturado, você depende de indicação e acaso. Não é escalável." },
          { tipo: "destaque", titulo: "Janela de oportunidade", texto: "Concorrentes no seu setor ainda não dominam estratégias avançadas. Agir agora gera autoridade." },
          { tipo: "neutro", titulo: "Potencial estimado", texto: "Com a estratégia certa, é possível aumentar de 30% a 60% o volume de novos clientes em 90 dias." }
        ],
        passos: [
          { n: "01", texto: "<strong>Diagnóstico estratégico</strong> — entender o que já funciona e o que falta ativar." },
          { n: "02", texto: "<strong>Estratégia de aquisição</strong> — Google e Meta Ads com foco em resultado local e ROI mensurável." },
          { n: "03", texto: "<strong>Escala gradual</strong> — montar o sistema que traz cliente qualificado todo mês de forma contínua." }
        ]
      };
    }

    return {
      perfil: "Pronto para Escalar",
      titulo: "Você já tem tração, mas ainda não atingiu a eficiência máxima",
      subtitulo: "A engrenagem existe. O que falta é otimização de funil, inteligência de dados e uma estratégia para multiplicar os resultados.",
      nivel: "escala",
      diagnostico: [
        { tipo: "destaque", titulo: "Base instalada", texto: "Você já tem presença e experiência com tráfego — a curva de aprendizado inicial já foi superada." },
        { tipo: "alerta", titulo: "Otimização de verba", texto: "Na maioria das contas que analisamos, há pelo menos 30% de budget sendo desperdiçado em campanhas mal calibradas." },
        { tipo: "destaque", titulo: "Alavancagem", texto: "Com os dados certos e ajuste do funil de conversão, o mesmo investimento pode trazer o dobro de leads." },
        { tipo: "neutro", titulo: "O que vamos analisar", texto: "Estrutura de campanhas, criativos, landing pages e métricas de conversão ponta a ponta." }
      ],
      passos: [
        { n: "01", texto: "<strong>Auditoria avançada</strong> — mapear exatamente onde o orçamento está tendo atrito." },
        { n: "02", texto: "<strong>Reestruturação do funil</strong> — novas segmentações, testes A/B de criativos e landing page dedicada." },
        { n: "03", texto: "<strong>Dashboard de escala</strong> — acompanhamento diário de métricas e tomada de decisão orientada a dados." }
      ]
    };
  }

  // Application State
  let estado = {
    tela: "intro", // "intro" | "quiz" | "loading" | "resultado"
    perguntaAtual: 0,
    respostas: {},
    contato: {
      email: '',
      telefone: '',
      lgpd: true,
      hp: ''
    },
    pontuacaoTotal: 0,
    submitting: false,
    errorMessage: '',
    leadId: null
  };

  /**
   * Main Render function
   */
  function render() {
    const app = document.getElementById("sued-quiz-app");
    if (!app) return;

    if (estado.tela === "intro") {
      app.innerHTML = renderIntro();
    } else if (estado.tela === "quiz") {
      app.innerHTML = renderQuiz();
      bindQuizEvents();
    } else if (estado.tela === "loading") {
      app.innerHTML = renderLoading();
    } else if (estado.tela === "resultado") {
      app.innerHTML = renderResultado();
    }
  }

  function renderIntro() {
    return `
      <div class="sued-quiz-intro">
        <div class="sued-quiz-intro-eyebrow">Diagnóstico Digital Gratuito · SUED Studio</div>
        <h1 class="sued-quiz-intro-title">Seu negócio está perdendo <span>clientes online</span>?</h1>
        <p class="sued-quiz-intro-sub">Responda perguntas rápidas e receba um diagnóstico exclusivo com os pontos críticos do seu posicionamento e marketing — gratuito e direto ao ponto.</p>
        <div class="sued-quiz-pills">
          <div class="sued-quiz-pill"><span class="dot">·</span> Análise personalizada</div>
          <div class="sued-quiz-pill"><span class="dot">·</span> Sem jargão técnico</div>
          <div class="sued-quiz-pill"><span class="dot">·</span> Próximos passos acionáveis</div>
        </div>
        <button class="sued-btn-primary" onclick="window.suedQuiz.iniciarQuiz()">
          Iniciar diagnóstico grátis →
        </button>
        <p class="sued-quiz-intro-time">⏱ Leva menos de 3 minutos</p>
      </div>
    `;
  }

  function renderQuiz() {
    const p = perguntas[estado.perguntaAtual];
    const totalEtapas = perguntas.length;
    const progresso = Math.round(((estado.perguntaAtual) / totalEtapas) * 100);
    const respostaAtual = estado.respostas[p.id];

    let conteudoEtapa = "";

    if (p.tipo === "opcao") {
      conteudoEtapa = `<div class="sued-quiz-options">` +
        p.opcoes.map((op, i) => `
          <button type="button" class="sued-quiz-opt-btn ${respostaAtual?.valor === op.valor ? 'selected' : ''}"
            onclick="window.suedQuiz.selecionarOpcao('${p.id}', ${i})">
            <div class="sued-quiz-opt-icon">${op.emoji}</div>
            <div>
              <div class="sued-quiz-opt-label">${escapeHtml(op.label)}</div>
              ${op.sub ? `<div class="sued-quiz-opt-sub">${escapeHtml(op.sub)}</div>` : ''}
            </div>
          </button>
        `).join('') +
      `</div>`;
    } else if (p.tipo === "texto") {
      const val = respostaAtual?.valor || '';
      conteudoEtapa = `
        <div class="sued-quiz-input-group">
          <input
            type="text"
            class="sued-quiz-text-input"
            id="sued-texto-input"
            placeholder="${escapeHtml(p.placeholder)}"
            value="${escapeHtml(val)}"
            oninput="window.suedQuiz.digitarTexto('${p.id}', this.value)"
            onkeydown="if(event.key==='Enter') window.suedQuiz.avancar()"
            autocomplete="off"
          />
        </div>
      `;
    } else if (p.tipo === "contato") {
      conteudoEtapa = `
        <div class="sued-quiz-input-group">
          <label class="sued-quiz-label" for="sued-input-email">Seu melhor E-mail (obrigatório)</label>
          <input
            type="email"
            class="sued-quiz-text-input"
            id="sued-input-email"
            placeholder="exemplo@suaempresa.com.br"
            value="${escapeHtml(estado.contato.email)}"
            oninput="window.suedQuiz.atualizarContato('email', this.value)"
            required
          />
        </div>

        <div class="sued-quiz-input-group">
          <label class="sued-quiz-label" for="sued-input-phone">WhatsApp com DDD (para envio do relatório)</label>
          <input
            type="tel"
            class="sued-quiz-text-input"
            id="sued-input-phone"
            placeholder="(16) 99999-9999"
            value="${escapeHtml(estado.contato.telefone)}"
            oninput="window.suedQuiz.atualizarContato('telefone', this.value)"
          />
        </div>

        <!-- Anti-spam Honeypot Field -->
        <input
          type="text"
          name="sued_hp_check"
          id="sued-hp-field"
          style="display:none!important;position:absolute;left:-9999px;"
          tabindex="-1"
          autocomplete="off"
          value=""
        />

        <label class="sued-quiz-consent">
          <input
            type="checkbox"
            id="sued-input-lgpd"
            ${estado.contato.lgpd ? 'checked' : ''}
            onchange="window.suedQuiz.atualizarContato('lgpd', this.checked)"
          />
          <span>Concordo em receber meu diagnóstico e contato da SUED Studio, em conformidade com a LGPD e a Política de Privacidade.</span>
        </label>

        ${estado.errorMessage ? `<div class="sued-quiz-error-msg">${escapeHtml(estado.errorMessage)}</div>` : ''}
      `;
    }

    const isNextActive = p.tipo === "contato"
      ? (estado.contato.email.includes('@') && estado.contato.lgpd)
      : !!respostaAtual;

    return `
      <div class="sued-quiz-progress">
        <div class="sued-quiz-progress-meta">
          <span>Pergunta ${estado.perguntaAtual + 1} de ${totalEtapas}</span>
          <span>${progresso}% concluído</span>
        </div>
        <div class="sued-quiz-progress-bg">
          <div class="sued-quiz-progress-fill" style="width: ${progresso}%"></div>
        </div>
      </div>

      <div class="sued-quiz-card">
        <div class="sued-quiz-eyebrow">${escapeHtml(p.eyebrow)}</div>
        <h2 class="sued-quiz-question-text">${escapeHtml(p.texto)}</h2>
        ${p.hint ? `<div class="sued-quiz-hint">${escapeHtml(p.hint)}</div>` : ''}
        
        ${conteudoEtapa}

        <div class="sued-quiz-nav">
          <button type="button" class="sued-quiz-btn-back" onclick="window.suedQuiz.voltar()" ${estado.perguntaAtual === 0 ? 'disabled' : ''}>
            ← Voltar
          </button>
          <button type="button" class="sued-quiz-btn-next ${isNextActive ? 'active' : ''}" id="sued-btn-next" onclick="window.suedQuiz.avancar()">
            ${estado.perguntaAtual === totalEtapas - 1 ? 'Ver diagnóstico completo →' : 'Continuar →'}
          </button>
        </div>
      </div>
    `;
  }

  function renderLoading() {
    return `
      <div class="sued-quiz-card sued-quiz-loading">
        <div class="sued-quiz-spinner"></div>
        <h3 class="sued-quiz-loading-title">Gerando seu diagnóstico digital...</h3>
        <p class="sued-quiz-loading-sub">Analisando suas respostas e calibrando os pontos de melhoria para o seu setor.</p>
      </div>
    `;
  }

  function renderResultado() {
    const r = calcularPerfil(estado.respostas, estado.pontuacaoTotal);
    const nomeSeguro = escapeHtml(estado.respostas.nome?.valor || "você");
    const negocioSeguro = escapeHtml(estado.respostas.negocio?.valor || "seu negócio");
    const emailSeguro = encodeURIComponent(estado.contato.email || "");
    const nomeEncoded = encodeURIComponent(estado.respostas.nome?.valor || "");

    const diagItems = r.diagnostico.map(d => `
      <div class="sued-diag-item ${d.tipo === 'destaque' ? 'destaque' : ''}">
        <div class="sued-diag-item-header">
          <div class="sued-diag-dot ${d.tipo}"></div>
          <div class="sued-diag-item-title">${escapeHtml(d.titulo)}</div>
        </div>
        <div class="sued-diag-item-text">${escapeHtml(d.texto)}</div>
      </div>
    `).join('');

    const passosHTML = r.passos.map(p => `
      <div class="sued-quiz-step-item">
        <div class="sued-quiz-step-num">${escapeHtml(p.n)}</div>
        <div class="sued-quiz-step-text">${p.texto}</div>
      </div>
    `).join('');

    // Pre-filled WhatsApp direct link
    const waText = encodeURIComponent(
      `Olá SUED Studio! Fiz o diagnóstico digital para ${estado.respostas.negocio?.valor || 'meu negócio'} (Score: ${estado.pontuacaoTotal} pontos - Perfil: ${r.perfil}). Gostaria de conversar com um especialista.`
    );
    const waLink = `https://wa.me/${CONFIG.whatsappNum}?text=${waText}`;

    const calendlyUrl = `${CONFIG.calendlyUrl}?name=${nomeEncoded}&email=${emailSeguro}&utm_source=quiz&utm_medium=diagnostico&utm_campaign=sued_quiz`;

    return `
      <div class="sued-quiz-result">
        <div class="sued-quiz-score-box">
          <div class="sued-quiz-score-circle">
            <div class="sued-quiz-score-num">${estado.pontuacaoTotal}</div>
            <div class="sued-quiz-score-label">pontos</div>
          </div>
          <div class="sued-quiz-profile-badge">${escapeHtml(r.perfil)}</div>
          <h2 class="sued-quiz-result-title">${escapeHtml(r.titulo)}</h2>
          <p class="sued-quiz-result-sub">${escapeHtml(r.subtitulo)}</p>
        </div>

        <div class="sued-diag-grid">${diagItems}</div>

        <div class="sued-quiz-steps">
          <div class="sued-quiz-steps-title">Plano recomendado para ${negocioSeguro}</div>
          ${passosHTML}
        </div>

        <div class="sued-quiz-cta-block">
          <div class="sued-quiz-cta-eyebrow">Próximo passo estratégico</div>
          <h3 class="sued-quiz-cta-title">Agende sua sessão de alinhamento gratuita</h3>
          <p class="sued-quiz-cta-sub">30 minutos com nossos estrategistas para detalhar seu plano e destravar o crescimento do seu negócio. Sem compromisso e sem enrolação.</p>
          
          <div class="sued-quiz-cta-actions">
            <a href="${calendlyUrl}" target="_blank" rel="noopener noreferrer" class="sued-btn-primary">
              Agendar minha sessão gratuita →
            </a>
            <a href="${waLink}" target="_blank" rel="noopener noreferrer" class="sued-btn-secondary">
              Conversar agora no WhatsApp ↗
            </a>
          </div>

          <p class="sued-quiz-cta-footer">🔒 Conversa 100% confidencial e estratégica.</p>
        </div>

        <div style="text-align:center; margin-top: 36px;">
          <button type="button" onclick="window.suedQuiz.reiniciar()" style="background:none;border:none;color:var(--sued-muted,#7A8B95);font-family:inherit;font-size:13px;cursor:pointer;text-decoration:underline;">
            Refazer diagnóstico
          </button>
        </div>
      </div>
    `;
  }

  function bindQuizEvents() {
    const input = document.getElementById('sued-texto-input');
    if (input) {
      setTimeout(() => input.focus(), 80);
    }
  }

  // Controller Actions
  window.suedQuiz = {
    iniciarQuiz: function () {
      estado.tela = "quiz";
      estado.perguntaAtual = 0;
      render();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    selecionarOpcao: function (id, idx) {
      const p = perguntas[estado.perguntaAtual];
      const op = p.opcoes[idx];
      estado.respostas[id] = { valor: op.valor, label: op.label, pontos: op.pontos };
      estado.pontuacaoTotal = Object.values(estado.respostas)
        .reduce((acc, r) => acc + (r.pontos || 0), 0);

      document.querySelectorAll('.sued-quiz-opt-btn').forEach((btn, i) => {
        btn.classList.toggle('selected', i === idx);
      });

      const btnNext = document.getElementById('sued-btn-next');
      if (btnNext) btnNext.classList.add('active');

      setTimeout(() => {
        window.suedQuiz.avancar();
      }, 320);
    },

    digitarTexto: function (id, valor) {
      const trimmed = valor.trim();
      estado.respostas[id] = { valor: trimmed, pontos: trimmed.length > 0 ? 1 : 0 };
      const btnNext = document.getElementById('sued-btn-next');
      if (btnNext) btnNext.classList.toggle('active', trimmed.length > 0);
    },

    atualizarContato: function (campo, valor) {
      if (campo === 'telefone') {
        // Simple phone mask (XX) XXXXX-XXXX
        let num = valor.replace(/\D/g, '').substring(0, 11);
        if (num.length > 6) {
          num = `(${num.substring(0, 2)}) ${num.substring(2, 7)}-${num.substring(7)}`;
        } else if (num.length > 2) {
          num = `(${num.substring(0, 2)}) ${num.substring(2)}`;
        }
        estado.contato.telefone = num;
        const phoneInput = document.getElementById('sued-input-phone');
        if (phoneInput) phoneInput.value = num;
      } else {
        estado.contato[campo] = valor;
      }

      const isValid = estado.contato.email.includes('@') && estado.contato.email.includes('.') && estado.contato.lgpd;
      const btnNext = document.getElementById('sued-btn-next');
      if (btnNext) btnNext.classList.toggle('active', isValid);
    },

    avancar: function () {
      const p = perguntas[estado.perguntaAtual];
      if (p.tipo === "opcao" && !estado.respostas[p.id]) return;
      if (p.tipo === "texto" && !estado.respostas[p.id]?.valor) return;

      if (p.tipo === "contato") {
        // Honeypot check
        const hpField = document.getElementById('sued-hp-field');
        if (hpField && hpField.value) {
          estado.contato.hp = hpField.value;
        }

        if (!estado.contato.email || !estado.contato.email.includes('@')) {
          estado.errorMessage = 'Por favor, informe um endereço de e-mail válido.';
          render();
          return;
        }

        if (!estado.contato.lgpd) {
          estado.errorMessage = 'O consentimento com a LGPD é necessário para gerar seu diagnóstico.';
          render();
          return;
        }

        window.suedQuiz.enviarRespostas();
        return;
      }

      if (estado.perguntaAtual < perguntas.length - 1) {
        estado.perguntaAtual++;
        render();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    },

    voltar: function () {
      if (estado.perguntaAtual > 0) {
        estado.perguntaAtual--;
        render();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    },

    reiniciar: function () {
      estado = {
        tela: "intro",
        perguntaAtual: 0,
        respostas: {},
        contato: { email: '', telefone: '', lgpd: true, hp: '' },
        pontuacaoTotal: 0,
        submitting: false,
        errorMessage: '',
        leadId: null
      };
      render();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    enviarRespostas: async function () {
      estado.tela = "loading";
      render();

      const r = calcularPerfil(estado.respostas, estado.pontuacaoTotal);

      const payload = {
        nonce: CONFIG.nonce,
        nome: estado.respostas.nome?.valor || '',
        negocio: estado.respostas.negocio?.valor || '',
        email: estado.contato.email,
        telefone: estado.contato.telefone,
        setor: estado.respostas.setor?.valor || '',
        presenca: estado.respostas.presenca?.valor || '',
        trafego: estado.respostas.trafego?.valor || '',
        objetivo: estado.respostas.objetivo?.valor || '',
        budget: estado.respostas.budget?.valor || '',
        dor: estado.respostas.dor?.valor || '',
        pontuacaoTotal: estado.pontuacaoTotal,
        perfil: r.perfil,
        lgpd: estado.contato.lgpd ? 1 : 0,
        sued_hp_check: estado.contato.hp,
        source: window.location.href,
        respostas: estado.respostas
      };

      try {
        const response = await fetch(CONFIG.restUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': CONFIG.nonce
          },
          body: JSON.stringify(payload)
        });

        const data = await response.json();
        if (response.ok && data.success) {
          estado.leadId = data.lead_id;
        } else {
          console.warn('SUED Quiz API Warning:', data.message || 'Falha ao sincronizar');
        }
      } catch (err) {
        console.error('SUED Quiz Connection Error:', err);
      } finally {
        // In case of error or success, proceed to show result so user experience isn't blocked
        estado.tela = "resultado";
        render();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    }
  };

  // Initial render when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', render);
  } else {
    render();
  }
})();
