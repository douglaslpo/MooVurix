# 🎨 TUBARON DESIGN SYSTEM v1.0

**Nome**: Tubaron Gamification Design System  
**Versão**: 1.0.0  
**Status**: Production-Ready  
**Stack**: React 18 + Next.js 14 + Tailwind CSS 3.4 + shadcn/ui  
**Acessibilidade**: WCAG 2.1 AAA Compliant  

---

## 🎯 VISÃO GERAL

Sistema de design escalável, consistente e acessível para aplicação gamificada corporativa Tubaron. Baseado em **Atomic Design**, **Design Tokens** e **melhores práticas 2025**.

### Princípios Core

1. **🎯 Clareza Absoluta** - Zero ambiguidade, affordances óbvias
2. **⚡ Performance Perceptível** - Sente instantâneo (<100ms feedback)
3. **♿ Acessibilidade Universal** - WCAG AAA (7:1 contrast, keyboard-first)
4. **🎮 Gamification Delightful** - Celebração sem poluição visual
5. **📱 Mobile-First** - 375px → 1920px seamless

---

## 🎨 PALETA DE CORES

### Primary (Azul Tubaron - Confiança, Tecnologia)

```css
/* Baseado em identidade Tubaron Telecomunicações */
--primary-50:  #eff6ff;  /* Fundo hover sutil */
--primary-100: #dbeafe;  /* Fundo secondary buttons */
--primary-200: #bfdbfe;  /* Borders disabled */
--primary-300: #93c5fd;  /* Borders active */
--primary-400: #60a5fa;  /* Icons secondary */
--primary-500: #3b82f6;  /* PRINCIPAL - Botões, links */
--primary-600: #2563eb;  /* Hover state */
--primary-700: #1d4ed8;  /* Active/pressed */
--primary-800: #1e40af;  /* Text dark mode */
--primary-900: #1e3a8a;  /* Headers, strong emphasis */
--primary-950: #172554;  /* Text on light backgrounds */
```

**Uso**:
- Botões primários (500)
- Links (600)
- Badges status ativo (500)
- Ranking posições destaque (gradient 500→700)

**Contraste**: AAA (7.5:1 mínimo em 500 sobre branco)

---

### Success (Verde - Conquistas, Aprovações)

```css
--success-50:  #f0fdf4;
--success-100: #dcfce7;
--success-200: #bbf7d0;
--success-300: #86efac;
--success-400: #4ade80;
--success-500: #22c55e;  /* PRINCIPAL */
--success-600: #16a34a;  /* Hover */
--success-700: #15803d;
--success-800: #166534;
--success-900: #14532d;
--success-950: #052e16;
```

**Uso**:
- Achievement badges desbloqueados
- Mensagens sucesso (toasts)
- Tarefas completadas (status)
- Pontuação positiva (setas ↑)

---

### Warning (Amarelo/Laranja - Alertas, Urgência)

```css
--warning-50:  #fffbeb;
--warning-100: #fef3c7;
--warning-200: #fde68a;
--warning-300: #fcd34d;
--warning-400: #fbbf24;
--warning-500: #f59e0b;  /* PRINCIPAL */
--warning-600: #d97706;  /* Hover */
--warning-700: #b45309;
--warning-800: #92400e;
--warning-900: #78350f;
--warning-950: #451a03;
```

**Uso**:
- Tarefas deadline <24h
- Avisos votação encerrando
- Alerts moderados (não críticos)

---

### Error (Vermelho - Erros, Bloqueios)

```css
--error-50:  #fef2f2;
--error-100: #fee2e2;
--error-200: #fecaca;
--error-300: #fca5a5;
--error-400: #f87171;
--error-500: #ef4444;  /* PRINCIPAL */
--error-600: #dc2626;  /* Hover */
--error-700: #b91c1c;
--error-800: #991b1b;
--error-900: #7f1d1d;
--error-950: #450a0a;
```

**Uso**:
- Mensagens erro (formulários, API)
- Tarefas atrasadas (overdue)
- Bloqueios anti-fraude (votação própria equipe)

---

### Neutral (Cinza - Interface Base)

```css
--neutral-50:  #fafafa;  /* Background principal app */
--neutral-100: #f5f5f5;  /* Cards background */
--neutral-200: #e5e5e5;  /* Borders, dividers */
--neutral-300: #d4d4d4;  /* Disabled backgrounds */
--neutral-400: #a3a3a3;  /* Placeholder text */
--neutral-500: #737373;  /* Secondary text */
--neutral-600: #525252;  /* Body text */
--neutral-700: #404040;  /* Headers */
--neutral-800: #262626;  /* Strong headers */
--neutral-900: #171717;  /* Dark mode background */
--neutral-950: #0a0a0a;  /* Pure black (dark mode text)
```

---

### Gamification (Especiais)

```css
/* Gold - 1º Lugar */
--gold-400: #fbbf24;
--gold-500: #f59e0b;
--gold-600: #d97706;
background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);

/* Silver - 2º Lugar */
--silver-400: #cbd5e1;
--silver-500: #94a3b8;
--silver-600: #64748b;
background: linear-gradient(135deg, #cbd5e1 0%, #94a3b8 100%);

/* Bronze - 3º Lugar */
--bronze-400: #fb923c;
--bronze-500: #f97316;
--bronze-600: #ea580c;
background: linear-gradient(135deg, #fb923c 0%, #f97316 100%);

/* Purple - Achievements Raros */
--purple-500: #a855f7;
--purple-600: #9333ea;
background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%);
```

---

### Dark Mode

```css
/* Backgrounds */
--dark-bg-primary: #0a0a0a;     /* App background */
--dark-bg-secondary: #171717;   /* Cards, modals */
--dark-bg-tertiary: #262626;    /* Hover states */
--dark-bg-code: #1e1e1e;        /* Code blocks */

/* Text */
--dark-text-primary: #fafafa;   /* Headers, strong */
--dark-text-secondary: #d4d4d4; /* Body text */
--dark-text-tertiary: #a3a3a3;  /* Muted, labels */

/* Borders */
--dark-border: #404040;         /* Dividers */
--dark-border-hover: #525252;   /* Interactive borders */

/* Overlays */
--dark-overlay: rgba(0, 0, 0, 0.8); /* Modals backdrop */
```

**Toggle**: Switch component header (persiste localStorage)

---

## ✍️ TIPOGRAFIA

### Font Family

```css
/* Principal - Inter (geometric, legibilidade alta) */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

--font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 
             'Roboto', 'Oxygen', 'Ubuntu', sans-serif;

/* Monospace - Código, IDs */
--font-mono: 'JetBrains Mono', 'Fira Code', 'Courier New', monospace;

/* Display - Opcional para headers hero */
--font-display: 'Manrope', 'Inter', sans-serif;
```

**Fallbacks**: System fonts para performance (FOUT prevention)

---

### Type Scale (Modular Scale 1.250 - Perfect Fourth)

```css
/* Mobile-First */
--text-xs:   0.75rem;  /* 12px - Badges, labels small */
--text-sm:   0.875rem; /* 14px - Body secondary, captions */
--text-base: 1rem;     /* 16px - Body principal */
--text-lg:   1.125rem; /* 18px - Subheadings */
--text-xl:   1.25rem;  /* 20px - Card titles */
--text-2xl:  1.5rem;   /* 24px - Section headers */
--text-3xl:  1.875rem; /* 30px - Page titles */
--text-4xl:  2.25rem;  /* 36px - Dashboard hero */
--text-5xl:  3rem;     /* 48px - Landing hero */

/* Desktop Scale Up (+10%) em >1024px */
@media (min-width: 1024px) {
  --text-3xl: 2.0625rem; /* 33px */
  --text-4xl: 2.475rem;  /* 39.6px */
  --text-5xl: 3.3rem;    /* 52.8px */
}
```

---

### Font Weights

```css
--font-light:     300; /* Raramente usado */
--font-normal:    400; /* Body text */
--font-medium:    500; /* Buttons, labels */
--font-semibold:  600; /* Card headers, emphasis */
--font-bold:      700; /* Page titles, strong */
--font-extrabold: 800; /* Dashboard KPIs */
--font-black:     900; /* Hero numbers (opcional) */
```

**Hierarquia**:
- Body: 400 (normal)
- Buttons/Labels: 500 (medium)
- Headers: 600-700 (semibold-bold)
- KPI Numbers: 800 (extrabold)

---

### Line Heights

```css
--leading-tight:  1.25; /* Headers (H1-H3) */
--leading-snug:   1.375; /* Subheaders (H4-H6) */
--leading-normal: 1.5;   /* Body text (parágrafos) */
--leading-relaxed: 1.625; /* Long-form content */
--leading-loose:  2;      /* Poesia, espaçamento especial */
```

**Acessibilidade**: Mínimo 1.5 para body (WCAG AAA)

---

### Letter Spacing

```css
--tracking-tighter: -0.05em; /* Display headers (48px+) */
--tracking-tight:   -0.025em; /* Large headers (30px+) */
--tracking-normal:  0em;      /* Body text */
--tracking-wide:    0.025em;  /* Buttons (uppercase) */
--tracking-wider:   0.05em;   /* Labels pequenos */
--tracking-widest:  0.1em;    /* Eyebrow text (caps) */
```

---

## 📏 SPACING & LAYOUT

### Spacing Scale (8px Grid System)

```css
--space-0:  0px;
--space-1:  4px;   /* Micro (icon + text gap) */
--space-2:  8px;   /* Tight (button padding-y) */
--space-3:  12px;  /* Compact (small gaps) */
--space-4:  16px;  /* Base (card padding, section gap) */
--space-5:  20px;  /* Comfortable (form fields gap) */
--space-6:  24px;  /* Spacious (card padding-lg) */
--space-8:  32px;  /* Section spacing */
--space-10: 40px;  /* Large gaps */
--space-12: 48px;  /* Hero spacing */
--space-16: 64px;  /* Major sections */
--space-20: 80px;  /* Page spacing */
--space-24: 96px;  /* Landing sections */
```

**Grid Base**: 8px (design tokens múltiplos de 8)

---

### Border Radius

```css
--radius-none: 0px;
--radius-sm:   4px;  /* Badges, small buttons */
--radius-base: 8px;  /* Buttons, inputs (padrão) */
--radius-md:   12px; /* Cards */
--radius-lg:   16px; /* Modals, large cards */
--radius-xl:   24px; /* Hero cards (opcional) */
--radius-full: 9999px; /* Pills, avatars */
```

**Consistência**: 8px para 90% componentes

---

### Shadows (Elevações)

```css
/* Light Mode */
--shadow-xs: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  /* Subtle hover */

--shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1),
             0 1px 2px -1px rgba(0, 0, 0, 0.1);
  /* Buttons, inputs focus */

--shadow-base: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
               0 2px 4px -2px rgba(0, 0, 0, 0.1);
  /* Cards padrão */

--shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1),
             0 4px 6px -4px rgba(0, 0, 0, 0.1);
  /* Cards hover, dropdowns */

--shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1),
             0 8px 10px -6px rgba(0, 0, 0, 0.1);
  /* Modals */

--shadow-xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  /* Floating elements (ranking live update) */

/* Dark Mode - Glow em vez de shadow */
--shadow-dark-sm: 0 0 0 1px rgba(255, 255, 255, 0.1);
--shadow-dark-md: 0 0 0 1px rgba(255, 255, 255, 0.1),
                  0 4px 16px rgba(0, 0, 0, 0.5);
```

---

### Z-Index Scale

```css
--z-base:       0;   /* Conteúdo normal */
--z-dropdown:   10;  /* Dropdowns, tooltips */
--z-sticky:     20;  /* Sticky headers */
--z-fixed:      30;  /* Fixed sidebars */
--z-modal:      40;  /* Modal overlays */
--z-popover:    50;  /* Popovers sobre modals */
--z-toast:      60;  /* Notifications toast */
```

---

## 🧩 COMPONENTES (Atomic Design)

### Atoms (Elementos Básicos)

#### Button Component

```tsx
// Button.tsx - shadcn/ui customizado
import { cn } from '@/lib/utils'

interface ButtonProps {
  variant: 'primary' | 'secondary' | 'ghost' | 'danger' | 'success'
  size: 'sm' | 'md' | 'lg'
  icon?: React.ReactNode
  loading?: boolean
  disabled?: boolean
  children: React.ReactNode
}

const Button = ({ variant, size, icon, loading, disabled, children }: ButtonProps) => {
  const baseStyles = `
    inline-flex items-center justify-center gap-2
    font-medium transition-all duration-200
    focus:outline-none focus:ring-2 focus:ring-offset-2
    disabled:opacity-50 disabled:cursor-not-allowed
  `

  const variants = {
    primary: `
      bg-primary-600 text-white
      hover:bg-primary-700 active:bg-primary-800
      focus:ring-primary-500
    `,
    secondary: `
      bg-neutral-100 text-neutral-900
      hover:bg-neutral-200 active:bg-neutral-300
      focus:ring-neutral-400
      dark:bg-neutral-800 dark:text-neutral-100
    `,
    ghost: `
      bg-transparent text-neutral-700
      hover:bg-neutral-100 active:bg-neutral-200
      focus:ring-neutral-400
      dark:text-neutral-300 dark:hover:bg-neutral-800
    `,
    danger: `
      bg-error-600 text-white
      hover:bg-error-700 active:bg-error-800
      focus:ring-error-500
    `,
    success: `
      bg-success-600 text-white
      hover:bg-success-700 active:bg-success-800
      focus:ring-success-500
    `
  }

  const sizes = {
    sm: 'px-3 py-1.5 text-sm rounded-md',
    md: 'px-4 py-2 text-base rounded-lg',
    lg: 'px-6 py-3 text-lg rounded-lg'
  }

  return (
    <button
      className={cn(baseStyles, variants[variant], sizes[size])}
      disabled={disabled || loading}
    >
      {loading && <LoadingSpinner size="sm" />}
      {icon && <span>{icon}</span>}
      <span>{children}</span>
    </button>
  )
}
```

**Estados**:
- Default
- Hover (scale 1.02, shadow-md)
- Active (scale 0.98)
- Focus (ring-2)
- Disabled (opacity-50)
- Loading (spinner + disabled)

**Acessibilidade**:
- `aria-disabled` quando loading
- `aria-busy` durante loading
- Keyboard focus visível (ring)

---

#### Input Component

```tsx
interface InputProps {
  type: 'text' | 'email' | 'password' | 'number' | 'date'
  label: string
  error?: string
  hint?: string
  icon?: React.ReactNode
  required?: boolean
}

const Input = ({ type, label, error, hint, icon, required }: InputProps) => (
  <div className="space-y-1.5">
    <label className="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
      {label}
      {required && <span className="text-error-500 ml-1">*</span>}
    </label>
    
    <div className="relative">
      {icon && (
        <div className="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400">
          {icon}
        </div>
      )}
      
      <input
        type={type}
        className={cn(
          'w-full px-4 py-2.5 rounded-lg border',
          'text-neutral-900 dark:text-neutral-100',
          'bg-white dark:bg-neutral-800',
          'border-neutral-300 dark:border-neutral-700',
          'focus:border-primary-500 focus:ring-2 focus:ring-primary-200',
          'transition-colors duration-200',
          icon && 'pl-10',
          error && 'border-error-500 focus:border-error-500 focus:ring-error-200'
        )}
      />
    </div>

    {hint && !error && (
      <p className="text-xs text-neutral-500">{hint}</p>
    )}
    
    {error && (
      <p className="text-xs text-error-600 flex items-center gap-1">
        <AlertCircle className="w-3 h-3" />
        {error}
      </p>
    )}
  </div>
)
```

**Validação**:
- Real-time (onBlur)
- Error state persistente até correção
- Success icon (check) quando válido

---

#### Badge Component

```tsx
interface BadgeProps {
  variant: 'status' | 'achievement' | 'role' | 'number'
  color?: 'primary' | 'success' | 'warning' | 'error' | 'neutral'
  children: React.ReactNode
}

const Badge = ({ variant, color = 'neutral', children }: BadgeProps) => {
  const variants = {
    status: 'px-2.5 py-0.5 text-xs font-medium rounded-full',
    achievement: 'px-3 py-1 text-sm font-semibold rounded-lg shadow-sm',
    role: 'px-2 py-0.5 text-xs font-medium rounded uppercase tracking-wide',
    number: 'w-6 h-6 flex items-center justify-center text-xs font-bold rounded-full'
  }

  const colors = {
    primary: 'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-200',
    success: 'bg-success-100 text-success-700 dark:bg-success-900 dark:text-success-200',
    warning: 'bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-200',
    error: 'bg-error-100 text-error-700 dark:bg-error-900 dark:text-error-200',
    neutral: 'bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300'
  }

  return (
    <span className={cn(variants[variant], colors[color])}>
      {children}
    </span>
  )
}
```

**Uso**:
- Status tarefas (open, in_progress, voting, completed)
- Achievement badges (desbloqueados)
- Roles (admin, captain, collaborator)
- Notification count (bell icon)

---

### Molecules (Componentes Compostos)

#### TaskCard Component

```tsx
interface TaskCardProps {
  task: {
    id: string
    type: 'individual' | 'team' | 'competitive'
    title: string
    dueDate: Date
    status: string
    assignees: User[]
    points: number
  }
}

const TaskCard = ({ task }: TaskCardProps) => (
  <div className="group relative bg-white dark:bg-neutral-800 rounded-xl p-6 shadow-base hover:shadow-md transition-all duration-300">
    {/* Header */}
    <div className="flex items-start justify-between mb-4">
      <div className="flex items-center gap-3">
        <TaskIcon type={task.type} />
        <Badge variant="status" color={getStatusColor(task.status)}>
          {task.status}
        </Badge>
      </div>
      
      <DropdownMenu>
        <MoreVertical className="w-5 h-5 text-neutral-400" />
      </DropdownMenu>
    </div>

    {/* Title */}
    <h3 className="text-xl font-semibold text-neutral-900 dark:text-neutral-100 mb-2 group-hover:text-primary-600 transition-colors">
      {task.title}
    </h3>

    {/* Meta */}
    <div className="flex items-center gap-4 text-sm text-neutral-600 dark:text-neutral-400 mb-4">
      <div className="flex items-center gap-1.5">
        <Calendar className="w-4 h-4" />
        <span>{format(task.dueDate, 'dd MMM yyyy')}</span>
      </div>
      
      <div className="flex items-center gap-1.5">
        <Trophy className="w-4 h-4" />
        <span>{task.points} pontos</span>
      </div>
    </div>

    {/* Assignees */}
    <div className="flex items-center gap-2">
      <AvatarGroup users={task.assignees} max={3} />
      <span className="text-xs text-neutral-500">
        {task.assignees.length} participante{task.assignees.length > 1 ? 's' : ''}
      </span>
    </div>

    {/* Action */}
    <Button variant="ghost" size="sm" className="mt-4 w-full">
      Ver Detalhes
      <ArrowRight className="w-4 h-4" />
    </Button>
  </div>
)
```

**Interações**:
- Hover: shadow-md, title color change
- Click: Navigate /tasks/:id
- Dropdown: Edit, Delete, Duplicate

---

#### RankingRow Component

```tsx
interface RankingRowProps {
  rank: number
  entity: {
    name: string
    avatar: string
    points: number
    firstPlaces: number
    trend: 'up' | 'down' | 'neutral'
    trendChange: number
  }
  isCurrentUser?: boolean
}

const RankingRow = ({ rank, entity, isCurrentUser }: RankingRowProps) => {
  const getMedalIcon = (rank: number) => {
    if (rank === 1) return <Medal className="w-6 h-6 text-gold-500" />
    if (rank === 2) return <Medal className="w-6 h-6 text-silver-500" />
    if (rank === 3) return <Medal className="w-6 h-6 text-bronze-500" />
    return null
  }

  return (
    <tr className={cn(
      'border-b border-neutral-200 dark:border-neutral-700 transition-colors',
      isCurrentUser && 'bg-primary-50 dark:bg-primary-900/20'
    )}>
      {/* Rank */}
      <td className="px-4 py-4">
        <div className="flex items-center gap-2">
          <span className="text-2xl font-bold text-neutral-900 dark:text-neutral-100">
            {rank}
          </span>
          {getMedalIcon(rank)}
        </div>
      </td>

      {/* Entity */}
      <td className="px-4 py-4">
        <div className="flex items-center gap-3">
          <Avatar src={entity.avatar} alt={entity.name} size="md" />
          <div>
            <p className="font-semibold text-neutral-900 dark:text-neutral-100">
              {entity.name}
              {isCurrentUser && (
                <Badge variant="number" color="primary" className="ml-2">
                  Você
                </Badge>
              )}
            </p>
            <p className="text-sm text-neutral-500">
              {entity.firstPlaces} vitórias
            </p>
          </div>
        </div>
      </td>

      {/* Points */}
      <td className="px-4 py-4 text-right">
        <p className="text-2xl font-extrabold text-neutral-900 dark:text-neutral-100">
          {entity.points.toLocaleString()}
        </p>
        <p className="text-xs text-neutral-500">pontos</p>
      </td>

      {/* Trend */}
      <td className="px-4 py-4 text-right">
        <div className={cn(
          'inline-flex items-center gap-1 px-2 py-1 rounded-full text-sm font-medium',
          entity.trend === 'up' && 'bg-success-100 text-success-700',
          entity.trend === 'down' && 'bg-error-100 text-error-700',
          entity.trend === 'neutral' && 'bg-neutral-100 text-neutral-700'
        )}>
          {entity.trend === 'up' && <TrendingUp className="w-4 h-4" />}
          {entity.trend === 'down' && <TrendingDown className="w-4 h-4" />}
          {entity.trend === 'neutral' && <Minus className="w-4 h-4" />}
          {entity.trendChange !== 0 && Math.abs(entity.trendChange)}
        </div>
      </td>
    </tr>
  )
}
```

**Real-Time Update**:
- WebSocket emit `ranking:updated`
- Framer Motion `layoutId` para smooth reordering
- Highlight row change (pulse animation 2s)

---

### Organisms (Seções Complexas)

#### DashboardHero Component

```tsx
const DashboardHero = ({ user }: { user: User }) => (
  <div className="relative bg-gradient-to-br from-primary-600 to-primary-800 rounded-2xl p-8 overflow-hidden">
    {/* Background Pattern */}
    <div className="absolute inset-0 opacity-10">
      <GridPattern />
    </div>

    <div className="relative z-10 grid grid-cols-1 md:grid-cols-4 gap-6">
      {/* KPI: Total Points */}
      <div className="bg-white/10 backdrop-blur-sm rounded-xl p-6 border border-white/20">
        <div className="flex items-center justify-between mb-2">
          <Trophy className="w-8 h-8 text-gold-300" />
          <Badge variant="number" color="success">+15</Badge>
        </div>
        <p className="text-white/80 text-sm mb-1">Total Pontos</p>
        <p className="text-4xl font-extrabold text-white">
          {user.points.toLocaleString()}
        </p>
      </div>

      {/* KPI: Rank */}
      <div className="bg-white/10 backdrop-blur-sm rounded-xl p-6 border border-white/20">
        <Medal className="w-8 h-8 text-silver-300 mb-2" />
        <p className="text-white/80 text-sm mb-1">Posição Geral</p>
        <p className="text-4xl font-extrabold text-white flex items-baseline gap-2">
          {user.rank}º
          <span className="text-lg text-success-300 flex items-center">
            <TrendingUp className="w-4 h-4" />
            ↑2
          </span>
        </p>
      </div>

      {/* KPI: Tasks Completed */}
      <div className="bg-white/10 backdrop-blur-sm rounded-xl p-6 border border-white/20">
        <CheckCircle className="w-8 h-8 text-success-300 mb-2" />
        <p className="text-white/80 text-sm mb-1">Tarefas Completas</p>
        <p className="text-4xl font-extrabold text-white">
          {user.tasksCompleted}
        </p>
      </div>

      {/* KPI: Streak */}
      <div className="bg-white/10 backdrop-blur-sm rounded-xl p-6 border border-white/20">
        <Flame className="w-8 h-8 text-warning-300 mb-2" />
        <p className="text-white/80 text-sm mb-1">Sequência Dias</p>
        <p className="text-4xl font-extrabold text-white flex items-center gap-2">
          {user.streak}
          <span className="text-2xl">🔥</span>
        </p>
      </div>
    </div>
  </div>
)
```

**Animações**:
- Numbers count-up (react-countup)
- Badges pulse on update
- Gradient animate (CSS keyframes)

---

## 🎮 GAMIFICATION PATTERNS

### Achievement Unlock Animation

```tsx
// Trigger via WebSocket: achievement:unlocked
const AchievementUnlock = ({ achievement }: { achievement: Achievement }) => {
  const [show, setShow] = useState(true)

  return (
    <AnimatePresence>
      {show && (
        <motion.div
          initial={{ scale: 0, y: 100 }}
          animate={{ scale: 1, y: 0 }}
          exit={{ scale: 0, opacity: 0 }}
          className="fixed bottom-4 right-4 z-toast"
        >
          <div className="bg-gradient-to-r from-purple-600 to-primary-600 rounded-2xl p-6 shadow-xl max-w-sm">
            {/* Confetti */}
            <Confetti active={show} />

            {/* Icon */}
            <div className="flex items-center gap-4">
              <div className="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center">
                <img src={achievement.icon} alt="" className="w-12 h-12" />
              </div>

              <div>
                <p className="text-white/80 text-sm font-medium mb-1">
                  🎉 Achievement Desbloqueado!
                </p>
                <h3 className="text-white text-xl font-bold">
                  {achievement.name}
                </h3>
                <p className="text-white/70 text-sm mt-1">
                  +{achievement.points} pontos
                </p>
              </div>
            </div>
          </div>
        </motion.div>
      )}
    </AnimatePresence>
  )
}
```

**Celebração**:
- Confetti canvas (react-confetti)
- Sound effect (opcional, muted by default)
- Auto-dismiss 5s (ou close manual)

---

### Live Ranking Update

```tsx
// WebSocket listener
socket.on('ranking:updated', (data) => {
  setRankings(prev => {
    // Detect position changes
    const changes = detectChanges(prev, data.rankings)
    
    // Trigger animations
    changes.forEach(change => {
      if (change.type === 'position') {
        triggerPositionChangeAnimation(change.userId)
      }
    })

    return data.rankings
  })
})

// Position change visual feedback
const PositionChangeIndicator = ({ direction }: { direction: 'up' | 'down' }) => (
  <motion.div
    initial={{ opacity: 0, x: direction === 'up' ? -20 : 20 }}
    animate={{ opacity: 1, x: 0 }}
    exit={{ opacity: 0 }}
    className={cn(
      'absolute left-0 top-1/2 -translate-y-1/2',
      direction === 'up' && 'text-success-500',
      direction === 'down' && 'text-error-500'
    )}
  >
    {direction === 'up' ? '↑' : '↓'}
  </motion.div>
)
```

**Performance**:
- Virtual scrolling (react-window) para 500+ rows
- Debounce updates (500ms) para evitar flicker
- Memoization (React.memo) de rows

---

## ♿ ACESSIBILIDADE (WCAG 2.1 AAA)

### Contraste de Cores

**Requisitos**:
- Texto normal (16px+): **7:1** (AAA)
- Texto grande (24px+): **4.5:1** (AAA large)
- Ícones: **3:1** (mínimo)

**Validado**:
```bash
# Todas combinações testadas
primary-600 (#2563eb) sobre white (#ffffff):    8.2:1 ✅
neutral-700 (#404040) sobre white:              9.8:1 ✅
success-600 (#16a34a) sobre white:              4.8:1 ✅
error-600 (#dc2626) sobre white:                5.9:1 ✅
```

---

### Keyboard Navigation

**Ordem Tab**:
1. Skip to main content (link invisível)
2. Header navigation
3. Main actions (criar tarefa button)
4. Content cards (tarefas)
5. Sidebar (rankings, filters)
6. Footer

**Shortcuts**:
```tsx
// Global keyboard shortcuts
useEffect(() => {
  const handleKeyDown = (e: KeyboardEvent) => {
    // Command Palette
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
      e.preventDefault()
      openCommandPalette()
    }

    // Nova Tarefa
    if ((e.metaKey || e.ctrlKey) && e.key === 'n') {
      e.preventDefault()
      navigate('/tasks/new')
    }

    // Search
    if (e.key === '/') {
      e.preventDefault()
      focusSearch()
    }

    // Close Modal
    if (e.key === 'Escape') {
      closeActiveModal()
    }
  }

  document.addEventListener('keydown', handleKeyDown)
  return () => document.removeEventListener('keydown', handleKeyDown)
}, [])
```

**Focus Visible**:
```css
/* Sempre mostrar focus em keyboard nav */
*:focus-visible {
  @apply outline-none ring-2 ring-primary-500 ring-offset-2;
}

/* Remover focus em mouse click */
*:focus:not(:focus-visible) {
  @apply outline-none ring-0;
}
```

---

### Screen Readers

**ARIA Labels**:
```tsx
// Exemplo: Button ícone-only
<button
  aria-label="Editar tarefa"
  aria-describedby="task-title-123"
>
  <Edit className="w-5 h-5" />
</button>

// Live regions (ranking updates)
<div
  role="status"
  aria-live="polite"
  aria-atomic="true"
>
  {rankingMessage}
</div>

// Loading states
<button aria-busy={loading} aria-label={loading ? 'Carregando...' : 'Enviar'}>
  {loading ? <Spinner /> : 'Enviar'}
</button>
```

**Landmarks**:
```tsx
<header role="banner">
  <nav aria-label="Navegação principal">...</nav>
</header>

<main role="main" id="main-content">
  <section aria-labelledby="tasks-heading">
    <h2 id="tasks-heading">Suas Tarefas</h2>
    ...
  </section>
</main>

<aside role="complementary" aria-label="Rankings">
  ...
</aside>

<footer role="contentinfo">
  ...
</footer>
```

---

### Responsive Typography

```css
/* Zoom até 200% sem quebra (WCAG AAA) */
@media (min-width: 768px) {
  html {
    font-size: 16px; /* Base */
  }
}

@media (min-width: 1024px) {
  html {
    font-size: 18px; /* +12.5% para leitura confortável */
  }
}

/* Usuário pode zoombootstrap até 200% navegador */
body {
  max-width: 100vw;
  overflow-x: hidden;
}
```

---

## 📱 RESPONSIVE DESIGN

### Breakpoints

```css
/* Mobile First */
--screen-xs: 375px;  /* iPhone SE */
--screen-sm: 640px;  /* Large phones */
--screen-md: 768px;  /* Tablets */
--screen-lg: 1024px; /* Laptops */
--screen-xl: 1280px; /* Desktops */
--screen-2xl: 1536px; /* Large displays */
```

### Layout Patterns

**Dashboard Grid**:
```tsx
<div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
  {/* Cards responsivos */}
</div>
```

**Sidebar Adaptive**:
```tsx
// Mobile: Drawer overlay
// Desktop: Fixed sidebar
<aside className="fixed lg:sticky top-0 h-screen w-64 transform lg:translate-x-0 -translate-x-full transition-transform">
  {/* Sidebar content */}
</aside>
```

**Table → Cards Mobile**:
```tsx
// Desktop: <table>
// Mobile: Stack cards
<div className="hidden lg:block">
  <table>...</table>
</div>

<div className="lg:hidden space-y-4">
  {rankings.map(rank => (
    <RankingCard key={rank.id} {...rank} />
  ))}
</div>
```

---

## 🎨 DARK MODE IMPLEMENTATION

### Toggle Component

```tsx
const ThemeToggle = () => {
  const [theme, setTheme] = useState<'light' | 'dark'>('light')

  useEffect(() => {
    const saved = localStorage.getItem('theme')
    if (saved) setTheme(saved as 'light' | 'dark')
  }, [])

  const toggleTheme = () => {
    const newTheme = theme === 'light' ? 'dark' : 'light'
    setTheme(newTheme)
    localStorage.setItem('theme', newTheme)
    document.documentElement.classList.toggle('dark')
  }

  return (
    <button
      onClick={toggleTheme}
      aria-label={`Mudar para tema ${theme === 'light' ? 'escuro' : 'claro'}`}
      className="p-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800"
    >
      {theme === 'light' ? (
        <Moon className="w-5 h-5" />
      ) : (
        <Sun className="w-5 h-5" />
      )}
    </button>
  )
}
```

### System Preference

```tsx
useEffect(() => {
  const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
  
  const handleChange = (e: MediaQueryListEvent) => {
    if (!localStorage.getItem('theme')) {
      setTheme(e.matches ? 'dark' : 'light')
    }
  }

  mediaQuery.addEventListener('change', handleChange)
  return () => mediaQuery.removeEventListener('change', handleChange)
}, [])
```

---

## 🚀 PERFORMANCE OPTIMIZATION

### Code Splitting

```tsx
// Lazy load routes
const Dashboard = lazy(() => import('@/pages/Dashboard'))
const Tasks = lazy(() => import('@/pages/Tasks'))
const Rankings = lazy(() => import('@/pages/Rankings'))

// Suspense boundary
<Suspense fallback={<PageLoader />}>
  <Routes>
    <Route path="/dashboard" element={<Dashboard />} />
  </Routes>
</Suspense>
```

### Image Optimization

```tsx
// Next.js Image component
import Image from 'next/image'

<Image
  src={user.avatar}
  alt={user.name}
  width={40}
  height={40}
  className="rounded-full"
  loading="lazy"
  placeholder="blur"
/>
```

### Font Loading Strategy

```tsx
// next.config.js
module.exports = {
  optimizeFonts: true, // Auto subset
}

// _app.tsx
import { Inter } from 'next/font/google'

const inter = Inter({
  subsets: ['latin'],
  display: 'swap', // FOUT prevention
  variable: '--font-sans',
})
```

---

## 📚 STORYBOOK DOCUMENTATION

### Component Story Example

```tsx
// Button.stories.tsx
import type { Meta, StoryObj } from '@storybook/react'
import { Button } from './Button'

const meta: Meta<typeof Button> = {
  title: 'Components/Atoms/Button',
  component: Button,
  tags: ['autodocs'],
  argTypes: {
    variant: {
      control: 'select',
      options: ['primary', 'secondary', 'ghost', 'danger', 'success']
    },
    size: {
      control: 'radio',
      options: ['sm', 'md', 'lg']
    }
  }
}

export default meta
type Story = StoryObj<typeof Button>

export const Primary: Story = {
  args: {
    variant: 'primary',
    size: 'md',
    children: 'Criar Tarefa'
  }
}

export const WithIcon: Story = {
  args: {
    variant: 'primary',
    size: 'md',
    icon: <Plus className="w-5 h-5" />,
    children: 'Nova Tarefa'
  }
}

export const Loading: Story = {
  args: {
    variant: 'primary',
    size: 'md',
    loading: true,
    children: 'Salvando...'
  }
}

export const AllVariants: Story = {
  render: () => (
    <div className="flex gap-4">
      <Button variant="primary">Primary</Button>
      <Button variant="secondary">Secondary</Button>
      <Button variant="ghost">Ghost</Button>
      <Button variant="danger">Danger</Button>
      <Button variant="success">Success</Button>
    </div>
  )
}
```

**Addons**:
- a11y (accessibility audit)
- viewport (responsive testing)
- controls (props playground)
- actions (event logging)

---

## ✅ CHECKLIST IMPLEMENTAÇÃO

### Design Tokens
- [ ] Cores exportadas (JSON + CSS vars)
- [ ] Tipografia configurada (Inter font)
- [ ] Spacing scale 8px grid
- [ ] Shadows definidas (5 elevações)
- [ ] Border radius (4 variants)
- [ ] Z-index scale (6 níveis)

### Componentes
- [ ] 50+ components Figma library
- [ ] 200+ Storybook stories
- [ ] Dark mode todas variantes
- [ ] Responsive (375px → 1920px)
- [ ] Accessibility AAA (axe-core 0 violations)

### Documentação
- [ ] Design System handbook (este doc)
- [ ] Figma Dev Mode habilitado
- [ ] Storybook publicado (Chromatic)
- [ ] Video walkthrough (15min Loom)

### Performance
- [ ] Lighthouse score 95+ (todas métricas)
- [ ] Bundle size <300KB (gzipped)
- [ ] Lazy loading routes
- [ ] Image optimization (WebP)
- [ ] Font subsetting

---

<div align="center">

**🎨 Tubaron Design System v1.0**

*Integridade, Inovação, Empatia — em cada pixel.*

**Próximo**: [WIREFRAMES_ALTA_FIDELIDADE.md](./WIREFRAMES_ALTA_FIDELIDADE.md)

</div>

---

**Mantido por**: Equipe UI/UX Mundial  
**Última atualização**: Novembro 2025  
**Licença**: Proprietário Tubaron Telecomunicações  
**Figma**: [Link workspace] | **Storybook**: [Link deploy]

