# ⚡ PADRÕES DE INTERAÇÃO & MICRO-ANIMAÇÕES - TUBARON

**Design System**: v1.0  
**Framework**: Framer Motion + CSS Animations  
**Performance**: 60fps (16ms/frame)  
**Acessibilidade**: prefers-reduced-motion support  

---

## 🎬 FILOSOFIA DE ANIMAÇÃO

### Princípios Disney Aplicados

1. **Squash & Stretch** - Botões comprimem ao clicar
2. **Anticipation** - Hover antes de click
3. **Staging** - Guiar olhar do usuário
4. **Follow Through** - Elementos continuam movimento
5. **Ease In/Out** - Natural, não linear
6. **Arc** - Movimentos em curva, não retos
7. **Secondary Action** - Elementos secundários reagem
8. **Timing** - 200ms médio, 100ms rápido, 500ms slow
9. **Exaggeration** - Achievements celebram!
10. **Solid Drawing** - Smooth 60fps

---

## 🎨 LIBRARY ANIMAÇÕES (Framer Motion)

### 1. Fade In (Entrada Padrão)

```tsx
import { motion } from 'framer-motion'

const fadeIn = {
  initial: { opacity: 0 },
  animate: { opacity: 1 },
  exit: { opacity: 0 },
  transition: { duration: 0.2 }
}

<motion.div {...fadeIn}>
  Conteúdo aparece suavemente
</motion.div>
```

---

### 2. Slide In (Modais, Drawers)

```tsx
// Slide from right (Drawer mobile)
const slideInRight = {
  initial: { x: '100%' },
  animate: { x: 0 },
  exit: { x: '100%' },
  transition: { type: 'spring', damping: 25, stiffness: 300 }
}

// Slide from bottom (Mobile sheets)
const slideInBottom = {
  initial: { y: '100%' },
  animate: { y: 0 },
  exit: { y: '100%' },
  transition: { type: 'spring', damping: 30, stiffness: 350 }
}

// Uso
<motion.div {...slideInRight} className="fixed right-0 top-0 h-full">
  Sidebar mobile
</motion.div>
```

---

### 3. Scale (Modais Centrais)

```tsx
const scaleIn = {
  initial: { scale: 0.9, opacity: 0 },
  animate: { scale: 1, opacity: 1 },
  exit: { scale: 0.9, opacity: 0 },
  transition: { duration: 0.2, ease: 'easeOut' }
}

<motion.div {...scaleIn} className="modal">
  Modal aparece com zoom suave
</motion.div>
```

---

### 4. Button Press (Feedback Tátil)

```tsx
const buttonTap = {
  whileTap: { scale: 0.95 },
  whileHover: { scale: 1.02 },
  transition: { duration: 0.1 }
}

<motion.button {...buttonTap} className="btn-primary">
  Botão com feedback
</motion.button>
```

---

### 5. List Animations (Stagger)

```tsx
const container = {
  hidden: { opacity: 0 },
  show: {
    opacity: 1,
    transition: {
      staggerChildren: 0.1 // 100ms entre cada item
    }
  }
}

const item = {
  hidden: { opacity: 0, y: 20 },
  show: { opacity: 1, y: 0 }
}

<motion.ul variants={container} initial="hidden" animate="show">
  {tasks.map(task => (
    <motion.li key={task.id} variants={item}>
      <TaskCard task={task} />
    </motion.li>
  ))}
</motion.ul>
```

---

### 6. Ranking Live Update (Layout Animation)

```tsx
import { motion, AnimatePresence } from 'framer-motion'

// Layout animation (auto smooth reordering)
<AnimatePresence>
  {rankings.map(rank => (
    <motion.tr
      key={rank.id}
      layout // ← Mágica! Auto-anima posição
      layoutId={rank.id}
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      exit={{ opacity: 0 }}
      transition={{ layout: { duration: 0.4, ease: 'easeInOut' } }}
    >
      <td>{rank.name}</td>
      <td>{rank.points}</td>
    </motion.tr>
  ))}
</AnimatePresence>
```

---

### 7. Achievement Unlock (Celebration)

```tsx
import Confetti from 'react-confetti'

const achievementUnlock = {
  initial: { scale: 0, rotate: -180, y: 100 },
  animate: {
    scale: [0, 1.2, 1],
    rotate: [0, 10, -10, 0],
    y: 0
  },
  transition: {
    duration: 0.6,
    ease: 'backOut'
  }
}

const AchievementToast = ({ achievement }: { achievement: Achievement }) => {
  const [showConfetti, setShowConfetti] = useState(true)

  useEffect(() => {
    const timer = setTimeout(() => setShowConfetti(false), 3000)
    return () => clearTimeout(timer)
  }, [])

  return (
    <>
      {showConfetti && (
        <Confetti
          width={window.innerWidth}
          height={window.innerHeight}
          numberOfPieces={200}
          recycle={false}
          colors={['#3b82f6', '#22c55e', '#f59e0b', '#a855f7']}
        />
      )}
      
      <motion.div {...achievementUnlock} className="achievement-toast">
        <div className="achievement-icon">
          <img src={achievement.icon} alt="" />
        </div>
        <h3>🎉 {achievement.name}</h3>
        <p>+{achievement.points} pontos</p>
      </motion.div>
    </>
  )
}
```

---

### 8. Loading States

#### Skeleton Loading

```tsx
// CSS Shimmer Effect
.skeleton {
  @apply animate-pulse bg-neutral-200 dark:bg-neutral-700 rounded;
  background: linear-gradient(
    90deg,
    var(--tw-gradient-from) 0%,
    var(--tw-gradient-to) 50%,
    var(--tw-gradient-from) 100%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s ease-in-out infinite;
}

@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}

// Component
const TaskCardSkeleton = () => (
  <div className="card">
    <div className="skeleton h-6 w-3/4 mb-4" />
    <div className="skeleton h-4 w-1/2 mb-2" />
    <div className="skeleton h-4 w-2/3" />
  </div>
)
```

#### Spinner

```tsx
// Tailwind Spinner
<div className="flex items-center gap-2">
  <div className="w-5 h-5 border-2 border-primary-600 border-t-transparent rounded-full animate-spin" />
  <span>Carregando...</span>
</div>

// Framer Motion Spinner
const spinnerVariants = {
  animate: {
    rotate: 360,
    transition: {
      repeat: Infinity,
      duration: 1,
      ease: 'linear'
    }
  }
}

<motion.div
  variants={spinnerVariants}
  animate="animate"
  className="w-8 h-8 border-4 border-primary-600 border-t-transparent rounded-full"
/>
```

---

## 🎯 INTERAÇÕES ESPECÍFICAS TUBARON

### 1. Vote Star Rating (Hover + Click)

```tsx
import { useState } from 'react'
import { motion } from 'framer-motion'
import { Star } from 'lucide-react'

const StarRating = ({ onRate }: { onRate: (rating: number) => void }) => {
  const [rating, setRating] = useState(0)
  const [hover, setHover] = useState(0)

  return (
    <div className="flex items-center gap-1">
      {[1, 2, 3, 4, 5, 6, 7, 8, 9, 10].map(star => (
        <motion.button
          key={star}
          onClick={() => {
            setRating(star)
            onRate(star)
          }}
          onMouseEnter={() => setHover(star)}
          onMouseLeave={() => setHover(0)}
          whileHover={{ scale: 1.2 }}
          whileTap={{ scale: 0.9 }}
          className="transition-transform"
        >
          <Star
            className={cn(
              'w-6 h-6 transition-colors duration-150',
              (hover >= star || rating >= star)
                ? 'fill-warning-500 text-warning-500'
                : 'fill-none text-neutral-300'
            )}
          />
        </motion.button>
      ))}
      <span className="ml-3 text-2xl font-bold text-primary-600">
        {(rating || hover).toFixed(1)} / 10
      </span>
    </div>
  )
}
```

---

### 2. Live Ranking Update Pulse

```tsx
// Highlight novo na lista (3s)
const HighlightRow = ({ isNew }: { isNew: boolean }) => (
  <motion.tr
    className={cn(
      'transition-colors',
      isNew && 'bg-warning-50 dark:bg-warning-900/20'
    )}
    animate={isNew ? {
      backgroundColor: [
        'rgba(251, 191, 36, 0.2)',
        'rgba(251, 191, 36, 0.05)',
        'rgba(251, 191, 36, 0)'
      ]
    } : {}}
    transition={{ duration: 3 }}
  >
    {/* Row content */}
  </motion.tr>
)
```

---

### 3. Toast Notifications

```tsx
import { Toaster, toast } from 'react-hot-toast'

// Config global
<Toaster
  position="top-right"
  toastOptions={{
    duration: 4000,
    style: {
      background: 'var(--background)',
      color: 'var(--foreground)',
      borderRadius: '12px',
      padding: '16px',
      fontSize: '14px'
    },
    success: {
      iconTheme: {
        primary: '#22c55e',
        secondary: '#fff'
      }
    },
    error: {
      iconTheme: {
        primary: '#ef4444',
        secondary: '#fff'
      }
    }
  }}
/>

// Usage
toast.success('Tarefa criada com sucesso!')
toast.error('Erro ao salvar. Tente novamente.')
toast.loading('Processando votação...')

// Custom com componente
toast.custom((t) => (
  <motion.div
    initial={{ x: 400 }}
    animate={{ x: 0 }}
    exit={{ x: 400 }}
    className="bg-white dark:bg-neutral-800 p-4 rounded-xl shadow-lg"
  >
    <div className="flex items-center gap-3">
      <Trophy className="w-6 h-6 text-gold-500" />
      <div>
        <p className="font-semibold">Nova conquista!</p>
        <p className="text-sm text-neutral-600">Líder do Mês desbloqueado</p>
      </div>
    </div>
  </motion.div>
))
```

---

### 4. Command Palette (Cmd+K)

```tsx
import { Dialog } from '@headlessui/react'
import { Search } from 'lucide-react'

const CommandPalette = () => {
  const [isOpen, setIsOpen] = useState(false)
  const [query, setQuery] = useState('')

  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault()
        setIsOpen(true)
      }
    }

    document.addEventListener('keydown', handleKeyDown)
    return () => document.removeEventListener('keydown', handleKeyDown)
  }, [])

  return (
    <Dialog
      open={isOpen}
      onClose={() => setIsOpen(false)}
      className="relative z-50"
    >
      {/* Backdrop */}
      <motion.div
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        exit={{ opacity: 0 }}
        className="fixed inset-0 bg-black/50"
        aria-hidden="true"
      />

      {/* Modal */}
      <div className="fixed inset-0 flex items-start justify-center pt-[20vh]">
        <Dialog.Panel
          as={motion.div}
          initial={{ scale: 0.95, opacity: 0 }}
          animate={{ scale: 1, opacity: 1 }}
          exit={{ scale: 0.95, opacity: 0 }}
          className="w-full max-w-2xl bg-white dark:bg-neutral-800 rounded-2xl shadow-xl overflow-hidden"
        >
          {/* Search Input */}
          <div className="flex items-center gap-3 px-4 py-3 border-b border-neutral-200 dark:border-neutral-700">
            <Search className="w-5 h-5 text-neutral-400" />
            <input
              type="text"
              placeholder="Buscar tarefas, equipes, rankings..."
              value={query}
              onChange={(e) => setQuery(e.target.value)}
              className="flex-1 bg-transparent border-none outline-none text-neutral-900 dark:text-neutral-100"
              autoFocus
            />
            <kbd className="px-2 py-1 text-xs bg-neutral-100 dark:bg-neutral-700 rounded">
              ESC
            </kbd>
          </div>

          {/* Results */}
          <div className="max-h-96 overflow-y-auto p-2">
            {/* Render filtered results */}
          </div>
        </Dialog.Panel>
      </div>
    </Dialog>
  )
}
```

---

## 🚀 PERFORMANCE OPTIMIZATIONS

### 1. Reduce Motion (Acessibilidade)

```tsx
import { useReducedMotion } from 'framer-motion'

const MyComponent = () => {
  const shouldReduceMotion = useReducedMotion()

  return (
    <motion.div
      initial={{ opacity: 0, y: shouldReduceMotion ? 0 : 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: shouldReduceMotion ? 0 : 0.3 }}
    >
      Respeita preferências do usuário
    </motion.div>
  )
}

// CSS alternativo
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
  }
}
```

---

### 2. Will-Change (GPU Acceleration)

```css
/* Elementos que animam frequentemente */
.ranking-row {
  will-change: transform, opacity;
}

/* Remover após animação */
.ranking-row:not(.animating) {
  will-change: auto;
}
```

---

### 3. Transform > Position (60fps)

```tsx
// ❌ EVITAR (causa reflow)
<motion.div
  animate={{ top: 100, left: 200 }}
>
  Elemento
</motion.div>

// ✅ MELHOR (GPU-accelerated)
<motion.div
  animate={{ x: 200, y: 100 }}
>
  Elemento
</motion.div>
```

---

## 📊 TIMINGS RECOMENDADOS

### Duração por Tipo

| Tipo Animação | Duração | Easing | Uso |
|---------------|---------|--------|-----|
| **Micro** (hover, focus) | 100ms | `ease-out` | Feedback imediato |
| **Rápida** (toast, badge) | 200ms | `ease-in-out` | Notificações |
| **Média** (modal, drawer) | 300ms | `spring` | Transições principais |
| **Lenta** (page transitions) | 500ms | `ease-in-out` | Mudança contexto |
| **Celebração** (achievement) | 600-1000ms | `backOut` | Gamification |

---

### Easing Functions

```typescript
// Custom easings (Framer Motion)
const easings = {
  easeOutQuart: [0.25, 1, 0.5, 1],
  easeInOutCubic: [0.65, 0, 0.35, 1],
  backOut: [0.34, 1.56, 0.64, 1], // Bounce effect
  anticipate: [0, 0.8, 0.2, 1] // Stretch before action
}

<motion.div
  animate={{ x: 100 }}
  transition={{ duration: 0.3, ease: easings.backOut }}
/>
```

---

## 🎨 CSS ANIMATIONS (Alternativas Leves)

### 1. Pulse (Badge Notification)

```css
@keyframes pulse {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.8;
    transform: scale(1.05);
  }
}

.notification-badge {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
```

---

### 2. Shimmer (Loading)

```css
@keyframes shimmer {
  0% {
    background-position: -1000px 0;
  }
  100% {
    background-position: 1000px 0;
  }
}

.skeleton {
  background: linear-gradient(
    90deg,
    #f0f0f0 0px,
    #f8f8f8 40px,
    #f0f0f0 80px
  );
  background-size: 1000px 100%;
  animation: shimmer 1.5s linear infinite;
}
```

---

### 3. Bounce (Floating FAB)

```css
@keyframes bounce-subtle {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-8px);
  }
}

.floating-button {
  animation: bounce-subtle 2s ease-in-out infinite;
}
```

---

## ✅ CHECKLIST ANIMAÇÕES

### Antes de Deploy

- [ ] Todas animações testadas 60fps (Chrome DevTools Performance)
- [ ] `prefers-reduced-motion` implementado (todas animações)
- [ ] Skeleton loading (todas listas/cards)
- [ ] Loading spinners (todas actions async)
- [ ] Toast notifications (sucesso/erro/info)
- [ ] Modal transitions (fade + scale)
- [ ] Drawer transitions (slide)
- [ ] Button feedback (hover + tap)
- [ ] List stagger (tarefas, rankings)
- [ ] Layout animation (ranking live)
- [ ] Achievement celebration (confetti)
- [ ] Progress bars animated
- [ ] Form validation transitions
- [ ] Page transitions (Next.js)
- [ ] WebSocket live updates (smooth)

### Performance Targets

- [ ] First Contentful Paint < 1.5s
- [ ] Time to Interactive < 3.5s
- [ ] Cumulative Layout Shift < 0.1
- [ ] No janky animations (todas 60fps)
- [ ] Bundle size animations < 30KB
- [ ] GPU acceleration elements críticos
- [ ] Lazy load Lottie animations

---

## 📚 RECURSOS APRENDIZADO

### Bibliotecas Recomendadas

```bash
# Animações
npm install framer-motion
npm install react-spring
npm install lottie-react

# Efeitos
npm install react-confetti
npm install react-hot-toast
npm install react-loading-skeleton

# Utilidades
npm install @use-gesture/react
npm install react-intersection-observer
```

### Inspiração

- [Stripe](https://stripe.com) - Micro-interactions
- [Linear](https://linear.app) - Keyboard shortcuts
- [Vercel](https://vercel.com) - Page transitions
- [Framer](https://framer.com) - Component animations
- [Dribbble](https://dribbble.com/tags/micro-interactions) - UI inspiration

---

<div align="center">

**⚡ Padrões de Interação & Micro-Animações**

*Delight sem comprometer performance*

</div>

---

**Criado por**: Equipe UI/UX Mundial  
**Framework**: Framer Motion + Tailwind CSS  
**Performance**: 60fps guarantee  
**Acessibilidade**: WCAG AAA (prefers-reduced-motion)  
**Última atualização**: Novembro 2025

