# 🚀 SPRINT 6 - TESTES, OTIMIZAÇÕES & DEPLOY PREPARATION

**Período**: Semanas 11-12  
**Início**: 06 de Novembro de 2025  
**Foco**: QA Completo + Performance + Security Hardening + Deploy Prep  
**Status**: 🚀 **INICIANDO AGORA**  

---

## 🎯 OBJETIVOS SPRINT 6

### Principais Entregas

1. **Testes Automatizados Completos**
   - ✅ PHPUnit tests (90%+ coverage)
   - ✅ Behat acceptance tests
   - ✅ JavaScript unit tests
   - ✅ Integration tests
   - ✅ Performance tests

2. **Otimizações de Performance**
   - ✅ Database query optimization
   - ✅ Caching strategy (Redis/Memcached)
   - ✅ JavaScript minification/bundling
   - ✅ Image optimization
   - ✅ Lazy loading implementation

3. **Security Hardening**
   - ✅ SQL injection prevention audit
   - ✅ XSS protection review
   - ✅ CSRF token implementation
   - ✅ Input validation strengthening
   - ✅ Security headers configuration
   - ✅ Rate limiting API endpoints

4. **Mobile Responsiveness Final**
   - ✅ Touch gestures optimization
   - ✅ Mobile navigation refinement
   - ✅ PWA capabilities
   - ✅ Offline mode (basic)
   - ✅ Mobile performance < 3s load

5. **Deploy Preparation**
   - ✅ Production Docker configuration
   - ✅ CI/CD pipeline (GitHub Actions)
   - ✅ Database migrations scripts
   - ✅ Rollback procedures
   - ✅ Monitoring setup (APM)
   - ✅ Backup strategy

6. **Documentação Final**
   - ✅ Installation guide
   - ✅ Admin manual
   - ✅ User manual
   - ✅ API documentation
   - ✅ Troubleshooting guide
   - ✅ Video tutorials

---

## 🧪 TESTES AUTOMATIZADOS

### 1. PHPUnit Tests

**Arquivo**: `tests/phpunit/` (múltiplos arquivos)

```php
// Classes a testar:
- achievements_manager_test.php (20+ tests)
- voting_manager_test.php (25+ tests)
- team_manager_test.php (15+ tests)
- task_manager_test.php (30+ tests)
- season_manager_test.php (12+ tests)
- leaderboard_manager_test.php (18+ tests)

// Coverage target: 90%+
// Assertions: 150+
// Edge cases testados
```

### 2. Behat Acceptance Tests

**Arquivo**: `tests/behat/` (features)

```gherkin
# Scenarios principais:
- create_task.feature (10 scenarios)
- voting_workflow.feature (15 scenarios)
- team_management.feature (12 scenarios)
- achievements_unlock.feature (8 scenarios)
- leaderboards_display.feature (6 scenarios)
- export_data.feature (5 scenarios)

# Total: 56 scenarios
# Browser: Chrome headless
```

### 3. JavaScript Unit Tests

**Arquivo**: `amd/src/tests/` (Jest/Mocha)

```javascript
// Módulos a testar:
- charts_test.js (8 tests)
- filters_test.js (12 tests)
- voting_ui_test.js (10 tests)
- achievements_popup_test.js (6 tests)

// Total: 36 tests JavaScript
```

### 4. Performance Tests

**Arquivo**: `tests/performance/load_test.php`

```php
// Scenarios de carga:
- 100 usuários simultâneos votando
- 50 equipes criando tarefas
- Dashboard analytics com 1000+ registros
- Leaderboards com 500+ usuários

// Targets:
- Response time < 500ms (p95)
- Throughput > 100 req/s
- No memory leaks
- Database connections < 50
```

---

## ⚡ OTIMIZAÇÕES DE PERFORMANCE

### 1. Database Optimization

**Arquivo**: `db/upgrade.php` (add indexes)

```php
// Indexes a criar:
ALTER TABLE {local_tubaron_tasks} 
    ADD INDEX idx_season_status (seasonid, status);

ALTER TABLE {local_tubaron_votes} 
    ADD INDEX idx_task_user (taskid, userid);

ALTER TABLE {local_tubaron_achievements} 
    ADD INDEX idx_user_unlocked (userid, unlocked);

ALTER TABLE {local_tubaron_leaderboard} 
    ADD INDEX idx_season_points (seasonid, points DESC);

// Query optimization:
- Use EXISTS instead of COUNT
- Batch inserts (100 rows)
- Prepared statements
- Connection pooling
```

### 2. Caching Strategy

**Arquivo**: `classes/cache_manager.php`

```php
class cache_manager {
    // Cache layers:
    - Application cache (MUC)
    - Redis session cache
    - Static files CDN
    
    // Cache keys:
    - leaderboard_{seasonid}_{type} (TTL: 5min)
    - achievements_{userid} (TTL: 1h)
    - season_stats_{seasonid} (TTL: 15min)
    - analytics_data_{range} (TTL: 10min)
    
    // Invalidation:
    - On task complete
    - On vote cast
    - On achievement unlock
    - Manual admin purge
}
```

### 3. Frontend Optimization

**Arquivo**: `Gruntfile.js` (atualizar)

```javascript
// Tasks Grunt:
- uglify: Minify JavaScript (40% reduction)
- cssmin: Minify CSS (35% reduction)
- imagemin: Optimize images (60% reduction)
- concat: Bundle AMD modules
- critical: Inline critical CSS

// Build pipeline:
grunt build --production
```

### 4. Lazy Loading

**Arquivo**: `amd/src/lazy_loader.js`

```javascript
// Componentes lazy loaded:
- Charts (only when visible)
- Achievements showcase (on scroll)
- Leaderboards infinite scroll
- Analytics dashboard (on tab switch)

// Intersection Observer:
const observer = new IntersectionObserver(callback, {
    threshold: 0.1,
    rootMargin: '50px'
});
```

---

## 🔒 SECURITY HARDENING

### 1. SQL Injection Prevention

**Checklist**:
```
✅ All queries use $DB->get_records()
✅ No raw SQL concatenation
✅ Prepared statements everywhere
✅ Type casting user inputs
✅ sqlinjection_test.php (10 tests)
```

### 2. XSS Protection

**Checklist**:
```
✅ All outputs use s() or format_text()
✅ Mustache templates auto-escape
✅ JSON outputs use json_encode()
✅ HTMLPurifier for rich text
✅ xss_test.php (12 tests)
```

### 3. CSRF Protection

**Arquivo**: `lib.php` (require_sesskey)

```php
// All state-changing actions:
- Task creation: require_sesskey()
- Vote submission: require_sesskey()
- Team management: require_sesskey()
- Settings changes: require_sesskey()

// AJAX endpoints:
- Validate sesskey header
- Reject requests without token
```

### 4. Input Validation

**Arquivo**: `classes/validators.php`

```php
class validators {
    public static function validate_task_data($data)
    public static function validate_vote_data($data)
    public static function validate_team_data($data)
    public static function sanitize_user_input($input)
    
    // Validation rules:
    - Title: 3-200 chars, no HTML
    - Description: max 2000 chars, allowed tags
    - Points: integer 1-1000
    - Deadline: future date, max 1 year
    - Email: valid format, domain check
}
```

### 5. Security Headers

**Arquivo**: `.htaccess`

```apache
# Security headers:
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
Header set Referrer-Policy "strict-origin-when-cross-origin"
Header set Content-Security-Policy "default-src 'self'"
Header set Strict-Transport-Security "max-age=31536000"

# Disable directory listing
Options -Indexes
```

### 6. Rate Limiting

**Arquivo**: `classes/rate_limiter.php`

```php
class rate_limiter {
    // Limits:
    - Voting: 10/minute per user
    - Task creation: 5/minute per user
    - AJAX requests: 60/minute per IP
    - Login attempts: 5/5min per IP
    
    // Storage: Redis
    // Response: HTTP 429 Too Many Requests
}
```

---

## 📱 MOBILE RESPONSIVENESS FINAL

### 1. Touch Gestures

**Arquivo**: `amd/src/touch_gestures.js`

```javascript
// Implementar:
- Swipe to navigate (leaderboards)
- Pull to refresh (dashboard)
- Long press context menu
- Pinch to zoom (charts)
- Double tap quick actions
```

### 2. Mobile Navigation

**Arquivo**: `styles.css` (mobile overrides)

```css
@media (max-width: 768px) {
    /* Bottom navigation bar */
    .tubaron-mobile-nav {
        position: fixed;
        bottom: 0;
        width: 100%;
        height: 60px;
        z-index: 1000;
    }
    
    /* Larger touch targets */
    .btn-mobile {
        min-height: 44px;
        min-width: 44px;
    }
    
    /* Simplified layouts */
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}
```

### 3. PWA Capabilities

**Arquivo**: `manifest.json`

```json
{
  "name": "Tubaron - MooVurix",
  "short_name": "Tubaron",
  "start_url": "/local/tubaron/",
  "display": "standalone",
  "background_color": "#1e3a8a",
  "theme_color": "#3b82f6",
  "icons": [
    {
      "src": "pix/icon-192.png",
      "sizes": "192x192",
      "type": "image/png"
    },
    {
      "src": "pix/icon-512.png",
      "sizes": "512x512",
      "type": "image/png"
    }
  ]
}
```

**Arquivo**: `service-worker.js`

```javascript
// Cache strategy:
- Static assets: Cache First
- API calls: Network First
- Images: Cache First with expiration
- Offline fallback page
```

### 4. Mobile Performance

**Targets**:
```
- First Contentful Paint: < 1.5s
- Time to Interactive: < 3s
- Speed Index: < 2.5s
- Largest Contentful Paint: < 2.5s
- Cumulative Layout Shift: < 0.1
- First Input Delay: < 100ms

// Tools:
- Lighthouse CI
- WebPageTest mobile tests
- Chrome DevTools throttling
```

---

## 🐳 DEPLOY PREPARATION

### 1. Production Docker

**Arquivo**: `docker-compose.prod.yml`

```yaml
version: '3.8'

services:
  nginx:
    image: nginx:alpine
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf
    depends_on:
      - php-fpm
    restart: always

  php-fpm:
    build:
      context: .
      dockerfile: Dockerfile.prod
    environment:
      - PHP_OPCACHE_ENABLE=1
      - PHP_MEMORY_LIMIT=256M
    volumes:
      - ./:/var/www/html
    restart: always

  postgres:
    image: postgres:15-alpine
    environment:
      POSTGRES_DB: moodle
      POSTGRES_USER: moodle
      POSTGRES_PASSWORD_FILE: /run/secrets/db_password
    volumes:
      - postgres_data:/var/lib/postgresql/data
    secrets:
      - db_password
    restart: always

  redis:
    image: redis:7-alpine
    command: redis-server --requirepass ${REDIS_PASSWORD}
    volumes:
      - redis_data:/data
    restart: always

volumes:
  postgres_data:
  redis_data:

secrets:
  db_password:
    file: ./secrets/db_password.txt
```

### 2. CI/CD Pipeline

**Arquivo**: `.github/workflows/deploy.yml`

```yaml
name: Deploy Tubaron

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
          extensions: pgsql, redis
      
      - name: Install dependencies
        run: composer install
      
      - name: Run PHPUnit
        run: vendor/bin/phpunit
      
      - name: Run Behat
        run: vendor/bin/behat
      
      - name: Code quality
        run: vendor/bin/phpcs
  
  deploy:
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    steps:
      - name: Deploy to production
        run: |
          ssh ${{ secrets.DEPLOY_USER }}@${{ secrets.DEPLOY_HOST }} \
          "cd /var/www/moodle && \
           git pull origin main && \
           docker-compose -f docker-compose.prod.yml up -d --build"
```

### 3. Database Migrations

**Arquivo**: `db/upgrade.php` (production-ready)

```php
function xmldb_local_tubaron_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();
    
    // Add migration logging
    $migration_log = [
        'plugin' => 'local_tubaron',
        'version' => $oldversion,
        'timestamp' => time(),
        'success' => false
    ];
    
    try {
        // Migrations...
        
        // Backup before critical changes
        if ($oldversion < 2025110601) {
            backup_tables(['local_tubaron_tasks', 'local_tubaron_votes']);
            
            // Critical migration
            // ...
            
            $migration_log['success'] = true;
        }
        
        log_migration($migration_log);
        return true;
        
    } catch (Exception $e) {
        $migration_log['error'] = $e->getMessage();
        log_migration($migration_log);
        throw $e;
    }
}
```

### 4. Rollback Procedures

**Arquivo**: `scripts/rollback.sh`

```bash
#!/bin/bash

# Rollback script
VERSION=$1

if [ -z "$VERSION" ]; then
    echo "Usage: ./rollback.sh <version>"
    exit 1
fi

echo "🔄 Rolling back to version $VERSION"

# Stop containers
docker-compose -f docker-compose.prod.yml down

# Restore database
echo "📦 Restoring database backup..."
docker exec postgres pg_restore \
    -U moodle \
    -d moodle \
    /backups/moodle_pre_${VERSION}.sql

# Checkout code version
git checkout tags/v${VERSION}

# Restart containers
docker-compose -f docker-compose.prod.yml up -d

echo "✅ Rollback completed to version $VERSION"
```

### 5. Monitoring Setup

**Arquivo**: `monitoring/prometheus.yml`

```yaml
global:
  scrape_interval: 15s

scrape_configs:
  - job_name: 'moodle'
    static_configs:
      - targets: ['localhost:9090']
  
  - job_name: 'postgres'
    static_configs:
      - targets: ['localhost:9187']
  
  - job_name: 'nginx'
    static_configs:
      - targets: ['localhost:9113']

alerting:
  alertmanagers:
    - static_configs:
        - targets: ['localhost:9093']

rule_files:
  - 'alerts.yml'
```

**Arquivo**: `monitoring/alerts.yml`

```yaml
groups:
  - name: tubaron_alerts
    rules:
      - alert: HighResponseTime
        expr: http_request_duration_seconds > 1
        for: 5m
        annotations:
          summary: "High response time detected"
      
      - alert: DatabaseConnectionsHigh
        expr: pg_stat_database_numbackends > 80
        for: 5m
        annotations:
          summary: "Database connections too high"
      
      - alert: DiskSpaceLow
        expr: node_filesystem_avail_bytes / node_filesystem_size_bytes < 0.1
        for: 5m
        annotations:
          summary: "Disk space below 10%"
```

### 6. Backup Strategy

**Arquivo**: `scripts/backup.sh`

```bash
#!/bin/bash

# Daily backup script
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups"

# Database backup
echo "📦 Backing up database..."
docker exec postgres pg_dump \
    -U moodle \
    -F c \
    -f /backups/moodle_${DATE}.dump \
    moodle

# Files backup (moodledata)
echo "📁 Backing up files..."
tar -czf ${BACKUP_DIR}/moodledata_${DATE}.tar.gz \
    /var/moodledata

# Plugin files backup
echo "🔌 Backing up plugin..."
tar -czf ${BACKUP_DIR}/tubaron_${DATE}.tar.gz \
    /var/www/html/local/tubaron

# Upload to S3
echo "☁️  Uploading to S3..."
aws s3 cp ${BACKUP_DIR}/moodle_${DATE}.dump \
    s3://tubaron-backups/db/

aws s3 cp ${BACKUP_DIR}/moodledata_${DATE}.tar.gz \
    s3://tubaron-backups/files/

# Clean old backups (keep 30 days)
find ${BACKUP_DIR} -type f -mtime +30 -delete

echo "✅ Backup completed: ${DATE}"
```

---

## 📖 DOCUMENTAÇÃO FINAL

### 1. Installation Guide

**Arquivo**: `docs/INSTALLATION.md`

```markdown
# Guia de Instalação Tubaron

## Requisitos
- Moodle 4.1+
- PHP 8.1+
- PostgreSQL 13+
- Redis 6+

## Passo a Passo
1. Clone repository
2. Install dependencies
3. Run migrations
4. Configure settings
5. Test installation

## Troubleshooting
- Common errors
- Log locations
- Support contacts
```

### 2. Admin Manual

**Arquivo**: `docs/ADMIN_MANUAL.md`

```markdown
# Manual do Administrador

## Gestão de Temporadas
- Criar temporada
- Configurar pontos
- Gerenciar equipes
- Monitorar atividades

## Analytics Dashboard
- Interpretar métricas
- Exportar relatórios
- Configurar alertas

## Manutenção
- Backup procedures
- Performance tuning
- Security best practices
```

### 3. User Manual

**Arquivo**: `docs/USER_MANUAL.md`

```markdown
# Manual do Usuário

## Primeiros Passos
- Criar conta
- Completar perfil
- Entrar em equipe

## Usando Tubaron
- Criar tarefas
- Participar votações
- Ganhar achievements
- Ver rankings

## FAQ
- 20 perguntas frequentes
- Dicas e truques
```

### 4. API Documentation

**Arquivo**: `docs/API_DOCUMENTATION.md`

```markdown
# API Documentation

## Endpoints

### Tasks API
- POST /api/tasks/create
- GET /api/tasks/{id}
- PUT /api/tasks/{id}
- DELETE /api/tasks/{id}

### Voting API
- POST /api/votes/cast
- GET /api/votes/results/{taskid}

### Achievements API
- GET /api/achievements/user/{userid}
- POST /api/achievements/unlock

## Authentication
- Bearer token
- Rate limits
- Error codes
```

### 5. Video Tutorials

**Lista de vídeos**:
```
1. Introdução ao Tubaron (5min)
2. Criando sua primeira tarefa (3min)
3. Como funciona a votação (4min)
4. Trabalhando em equipe (6min)
5. Desbloqueando achievements (5min)
6. Dashboard analytics (7min)
7. Administração completa (15min)

Total: 45min de vídeos
Plataforma: YouTube/Vimeo
Legendas: PT-BR
```

---

## 🚀 CRONOGRAMA SPRINT 6

### Semana 1 (Dias 1-4)

**Dia 1-2: Testes**
- [ ] PHPUnit tests (90%+ coverage)
- [ ] Behat acceptance tests
- [ ] JavaScript unit tests
- [ ] Fix identified bugs

**Dia 3-4: Performance**
- [ ] Database query optimization
- [ ] Caching implementation
- [ ] Frontend optimization (minify/bundle)
- [ ] Lazy loading implementation
- [ ] Performance tests

### Semana 2 (Dias 5-8)

**Dia 5-6: Security & Mobile**
- [ ] Security audit completo
- [ ] Rate limiting implementation
- [ ] Security headers configuration
- [ ] Mobile touch gestures
- [ ] PWA implementation
- [ ] Mobile performance optimization

**Dia 7-8: Deploy & Docs**
- [ ] Production Docker setup
- [ ] CI/CD pipeline configuration
- [ ] Rollback procedures
- [ ] Monitoring setup
- [ ] Backup strategy
- [ ] Documentação completa
- [ ] Video tutorials

---

## 📋 ARQUIVOS A CRIAR/ATUALIZAR

### Testes (10+ arquivos)
1. `tests/phpunit/achievements_manager_test.php`
2. `tests/phpunit/voting_manager_test.php`
3. `tests/phpunit/task_manager_test.php`
4. `tests/behat/voting_workflow.feature`
5. `tests/behat/achievements_unlock.feature`
6. `amd/src/tests/charts_test.js`

### Performance (5 arquivos)
7. `classes/cache_manager.php`
8. `classes/performance_monitor.php`
9. `amd/src/lazy_loader.js`
10. `db/upgrade.php` (add indexes)

### Security (6 arquivos)
11. `classes/validators.php`
12. `classes/rate_limiter.php`
13. `.htaccess` (security headers)
14. `tests/security/sqlinjection_test.php`
15. `tests/security/xss_test.php`

### Deploy (8 arquivos)
16. `docker-compose.prod.yml`
17. `.github/workflows/deploy.yml`
18. `scripts/backup.sh`
19. `scripts/rollback.sh`
20. `monitoring/prometheus.yml`
21. `monitoring/alerts.yml`
22. `manifest.json`
23. `service-worker.js`

### Documentação (6 arquivos)
24. `docs/INSTALLATION.md`
25. `docs/ADMIN_MANUAL.md`
26. `docs/USER_MANUAL.md`
27. `docs/API_DOCUMENTATION.md`
28. `docs/TROUBLESHOOTING.md`
29. `docs/VIDEO_TUTORIALS.md`

---

## 🎯 MÉTRICAS SUCESSO SPRINT 6

### Qualidade

- ✅ Test coverage ≥ 90%
- ✅ 0 critical bugs
- ✅ Code quality score A
- ✅ Security score 95+/100
- ✅ Accessibility WCAG 2.1 AA

### Performance

- ✅ Page load < 2s
- ✅ Time to Interactive < 3s
- ✅ Database queries < 50/page
- ✅ Memory usage < 128MB
- ✅ Mobile performance 90+/100

### Deploy

- ✅ CI/CD pipeline 100% functional
- ✅ Rollback tested successfully
- ✅ Monitoring capturing metrics
- ✅ Backups running daily
- ✅ Zero-downtime deployment

### Documentação

- ✅ All docs completed
- ✅ API 100% documented
- ✅ Video tutorials published
- ✅ FAQ with 20+ questions
- ✅ Support knowledge base

---

## 🔧 FERRAMENTAS & TECNOLOGIAS

### Testing
- PHPUnit 9.6+
- Behat 3.13+
- Jest/Mocha (JavaScript)
- Selenium WebDriver
- Lighthouse CI

### Performance
- Redis 7+
- Memcached (opcional)
- Grunt/Webpack
- ImageOptim
- Google PageSpeed Insights

### Security
- OWASP ZAP
- Snyk
- SonarQube
- ModSecurity WAF
- Let's Encrypt SSL

### Deploy
- Docker 24+
- GitHub Actions
- Prometheus
- Grafana
- AWS S3 (backups)

### Documentação
- Markdown
- Mermaid (diagramas)
- Postman (API)
- OBS Studio (vídeos)
- Draw.io (fluxogramas)

---

## 📊 CHECKLIST PRÉ-DEPLOY

### Code Quality
- [ ] All tests passing (150+ tests)
- [ ] Code coverage ≥ 90%
- [ ] No linter errors
- [ ] No console errors/warnings
- [ ] No TODO comments in production code

### Performance
- [ ] Lighthouse score ≥ 90
- [ ] Database queries optimized
- [ ] Caching implemented
- [ ] Static assets minified
- [ ] Images optimized
- [ ] Lazy loading working

### Security
- [ ] SQL injection tests passed
- [ ] XSS protection verified
- [ ] CSRF tokens implemented
- [ ] Rate limiting active
- [ ] Security headers configured
- [ ] SSL/TLS enabled
- [ ] Secrets not in code

### Functionality
- [ ] All features working
- [ ] Voting system tested
- [ ] Achievements unlocking
- [ ] Leaderboards accurate
- [ ] Notifications sending
- [ ] Export functioning
- [ ] LGPD compliance

### Deployment
- [ ] Docker images built
- [ ] CI/CD pipeline tested
- [ ] Rollback procedure tested
- [ ] Monitoring configured
- [ ] Backups scheduled
- [ ] DNS configured
- [ ] SSL certificate installed

### Documentation
- [ ] Installation guide complete
- [ ] Admin manual done
- [ ] User manual done
- [ ] API documented
- [ ] Video tutorials published
- [ ] FAQ populated
- [ ] Changelog updated

---

## 🎉 ENTREGÁVEIS SPRINT 6

### Código
- 1.200+ linhas de testes
- 800+ linhas otimizações
- 500+ linhas segurança
- 400+ linhas deploy config

### Documentação
- 6 manuais completos
- 7 vídeos tutoriais
- 20+ FAQs
- API reference completa

### Infraestrutura
- Production Docker setup
- CI/CD pipeline funcional
- Monitoring dashboard
- Backup automático

---

<div align="center">

## 🚀 SPRINT 6 - PRODUCTION READY

**Foco**: Testes + Performance + Security + Deploy  
**Duração**: 2 semanas  
**Entregas**: 3.000+ linhas código + Docs completas  
**Status**: 🚀 INICIANDO AGORA!

**Após Sprint 6**: 95% projeto completo  
**Próxima**: Sprint 7 (Launch & Support)

</div>

---

**Squad**: Tech Lead + QA Engineer + DevOps + Security Specialist + Technical Writer  
**Cliente**: Tubaron Telecomunicações  
**Plataforma**: MooVurix LMS  
**Versão Target**: v1.6.0 (Release Candidate)  
**Go-Live**: Previsto para final Sprint 7

