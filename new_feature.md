# New Feature / Ajustes — Tema WordPress SUED Studio

## Objetivo
Implementar melhorias visuais, tipográficas e de experiência no tema WordPress atual, garantindo consistência entre desktop e mobile, além de elevar o padrão premium da interface.

---

## 1. Tipografia

### Importação de fontes
Adicionar as variações da fonte Montserrat ao tema:
- Montserrat Black (900)
- Montserrat Bold (700)
- Montserrat ExtraBold (800)

As fontes devem ser carregadas de forma otimizada (preferencialmente via self-host ou Google Fonts com preload).

---

### Padronização de headings
- H1 → Montserrat Black (900)
- H2 → Montserrat ExtraBold (800)

Garantir consistência em todo o site (inclusive blocos dinâmicos do WordPress).

---

## 2. Footer

### Inserção de logo
- Adicionar o logo da empresa no footer
- Garantir boa legibilidade em fundo escuro
- Considerar versão em SVG

### Ajustes mobile
- Corrigir margens laterais
- Ajustar espaçamento vertical entre elementos
- Garantir alinhamento consistente

---

## 3. Consistência de Layout

### Padronização de elementos gráficos
Uniformizar comportamento entre desktop e mobile:

Exemplo crítico:
- Timeline da seção "Como Trabalhamos"

Ajustes esperados:
- Mesma lógica visual entre breakpoints
- Alinhamento consistente
- Espaçamento proporcional
- Legibilidade preservada

---

## 4. Flip Cards (Mobile)

### Problema atual
Os cards estão com comportamento acoplado (interdependente) no mobile.

### Ajuste necessário
- Cada card deve funcionar de forma independente
- Interação baseada em toque (tap)
- Evitar dependência de hover

### Recomendação técnica
- Controle de estado individual por card
- Evitar compartilhamento de estado global

---

## 5. Menu Mobile

### Avaliação e correção
Revisar completamente o menu mobile aberto:

- Espaçamentos
- Hierarquia visual
- Legibilidade
- Área de toque
- Comportamento de abertura/fechamento

Objetivo: garantir experiência fluida e consistente com o posicionamento premium.

---

## 6. Botão Flutuante WhatsApp

### Requisito
Adicionar botão flutuante fixo para WhatsApp em todas as páginas (desktop e mobile).

### Especificação CSS

```css
.sued-whatsapp-float {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--sued-accent);
    background: rgba(15,25,35,0.8);
    border: 1px solid rgba(38,175,255,0.2);
    box-shadow: 0 4px 12px rgba(15,25,35,0.6);
    z-index: 1000;
    transition: transform 0.3s var(--sued-ease), box-shadow 0.3s var(--sued-ease), border-color 0.3s, color 0.3s;
}
```

### Comportamento esperado
- Sempre visível (não ocultar em scroll)
- Microinterações no hover (desktop)
- Feedback ao toque (mobile)

---

## 7. Critérios de Aceite

- Tipografia aplicada corretamente em todo o site
- Footer consistente e responsivo
- Elementos gráficos padronizados entre dispositivos
- Flip-cards funcionando de forma independente no mobile
- Menu mobile revisado e funcional
- Botão WhatsApp funcional e bem posicionado

---

## 8. Restrições

- Não comprometer performance
- Não adicionar dependências desnecessárias
- Manter consistência com design system existente

---

## 9. Observação Final

Todas as alterações devem respeitar o posicionamento premium da marca, priorizando clareza, consistência e fluidez na experiência do usuário.

