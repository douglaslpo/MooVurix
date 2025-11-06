#!/bin/bash
set -e

echo "==================================="
echo "🚀 Moodle Docker Entrypoint"
echo "==================================="

# Cores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Função para log
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Aguardar banco de dados estar pronto
wait_for_db() {
    log_info "Aguardando PostgreSQL estar pronto..."
    
    MAX_RETRIES=30
    RETRY_COUNT=0
    
    until PGPASSWORD=$MOODLE_DATABASE_PASSWORD psql -h "$MOODLE_DATABASE_HOST" -U "$MOODLE_DATABASE_USER" -d "$MOODLE_DATABASE_NAME" -c '\q' 2>/dev/null; do
        RETRY_COUNT=$((RETRY_COUNT + 1))
        if [ $RETRY_COUNT -ge $MAX_RETRIES ]; then
            log_error "Timeout aguardando PostgreSQL"
            exit 1
        fi
        log_warn "PostgreSQL ainda não está pronto. Tentativa $RETRY_COUNT/$MAX_RETRIES..."
        sleep 2
    done
    
    log_info "✅ PostgreSQL está pronto!"
}

# Configurar Moodle config.php
setup_config() {
    CONFIG_FILE="/var/www/html/config.php"
    
    if [ ! -f "$CONFIG_FILE" ]; then
        log_info "Criando arquivo config.php..."
        
        cat > "$CONFIG_FILE" <<EOF
<?php
unset(\$CFG);
global \$CFG;
\$CFG = new stdClass();

//=========================================================================
// 1. DATABASE SETUP
//=========================================================================
\$CFG->dbtype    = '${MOODLE_DATABASE_TYPE:-pgsql}';
\$CFG->dblibrary = 'native';
\$CFG->dbhost    = '${MOODLE_DATABASE_HOST:-db}';
\$CFG->dbname    = '${MOODLE_DATABASE_NAME:-moodle}';
\$CFG->dbuser    = '${MOODLE_DATABASE_USER:-moodleuser}';
\$CFG->dbpass    = '${MOODLE_DATABASE_PASSWORD:-moodlepass123}';
\$CFG->prefix    = 'mdl_';
\$CFG->dboptions = [
    'dbpersist' => 0,
    'dbport' => ${MOODLE_DATABASE_PORT_NUMBER:-5432},
    'dbsocket' => '',
];

//=========================================================================
// 2. WEB SITE LOCATION
//=========================================================================
\$CFG->wwwroot   = 'http://localhost:9080';

//=========================================================================
// 3. DATA FILES LOCATION
//=========================================================================
\$CFG->dataroot  = '/var/moodledata';

//=========================================================================
// 4. ADMIN DIRECTORY LOCATION
//=========================================================================
\$CFG->admin     = 'admin';

//=========================================================================
// 5. OTHER DIRROOT
//=========================================================================
\$CFG->directorypermissions = 0777;

//=========================================================================
// 6. PERFORMANCE
//=========================================================================
\$CFG->cachedir = '/var/moodledata/cache';
\$CFG->localcachedir = '/var/moodledata/localcache';
\$CFG->tempdir = '/var/moodledata/temp';

//=========================================================================
// 7. DEBUG
//=========================================================================
// Ambiente de desenvolvimento
\$CFG->debug = (E_ALL | E_STRICT);
\$CFG->debugdisplay = 1;
@ini_set('display_errors', '1');

//=========================================================================
// 8. LANGUAGE
//=========================================================================
\$CFG->lang = '${MOODLE_LANG:-pt_br}';

//=========================================================================
// ALL DONE!
//=========================================================================
require_once(__DIR__ . '/lib/setup.php');
EOF
        
        chown www-data:www-data "$CONFIG_FILE"
        log_info "✅ config.php criado com sucesso!"
    else
        log_info "ℹ️  config.php já existe"
    fi
}

# Verificar se Moodle está instalado
check_moodle_installed() {
    if PGPASSWORD=$MOODLE_DATABASE_PASSWORD psql -h "$MOODLE_DATABASE_HOST" -U "$MOODLE_DATABASE_USER" -d "$MOODLE_DATABASE_NAME" -tAc "SELECT 1 FROM information_schema.tables WHERE table_name='mdl_config'" 2>/dev/null | grep -q 1; then
        return 0
    else
        return 1
    fi
}

# Instalar Moodle
install_moodle() {
    if check_moodle_installed; then
        log_info "ℹ️  Moodle já está instalado"
        return
    fi
    
    log_info "Instalando Moodle..."
    
    cd /var/www/html
    
    php admin/cli/install_database.php \
        --lang="${MOODLE_LANG:-pt_br}" \
        --adminuser="${MOODLE_USERNAME:-admin}" \
        --adminpass="${MOODLE_PASSWORD:-Admin@123}" \
        --adminemail="${MOODLE_EMAIL:-admin@moodle.local}" \
        --fullname="${MOODLE_SITE_NAME:-Moodle - Ambiente de Testes}" \
        --shortname="Moodle" \
        --agree-license
    
    if [ $? -eq 0 ]; then
        log_info "✅ Moodle instalado com sucesso!"
    else
        log_error "❌ Falha na instalação do Moodle"
        exit 1
    fi
}

# Ajustar permissões
fix_permissions() {
    log_info "Ajustando permissões..."
    chown -R www-data:www-data /var/www/html 2>/dev/null || true
    chown -R www-data:www-data /var/moodledata
    chmod -R 755 /var/www/html 2>/dev/null || true
    chmod -R 777 /var/moodledata
    log_info "✅ Permissões ajustadas!"
}

# Main execution
main() {
    log_info "Iniciando configuração do Moodle..."
    
    # Aguardar banco de dados
    wait_for_db
    
    # Configurar config.php
    setup_config
    
    # Ajustar permissões
    fix_permissions
    
    # Instalar Moodle se necessário
    if [ "${MOODLE_SKIP_INSTALL:-no}" != "yes" ]; then
        install_moodle
    fi
    
    log_info "==================================="
    log_info "✅ Moodle está pronto!"
    log_info "==================================="
    log_info "🌐 URL: http://localhost:8080"
    log_info "👤 Usuário: ${MOODLE_USERNAME:-admin}"
    log_info "🔑 Senha: ${MOODLE_PASSWORD:-Admin@123}"
    log_info "==================================="
    
    # Executar comando original (apache2-foreground)
    exec "$@"
}

# Executar
main "$@"


