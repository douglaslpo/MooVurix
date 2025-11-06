# 🗳️ SPRINT 3 - VOTAÇÃO & SCORING SYSTEM

**Período**: Semanas 5-6  
**Início**: 06 de Novembro de 2025  
**Foco**: Sistema de Votação + Anti-fraude + Scoring Automático  
**Status**: 🚀 **INICIANDO**  

---

## 🎯 OBJETIVOS SPRINT 3

### Principais Entregas

1. **Sistema de Votação Completo** (3 métodos)
   - ✅ Maioria simples (aprovado/rejeitado)
   - ✅ Notas 0-10 (média ponderada)
   - ✅ Ranking 1º/2º/3º (peso diferenciado)

2. **Anti-fraude** (segurança)
   - ✅ Rate limiting (10 votos/minuto)
   - ✅ Elegibilidade (apenas participantes)
   - ✅ Voto único por tarefa
   - ✅ Janela de votação (configurável)

3. **Scoring Automático**
   - ✅ Cálculo pontos por votação
   - ✅ Atualização rankings real-time
   - ✅ Bônus/penalidades
   - ✅ Streaks e multiplicadores

4. **Interfaces Votação**
   - ✅ Página votação individual
   - ✅ Lista tarefas em votação
   - ✅ Resultados em tempo real
   - ✅ Dashboard votações

---

## 📋 ARQUIVOS A CRIAR

### 1. Voting Manager (Core)

**Arquivo**: `classes/voting_manager.php` (~500 linhas)

```php
class voting_manager {
    // Métodos de votação
    public function cast_vote($taskid, $userid, $votedata)
    public function check_eligibility($taskid, $userid)
    public function check_rate_limit($userid)
    public function calculate_results($taskid)
    
    // Anti-fraude
    public function validate_vote($taskid, $userid, $votedata)
    public function log_vote_attempt($userid, $taskid, $success)
    
    // Scoring
    public function apply_score($taskid, $results)
    public function update_rankings($seasonid)
}
```

### 2. Páginas Votação

**Arquivo**: `voting/index.php` (~300 linhas)
- Lista tarefas em votação
- Filtros por tipo
- Status votação

**Arquivo**: `voting/vote.php` (~400 linhas)
- Interface votação (3 métodos)
- Validação client-side
- Confirmação voto

**Arquivo**: `voting/results.php` (~250 linhas)
- Resultados votação
- Gráficos (Chart.js)
- Estatísticas detalhadas

### 3. AJAX Endpoints

**Arquivo**: `ajax/vote_submit.php` (~200 linhas)
- Processar voto via AJAX
- Retornar resultado imediato
- Atualizar contadores

**Arquivo**: `ajax/voting_stats.php` (~150 linhas)
- Estatísticas real-time
- Progresso votação
- Rankings atualizados

### 4. Scoring System

**Arquivo**: `classes/scoring_engine.php` (~400 linhas)
- Cálculo pontos
- Bônus e penalidades
- Streaks
- Achievements unlock

---

## 🔒 ANTI-FRAUDE - ESPECIFICAÇÕES

### 1. Rate Limiting

```php
// Limite: 10 votos por minuto
$window = 60; // segundos
$maxvotes = 10;

if (count_recent_votes($userid, $window) >= $maxvotes) {
    throw new moodle_exception('ratelimit', 'local_tubaron');
}
```

### 2. Elegibilidade

**Pode votar SE**:
- Participou da tarefa (criador, atribuído, membro equipe)
- **OU** tarefa é tipo "competitive" (todos elegíveis)
- **E** status tarefa = "voting"
- **E** dentro janela votação
- **E** não votou ainda

### 3. Validação Voto

```php
// Maioria: true/false
// Notas: 0-10 (inteiro)
// Ranking: array(1=>userid, 2=>userid, 3=>userid)

switch ($method) {
    case 'majority':
        return is_bool($vote);
    case 'rating':
        return is_int($vote) && $vote >= 0 && $vote <= 10;
    case 'ranking':
        return is_array($vote) && count($vote) === 3;
}
```

---

## 📊 MÉTODOS DE VOTAÇÃO

### 1. Maioria Simples

**Uso**: Aprovar/Rejeitar tarefa

```
Votação: ✅ Aprovado / ❌ Rejeitado
Critério: > 50% aprovações
Pontos: 100% se aprovado, 0% se rejeitado
```

**Exemplo**:
- 7 votos ✅ aprovado
- 3 votos ❌ rejeitado
- **Resultado**: APROVADO (70% aprovação)
- **Pontos**: 100 pts (tarefa vale 100)

### 2. Notas 0-10

**Uso**: Qualidade, esforço, criatividade

```
Votação: Nota 0-10
Critério: Média ponderada
Pontos: (média/10) * pontos_tarefa
```

**Exemplo**:
- Votos: 8, 9, 7, 10, 8
- **Média**: 8.4
- **Pontos**: (8.4/10) * 100 = 84 pts

### 3. Ranking 1º/2º/3º

**Uso**: Competições, múltiplas submissões

```
Votação: Ordenar top 3
Peso: 1º = 3pts, 2º = 2pts, 3º = 1pt
Critério: Soma ponderada
Pontos: Proporcional posição final
```

**Exemplo**:
- Submissão A: 5x1º + 2x2º + 1x3º = 19 pts
- Submissão B: 3x1º + 4x2º + 1x3º = 18 pts
- Submissão C: 0x1º + 2x2º + 6x3º = 10 pts
- **Pontos**: A=100, B=85, C=50

---

## 🏆 SCORING ENGINE

### Fórmula Base

```
Pontos Finais = (Pontos Base * Votação%) + Bônus - Penalidades

Onde:
- Pontos Base: Valor tarefa (ex: 100 pts)
- Votação%: Resultado votação (0-100%)
- Bônus: Streaks, first-complete, quality
- Penalidades: Atraso, rejeição
```

### Bônus Disponíveis

| Bônus | Condição | Valor |
|-------|----------|-------|
| **First Complete** | Primeira submissão aprovada | +20% |
| **Perfect Score** | Nota 10/10 ou 100% aprovação | +15% |
| **Streak 3** | 3 tarefas seguidas aprovadas | +10% |
| **Streak 5** | 5 tarefas seguidas | +20% |
| **Early Submit** | Antes 50% deadline | +10% |
| **Team Bonus** | Todos membros contribuíram | +15% |

### Penalidades

| Penalidade | Condição | Valor |
|------------|----------|-------|
| **Late Submit** | Depois deadline | -20% |
| **Rejected** | Maioria rejeitou | -50% |
| **Low Quality** | Nota < 5/10 | -30% |
| **Incomplete** | Critérios não atendidos | -40% |

---

## 🎨 DESIGN UI VOTAÇÃO

### Card Votação (Maioria)

```
┌─────────────────────────────────────────┐
│ 📋 Implementar Feature X                │
│ Individual • 100 pts • ⏰ 2h restantes  │
├─────────────────────────────────────────┤
│ Descrição breve da tarefa...            │
├─────────────────────────────────────────┤
│ 👤 Douglas Leonardo                     │
│ 📎 2 arquivos anexados                  │
├─────────────────────────────────────────┤
│         Aprovar esta tarefa?            │
│                                         │
│   [✅ Aprovar]    [❌ Rejeitar]         │
│                                         │
│ 📊 5/10 votos recebidos                 │
└─────────────────────────────────────────┘
```

### Card Votação (Notas)

```
┌─────────────────────────────────────────┐
│ 🎨 Design Dashboard                     │
│ Team • 150 pts • ✅ Votação aberta      │
├─────────────────────────────────────────┤
│ Qual a qualidade desta entrega?         │
│                                         │
│ ⭐⭐⭐⭐⭐⭐⭐⭐⭐⭐                   │
│  0  1  2  3  4  5  6  7  8  9  10       │
│                                         │
│        [Confirmar Nota: 8]              │
│                                         │
│ 📊 Média atual: 7.5 (6 votos)           │
└─────────────────────────────────────────┘
```

### Card Votação (Ranking)

```
┌─────────────────────────────────────────┐
│ 🏆 Melhor Solução Técnica               │
│ Competitive • 200 pts • 8 submissões    │
├─────────────────────────────────────────┤
│ Ordene as 3 melhores submissões:        │
│                                         │
│ 🥇 1º lugar                             │
│ ▼ [Solução A - Douglas    ]             │
│                                         │
│ 🥈 2º lugar                             │
│ ▼ [Solução C - Maria      ]             │
│                                         │
│ 🥉 3º lugar                             │
│ ▼ [Solução B - João       ]             │
│                                         │
│        [Confirmar Ranking]              │
│                                         │
│ 📊 3/15 votos recebidos                 │
└─────────────────────────────────────────┘
```

---

## 🔄 FLUXO VOTAÇÃO

### 1. Tarefa Submetida

```
Status: open → in_progress → submitted
```

### 2. Abrir Votação

```php
// Admin ou automático após todas submissões
local_tubaron_open_voting($taskid);

// Notificar votantes elegíveis
local_tubaron_notify_voting_opened($taskid);
```

### 3. Período Votação

```
Duração: Configurável (padrão 48h)
Votantes: Apenas elegíveis
Votos mínimos: 3 (configurável)
```

### 4. Calcular Resultados

```php
// Automático ao atingir deadline ou votos mínimos
$results = local_tubaron_calculate_voting_results($taskid);

// Aplicar pontuação
local_tubaron_apply_voting_score($taskid, $results);

// Atualizar rankings
local_tubaron_update_rankings($seasonid);
```

### 5. Finalizar

```
Status: voting → completed
Pontos: Aplicados
Ranking: Atualizado
Notificação: Enviada
```

---

## 📊 ESTATÍSTICAS VOTAÇÃO

### Por Tarefa

- Total votos esperados
- Votos recebidos
- Taxa participação
- Tempo médio voto
- Distribuição votos

### Por Usuário

- Votos realizados
- Taxa participação geral
- Concordância com maioria
- Reputação votante

### Por Temporada

- Total votações
- Média votos/tarefa
- Taxa conclusão
- Tempo médio votação

---

## 🚀 CRONOGRAMA SPRINT 3

### Semana 1 (Dias 1-3)

- [ ] `classes/voting_manager.php` (core)
- [ ] `voting/index.php` (listagem)
- [ ] `voting/vote.php` (interface)
- [ ] Strings idioma (+40 strings)

### Semana 2 (Dias 4-6)

- [ ] `classes/scoring_engine.php`
- [ ] `ajax/vote_submit.php`
- [ ] `ajax/voting_stats.php`
- [ ] `voting/results.php`
- [ ] JavaScript voting.js
- [ ] Testes anti-fraude

---

## 📝 STRINGS IDIOMA NECESSÁRIAS

```php
// Votação geral
$string['voting'] = 'Votação';
$string['vote'] = 'Votar';
$string['castvote'] = 'Registrar Voto';
$string['openvoting'] = 'Em Votação';
$string['votingclosed'] = 'Votação Encerrada';

// Métodos
$string['majority'] = 'Maioria Simples';
$string['rating'] = 'Notas 0-10';
$string['ranking'] = 'Ranking Top 3';

// Ações
$string['approve'] = 'Aprovar';
$string['reject'] = 'Rejeitar';
$string['givenote'] = 'Dar Nota';
$string['selectrank'] = 'Selecionar Posição';

// Erros
$string['alreadyvoted'] = 'Você já votou nesta tarefa';
$string['noteligible'] = 'Você não é elegível para votar';
$string['ratelimit'] = 'Limite de votos excedido. Aguarde';
$string['votingnotopen'] = 'Votação não está aberta';

// Resultados
$string['approved'] = 'Aprovado';
$string['rejected'] = 'Rejeitado';
$string['averagescore'] = 'Nota Média';
$string['finalranking'] = 'Ranking Final';
```

---

## 🎯 MÉTRICAS SUCESSO

### Técnicas

- ✅ 3 métodos votação implementados
- ✅ Anti-fraude 100% funcional
- ✅ Scoring automático < 1s
- ✅ Rate limiting configurável
- ✅ AJAX real-time

### Funcionalidade

- ✅ Votação em 3 cliques
- ✅ Resultados instantâneos
- ✅ 100% elegibilidade validada
- ✅ Zero votos duplicados
- ✅ Notificações automáticas

### Performance

- ✅ Cálculo resultados < 500ms
- ✅ Rankings update < 1s
- ✅ Votação AJAX < 300ms
- ✅ Suporta 100 votos simultâneos

---

<div align="center">

## 🗳️ SPRINT 3 - VOTAÇÃO & SCORING

**Foco**: Sistema votação democrático + Anti-fraude  
**Duração**: 2 semanas  
**Entregas**: 2.000+ linhas código  
**Status**: 🚀 INICIANDO AGORA!

</div>

---

**Squad**: Tech Lead PHP + Backend Dev + Frontend Dev  
**Cliente**: Tubaron Telecomunicações  
**Plataforma**: MooVurix LMS  
**Versão Target**: v1.3.0  
**Após Sprint 3**: 45% projeto completo

