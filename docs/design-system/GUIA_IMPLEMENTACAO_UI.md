# 🚀 GUIA DE IMPLEMENTAÇÃO UI/UX - TUBARON

**Para**: Squad Frontend Development  
**Stack**: React 18 + Next.js 14 + Tailwind CSS + shadcn/ui  
**Nível**: Production-Ready Code  
**Versão**: 1.0  

---

## 📋 SETUP INICIAL DO PROJETO

### 1. Criar Projeto Next.js 14

```bash
# Create Next.js app with TypeScript
npx create-next-app@latest tubaron-gamification \
  --typescript \
  --tailwind \
  --app \
  --import-alias "@/*"

cd tubaron-gamification
```

---

### 2. Instalar Dependências Core

```bash
# UI Libraries
npm install @radix-ui/react-dialog \
  @radix-ui/react-dropdown-menu \
  @radix-ui/react-select \
  @radix-ui/react-tabs \
  @radix-ui/react-tooltip \
  @radix-ui/react-avatar \
  @radix-ui/react-toast \
  class-variance-authority \
  clsx \
  tailwind-merge

# Charts
npm install chart.js react-chartjs-2 recharts

# Calendar
npm install @fullcalendar/react \
  @fullcalendar/daygrid \
  @fullcalendar/interaction

# Forms
npm install react-hook-form zod @hookform/resolvers

# Animations
npm install framer-motion

# Icons
npm install lucide-react

# Date handling
npm install date-fns

# State Management
npm install zustand @tanstack/react-query

# WebSocket
npm install socket.io-client

# Utils
npm install axios
```

---

### 3. Configurar Tailwind (tailwind.config.ts)

```typescript
import type { Config } from 'tailwindcss'

const config: Config = {
  darkMode: ['class'],
  content: [
    './src/pages/**/*.{js,ts,jsx,tsx,mdx}',
    './src/components/**/*.{js,ts,jsx,tsx,mdx}',
    './src/app/**/*.{js,ts,jsx,tsx,mdx}',
  ],
  theme: {
    extend: {
      colors: {
        // Primary (Azul Tubaron)
        primary: {
          50: '#eff6ff',
          100: '#dbeafe',
          200: '#bfdbfe',
          300: '#93c5fd',
          400: '#60a5fa',
          500: '#3b82f6', // Base
          600: '#2563eb',
          700: '#1d4ed8',
          800: '#1e40af',
          900: '#1e3a8a',
          950: '#172554',
        },
        // Success
        success: {
          50: '#f0fdf4',
          100: '#dcfce7',
          200: '#bbf7d0',
          300: '#86efac',
          400: '#4ade80',
          500: '#22c55e', // Base
          600: '#16a34a',
          700: '#15803d',
          800: '#166534',
          900: '#14532d',
          950: '#052e16',
        },
        // Warning
        warning: {
          50: '#fffbeb',
          100: '#fef3c7',
          200: '#fde68a',
          300: '#fcd34d',
          400: '#fbbf24',
          500: '#f59e0b', // Base
          600: '#d97706',
          700: '#b45309',
          800: '#92400e',
          900: '#78350f',
          950: '#451a03',
        },
        // Error
        error: {
          50: '#fef2f2',
          100: '#fee2e2',
          200: '#fecaca',
          300: '#fca5a5',
          400: '#f87171',
          500: '#ef4444', // Base
          600: '#dc2626',
          700: '#b91c1c',
          800: '#991b1b',
          900: '#7f1d1d',
          950: '#450a0a',
        },
        // Neutral
        neutral: {
          50: '#fafafa',
          100: '#f5f5f5',
          200: '#e5e5e5',
          300: '#d4d4d4',
          400: '#a3a3a3',
          500: '#737373',
          600: '#525252',
          700: '#404040',
          800: '#262626',
          900: '#171717',
          950: '#0a0a0a',
        },
        // Gamification
        gold: {
          400: '#fbbf24',
          500: '#f59e0b',
          600: '#d97706',
        },
        silver: {
          400: '#cbd5e1',
          500: '#94a3b8',
          600: '#64748b',
        },
        bronze: {
          400: '#fb923c',
          500: '#f97316',
          600: '#ea580c',
        },
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
        mono: ['JetBrains Mono', 'monospace'],
      },
      spacing: {
        '128': '32rem',
        '144': '36rem',
      },
      borderRadius: {
        'sm': '4px',
        'base': '8px',
        'lg': '12px',
        'xl': '16px',
        '2xl': '24px',
      },
      boxShadow: {
        'xs': '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
        'sm': '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1)',
        'base': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1)',
        'md': '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1)',
        'lg': '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1)',
        'xl': '0 25px 50px -12px rgba(0, 0, 0, 0.25)',
      },
      keyframes: {
        'pulse-slow': {
          '0%, 100%': { opacity: '1' },
          '50%': { opacity: '0.5' },
        },
        'slide-in-right': {
          '0%': { transform: 'translateX(100%)' },
          '100%': { transform: 'translateX(0)' },
        },
        'slide-out-right': {
          '0%': { transform: 'translateX(0)' },
          '100%': { transform: 'translateX(100%)' },
        },
        'bounce-subtle': {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-5px)' },
        },
      },
      animation: {
        'pulse-slow': 'pulse-slow 3s ease-in-out infinite',
        'slide-in-right': 'slide-in-right 0.3s ease-out',
        'slide-out-right': 'slide-out-right 0.3s ease-in',
        'bounce-subtle': 'bounce-subtle 1s ease-in-out infinite',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
}

export default config
```

---

### 4. Configurar globals.css

```css
/* src/app/globals.css */
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
  :root {
    /* Light mode custom properties */
    --background: 250 250 250; /* neutral-50 */
    --foreground: 23 23 23; /* neutral-900 */
  }

  .dark {
    /* Dark mode custom properties */
    --background: 10 10 10; /* neutral-950 */
    --foreground: 250 250 250; /* neutral-50 */
  }

  * {
    @apply border-neutral-200 dark:border-neutral-800;
  }

  body {
    @apply bg-neutral-50 text-neutral-900 dark:bg-neutral-950 dark:text-neutral-50;
    font-feature-settings: "rlig" 1, "calt" 1;
  }

  /* Scroll personalizado */
  ::-webkit-scrollbar {
    @apply w-2 h-2;
  }

  ::-webkit-scrollbar-track {
    @apply bg-neutral-100 dark:bg-neutral-900;
  }

  ::-webkit-scrollbar-thumb {
    @apply bg-neutral-400 dark:bg-neutral-600 rounded-full;
  }

  ::-webkit-scrollbar-thumb:hover {
    @apply bg-neutral-500 dark:bg-neutral-500;
  }

  /* Focus visible (keyboard navigation) */
  *:focus-visible {
    @apply outline-none ring-2 ring-primary-500 ring-offset-2 ring-offset-white dark:ring-offset-neutral-950;
  }

  /* Remove focus em mouse clicks */
  *:focus:not(:focus-visible) {
    @apply outline-none ring-0;
  }

  /* Smooth scroll */
  html {
    @apply scroll-smooth;
  }

  /* Selection */
  ::selection {
    @apply bg-primary-200 dark:bg-primary-800 text-neutral-900 dark:text-neutral-100;
  }
}

@layer components {
  /* Gradient text */
  .gradient-text {
    @apply bg-gradient-to-r from-primary-600 to-primary-800 bg-clip-text text-transparent;
  }

  /* Glass morphism */
  .glass {
    @apply bg-white/10 backdrop-blur-lg border border-white/20;
  }

  /* Container responsive */
  .container-custom {
    @apply max-w-7xl mx-auto px-4 sm:px-6 lg:px-8;
  }

  /* Card padrão */
  .card {
    @apply bg-white dark:bg-neutral-800 rounded-xl p-6 shadow-base;
  }

  /* Button base (extend com variants) */
  .btn {
    @apply inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed;
  }

  /* Input base */
  .input {
    @apply w-full px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:focus:ring-primary-800 transition-colors duration-200;
  }

  /* Badge base */
  .badge {
    @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
  }

  /* Skeleton loading */
  .skeleton {
    @apply animate-pulse bg-neutral-200 dark:bg-neutral-700 rounded;
  }

  /* Divisor com texto */
  .divider {
    @apply relative flex items-center py-5;
  }

  .divider::before,
  .divider::after {
    @apply flex-1 border-t border-neutral-200 dark:border-neutral-700;
    content: '';
  }

  .divider span {
    @apply px-4 text-sm text-neutral-500 dark:text-neutral-400;
  }
}

@layer utilities {
  /* Text truncate lines */
  .line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  /* Responsive padding */
  .section-padding {
    @apply py-12 md:py-16 lg:py-20;
  }

  /* Responsive grid */
  .grid-responsive {
    @apply grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6;
  }

  /* Safe area (mobile) */
  .safe-area-inset {
    padding-top: env(safe-area-inset-top);
    padding-bottom: env(safe-area-inset-bottom);
    padding-left: env(safe-area-inset-left);
    padding-right: env(safe-area-inset-right);
  }

  /* Hide scrollbar */
  .scrollbar-hide::-webkit-scrollbar {
    display: none;
  }

  .scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
  }
}

/* Animations personalizadas */
@keyframes shimmer {
  0% {
    background-position: -1000px 0;
  }
  100% {
    background-position: 1000px 0;
  }
}

.animate-shimmer {
  animation: shimmer 2s infinite;
  background: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
  background-size: 1000px 100%;
}

.dark .animate-shimmer {
  background: linear-gradient(to right, #262626 0%, #404040 20%, #262626 40%, #262626 100%);
}
```

---

## 🧩 COMPONENTES REACT IMPLEMENTADOS

### 1. Button Component (src/components/ui/Button.tsx)

```tsx
import { forwardRef, ButtonHTMLAttributes } from 'react'
import { cva, type VariantProps } from 'class-variance-authority'
import { Loader2 } from 'lucide-react'
import { cn } from '@/lib/utils'

const buttonVariants = cva(
  'inline-flex items-center justify-center gap-2 rounded-lg font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.98]',
  {
    variants: {
      variant: {
        primary: 'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500',
        secondary: 'bg-neutral-100 text-neutral-900 hover:bg-neutral-200 focus:ring-neutral-400 dark:bg-neutral-800 dark:text-neutral-100 dark:hover:bg-neutral-700',
        ghost: 'bg-transparent text-neutral-700 hover:bg-neutral-100 focus:ring-neutral-400 dark:text-neutral-300 dark:hover:bg-neutral-800',
        danger: 'bg-error-600 text-white hover:bg-error-700 focus:ring-error-500',
        success: 'bg-success-600 text-white hover:bg-success-700 focus:ring-success-500',
        outline: 'border-2 border-primary-600 text-primary-600 hover:bg-primary-50 focus:ring-primary-500 dark:border-primary-400 dark:text-primary-400 dark:hover:bg-primary-900/20',
      },
      size: {
        sm: 'h-9 px-3 text-sm',
        md: 'h-10 px-4 text-base',
        lg: 'h-12 px-6 text-lg',
      },
      fullWidth: {
        true: 'w-full',
      },
    },
    defaultVariants: {
      variant: 'primary',
      size: 'md',
    },
  }
)

export interface ButtonProps
  extends ButtonHTMLAttributes<HTMLButtonElement>,
    VariantProps<typeof buttonVariants> {
  loading?: boolean
  leftIcon?: React.ReactNode
  rightIcon?: React.ReactNode
}

const Button = forwardRef<HTMLButtonElement, ButtonProps>(
  (
    {
      className,
      variant,
      size,
      fullWidth,
      loading,
      disabled,
      leftIcon,
      rightIcon,
      children,
      ...props
    },
    ref
  ) => {
    return (
      <button
        ref={ref}
        className={cn(buttonVariants({ variant, size, fullWidth }), className)}
        disabled={disabled || loading}
        aria-busy={loading}
        {...props}
      >
        {loading ? (
          <Loader2 className="w-4 h-4 animate-spin" />
        ) : (
          leftIcon && <span className="flex-shrink-0">{leftIcon}</span>
        )}
        <span>{children}</span>
        {rightIcon && !loading && <span className="flex-shrink-0">{rightIcon}</span>}
      </button>
    )
  }
)

Button.displayName = 'Button'

export { Button, buttonVariants }
```

**Uso**:
```tsx
<Button variant="primary" size="md" leftIcon={<Plus />}>
  Nova Tarefa
</Button>

<Button variant="danger" loading>
  Salvando...
</Button>

<Button variant="ghost" fullWidth>
  Cancelar
</Button>
```

---

### 2. TaskCard Component (src/components/tasks/TaskCard.tsx)

```tsx
'use client'

import { format, formatDistanceToNow } from 'date-fns'
import { ptBR } from 'date-fns/locale'
import { Calendar, Trophy, Users, MoreVertical, ArrowRight, Clock } from 'lucide-react'
import { cn } from '@/lib/utils'
import { Badge } from '@/components/ui/Badge'
import { Button } from '@/components/ui/Button'
import { AvatarGroup } from '@/components/ui/AvatarGroup'
import { DropdownMenu } from '@/components/ui/DropdownMenu'

interface TaskCardProps {
  task: {
    id: string
    type: 'individual' | 'team' | 'competitive'
    title: string
    dueDate: Date
    status: 'open' | 'in_progress' | 'voting' | 'completed'
    assignees: Array<{ id: string; name: string; avatar: string }>
    points: number
    progress?: {
      completed: number
      total: number
      percentage: number
    }
  }
  onView?: () => void
  onEdit?: () => void
  onDelete?: () => void
}

const getTaskTypeIcon = (type: string) => {
  switch (type) {
    case 'individual':
      return '📋'
    case 'team':
      return '👥'
    case 'competitive':
      return '🎯'
    default:
      return '📄'
  }
}

const getStatusColor = (status: string): 'primary' | 'success' | 'warning' | 'error' => {
  switch (status) {
    case 'open':
      return 'primary'
    case 'in_progress':
      return 'warning'
    case 'voting':
      return 'warning'
    case 'completed':
      return 'success'
    default:
      return 'primary'
  }
}

const getStatusLabel = (status: string) => {
  switch (status) {
    case 'open':
      return 'Aberta'
    case 'in_progress':
      return 'Em Andamento'
    case 'voting':
      return 'Em Votação'
    case 'completed':
      return 'Completa'
    default:
      return status
  }
}

const getUrgency = (dueDate: Date): 'urgent' | 'due_soon' | 'normal' => {
  const now = new Date()
  const diffHours = (dueDate.getTime() - now.getTime()) / (1000 * 60 * 60)

  if (diffHours < 0) return 'urgent' // Atrasada
  if (diffHours < 24) return 'urgent'
  if (diffHours < 48) return 'due_soon'
  return 'normal'
}

export const TaskCard = ({ task, onView, onEdit, onDelete }: TaskCardProps) => {
  const urgency = getUrgency(task.dueDate)

  return (
    <div
      className={cn(
        'group relative bg-white dark:bg-neutral-800 rounded-xl p-6 shadow-base',
        'hover:shadow-md transition-all duration-300 cursor-pointer',
        'border-2',
        urgency === 'urgent' && 'border-error-500',
        urgency === 'due_soon' && 'border-warning-500',
        urgency === 'normal' && 'border-transparent'
      )}
      onClick={onView}
    >
      {/* Header */}
      <div className="flex items-start justify-between mb-4">
        <div className="flex items-center gap-3">
          <span className="text-2xl">{getTaskTypeIcon(task.type)}</span>
          <Badge variant="status" color={getStatusColor(task.status)}>
            {getStatusLabel(task.status)}
          </Badge>
          
          {urgency === 'urgent' && (
            <Badge variant="status" color="error">
              🔴 URGENTE
            </Badge>
          )}
          
          {urgency === 'due_soon' && (
            <Badge variant="status" color="warning">
              ⚠️ {formatDistanceToNow(task.dueDate, { locale: ptBR, addSuffix: true })}
            </Badge>
          )}
        </div>

        <DropdownMenu>
          <DropdownMenu.Trigger asChild>
            <button
              className="p-1 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors"
              onClick={(e) => e.stopPropagation()}
            >
              <MoreVertical className="w-5 h-5 text-neutral-400" />
            </button>
          </DropdownMenu.Trigger>
          <DropdownMenu.Content>
            <DropdownMenu.Item onClick={onEdit}>Editar</DropdownMenu.Item>
            <DropdownMenu.Item onClick={onDelete} className="text-error-600">
              Excluir
            </DropdownMenu.Item>
          </DropdownMenu.Content>
        </DropdownMenu>
      </div>

      {/* Title */}
      <h3 className="text-xl font-semibold text-neutral-900 dark:text-neutral-100 mb-3 line-clamp-2 group-hover:text-primary-600 transition-colors">
        {task.title}
      </h3>

      {/* Meta Info */}
      <div className="flex flex-wrap items-center gap-4 text-sm text-neutral-600 dark:text-neutral-400 mb-4">
        <div className="flex items-center gap-1.5">
          <Calendar className="w-4 h-4" />
          <span>{format(task.dueDate, "dd MMM yyyy 'às' HH:mm", { locale: ptBR })}</span>
        </div>

        {task.type !== 'individual' && (
          <div className="flex items-center gap-1.5">
            <Users className="w-4 h-4" />
            <span>{task.assignees.length} participante{task.assignees.length > 1 ? 's' : ''}</span>
          </div>
        )}

        <div className="flex items-center gap-1.5">
          <Trophy className="w-4 h-4 text-warning-500" />
          <span className="font-semibold">{task.points} pontos</span>
        </div>
      </div>

      {/* Progress Bar (Competitivas) */}
      {task.type === 'competitive' && task.progress && (
        <div className="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-3 mb-4 border border-neutral-200 dark:border-neutral-700">
          <p className="text-xs text-neutral-600 dark:text-neutral-400 mb-2 flex items-center gap-2">
            <Clock className="w-3 h-3" />
            Progresso: {task.progress.completed}/{task.progress.total} submissões
          </p>
          <div className="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2 overflow-hidden">
            <div
              className="bg-gradient-to-r from-primary-500 to-primary-700 h-2 rounded-full transition-all duration-500"
              style={{ width: `${task.progress.percentage}%` }}
            />
          </div>
        </div>
      )}

      {/* Assignees */}
      {task.assignees.length > 0 && (
        <div className="mb-4">
          <AvatarGroup users={task.assignees} max={3} size="sm" />
        </div>
      )}

      {/* Actions */}
      <Button
        variant="ghost"
        size="sm"
        fullWidth
        rightIcon={<ArrowRight className="w-4 h-4" />}
        onClick={(e) => {
          e.stopPropagation()
          onView?.()
        }}
      >
        Ver Detalhes
      </Button>
    </div>
  )
}
```

---

### 3. RankingTable Component (src/components/rankings/RankingTable.tsx)

```tsx
'use client'

import { useState, useEffect } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import { Trophy, TrendingUp, TrendingDown, Minus, Medal } from 'lucide-react'
import { cn } from '@/lib/utils'
import { Avatar } from '@/components/ui/Avatar'
import { Badge } from '@/components/ui/Badge'

interface RankingEntity {
  id: string
  rank: number
  name: string
  avatar: string
  points: number
  firstPlaces: number
  tasksCompleted: number
  trend: 'up' | 'down' | 'neutral'
  trendChange: number
}

interface RankingTableProps {
  rankings: RankingEntity[]
  currentUserId?: string
  type: 'users' | 'teams'
  loading?: boolean
}

const getMedalEmoji = (rank: number) => {
  switch (rank) {
    case 1:
      return '🥇'
    case 2:
      return '🥈'
    case 3:
      return '🥉'
    default:
      return null
  }
}

const getMedalGradient = (rank: number) => {
  switch (rank) {
    case 1:
      return 'from-gold-400 to-gold-600'
    case 2:
      return 'from-silver-400 to-silver-600'
    case 3:
      return 'from-bronze-400 to-bronze-600'
    default:
      return ''
  }
}

export const RankingTable = ({
  rankings,
  currentUserId,
  type,
  loading
}: RankingTableProps) => {
  const [highlightedIds, setHighlightedIds] = useState<Set<string>>(new Set())

  // Detectar mudanças de posição e highlight
  useEffect(() => {
    const newHighlights = new Set<string>()
    rankings.forEach(rank => {
      if (rank.trend !== 'neutral') {
        newHighlights.add(rank.id)
      }
    })
    setHighlightedIds(newHighlights)

    // Remove highlight após 3s
    const timer = setTimeout(() => {
      setHighlightedIds(new Set())
    }, 3000)

    return () => clearTimeout(timer)
  }, [rankings])

  if (loading) {
    return (
      <div className="space-y-3">
        {Array.from({ length: 10 }).map((_, i) => (
          <div key={i} className="skeleton h-20 w-full" />
        ))}
      </div>
    )
  }

  return (
    <div className="overflow-x-auto">
      <table className="w-full">
        <thead className="bg-neutral-100 dark:bg-neutral-900">
          <tr>
            <th className="px-6 py-4 text-left text-sm font-semibold text-neutral-700 dark:text-neutral-300">
              Posição
            </th>
            <th className="px-6 py-4 text-left text-sm font-semibold text-neutral-700 dark:text-neutral-300">
              {type === 'users' ? 'Colaborador' : 'Equipe'}
            </th>
            <th className="px-6 py-4 text-right text-sm font-semibold text-neutral-700 dark:text-neutral-300">
              Pontos
            </th>
            <th className="px-6 py-4 text-center text-sm font-semibold text-neutral-700 dark:text-neutral-300">
              🥇 Vitórias
            </th>
            <th className="px-6 py-4 text-center text-sm font-semibold text-neutral-700 dark:text-neutral-300">
              Tarefas
            </th>
            <th className="px-6 py-4 text-center text-sm font-semibold text-neutral-700 dark:text-neutral-300">
              Tendência
            </th>
          </tr>
        </thead>
        <tbody>
          <AnimatePresence initial={false}>
            {rankings.map((entity) => {
              const isCurrentUser = entity.id === currentUserId
              const medal = getMedalEmoji(entity.rank)
              const isHighlighted = highlightedIds.has(entity.id)

              return (
                <motion.tr
                  key={entity.id}
                  layout
                  layoutId={entity.id}
                  initial={{ opacity: 0 }}
                  animate={{ opacity: 1 }}
                  exit={{ opacity: 0 }}
                  className={cn(
                    'border-b border-neutral-200 dark:border-neutral-700 transition-colors',
                    isCurrentUser && 'bg-primary-50 dark:bg-primary-900/20',
                    isHighlighted && 'bg-warning-50 dark:bg-warning-900/20'
                  )}
                >
                  {/* Rank */}
                  <td className="px-6 py-4">
                    <div className="flex items-center gap-3">
                      {entity.rank <= 3 ? (
                        <div className={cn(
                          'w-10 h-10 rounded-full flex items-center justify-center',
                          'bg-gradient-to-br text-white font-bold text-lg',
                          getMedalGradient(entity.rank)
                        )}>
                          {entity.rank}
                        </div>
                      ) : (
                        <div className="w-10 h-10 rounded-full flex items-center justify-center bg-neutral-100 dark:bg-neutral-800 font-bold text-lg text-neutral-700 dark:text-neutral-300">
                          {entity.rank}
                        </div>
                      )}
                      {medal && <span className="text-2xl">{medal}</span>}
                    </div>
                  </td>

                  {/* Entity Info */}
                  <td className="px-6 py-4">
                    <div className="flex items-center gap-4">
                      <Avatar
                        src={entity.avatar}
                        alt={entity.name}
                        size="md"
                        className={cn(
                          entity.rank <= 3 && 'ring-2 ring-offset-2',
                          entity.rank === 1 && 'ring-gold-500',
                          entity.rank === 2 && 'ring-silver-500',
                          entity.rank === 3 && 'ring-bronze-500'
                        )}
                      />
                      <div>
                        <p className="font-semibold text-neutral-900 dark:text-neutral-100 flex items-center gap-2">
                          {entity.name}
                          {isCurrentUser && (
                            <Badge variant="number" color="primary">
                              Você
                            </Badge>
                          )}
                        </p>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                          {entity.firstPlaces} vitória{entity.firstPlaces !== 1 ? 's' : ''}
                        </p>
                      </div>
                    </div>
                  </td>

                  {/* Points */}
                  <td className="px-6 py-4 text-right">
                    <p className="text-2xl font-extrabold text-neutral-900 dark:text-neutral-100">
                      {entity.points.toLocaleString('pt-BR')}
                    </p>
                    <p className="text-xs text-neutral-500 dark:text-neutral-400">pontos</p>
                  </td>

                  {/* First Places */}
                  <td className="px-6 py-4 text-center">
                    <div className="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-gold-100 dark:bg-gold-900/20">
                      <Trophy className="w-4 h-4 text-gold-600 dark:text-gold-400" />
                      <span className="font-bold text-gold-700 dark:text-gold-300">
                        {entity.firstPlaces}
                      </span>
                    </div>
                  </td>

                  {/* Tasks Completed */}
                  <td className="px-6 py-4 text-center">
                    <p className="font-semibold text-neutral-700 dark:text-neutral-300">
                      {entity.tasksCompleted}
                    </p>
                  </td>

                  {/* Trend */}
                  <td className="px-6 py-4">
                    <div className="flex justify-center">
                      <div
                        className={cn(
                          'inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium',
                          entity.trend === 'up' && 'bg-success-100 text-success-700 dark:bg-success-900/20 dark:text-success-300',
                          entity.trend === 'down' && 'bg-error-100 text-error-700 dark:bg-error-900/20 dark:text-error-300',
                          entity.trend === 'neutral' && 'bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300'
                        )}
                      >
                        {entity.trend === 'up' && <TrendingUp className="w-4 h-4" />}
                        {entity.trend === 'down' && <TrendingDown className="w-4 h-4" />}
                        {entity.trend === 'neutral' && <Minus className="w-4 h-4" />}
                        {entity.trend === 'up' && `↑ +${entity.trendChange}`}
                        {entity.trend === 'down' && `↓ ${entity.trendChange}`}
                        {entity.trend === 'neutral' && '─ 0'}
                      </div>
                    </div>
                  </td>
                </motion.tr>
              )
            })}
          </AnimatePresence>
        </tbody>
      </table>
    </div>
  )
}
```

---

## 🎨 PADRÕES & BEST PRACTICES

### 1. Organização de Arquivos

```
src/
├── app/                          # Next.js App Router
│   ├── (auth)/                   # Auth group
│   │   ├── login/
│   │   └── layout.tsx
│   ├── (dashboard)/              # Dashboard group
│   │   ├── dashboard/
│   │   ├── tasks/
│   │   ├── rankings/
│   │   ├── calendar/
│   │   └── layout.tsx
│   ├── globals.css
│   └── layout.tsx
├── components/
│   ├── ui/                       # Componentes base (shadcn/ui style)
│   │   ├── Button.tsx
│   │   ├── Input.tsx
│   │   ├── Badge.tsx
│   │   ├── Avatar.tsx
│   │   ├── Toast.tsx
│   │   └── ...
│   ├── tasks/                    # Componentes específicos Tasks
│   │   ├── TaskCard.tsx
│   │   ├── TaskForm.tsx
│   │   ├── TaskFilters.tsx
│   │   └── ...
│   ├── rankings/                 # Componentes Rankings
│   │   ├── RankingTable.tsx
│   │   ├── RankingChart.tsx
│   │   └── ...
│   └── layout/                   # Layout components
│       ├── Header.tsx
│       ├── Sidebar.tsx
│       └── Footer.tsx
├── hooks/                        # Custom React hooks
│   ├── useSocket.ts
│   ├── useAuth.ts
│   ├── useRankings.ts
│   └── useTheme.ts
├── lib/                          # Utilities
│   ├── utils.ts
│   ├── axios.ts
│   ├── socket.ts
│   └── validators.ts
├── stores/                       # Zustand stores
│   ├── authStore.ts
│   ├── tasksStore.ts
│   └── rankingsStore.ts
├── types/                        # TypeScript types
│   ├── task.ts
│   ├── user.ts
│   └── ranking.ts
└── constants/                    # Constantes
    ├── colors.ts
    └── routes.ts
```

---

### 2. Code Style Guide

#### Naming Conventions

```typescript
// ✅ BOM
// Components: PascalCase
const TaskCard = () => {}
const UserAvatar = () => {}

// Hooks: camelCase com prefixo 'use'
const useSocket = () => {}
const useAuth = () => {}

// Utils: camelCase
const formatDate = () => {}
const calculatePoints = () => {}

// Constants: UPPER_SNAKE_CASE
const API_BASE_URL = 'https://api.tubaron.com'
const MAX_FILE_SIZE = 5 * 1024 * 1024

// Types/Interfaces: PascalCase com sufixo descritivo
interface TaskCardProps {}
type UserRole = 'admin' | 'captain' | 'collaborator'

// ❌ EVITAR
const taskcard = () => {} // Componente deve ser PascalCase
const UseSocket = () => {} // Hook deve começar minúsculo
const apibaseurl = 'url' // Constante deve ser UPPER_SNAKE_CASE
```

---

#### Imports Order

```typescript
// 1. React & Next.js
import { useState, useEffect } from 'react'
import Image from 'next/image'

// 2. Bibliotecas externas
import { motion } from 'framer-motion'
import { format } from 'date-fns'

// 3. Componentes internos
import { Button } from '@/components/ui/Button'
import { TaskCard } from '@/components/tasks/TaskCard'

// 4. Hooks personalizados
import { useSocket } from '@/hooks/useSocket'
import { useAuth } from '@/hooks/useAuth'

// 5. Utils & Libs
import { cn } from '@/lib/utils'
import { API_BASE_URL } from '@/constants'

// 6. Types
import type { Task } from '@/types/task'

// 7. Styles (se houver CSS modules)
import styles from './TaskCard.module.css'
```

---

### 3. Performance Optimizations

#### Code Splitting

```tsx
// Lazy load páginas pesadas
import { lazy, Suspense } from 'react'

const AdminDashboard = lazy(() => import('@/app/(dashboard)/admin/page'))
const Rankings = lazy(() => import('@/app/(dashboard)/rankings/page'))

// Wrapper com Suspense
export default function App() {
  return (
    <Suspense fallback={<PageLoader />}>
      <Rankings />
    </Suspense>
  )
}
```

#### Memoization

```tsx
import { memo, useMemo, useCallback } from 'react'

// Memo component (evita re-renders desnecessários)
export const TaskCard = memo(({ task }: TaskCardProps) => {
  // ...
})

// useMemo (cálculos pesados)
const sortedRankings = useMemo(() => {
  return rankings.sort((a, b) => b.points - a.points)
}, [rankings])

// useCallback (funções em props)
const handleTaskClick = useCallback((taskId: string) => {
  navigate(`/tasks/${taskId}`)
}, [navigate])
```

---

### 4. Accessibility Checklist

```tsx
// ✅ Keyboard Navigation
<button
  onClick={handleClick}
  onKeyDown={(e) => e.key === 'Enter' && handleClick()}
  tabIndex={0}
>
  Clique ou pressione Enter
</button>

// ✅ ARIA Labels
<button aria-label="Fechar modal">
  <X className="w-5 h-5" />
</button>

// ✅ Screen Reader
<div role="status" aria-live="polite">
  {message}
</div>

// ✅ Focus Management
import { useEffect, useRef } from 'react'

const Modal = ({ isOpen }: { isOpen: boolean }) => {
  const buttonRef = useRef<HTMLButtonElement>(null)

  useEffect(() => {
    if (isOpen) {
      buttonRef.current?.focus()
    }
  }, [isOpen])

  return (
    <button ref={buttonRef}>Fechar</button>
  )
}

// ✅ Contrast Ratio
// Garantir mínimo 7:1 para WCAG AAA
// Testar com axe DevTools
```

---

## 📚 RECURSOS & FERRAMENTAS

### Extensões VS Code Recomendadas

```json
{
  "recommendations": [
    "bradlc.vscode-tailwindcss",
    "dbaeumer.vscode-eslint",
    "esbenp.prettier-vscode",
    "streetsidesoftware.code-spell-checker",
    "styled-components.vscode-styled-components",
    "ms-vscode.vscode-typescript-next",
    "usernamehw.errorlens",
    "formulahendry.auto-rename-tag",
    "deque-systems.vscode-axe-linter"
  ]
}
```

---

### Scripts package.json

```json
{
  "scripts": {
    "dev": "next dev",
    "build": "next build",
    "start": "next start",
    "lint": "next lint",
    "lint:fix": "next lint --fix",
    "format": "prettier --write \"src/**/*.{ts,tsx,css,md}\"",
    "type-check": "tsc --noEmit",
    "test": "jest",
    "test:watch": "jest --watch",
    "test:coverage": "jest --coverage",
    "storybook": "storybook dev -p 6006",
    "build-storybook": "storybook build",
    "analyze": "ANALYZE=true next build"
  }
}
```

---

## ✅ CHECKLIST GO-LIVE UI

- [ ] Todos componentes implementados (50+)
- [ ] Dark mode funciona (todas telas)
- [ ] Responsive (375px → 1920px testado)
- [ ] Acessibilidade WCAG AAA (axe-core 0 violations)
- [ ] Performance Lighthouse 95+ (todas métricas)
- [ ] Storybook completo (200+ stories)
- [ ] TypeScript strict mode (0 errors)
- [ ] ESLint configurado (0 warnings)
- [ ] Prettier formatação consistente
- [ ] Unit tests coverage 80%+
- [ ] E2E tests Playwright (10 scenarios)
- [ ] Bundle size <300KB gzipped
- [ ] Lazy loading routes implementado
- [ ] Image optimization Next.js Image
- [ ] Font loading strategy otimizada
- [ ] WebSocket integration testada
- [ ] Error boundaries implementados
- [ ] Loading states todas ações async
- [ ] Toast notifications funcionando
- [ ] Keyboard shortcuts implementados

---

<div align="center">

**🚀 Guia de Implementação Completo**

*Design System → Código Production-Ready*

</div>

---

**Mantido por**: Equipe UI/UX + Frontend  
**Stack**: React 18 + Next.js 14 + Tailwind CSS 3.4  
**Status**: ✅ PRODUCTION-READY  
**Última atualização**: Novembro 2025

