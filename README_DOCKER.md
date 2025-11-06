# 🚀 Moodle - Ambiente Docker Local

## 📋 Índice
- [Visão Geral](#visão-geral)
- [Pré-requisitos](#pré-requisitos)
- [Início Rápido](#início-rápido)
- [Serviços Disponíveis](#serviços-disponíveis)
- [Comandos Úteis](#comandos-úteis)
- [Troubleshooting](#troubleshooting)
- [Estrutura do Projeto](#estrutura-do-projeto)

---

## 🎯 Visão Geral

Este ambiente Docker fornece uma instalação completa do Moodle para desenvolvimento e testes, incluindo:

- **Moodle LMS** (PHP 8.1 + Apache)
- **PostgreSQL 15** (Banco de dados)
- **pgAdmin 4** (Interface web para gerenciar PostgreSQL)

### Características

✅ Instalação automatizada  
✅ Configuração otimizada para desenvolvimento  
✅ Debug habilitado  
✅ Dados persistentes em volumes Docker  
✅ Scripts de gerenciamento facilitados  
✅ Healthchecks configurados  

---

## 📦 Pré-requisitos

Antes de começar, certifique-se de ter instalado:

- [Docker](https://docs.docker.com/get-docker/) (versão 20.10+)
- [Docker Compose](https://docs.docker.com/compose/install/) (versão 2.0+)

### Verificar instalação

```bash
docker --version
docker-compose --version
```

### Requisitos de Sistema

- **RAM**: Mínimo 4GB (recomendado 8GB)
- **Disco**: Mínimo 10GB livres
- **Portas disponíveis**: 8080, 5432, 5050

---

## 🚀 Início Rápido

### Opção 1: Script Automatizado (Recomendado)

```bash
# Dar permissão de execução
chmod +x START_MOODLE.sh STOP_MOODLE.sh

# Iniciar Moodle
./START_MOODLE.sh
```

O script irá:
1. ✅ Verificar dependências (Docker, Docker Compose)
2. ✅ Criar arquivo `.env` se não existir
3. ✅ Construir as imagens Docker
4. ✅ Iniciar os containers
5. ✅ Aguardar instalação completa do Moodle
6. ✅ Exibir informações de acesso

### Opção 2: Manual

```bash
# 1. Criar arquivo .env
cp .env.example .env

# 2. Construir e iniciar containers
docker-compose up -d --build

# 3. Acompanhar logs
docker-compose logs -f moodle
```

---

## 🌐 Serviços Disponíveis

### 1. Moodle

- **URL**: http://localhost:8080
- **Usuário**: `admin`
- **Senha**: `Admin@123`

### 2. pgAdmin (Gerenciador PostgreSQL)

- **URL**: http://localhost:5050
- **Email**: `admin@moodle.local`
- **Senha**: `admin123`

#### Conectar ao PostgreSQL no pgAdmin:

1. Acesse http://localhost:5050
2. Faça login com as credenciais acima
3. Clique em "Add New Server"
4. Aba "General":
   - Name: `Moodle DB`
5. Aba "Connection":
   - Host: `db`
   - Port: `5432`
   - Database: `moodle`
   - Username: `moodleuser`
   - Password: `moodlepass123`

### 3. PostgreSQL (Acesso Direto)

```bash
# Via psql local (se instalado)
psql -h localhost -p 5432 -U moodleuser -d moodle

# Via Docker
docker-compose exec db psql -U moodleuser -d moodle
```

---

## 🛠️ Comandos Úteis

### Gerenciamento de Containers

```bash
# Ver logs em tempo real
docker-compose logs -f

# Logs de um serviço específico
docker-compose logs -f moodle
docker-compose logs -f db

# Status dos containers
docker-compose ps

# Parar containers (manter dados)
docker-compose stop

# Iniciar containers parados
docker-compose start

# Reiniciar containers
docker-compose restart

# Parar e remover containers (manter volumes)
docker-compose down

# Parar e remover TUDO (incluindo dados)
docker-compose down -v
```

### Acesso ao Container

```bash
# Shell no container do Moodle
docker-compose exec moodle bash

# Shell no container do PostgreSQL
docker-compose exec db bash

# Executar comando PHP no Moodle
docker-compose exec moodle php admin/cli/cron.php
```

### Backup e Restore

```bash
# Backup do banco de dados
docker-compose exec db pg_dump -U moodleuser moodle > backup_$(date +%Y%m%d).sql

# Restore do banco de dados
docker-compose exec -T db psql -U moodleuser moodle < backup_20251105.sql

# Backup dos arquivos do Moodle
docker run --rm -v moodle_moodle_data:/data -v $(pwd):/backup alpine tar czf /backup/moodledata_backup.tar.gz -C /data .
```

### Manutenção

```bash
# Limpar cache do Moodle
docker-compose exec moodle php admin/cli/purge_caches.php

# Executar cron manualmente
docker-compose exec moodle php admin/cli/cron.php

# Verificar versão do Moodle
docker-compose exec moodle php admin/cli/cfg.php --name=version

# Upgrade do banco de dados (se necessário)
docker-compose exec moodle php admin/cli/upgrade.php --non-interactive
```

---

## 🐛 Troubleshooting

### Container não inicia

```bash
# Verificar logs de erro
docker-compose logs

# Verificar espaço em disco
df -h

# Verificar portas em uso
netstat -tulpn | grep -E '8080|5432|5050'
```

### Moodle não carrega

```bash
# Verificar se container está healthy
docker-compose ps

# Reiniciar container
docker-compose restart moodle

# Verificar logs do Apache
docker-compose exec moodle tail -f /var/log/apache2/error.log
```

### Erro de conexão com banco de dados

```bash
# Verificar se PostgreSQL está rodando
docker-compose ps db

# Testar conexão
docker-compose exec moodle php -r "new PDO('pgsql:host=db;dbname=moodle', 'moodleuser', 'moodlepass123');"

# Reiniciar banco de dados
docker-compose restart db
```

### Limpar ambiente e recomeçar

```bash
# ATENÇÃO: Isso remove todos os dados!
docker-compose down -v
docker system prune -a

# Iniciar novamente
./START_MOODLE.sh
```

### Erro de permissões

```bash
# Ajustar permissões no container
docker-compose exec moodle chown -R www-data:www-data /var/www/html
docker-compose exec moodle chmod -R 755 /var/www/html
docker-compose exec moodle chmod -R 777 /var/moodledata
```

---

## 📁 Estrutura do Projeto

```
moodle/
├── docker-compose.yml          # Orquestração dos containers
├── Dockerfile                  # Imagem customizada do Moodle
├── docker-entrypoint.sh        # Script de inicialização
├── .dockerignore              # Arquivos ignorados no build
├── .env.example               # Variáveis de ambiente (exemplo)
├── .env                       # Variáveis de ambiente (criado automaticamente)
├── START_MOODLE.sh            # Script para iniciar
├── STOP_MOODLE.sh             # Script para parar
├── README_DOCKER.md           # Esta documentação
│
├── public/                    # Código fonte do Moodle
│   ├── admin/
│   ├── lib/
│   └── ...
│
├── config.php                 # Configuração do Moodle (auto-gerado)
│
└── Volumes Docker (gerenciados pelo Docker):
    ├── moodle_db_data/        # Dados do PostgreSQL
    ├── moodle_data/           # Arquivos do Moodle (moodledata)
    └── pgadmin_data/          # Configurações do pgAdmin
```

---

## 🔒 Segurança

### ⚠️ **IMPORTANTE**: Este ambiente é para DESENVOLVIMENTO/TESTES apenas!

**NÃO use em produção sem:**

1. ✅ Mudar todas as senhas padrão
2. ✅ Desabilitar debug (`$CFG->debug = 0`)
3. ✅ Configurar SSL/HTTPS
4. ✅ Configurar firewall
5. ✅ Implementar backups regulares
6. ✅ Revisar permissões de arquivos
7. ✅ Atualizar regularmente

---

## 📝 Variáveis de Ambiente

Edite o arquivo `.env` para customizar:

```env
# Database
POSTGRES_DB=moodle
POSTGRES_USER=moodleuser
POSTGRES_PASSWORD=moodlepass123

# Moodle Admin
MOODLE_USERNAME=admin
MOODLE_PASSWORD=Admin@123
MOODLE_EMAIL=admin@moodle.local
MOODLE_SITE_NAME=Moodle - Ambiente de Testes
MOODLE_LANG=pt_br
```

---

## 🆘 Suporte

### Documentação Oficial

- [Moodle Docs](https://docs.moodle.org/)
- [Moodle Dev](https://moodledev.io/)
- [Docker Docs](https://docs.docker.com/)

### Logs e Debugging

```bash
# Logs do Moodle
docker-compose logs -f moodle

# Logs do PostgreSQL
docker-compose logs -f db

# Logs do Apache dentro do container
docker-compose exec moodle tail -f /var/log/apache2/error.log
docker-compose exec moodle tail -f /var/log/apache2/access.log
```

---

## 📄 License

Este setup Docker é fornecido como está para facilitar o desenvolvimento com Moodle.

O Moodle é software livre sob a licença GNU GPL v3.

---

**Desenvolvido com ❤️ para a comunidade Moodle**

**Data**: 05 de novembro de 2025  
**Versão**: 1.0.0


