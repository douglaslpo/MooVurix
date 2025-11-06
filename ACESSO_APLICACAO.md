# 🚀 MOODLE - APLICAÇÃO RODANDO COM SUCESSO!

**Data**: 05 de Novembro de 2025  
**Status**: ✅ **APLICAÇÃO OPERACIONAL**

---

## 📊 STATUS DOS SERVIÇOS

| Serviço | Status | Container |
|---------|--------|-----------|
| **Moodle LMS** | ✅ Healthy | `moodle_app` |
| **PostgreSQL** | ✅ Healthy | `moodle_db` |
| **pgAdmin** | 🔄 Restarting | `moodle_pgadmin` |

---

## 🌐 INFORMAÇÕES DE ACESSO

### 1. **Moodle LMS**
```
🌐 URL:     http://localhost:9080
👤 Usuário: admin
🔑 Senha:   Admin@123
📧 Email:   admin@moodle.local
```

**Nota**: Ao acessar pela primeira vez, o Moodle irá redirecionar para concluir a instalação via interface web.

### 2. **PostgreSQL (Acesso Direto)**
```
🗄️  Host:     localhost
🔌 Porta:    15432
📦 Database: moodle
👤 Usuário:  moodleuser
🔑 Senha:    moodlepass123
```

**Conexão via linha de comando:**
```bash
psql -h localhost -p 15432 -U moodleuser -d moodle
```

**Conexão via Docker:**
```bash
docker compose exec db psql -U moodleuser -d moodle
```

### 3. **pgAdmin (Gerenciador PostgreSQL)**
```
🌐 URL:   http://localhost:5050
📧 Email: admin@moodle.local
🔑 Senha: admin123
```

**Nota**: O pgAdmin está com problemas de inicialização. Para acessar o banco, use psql ou DBeaver.

---

## 🛠️ COMANDOS ÚTEIS

### Gerenciar Aplicação

```bash
# Ver status
docker compose ps

# Parar aplicação (manter dados)
docker compose stop

# Iniciar aplicação
docker compose start

# Reiniciar aplicação
docker compose restart

# Parar e remover containers (manter volumes)
docker compose down

# Ver logs em tempo real
docker compose logs -f

# Ver logs do Moodle
docker compose logs -f moodle

# Ver logs do PostgreSQL
docker compose logs -f db
```

### Acesso aos Containers

```bash
# Shell no container Moodle
docker compose exec moodle bash

# Shell no container PostgreSQL
docker compose exec db bash

# Executar comando PHP no Moodle
docker compose exec moodle php -v
```

### Backup e Manutenção

```bash
# Backup do banco de dados
docker compose exec db pg_dump -U moodleuser moodle > backup_moodle_$(date +%Y%m%d).sql

# Restaurar backup
docker compose exec -T db psql -U moodleuser moodle < backup_moodle_20251105.sql

# Limpar cache do Moodle
docker compose exec moodle php admin/cli/purge_caches.php

# Executar cron do Moodle
docker compose exec moodle php admin/cli/cron.php
```

---

## 📁 ESTRUTURA DE ARQUIVOS

```
moodle/
├── docker-compose.yml      # Configuração dos serviços
├── Dockerfile              # Imagem customizada Moodle
├── docker-entrypoint.sh    # Script de inicialização
├── START_MOODLE.sh         # Script para iniciar (opcional)
├── STOP_MOODLE.sh          # Script para parar (opcional)
├── config.php              # Configuração do Moodle (auto-gerado)
│
├── public/                 # Código fonte do Moodle
│
└── Volumes Docker (gerenciados automaticamente):
    ├── moodle_db_data/     # Dados do PostgreSQL
    ├── moodle_data/        # Arquivos do Moodle (moodledata)
    └── pgadmin_data/       # Configurações do pgAdmin
```

---

## ⚙️ ESPECIFICAÇÕES TÉCNICAS

| Componente | Versão/Configuração |
|------------|---------------------|
| **PHP** | 8.2-apache |
| **PostgreSQL** | 15-alpine |
| **Moodle** | 5.1+ |
| **Servidor Web** | Apache 2.4 |
| **Sistema** | Debian (Docker) |

### Extensões PHP Instaladas

- ✅ GD (com FreeType e JPEG)
- ✅ Intl (Internacionalização)
- ✅ PDO + PDO_PostgreSQL
- ✅ PostgreSQL
- ✅ Zip
- ✅ SOAP
- ✅ XSL
- ✅ LDAP
- ✅ EXIF
- ✅ FileInfo
- ✅ OPcache
- ✅ Redis (via PECL)

### Configurações PHP

```ini
memory_limit = 512M
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
max_input_vars = 5000
opcache.enable = 1
opcache.memory_consumption = 256
date.timezone = America/Sao_Paulo
```

---

## 🔧 CONFIGURAÇÃO DO MOODLE

### Arquivo config.php (Gerado automaticamente)

```php
$CFG->dbtype    = 'pgsql';
$CFG->dbhost    = 'db';
$CFG->dbname    = 'moodle';
$CFG->dbuser    = 'moodleuser';
$CFG->dbpass    = 'moodlepass123';
$CFG->prefix    = 'mdl_';

$CFG->wwwroot   = 'http://localhost:9080';
$CFG->dataroot  = '/var/moodledata';

$CFG->admin     = 'admin';
$CFG->lang      = 'pt_br';

// Debug habilitado (desenvolvimento)
$CFG->debug = (E_ALL | E_STRICT);
$CFG->debugdisplay = 1;
```

---

## 🚨 TROUBLESHOOTING

### Container reiniciando constantemente

```bash
# Verificar logs
docker compose logs moodle

# Verificar se PostgreSQL está pronto
docker compose logs db
```

### Erro de permissões

```bash
# Ajustar permissões (dentro do container)
docker compose exec moodle chown -R www-data:www-data /var/www/html
docker compose exec moodle chmod -R 777 /var/moodledata
```

### Porta já em uso

```bash
# Verificar processos nas portas
netstat -tulpn | grep -E '(9080|15432|5050)'

# Alterar portas no docker-compose.yml se necessário
```

### Moodle não carrega

```bash
# Verificar se Apache está rodando
docker compose exec moodle ps aux | grep apache

# Testar conexão
curl -I http://localhost:9080

# Reiniciar container
docker compose restart moodle
```

---

## ⚠️ OBSERVAÇÕES IMPORTANTES

### Ambiente de Desenvolvimento

Este ambiente é configurado para **DESENVOLVIMENTO E TESTES**. 

**NÃO use em produção sem:**
1. ✅ Mudar todas as senhas padrão
2. ✅ Desabilitar debug (`$CFG->debug = 0`)
3. ✅ Configurar SSL/HTTPS
4. ✅ Implementar backups regulares
5. ✅ Configurar firewall
6. ✅ Revisar permissões de arquivos
7. ✅ Atualizar regularmente

### Alterações de Portas

As portas foram alteradas para evitar conflitos:
- **PostgreSQL**: `5432` → `15432` (conflito com PostgreSQL do sistema)
- **Moodle**: `8080` → `9080` (conflito com projeto ACVEL na porta 8080)

### pgAdmin com problemas

O pgAdmin está reiniciando constantemente. Para gerenciar o banco, use:
- **psql** via linha de comando
- **DBeaver** (aplicação externa)
- **pgAdmin** instalado localmente

---

## 📚 PRÓXIMOS PASSOS

### 1. Acessar o Moodle

```bash
# Abrir navegador em:
http://localhost:9080
```

### 2. Completar Instalação

O Moodle irá redirecionar para concluir a instalação via interface web.

### 3. Configurar Idioma

O sistema já está configurado para **Português BR** (`pt_br`).

### 4. Criar Cursos e Usuários

Após instalação, você pode começar a criar:
- Categorias de cursos
- Cursos
- Usuários (alunos e professores)
- Conteúdos

---

## 📞 SUPORTE

### Documentação Oficial

- [Moodle Docs](https://docs.moodle.org/)
- [Moodle Dev](https://moodledev.io/)
- [Docker Docs](https://docs.docker.com/)
- [PostgreSQL Docs](https://www.postgresql.org/docs/)

### Logs e Debugging

```bash
# Logs do Moodle
docker compose logs -f moodle

# Logs do Apache (dentro do container)
docker compose exec moodle tail -f /var/log/apache2/error.log
docker compose exec moodle tail -f /var/log/apache2/access.log

# Logs do PostgreSQL
docker compose logs -f db
```

---

## ✅ CHECKLIST DE INSTALAÇÃO

- [x] Docker Desktop iniciado
- [x] Portas liberadas (9080, 15432)
- [x] Imagem Docker construída (PHP 8.2)
- [x] Containers iniciados
- [x] PostgreSQL conectado e healthy
- [x] Moodle respondendo em HTTP
- [x] config.php gerado automaticamente
- [ ] Instalação web concluída ← **PRÓXIMO PASSO**
- [ ] Idioma configurado (Português BR)
- [ ] Primeiro curso criado

---

**🎉 Parabéns! O ambiente Moodle está rodando com sucesso!**

**Desenvolvido em**: 05 de Novembro de 2025  
**Versão do Documento**: 1.0

---

**Nota**: Para parar a aplicação, execute `docker compose down` ou use o script `./STOP_MOODLE.sh`

