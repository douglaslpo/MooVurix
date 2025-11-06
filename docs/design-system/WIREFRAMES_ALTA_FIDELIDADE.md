# 🖼️ WIREFRAMES ALTA FIDELIDADE - TUBARON GAMIFICATION

**Design System**: v1.0  
**Ferramenta**: Figma (protótipo interativo)  
**Viewports**: Desktop 1920px, Tablet 768px, Mobile 375px  
**Status**: Production-Ready  

---

## 📐 ESTRUTURA GLOBAL

### Layout Master Template

```
┌─────────────────────────────────────────────────────────────────┐
│ HEADER (64px fixed)                                              │
│ ┌───────┬─────────────────────────────────────┬────────────────┐│
│ │ Logo  │ Navigation                          │ User Menu      ││
│ │ 160px │ (Dashboard, Tarefas, Rankings...)   │ + Notifications││
│ └───────┴─────────────────────────────────────┴────────────────┘│
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│ ┌────────────┬───────────────────────────────────────────────┐  │
│ │ SIDEBAR    │ MAIN CONTENT AREA                             │  │
│ │ (240px)    │                                                │  │
│ │            │                                                │  │
│ │ • Quick    │  [Conteúdo dinâmico baseado em rota]          │  │
│ │   Actions  │                                                │  │
│ │            │                                                │  │
│ │ • Filters  │                                                │  │
│ │            │                                                │  │
│ │ • Mini     │                                                │  │
│ │   Ranking  │                                                │  │
│ │            │                                                │  │
│ │ (collaps.  │                                                │  │
│ │  mobile)   │                                                │  │
│ └────────────┴───────────────────────────────────────────────┘  │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
│ FOOTER (opcional, 48px)                                          │
└─────────────────────────────────────────────────────────────────┘
```

### Header Component (Desktop)

```
╔══════════════════════════════════════════════════════════════════╗
║ 🎯 TUBARON      Dashboard  Tarefas  Rankings  Calendário  Admin  ║
║ Gamification    ─────────                                        ║
║                                                  🔍  🔔(3)  👤    ║
╚══════════════════════════════════════════════════════════════════╝
```

**Especificações**:
- Altura: 64px
- Background: `bg-white dark:bg-neutral-900`
- Border bottom: `border-b border-neutral-200 dark:border-neutral-800`
- Logo: 160px width (SVG, altura auto)
- Navigation: Gap 32px, text-base, font-medium
- Active state: primary-600 color + bottom border 2px
- Search icon: abre Command Palette (Cmd+K)
- Notifications: badge count, dropdown on click
- Avatar: 40px circle, dropdown menu (Perfil, Config, Sair)

---

## 🏠 DASHBOARD COLABORADOR

### Hero Section (Gradiente Primary)

```
╔═══════════════════════════════════════════════════════════════════╗
║  👋 Olá, João Silva!                            Temporada 2025    ║
║  Você está em 5º lugar. Continue assim! 🚀                        ║
║                                                                    ║
║  ┌────────────────┬────────────────┬────────────────┬───────────┐ ║
║  │ 🏆 PONTOS      │ 📊 POSIÇÃO     │ ✅ TAREFAS     │ 🔥 STREAK │ ║
║  │                │                │                │           │ ║
║  │ 285            │ 5º lugar       │ 23             │ 7 dias    │ ║
║  │ +15 hoje       │ ↑ subiu 2      │ 4 pendentes    │ 🔥🔥🔥   │ ║
║  └────────────────┴────────────────┴────────────────┴───────────┘ ║
╚═══════════════════════════════════════════════════════════════════╝
```

**Implementação**:
```tsx
<div className="bg-gradient-to-br from-primary-600 to-primary-800 rounded-2xl p-8 text-white">
  {/* Greeting */}
  <div className="flex justify-between items-start mb-6">
    <div>
      <h1 className="text-3xl font-bold mb-2">
        👋 Olá, {user.name}!
      </h1>
      <p className="text-white/80 text-lg">
        Você está em {user.rank}º lugar. Continue assim! 🚀
      </p>
    </div>
    <Badge variant="achievement" className="bg-white/20">
      Temporada {currentSeason.name}
    </Badge>
  </div>

  {/* KPI Grid */}
  <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
    <KPICard
      icon={<Trophy />}
      label="Pontos"
      value={user.points}
      change="+15 hoje"
      trend="positive"
    />
    {/* ... outros KPIs */}
  </div>
</div>
```

**Animações**:
- Numbers count-up ao entrar (react-countup)
- Badges pulse em updates real-time
- Gradient animado (keyframes)

---

### Tarefas Urgentes

```
╔════════════════════════════════════════════════════════════════╗
║  ⚡ URGENTE (Prazo <24h)                      [Ver Todas →]    ║
║                                                                 ║
║  ┌──────────────────────────────────────────────────────────┐  ║
║  │ 🎯 COMPETITIVA  🔴 URGENTE                               │  ║
║  │                                                           │  ║
║  │ Melhorar NPS Atendimento                                 │  ║
║  │ ──────────────────────────                               │  ║
║  │ 📅 Hoje, 18:00  👥 Equipe Alpha  🏆 50 pontos           │  ║
║  │                                                           │  ║
║  │ ┌────────────────────────────────────────────────────┐   │  ║
║  │ │ Progresso: 2/3 submissões                          │   │  ║
║  │ │ ██████████████████░░░░░░░░░░ 67%                   │   │  ║
║  │ └────────────────────────────────────────────────────┘   │  ║
║  │                                                           │  ║
║  │ [Ver Detalhes]  [Submeter Agora →]                      │  ║
║  └──────────────────────────────────────────────────────────┘  ║
║                                                                 ║
║  ┌──────────────────────────────────────────────────────────┐  ║
║  │ 📋 INDIVIDUAL   ⚠️ 18h restantes                         │  ║
║  │                                                           │  ║
║  │ Relatório Mensal Vendas                                  │  ║
║  │ ───────────────────────                                  │  ║
║  │ 📅 Amanhã, 12:00  🏆 10 pontos                           │  ║
║  │                                                           │  ║
║  │ [Marcar como Completa]  [Ver Detalhes]                   │  ║
║  └──────────────────────────────────────────────────────────┘  ║
╚════════════════════════════════════════════════════════════════╝
```

**TaskCard Component (Expandido)**:
```tsx
<div className={cn(
  "bg-white dark:bg-neutral-800 rounded-xl p-6",
  "border-2",
  task.urgency === 'urgent' && "border-error-500",
  task.urgency === 'due_soon' && "border-warning-500",
  task.urgency === 'normal' && "border-neutral-200 dark:border-neutral-700",
  "shadow-base hover:shadow-md transition-all duration-300"
)}>
  {/* Header */}
  <div className="flex items-start justify-between mb-4">
    <div className="flex items-center gap-3">
      <TaskTypeIcon type={task.type} />
      {task.urgency === 'urgent' && (
        <Badge variant="status" color="error">
          🔴 URGENTE
        </Badge>
      )}
      {task.urgency === 'due_soon' && (
        <Badge variant="status" color="warning">
          ⚠️ {task.timeRemaining}
        </Badge>
      )}
    </div>
  </div>

  {/* Title */}
  <h3 className="text-xl font-semibold text-neutral-900 dark:text-neutral-100 mb-3">
    {task.title}
  </h3>

  {/* Meta Info */}
  <div className="flex items-center gap-4 text-sm text-neutral-600 dark:text-neutral-400 mb-4">
    <div className="flex items-center gap-1.5">
      <Calendar className="w-4 h-4" />
      {formatDate(task.dueDate)}
    </div>
    
    {task.team && (
      <div className="flex items-center gap-1.5">
        <Users className="w-4 h-4" />
        Equipe {task.team.name}
      </div>
    )}

    <div className="flex items-center gap-1.5">
      <Trophy className="w-4 h-4" />
      {task.points} pontos
    </div>
  </div>

  {/* Progress (se competitiva) */}
  {task.type === 'competitive' && task.progress && (
    <div className="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-3 mb-4">
      <p className="text-xs text-neutral-600 dark:text-neutral-400 mb-2">
        Progresso: {task.progress.completed}/{task.progress.total} submissões
      </p>
      <div className="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2">
        <div
          className="bg-primary-600 h-2 rounded-full transition-all duration-500"
          style={{ width: `${task.progress.percentage}%` }}
        />
      </div>
    </div>
  )}

  {/* Actions */}
  <div className="flex gap-3">
    <Button variant="ghost" size="sm">
      Ver Detalhes
    </Button>
    <Button variant="primary" size="sm" className="flex-1">
      {task.type === 'competitive' ? 'Submeter Agora' : 'Marcar como Completa'}
      <ArrowRight className="w-4 h-4" />
    </Button>
  </div>
</div>
```

---

### Mini Ranking (Sidebar)

```
╔══════════════════════════════╗
║  🏆 TOP 5 GERAL              ║
║  ─────────────────           ║
║                              ║
║  1. 🥇 Maria (Beta)          ║
║     420 pts   ↑+2            ║
║                              ║
║  2. 🥈 Carlos (Gamma)        ║
║     380 pts   ─ 0            ║
║                              ║
║  3. 🥉 Ana (Alpha)           ║
║     350 pts   ↑+1            ║
║                              ║
║  4. 📍 Pedro (Delta)         ║
║     310 pts   ↓-2            ║
║                              ║
║  5. 📍 João (Alpha) ← Você   ║
║     285 pts   ↑+2            ║
║                              ║
║  [Ver Ranking Completo →]    ║
╚══════════════════════════════╝
```

**Implementação**:
```tsx
<div className="bg-white dark:bg-neutral-800 rounded-xl p-6 shadow-base">
  <h3 className="text-lg font-bold text-neutral-900 dark:text-neutral-100 mb-4 flex items-center gap-2">
    <Trophy className="w-5 h-5 text-gold-500" />
    Top 5 Geral
  </h3>

  <div className="space-y-3">
    {topRankings.map((item, index) => (
      <div
        key={item.userId}
        className={cn(
          "flex items-center gap-3 p-2 rounded-lg transition-colors",
          item.isCurrentUser && "bg-primary-50 dark:bg-primary-900/20"
        )}
      >
        {/* Rank Medal */}
        <div className="w-8 text-2xl">
          {index === 0 && '🥇'}
          {index === 1 && '🥈'}
          {index === 2 && '🥉'}
          {index > 2 && '📍'}
        </div>

        {/* User Info */}
        <div className="flex-1 min-w-0">
          <p className="font-semibold text-neutral-900 dark:text-neutral-100 truncate">
            {item.name}
            {item.isCurrentUser && (
              <span className="text-primary-600 text-xs ml-2">← Você</span>
            )}
          </p>
          <p className="text-sm text-neutral-600 dark:text-neutral-400">
            ({item.teamName})
          </p>
        </div>

        {/* Points & Trend */}
        <div className="text-right">
          <p className="font-bold text-neutral-900 dark:text-neutral-100">
            {item.points} pts
          </p>
          <div className={cn(
            "text-xs flex items-center justify-end gap-0.5",
            item.trend > 0 && "text-success-600",
            item.trend < 0 && "text-error-600",
            item.trend === 0 && "text-neutral-500"
          )}>
            {item.trend > 0 && `↑+${item.trend}`}
            {item.trend < 0 && `↓${item.trend}`}
            {item.trend === 0 && '─ 0'}
          </div>
        </div>
      </div>
    ))}
  </div>

  <Button variant="ghost" size="sm" className="w-full mt-4">
    Ver Ranking Completo
    <ArrowRight className="w-4 h-4" />
  </Button>
</div>
```

---

## ✅ PÁGINA: TAREFAS (Lista Completa)

### Header + Filtros

```
╔══════════════════════════════════════════════════════════════════╗
║  Tarefas                                                          ║
║  ════════                                                         ║
║                                                                   ║
║  [+ Nova Tarefa]                        🔍 Buscar...              ║
║                                                                   ║
║  Filtros:  [Todas ▼]  [Individual ▼]  [Status ▼]  [Ordenar ▼]   ║
║            ─────────                                              ║
║                                                                   ║
║  📊 23 tarefas encontradas  •  4 pendentes  •  19 completas       ║
╚══════════════════════════════════════════════════════════════════╝
```

**Filter Component**:
```tsx
<div className="flex flex-wrap items-center gap-4 mb-6">
  <Select
    label="Tipo"
    options={[
      { value: 'all', label: 'Todas' },
      { value: 'individual', label: 'Individual' },
      { value: 'team', label: 'Equipe' },
      { value: 'competitive', label: 'Competitiva' }
    ]}
    value={filters.type}
    onChange={handleTypeFilter}
  />

  <Select
    label="Status"
    options={[
      { value: 'all', label: 'Todos' },
      { value: 'open', label: 'Abertas' },
      { value: 'in_progress', label: 'Em Andamento' },
      { value: 'voting', label: 'Em Votação' },
      { value: 'completed', label: 'Completas' }
    ]}
    value={filters.status}
    onChange={handleStatusFilter}
  />

  <Select
    label="Ordenar"
    options={[
      { value: 'due_date', label: 'Prazo (mais próximo)' },
      { value: 'points', label: 'Pontos (maior)' },
      { value: 'created_at', label: 'Mais recentes' }
    ]}
    value={filters.sort}
    onChange={handleSortFilter}
  />
</div>
```

---

### Lista Tarefas (Grid Responsivo)

```
╔════════════════════════════════════════════════════════════════╗
║  ┌────────────────────┬────────────────────┬───────────────┐   ║
║  │ 🎯 COMPETITIVA     │ 📋 INDIVIDUAL      │ 👥 EQUIPE     │   ║
║  │ ────────────       │ ────────────       │ ────────      │   ║
║  │ Melhorar NPS       │ Relatório Vendas   │ Documentação  │   ║
║  │                    │                    │               │   ║
║  │ 🔴 Hoje, 18:00     │ ⚠️ Amanhã, 12:00   │ ✅ Completa   │   ║
║  │ 🏆 50 pts          │ 🏆 10 pts          │ 🏆 20 pts     │   ║
║  │                    │                    │               │   ║
║  │ [Submeter]         │ [Completar]        │ [Ver]         │   ║
║  └────────────────────┴────────────────────┴───────────────┘   ║
║                                                                 ║
║  ┌────────────────────┬────────────────────┬───────────────┐   ║
║  │ ...mais cards...   │                    │               │   ║
║  └────────────────────┴────────────────────┴───────────────┘   ║
║                                                                 ║
║  ┌──────────────────────────────────────────────────────────┐  ║
║  │          ← 1  2  3  4  5  6  7  8  9  10 →               │  ║
║  │          (Paginação: 12 cards por página)                │  ║
║  └──────────────────────────────────────────────────────────┘  ║
╚════════════════════════════════════════════════════════════════╝
```

**Grid Layout**:
```tsx
<div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
  {tasks.map(task => (
    <TaskCard key={task.id} task={task} />
  ))}
</div>

{/* Pagination */}
<div className="flex justify-center items-center gap-2 mt-8">
  <Button
    variant="ghost"
    size="sm"
    disabled={page === 1}
    onClick={() => setPage(page - 1)}
  >
    <ChevronLeft className="w-4 h-4" />
  </Button>

  {Array.from({ length: totalPages }, (_, i) => i + 1).map(p => (
    <button
      key={p}
      onClick={() => setPage(p)}
      className={cn(
        "w-8 h-8 rounded-lg font-medium transition-colors",
        p === page
          ? "bg-primary-600 text-white"
          : "bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-200 dark:hover:bg-neutral-700"
      )}
    >
      {p}
    </button>
  ))}

  <Button
    variant="ghost"
    size="sm"
    disabled={page === totalPages}
    onClick={() => setPage(page + 1)}
  >
    <ChevronRight className="w-4 h-4" />
  </Button>
</div>
```

---

## 🗳️ PÁGINA: VOTAÇÃO COMPETITIVA

### Header Votação (Timer + Progresso)

```
╔══════════════════════════════════════════════════════════════════╗
║  🗳️ Votação: Melhorar NPS Atendimento                            ║
║  ═══════════════════════════════════════                         ║
║                                                                   ║
║  ⏰ Encerra em: 18h 23min          Votos: 47/100  ███████░░░ 47% ║
║                                                                   ║
║  Método: Notas (0-10)  •  Seu voto: Pendente ⚠️                  ║
╚══════════════════════════════════════════════════════════════════╝
```

**Timer Component (Real-Time)**:
```tsx
const VotingTimer = ({ deadline }: { deadline: Date }) => {
  const [timeLeft, setTimeLeft] = useState('')

  useEffect(() => {
    const interval = setInterval(() => {
      const now = new Date()
      const diff = deadline.getTime() - now.getTime()
      
      if (diff <= 0) {
        setTimeLeft('Encerrada')
        clearInterval(interval)
      } else {
        const hours = Math.floor(diff / (1000 * 60 * 60))
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
        setTimeLeft(`${hours}h ${minutes}min`)
      }
    }, 1000)

    return () => clearInterval(interval)
  }, [deadline])

  return (
    <div className="flex items-center gap-2 text-lg">
      <Clock className="w-5 h-5" />
      <span className="font-semibold">Encerra em:</span>
      <span className={cn(
        "font-bold",
        timeLeft.includes('h') && parseInt(timeLeft) < 24 && "text-error-600"
      )}>
        {timeLeft}
      </span>
    </div>
  )
}
```

---

### Submissions Grid

```
╔══════════════════════════════════════════════════════════════════╗
║  Equipe Alpha - Submissão #1                                     ║
║  ══════════════════════════════                                  ║
║                                                                   ║
║  ┌────────────────────────────────────────────────────────────┐  ║
║  │ Nossa estratégia para melhorar NPS:                        │  ║
║  │                                                             │  ║
║  │ 1. Checklist pós-atendimento com 8 pontos-chave            │  ║
║  │ 2. Treinamento equipe (script conversacional)              │  ║
║  │ 3. Follow-up automático 24h após atendimento               │  ║
║  │ 4. Dashboard real-time para líderes                        │  ║
║  │                                                             │  ║
║  │ Resultados esperados: +15 pontos NPS em 60 dias            │  ║
║  └────────────────────────────────────────────────────────────┘  ║
║                                                                   ║
║  📎 Anexos (3):                                                   ║
║  📄 checklist.pdf  📊 planilha.xlsx  📝 script.docx              ║
║                                                                   ║
║  ┌────────────────────────────────────────────────────────────┐  ║
║  │ Sua Nota:  ⭐ ⭐ ⭐ ⭐ ⭐ ⭐ ⭐ ⭐ ⭐ ⭐  (9.0 / 10)         │  ║
║  │            ───────────────────────────                      │  ║
║  │                                                             │  ║
║  │            [Cancelar]    [Confirmar Voto →]                │  ║
║  └────────────────────────────────────────────────────────────┘  ║
║                                                                   ║
║  ─────────────────────────────────────────────────────────────   ║
║                                                                   ║
║  Equipe Beta - Submissão #2                                      ║
║  ══════════════════════════                                      ║
║  [... similar structure ...]                                     ║
║                                                                   ║
║  ⚠️ Você não pode votar na Equipe Alpha (sua equipe)             ║
╚══════════════════════════════════════════════════════════════════╝
```

**SubmissionVoteCard Component**:
```tsx
const SubmissionVoteCard = ({
  submission,
  votingConfig,
  onVote,
  disabled,
  disabledReason
}: SubmissionVoteCardProps) => {
  const [rating, setRating] = useState(0)
  const [hover, setHover] = useState(0)

  return (
    <div className="bg-white dark:bg-neutral-800 rounded-xl p-6 shadow-base">
      {/* Header */}
      <div className="flex items-center justify-between mb-4">
        <h3 className="text-xl font-bold text-neutral-900 dark:text-neutral-100">
          {submission.teamName} - Submissão #{submission.id}
        </h3>
        {submission.votesCount > 0 && (
          <Badge variant="status" color="primary">
            {submission.votesCount} voto{submission.votesCount > 1 ? 's' : ''}
          </Badge>
        )}
      </div>

      {/* Content */}
      <div className="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-4 mb-4">
        <p className="text-neutral-700 dark:text-neutral-300 whitespace-pre-line">
          {submission.content}
        </p>
      </div>

      {/* Attachments */}
      {submission.files.length > 0 && (
        <div className="mb-4">
          <p className="text-sm font-medium text-neutral-600 dark:text-neutral-400 mb-2">
            📎 Anexos ({submission.files.length}):
          </p>
          <div className="flex flex-wrap gap-2">
            {submission.files.map(file => (
              <a
                key={file.url}
                href={file.url}
                target="_blank"
                rel="noopener noreferrer"
                className="flex items-center gap-2 px-3 py-2 bg-neutral-100 dark:bg-neutral-800 rounded-lg hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors"
              >
                <FileIcon type={file.type} />
                <span className="text-sm text-neutral-700 dark:text-neutral-300">
                  {file.name}
                </span>
              </a>
            ))}
          </div>
        </div>
      )}

      {/* Voting Interface */}
      {disabled ? (
        <div className="bg-warning-50 dark:bg-warning-900/20 rounded-lg p-4 border border-warning-500">
          <p className="text-warning-700 dark:text-warning-300 flex items-center gap-2">
            <AlertTriangle className="w-5 h-5" />
            {disabledReason}
          </p>
        </div>
      ) : (
        <div className="bg-primary-50 dark:bg-primary-900/20 rounded-lg p-4 border border-primary-200 dark:border-primary-800">
          <p className="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">
            Sua Nota:
          </p>

          {/* Star Rating */}
          <div className="flex items-center gap-1 mb-4">
            {[1, 2, 3, 4, 5, 6, 7, 8, 9, 10].map(star => (
              <button
                key={star}
                onClick={() => setRating(star)}
                onMouseEnter={() => setHover(star)}
                onMouseLeave={() => setHover(0)}
                className="transition-transform hover:scale-110"
              >
                <Star
                  className={cn(
                    "w-6 h-6",
                    (hover >= star || rating >= star)
                      ? "fill-warning-500 text-warning-500"
                      : "fill-none text-neutral-300"
                  )}
                />
              </button>
            ))}
            <span className="ml-3 text-2xl font-bold text-primary-600">
              {rating.toFixed(1)} / 10
            </span>
          </div>

          {/* Actions */}
          <div className="flex gap-3">
            <Button
              variant="ghost"
              size="md"
              onClick={() => setRating(0)}
            >
              Cancelar
            </Button>
            <Button
              variant="primary"
              size="md"
              className="flex-1"
              disabled={rating === 0}
              onClick={() => onVote(submission.id, rating)}
            >
              Confirmar Voto
              <Check className="w-5 h-5" />
            </Button>
          </div>
        </div>
      )}
    </div>
  )
}
```

**Anti-Fraude Toast**:
```tsx
// Quando usuário tenta votar na própria equipe
<Toast variant="error">
  <AlertCircle className="w-5 h-5" />
  <div>
    <p className="font-semibold">Voto Bloqueado</p>
    <p className="text-sm">Você não pode votar na própria equipe.</p>
  </div>
</Toast>
```

---

## 🏆 PÁGINA: RANKING COMPLETO

### Tabs (Usuários vs Equipes)

```
╔══════════════════════════════════════════════════════════════════╗
║  🏆 Rankings - Temporada Inaugural 2025                          ║
║  ═══════════════════════════════════════                         ║
║                                                                   ║
║  ┌──────────┬──────────┐                                         ║
║  │ EQUIPES  │ USUÁRIOS │              📅 Atualizado há 3s         ║
║  │ ════════ │          │                                         ║
║  └──────────┴──────────┘                                         ║
╚══════════════════════════════════════════════════════════════════╝
```

---

### Tabela Ranking (Equipes)

```
╔════════════════════════════════════════════════════════════════════════╗
║  Pos. │ Equipe        │ Pontos │ 🥇 │ Tarefas │ Trend │ Ações         ║
║  ────┼───────────────┼────────┼────┼─────────┼───────┼──────────     ║
║       │               │        │    │         │       │               ║
║  🥇 1 │ 🛡️ Beta       │  420   │ 5  │   18    │ ↑ +2  │ [Ver →]      ║
║       │ Cap: Maria    │        │    │         │       │               ║
║       │               │        │    │         │       │               ║
║  🥈 2 │ ⚔️ Gamma      │  380   │ 4  │   20    │ ─  0  │ [Ver →]      ║
║       │ Cap: Carlos   │        │    │         │       │               ║
║       │               │        │    │         │       │               ║
║  🥉 3 │ 🏹 Alpha      │  350   │ 3  │   15    │ ↑ +1  │ [Ver →]      ║
║       │ Cap: Ana      │        │    │         │       │               ║
║       │               │        │    │         │       │               ║
║   4   │ 🗡️ Delta      │  310   │ 2  │   17    │ ↓ -2  │ [Ver →]      ║
║       │ Cap: Pedro    │        │    │         │       │               ║
║       │               │        │    │         │       │               ║
║  ... (mais linhas)                                                    ║
║                                                                        ║
║  ┌────────────────────────────────────────────────────────────────┐   ║
║  │               ← 1  2  3  4  5  6  7  8  9  10 →                │   ║
║  └────────────────────────────────────────────────────────────────┘   ║
╚════════════════════════════════════════════════════════════════════════╝
```

**RankingTable Component**:
```tsx
<table className="w-full">
  <thead className="bg-neutral-100 dark:bg-neutral-900">
    <tr>
      <th className="px-4 py-3 text-left">Pos.</th>
      <th className="px-4 py-3 text-left">Equipe</th>
      <th className="px-4 py-3 text-right">Pontos</th>
      <th className="px-4 py-3 text-center">🥇</th>
      <th className="px-4 py-3 text-center">Tarefas</th>
      <th className="px-4 py-3 text-center">Trend</th>
      <th className="px-4 py-3 text-right">Ações</th>
    </tr>
  </thead>
  <tbody>
    {rankings.map((team, index) => (
      <RankingRow
        key={team.id}
        rank={index + 1}
        entity={team}
        isCurrentUser={team.id === currentUser.teamId}
      />
    ))}
  </tbody>
</table>
```

**Live Update Indicator**:
```tsx
// WebSocket listener
useEffect(() => {
  socket.on('ranking:updated', (data) => {
    setRankings(data.rankings)
    setLastUpdate(new Date())
    
    // Toast notification
    toast.info('Rankings atualizados!', {
      icon: <TrendingUp className="w-5 h-5" />
    })
  })
}, [])

// Header indicator
<div className="flex items-center gap-2 text-sm text-neutral-600 dark:text-neutral-400">
  <div className="w-2 h-2 bg-success-500 rounded-full animate-pulse" />
  Atualizado há {formatDistanceToNow(lastUpdate, { locale: ptBR })}
</div>
```

---

### Gráfico Evolução (Chart.js)

```
╔═════════════════════════════════════════════════════════════════╗
║  📈 Evolução Pontos (Últimos 30 Dias)                           ║
║  ──────────────────────────────────                            ║
║                                                                  ║
║  500 │                                  ●────── Beta            ║
║  400 │                        ●────●                            ║
║  300 │              ●────●          ╲                           ║
║  200 │        ●────●                 ╲   ●────── Alpha          ║
║  100 │  ●────●                        ╲●                        ║
║    0 └────┬────┬────┬────┬────┬────┬────                       ║
║         01/11  08   15   22   29  06/12                         ║
║                                                                  ║
║  Legenda:  ━━ Beta  ━━ Alpha  ━━ Gamma  ━━ Delta  ━━ Epsilon   ║
╚═════════════════════════════════════════════════════════════════╝
```

**LineChart Component**:
```tsx
import { Line } from 'react-chartjs-2'

const RankingEvolutionChart = ({ data }: { data: RankingHistory[] }) => {
  const chartData = {
    labels: data.map(d => format(d.date, 'dd/MM')),
    datasets: data.teams.map(team => ({
      label: team.name,
      data: team.pointsHistory,
      borderColor: getTeamColor(team.id),
      backgroundColor: getTeamColor(team.id, 0.1),
      borderWidth: 3,
      tension: 0.4, // Smooth curve
      pointRadius: 4,
      pointHoverRadius: 6
    }))
  }

  const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'bottom',
        labels: {
          usePointStyle: true,
          padding: 15
        }
      },
      tooltip: {
        mode: 'index',
        intersect: false,
        callbacks: {
          label: (context) => {
            return `${context.dataset.label}: ${context.parsed.y} pontos`
          }
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          callback: (value) => `${value} pts`
        }
      }
    }
  }

  return (
    <div className="bg-white dark:bg-neutral-800 rounded-xl p-6 shadow-base">
      <h3 className="text-xl font-bold mb-4 flex items-center gap-2">
        <TrendingUp className="w-6 h-6 text-primary-600" />
        Evolução Pontos (Últimos 30 Dias)
      </h3>
      <div className="h-80">
        <Line data={chartData} options={options} />
      </div>
    </div>
  )
}
```

---

## 📅 PÁGINA: CALENDÁRIO

### FullCalendar Interface

```
╔══════════════════════════════════════════════════════════════════════╗
║  📅 Calendário de Eventos                   Novembro 2025            ║
║  ══════════════════════                                              ║
║                                                                       ║
║  [Hoje] [Semana] [Mês]        [Filtros ▼]             [← Anterior | Próximo →] ║
║                                                                       ║
║  ┌─────┬─────┬─────┬─────┬─────┬─────┬─────┐                        ║
║  │ Dom │ Seg │ Ter │ Qua │ Qui │ Sex │ Sáb │                        ║
║  ├─────┼─────┼─────┼─────┼─────┼─────┼─────┤                        ║
║  │     │     │     │     │  1  │  2  │  3  │                        ║
║  │     │     │     │     │     │     │     │                        ║
║  ├─────┼─────┼─────┼─────┼─────┼─────┼─────┤                        ║
║  │  4  │  5  │  6  │  7  │  8  │  9  │ 10  │                        ║
║  │     │ 🎯  │ 📋  │     │ 🗳️  │     │     │                        ║
║  │     │Task │Task │     │Vote │     │     │                        ║
║  │     │     │     │     │Opens│     │     │                        ║
║  ├─────┼─────┼─────┼─────┼─────┼─────┼─────┤                        ║
║  │ 11  │ 12  │ 13  │ 14  │ 15  │ 16  │ 17  │                        ║
║  │     │ 🏆  │     │     │ ⚠️  │     │     │                        ║
║  │     │Msn  │     │     │Dead │     │     │                        ║
║  │     │Start│     │     │line │     │     │                        ║
║  └─────┴─────┴─────┴─────┴─────┴─────┴─────┘                        ║
║                                                                       ║
║  Legenda:  🎯 Tarefas  🗳️ Votações  🏆 Missions  ⚠️ Deadlines       ║
╚══════════════════════════════════════════════════════════════════════╝
```

**Calendar Component (FullCalendar + Custom)**:
```tsx
import FullCalendar from '@fullcalendar/react'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'

const Calendar = ({ events }: { events: CalendarEvent[] }) => {
  const handleEventClick = (info: EventClickArg) => {
    // Open modal with event details
    openEventModal(info.event.extendedProps)
  }

  const eventContent = (eventInfo: EventContentArg) => {
    const { event } = eventInfo
    const icon = getEventIcon(event.extendedProps.type)
    
    return (
      <div className="flex items-center gap-1 px-2 py-1 text-xs font-medium truncate">
        <span>{icon}</span>
        <span>{event.title}</span>
      </div>
    )
  }

  return (
    <div className="bg-white dark:bg-neutral-800 rounded-xl p-6 shadow-base">
      <FullCalendar
        plugins={[dayGridPlugin, interactionPlugin]}
        initialView="dayGridMonth"
        events={events}
        eventClick={handleEventClick}
        eventContent={eventContent}
        headerToolbar={{
          left: 'today prev,next',
          center: 'title',
          right: 'dayGridMonth,dayGridWeek,dayGridDay'
        }}
        locale="pt-br"
        height="auto"
        buttonText={{
          today: 'Hoje',
          month: 'Mês',
          week: 'Semana',
          day: 'Dia'
        }}
        // Custom styling
        dayMaxEvents={3}
        moreLinkText={(num) => `+${num} mais`}
      />
    </div>
  )
}
```

---

## 📊 PÁGINA: ADMIN DASHBOARD

### KPIs Hero Section

```
╔════════════════════════════════════════════════════════════════════╗
║  Admin Dashboard - Temporada Inaugural 2025                        ║
║  ═══════════════════════════════════════════                       ║
║                                                                     ║
║  ┌──────────────┬──────────────┬──────────────┬──────────────┐     ║
║  │ 👥 USERS     │ ✅ TAREFAS   │ 🗳️ VOTAÇÕES  │ 📊 ENGAJAM.  │     ║
║  │              │              │              │              │     ║
║  │ 287 ativos   │ 142 completas│ 23 ativas    │ 87%          │     ║
║  │ +12 esta sem.│ +45 esta sem.│ 5 encerram   │ +5pp         │     ║
║  │              │              │ hoje         │              │     ║
║  └──────────────┴──────────────┴──────────────┴──────────────┘     ║
╚════════════════════════════════════════════════════════════════════╝
```

---

### Charts Grid

```
╔════════════════════════════════════════════════════════════════════╗
║  ┌──────────────────────────┬──────────────────────────────────┐  ║
║  │ 📊 Participação Unidade  │ 📈 Engajamento Mensal            │  ║
║  │ (Pie Chart)              │ (Line Chart)                     │  ║
║  │                          │                                  │  ║
║  │   TI ████ 35%            │  100%│                           │  ║
║  │   SAC ███ 28%            │   80%│        ●────●             │  ║
║  │   OP ██ 22%              │   60%│  ●────●                   │  ║
║  │   ADM █ 15%              │   40%│●                          │  ║
║  │                          │    0%└─────────────────          │  ║
║  └──────────────────────────┴──────────────────────────────────┘  ║
║                                                                     ║
║  ┌────────────────────────────────────────────────────────────┐   ║
║  │ 🔥 Heatmap Atividade (Tarefas Criadas por Dia)            │   ║
║  │                                                             │   ║
║  │        Seg  Ter  Qua  Qui  Sex  Sáb  Dom                   │   ║
║  │  Sem1   12   15   18   14   10    3    1                   │   ║
║  │  Sem2   14   16   20   18   12    4    2                   │   ║
║  │  Sem3   10   13   17   15   11    5    1                   │   ║
║  │  Sem4   16   19   22   19   14    6    3                   │   ║
║  │                                                             │   ║
║  │  Escala: ░░ 1-5  ▒▒ 6-10  ▓▓ 11-15  ██ 16+                │   ║
║  └────────────────────────────────────────────────────────────┘   ║
╚════════════════════════════════════════════════════════════════════╝
```

**HeatmapChart Component**:
```tsx
const HeatmapChart = ({ data }: { data: ActivityHeatmap }) => {
  const getColorIntensity = (count: number) => {
    if (count === 0) return 'bg-neutral-100 dark:bg-neutral-800'
    if (count <= 5) return 'bg-primary-200'
    if (count <= 10) return 'bg-primary-400'
    if (count <= 15) return 'bg-primary-600'
    return 'bg-primary-800'
  }

  return (
    <div className="bg-white dark:bg-neutral-800 rounded-xl p-6 shadow-base">
      <h3 className="text-xl font-bold mb-4 flex items-center gap-2">
        <Flame className="w-6 h-6 text-error-500" />
        Heatmap Atividade (Tarefas Criadas por Dia)
      </h3>

      <div className="overflow-x-auto">
        <table className="w-full border-collapse">
          <thead>
            <tr>
              <th className="p-2"></th>
              {['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'].map(day => (
                <th key={day} className="p-2 text-xs text-neutral-600 dark:text-neutral-400">
                  {day}
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {data.weeks.map((week, weekIndex) => (
              <tr key={weekIndex}>
                <td className="p-2 text-xs text-neutral-600 dark:text-neutral-400">
                  Sem{weekIndex + 1}
                </td>
                {week.days.map((day, dayIndex) => (
                  <td key={dayIndex} className="p-1">
                    <div
                      className={cn(
                        "w-12 h-12 rounded-lg flex items-center justify-center",
                        "font-semibold text-xs transition-all hover:scale-110 cursor-pointer",
                        getColorIntensity(day.count)
                      )}
                      title={`${day.date}: ${day.count} tarefas`}
                    >
                      {day.count}
                    </div>
                  </td>
                ))}
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Legend */}
      <div className="flex items-center gap-4 mt-4 text-xs text-neutral-600 dark:text-neutral-400">
        <span className="font-medium">Escala:</span>
        <div className="flex items-center gap-1">
          <div className="w-4 h-4 bg-neutral-100 dark:bg-neutral-800 rounded"></div>
          <span>0</span>
        </div>
        <div className="flex items-center gap-1">
          <div className="w-4 h-4 bg-primary-200 rounded"></div>
          <span>1-5</span>
        </div>
        <div className="flex items-center gap-1">
          <div className="w-4 h-4 bg-primary-400 rounded"></div>
          <span>6-10</span>
        </div>
        <div className="flex items-center gap-1">
          <div className="w-4 h-4 bg-primary-600 rounded"></div>
          <span>11-15</span>
        </div>
        <div className="flex items-center gap-1">
          <div className="w-4 h-4 bg-primary-800 rounded"></div>
          <span>16+</span>
        </div>
      </div>
    </div>
  )
}
```

---

## 🎨 ESPECIFICAÇÕES FIGMA

### Arquivo Figma Structure

```
Tubaron-Gamification-Design-System.fig
├── 📁 Cover (Capa apresentação)
├── 📁 Design Tokens
│   ├── Colors (50+ tokens)
│   ├── Typography (scale completa)
│   ├── Spacing (8px grid)
│   ├── Shadows (5 elevações)
│   └── Border Radius
├── 📁 Components Library
│   ├── Atoms
│   │   ├── Button (5 variants × 3 sizes = 15)
│   │   ├── Input (8 types)
│   │   ├── Badge (4 variants × 5 colors = 20)
│   │   ├── Avatar (4 sizes)
│   │   ├── Icon (300+ custom)
│   │   └── Loading States
│   ├── Molecules
│   │   ├── TaskCard (3 types × 4 states = 12)
│   │   ├── RankingRow
│   │   ├── NotificationItem
│   │   ├── AchievementBadge
│   │   └── FormField
│   └── Organisms
│       ├── DashboardHero
│       ├── VotingInterface
│       ├── RankingTable
│       └── CalendarView
├── 📁 Pages (High-Fidelity Mockups)
│   ├── Dashboard Colaborador (Desktop/Tablet/Mobile)
│   ├── Tarefas Lista (Desktop/Tablet/Mobile)
│   ├── Tarefa Detalhes (Desktop/Tablet/Mobile)
│   ├── Votação (Desktop/Tablet/Mobile)
│   ├── Rankings (Desktop/Tablet/Mobile)
│   ├── Calendário (Desktop/Tablet/Mobile)
│   └── Admin Dashboard (Desktop)
├── 📁 Prototypes
│   ├── User Flow: Criar Tarefa Competitiva
│   ├── User Flow: Votar em Submissão
│   ├── User Flow: Ver Ranking Real-Time
│   └── Admin Flow: Gerenciar Temporada
├── 📁 Dark Mode
│   └── (Todas páginas variantes dark)
├── 📁 Illustrations
│   ├── Empty States (8 ilustrações)
│   ├── Error States (4 ilustrações)
│   └── Achievement Icons (20+ custom)
└── 📁 Export Assets
    ├── Icons (SVG sprite)
    ├── Illustrations (PNG/SVG)
    └── Lottie Animations (JSON)
```

---

## ✅ CHECKLIST ENTREGA DESIGN

### Figma
- [ ] 50+ componentes library publicada
- [ ] 40+ telas high-fidelity (3 viewports cada)
- [ ] Dark mode completo (todas telas)
- [ ] Prototype interativo fluxos principais
- [ ] Design tokens exportados (JSON)
- [ ] 300+ icons SVG organizados
- [ ] 12+ illustrations empty/error states
- [ ] Dev Mode habilitado (inspect CSS)

### Storybook
- [ ] 200+ stories (todos componentes)
- [ ] Accessibility addon (axe-core)
- [ ] Responsive viewports
- [ ] Dark mode toggle
- [ ] Props documentation
- [ ] Usage examples
- [ ] Deploy Chromatic (visual regression)

### Documentação
- [ ] Design System Handbook (este doc)
- [ ] Component API reference
- [ ] Accessibility guidelines WCAG AAA
- [ ] Responsive patterns guide
- [ ] Animation choreography doc
- [ ] Icon usage guidelines
- [ ] Brand guidelines Tubaron

### Handoff Frontend
- [ ] Figma → Tailwind tokens mapping
- [ ] SVG assets exportados organizados
- [ ] Lottie animations JSON
- [ ] Implementation notes complexidades
- [ ] Video walkthrough (15min Loom)
- [ ] Workshop 16h agendado

---

<div align="center">

**🎨 Wireframes de Alta Fidelidade Completos**

*Próximo: [COMPONENTES_REACT.md](./COMPONENTES_REACT.md)*

</div>

---

**Criado por**: Equipe UI/UX Mundial  
**Para**: Tubaron Gamification System  
**Figma**: [workspace.figma.com/tubaron]  
**Última atualização**: Novembro 2025

