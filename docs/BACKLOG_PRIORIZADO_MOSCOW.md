# 📋 BACKLOG PRIORIZADO (MoSCoW) - TUBARON GAMIFICAÇÃO

**Cliente**: Tubaron Telecomunicações  
**Projeto**: Sistema de Tarefas Gamificado  
**Método**: MoSCoW (Must, Should, Could, Won't)  
**Data**: 04 de novembro de 2025  

---

## Legenda MoSCoW

- **MUST (Essencial)**: Sem isso, sistema não funciona ou não atende negócio
- **SHOULD (Importante)**: Agrega muito valor, mas sistema funciona sem
- **COULD (Desejável)**: Nice-to-have, implementar se sobrar tempo/budget
- **WON'T (Não será feito)**: Fora escopo atual, futuro roadmap

---

## ✅ MUST HAVE (Prioridade Crítica)

### MVP (Minimum Viable Product) - Sprints 1-10

| ID | Feature | Tarefas | Justificativa | Sprint |
|----|---------|---------|---------------|--------|
| M-001 | **Setup Infraestrutura** | T001, T002 | Sem isso, nada roda | 1 |
| M-002 | **Autenticação & RBAC** | T003, T004 | Segurança, controle acesso | 1-2 |
| M-003 | **CRUD Temporadas** | T005 | Core negócio (campeonatos 6-12 meses) | 2 |
| M-004 | **CRUD Equipes (min 3)** | T006 | Requisito explícito cliente | 2-3 |
| M-005 | **CRUD Tarefas (3 tipos)** | T007, T008, T009 | Individual, Team, Competitive | 3-4 |
| M-006 | **Sistema Votação** | T010 | Core competitivas, diferencial | 4-5 |
| M-007 | **Anti-Fraude Votação** | T010 | Confiabilidade, segurança | 5 |
| M-008 | **Pontuação & Rankings** | T011 | Core gamificação | 5-6 |
| M-009 | **Desempate Rankings** | T011 | Justiça, transparência | 6 |
| M-010 | **Integração RH** | T017 | Sync colaboradores, desligamentos | 8 |
| M-011 | **Dashboard Colaborador** | T015 | UX principal usuários | 6 |
| M-012 | **Frontend Tasks** | T019 | UI criar/submeter tarefas | 7-8 |
| M-013 | **Frontend Rankings** | T021 | Visualização leaderboard | 8 |
| M-014 | **LGPD Compliance** | T025 | Legal obrigatório (Art. 18) | 9-10 |
| M-015 | **Audit Trail** | T002 (audit_logs) | Compliance, anti-fraude | 1 |

**Total MUST**: 15 features, 18 tarefas, ~10 sprints (20 semanas)

---

## 🟢 SHOULD HAVE (Alta Prioridade)

### Pós-MVP - Sprints 11-16

| ID | Feature | Tarefas | Justificativa | Sprint |
|----|---------|---------|---------------|--------|
| S-001 | **Missões (Weights)** | T014 | Agrupamento temático, prioridades | 11 |
| S-002 | **Celery Async Jobs** | T012 | Performance (apuração não bloqueia) | 11-12 |
| S-003 | **WebSocket Real-Time** | T013, T020 | UX superior, engagement | 12 |
| S-004 | **Achievements/Badges** | T022 | Gamificação avançada, motivação | 13 |
| S-005 | **Notifications** | T023 | Engagement, alertas prazos | 13 |
| S-006 | **Calendário/Timeline** | T029 | Visualização eventos, planejamento | 14 |
| S-007 | **Dashboard Team** | T016 | Gestão equipe, identificar inatividade | 14 |
| S-008 | **Dashboard Admin** | T030 | KPIs corporativos, decisões gestão | 15 |
| S-009 | **Relatórios (CSV/Excel)** | T024 | Analytics, exportação dados | 15-16 |
| S-010 | **Testes Automatizados** | T026, T027 | Qualidade, manutenção | 16 |

**Total SHOULD**: 10 features, 12 tarefas, ~6 sprints

---

## 🟡 COULD HAVE (Média Prioridade)

### Refinos & Polish - Sprints 17-19

| ID | Feature | Justificativa | Sprint |
|----|---------|---------------|--------|
| C-001 | **Comentários em Tarefas** | Colaboração, discussões | 17 |
| C-002 | **Menções @username** | Notificações direcionadas | 17 |
| C-003 | **Upload Imagens Preview** | UX (ver submissions sem download) | 17 |
| C-004 | **Dark Mode** | Acessibilidade, preferência usuários | 18 |
| C-005 | **Exportar Relatórios PDF** | Além CSV/Excel, apresentações | 18 |
| C-006 | **Streaks Visualization** | Gamificação visual (fire icons) | 18 |
| C-007 | **Leaderboard Histórico** | Ver rankings passados (temporadas antigas) | 18 |
| C-008 | **Search/Filters Avançados** | Buscar tarefas por keyword, tags | 19 |
| C-009 | **Drag & Drop Upload** | UX moderna (vs file input) | 19 |
| C-010 | **PWA (Progressive Web App)** | Instalável, offline-first (básico) | 19 |

**Total COULD**: 10 features (implementar se sobrar tempo)

---

## 🔴 WON'T HAVE (Fora Escopo v1.0)

### Roadmap Futuro (v2.0, v3.0)

| ID | Feature | Por que WON'T | Quando? |
|----|---------|---------------|---------|
| W-001 | **Mobile App Nativo (React Native)** | v1.0 PWA suficiente | v2.0 (6 meses pós-launch) |
| W-002 | **IA Sugestões Tarefas** | Necessita dados históricos (min 6 meses) | v2.0 |
| W-003 | **Detecção Fraude Avançada (ML)** | Treinar modelos requer dados | v2.0 |
| W-004 | **Integrações Slack/Teams** | Nice-to-have, não core | v2.0 |
| W-005 | **API Pública (Partners)** | Sem parceiros externos v1.0 | v3.0 |
| W-006 | **Multi-Tenancy (outras empresas)** | Tubaron apenas agora | v3.0 |
| W-007 | **Gamificação Avançada (loot boxes, minigames)** | Over-engineering v1.0 | v3.0 |
| W-008 | **Vídeo Conferência In-App** | Teams/Zoom suficiente | Nunca |
| W-009 | **Blockchain Badges (NFT)** | Hype, sem valor negócio | Nunca |
| W-010 | **Realidade Aumentada** | Over-kill corporativo | Nunca |

**Total WON'T**: 10 features (roadmap futuro ou descartadas)

---

## 📊 Matriz Esforço × Valor

```
      │ Alto Valor
      │
   M  │  M-006 Votação      M-008 Rankings
   U  │  M-004 Equipes      M-011 Dashboard
   S  │  M-005 Tarefas      M-014 LGPD
   T  │
──────┼────────────────────────────────────
   S  │  S-003 WebSocket    S-008 Dash Admin
   H  │  S-004 Achievements S-009 Relatórios
   O  │  S-006 Calendário   
   U  │
   L  │
   D  │
──────┼────────────────────────────────────
   C  │  C-004 Dark Mode    C-010 PWA
   O  │  C-001 Comentários  
   U  │  C-008 Search       
   L  │
   D  │
──────┼────────────────────────────────────
   W  │  W-001 Mobile Native
   O  │  W-007 Loot Boxes   W-009 NFT Badges
   N  │  
   '  │
   T  │________________________________
      │  Baixo Esforço         Alto Esforço
```

**Estratégia Priorização:**
1. Implementar **MUST** primeiro (core negócio)
2. Adicionar **SHOULD** (alto valor, esforço justificável)
3. **COULD** apenas se sprint termina early (buffer)
4. **WON'T** documentar para roadmap futuro

---

## 🎯 Definition of Done (DoD)

Cada feature MUST/SHOULD atende:

- [ ] **Funcional**: Código implementado, testado manualmente
- [ ] **Testado**: Unit tests (se backend), component tests (se frontend)
- [ ] **Documentado**: Swagger (backend) ou Storybook (frontend)
- [ ] **Revisado**: Code review aprovado (2+ devs)
- [ ] **LGPD**: Audit log (se modifica dados), políticas respeitadas
- [ ] **Acessível**: axe-core 0 violations, keyboard nav
- [ ] **Deployed**: Staging environment funcional
- [ ] **Demo**: Product Owner aprovou (sprint review)

---

## 📅 Release Plan

### v1.0 MVP (Semana 20)

**Features Incluídas:**
- ✅ Todos MUST (M-001 a M-015)
- ✅ 70% SHOULD (S-001 a S-007)

**Critérios Go-Live:**
- 200+ tests passando (coverage 80%+)
- Zero bugs P0/P1
- LGPD compliance aprovado DPO
- Performance: API p95 <500ms, WS <100ms
- WCAG 2.1 AA (axe-core 0 violations)
- Treinamento realizado (admins, captains, colaboradores)

### v1.1 Refinos (Semana 24, +4 semanas pós-launch)

**Features Adicionais:**
- Restante SHOULD (S-008 a S-010)
- 50% COULD (C-001 a C-005) baseado em feedback v1.0

### v2.0 Major (6 meses pós-launch)

**Features:**
- Mobile App React Native (W-001)
- IA Sugestões (W-002)
- Integrações Slack/Teams (W-004)

---

## 🚦 Status Tracking (Template Sprint Planning)

```markdown
## Sprint X Planning

### Committed (MUST/SHOULD)
- [ ] T00X - Feature Y (8 story points)
- [ ] T00Y - Feature Z (5 story points)

### Stretch Goals (COULD)
- [ ] C-001 - Comentários (3 story points)

### Blocked
- [ ] T00Z - Aguardando API RH (dependência externa)

### Done (Semana Anterior)
- [x] T005 - CRUD Seasons ✅
- [x] T006 - CRUD Teams ✅

**Velocity**: 40 story points (média últimas 3 sprints)
**Capacity**: 8 pessoas × 30h = 240h disponíveis
```

---

## 🎓 Lições Aprendidas (Pré-Mortem)

**Riscos Identificados:**

1. **Over-Commitment COULD Features**:
   - Mitigação: Marcar COULD como "nice-to-have", não commit sprint
   - Aceitar: Algumas COULD não serão feitas v1.0

2. **Scope Creep (Stakeholders Pedem Mais)**:
   - Mitigação: Change request formal, avaliar impacto roadmap, negociar
   - Aceitar: Algumas solicitações vão para v1.1/v2.0

3. **Integrações Externas Atrasam (HR API)**:
   - Mitigação: Mock dev, validar contrato API early, testes contínuos
   - Aceitar: T017 pode atrasar, não bloqueia outras features (parallel)

---

## ✅ Checklist Priorização

Antes de adicionar feature ao backlog, validar:

- [ ] Alinha com objetivos negócio Tubaron (engajamento, gamificação)
- [ ] Viável tecnicamente (stack React/FastAPI suporta)
- [ ] Esforço conhecido (estimation session, poker planning)
- [ ] Valor mensurável (OKRs, métricas)
- [ ] Não duplica feature existente
- [ ] LGPD/Security considerados

Se **3+ checkboxes falsos** → categoria COULD ou WON'T.

---

**Documento vivo**: Atualizar após cada sprint review.

**Product Owner**: [Nome PO Tubaron]  
**Tech Lead**: [Nome Tech Lead Squad]  
**Última Revisão**: Sprint 0 (Planning)

