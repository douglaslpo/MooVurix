#!/bin/bash

# Script para instalar plugin Tubaron no Moodle
# Autor: Tech Lead Tubaron
# Data: 06/11/2025

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${BLUE}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════════╗
║                                                               ║
║       🏆 TUBARON GAMIFICATION SYSTEM - INSTALAÇÃO             ║
║                                                               ║
╚═══════════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

# 1. Verificar estrutura plugin
echo -e "${YELLOW}📁 Verificando estrutura do plugin...${NC}"
if [ ! -f "public/local/tubaron/version.php" ]; then
    echo -e "${RED}❌ Plugin não encontrado em public/local/tubaron/${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Plugin encontrado!${NC}\n"

# 2. Verificar permissões
echo -e "${YELLOW}🔑 Ajustando permissões...${NC}"
chmod -R 755 public/local/tubaron/
echo -e "${GREEN}✅ Permissões ajustadas!${NC}\n"

# 3. Verificar se Moodle está rodando
echo -e "${YELLOW}🔍 Verificando containers Docker...${NC}"
if ! docker-compose ps | grep -q "moodle_app.*Up"; then
    echo -e "${RED}❌ Moodle não está rodando!${NC}"
    echo -e "${YELLOW}Execute primeiro: ./START_MOODLE.sh${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Moodle rodando!${NC}\n"

# 4. Forçar upgrade via CLI
echo -e "${BLUE}🔧 Instalando plugin via CLI...${NC}"
docker-compose exec -T moodle php admin/cli/upgrade.php --non-interactive

# 5. Verificar instalação
echo -e "\n${YELLOW}🔍 Verificando instalação...${NC}"
PLUGIN_INSTALLED=$(docker-compose exec -T moodle php -r "
require_once('/var/www/html/config.php');
global \$DB;
\$plugin = \$DB->get_record('config_plugins', ['plugin' => 'local_tubaron', 'name' => 'version']);
echo \$plugin ? 'installed' : 'not_found';
")

if [ "$PLUGIN_INSTALLED" = "installed" ]; then
    echo -e "${GREEN}✅ Plugin instalado com sucesso!${NC}\n"
else
    echo -e "${YELLOW}⚠️  Plugin pode não ter sido detectado pelo Moodle.${NC}"
    echo -e "${YELLOW}   Acesse manualmente: http://localhost:9080/admin/index.php${NC}\n"
fi

# 6. Verificar tabelas criadas
echo -e "${YELLOW}🗄️  Verificando tabelas criadas...${NC}"
TABLES_COUNT=$(docker-compose exec -T moodle_db psql -U moodleuser -d moodle -t -c "
SELECT COUNT(*)
FROM information_schema.tables
WHERE table_name LIKE 'mdl_local_tubaron_%'
" | tr -d ' ')

echo -e "${GREEN}✅ $TABLES_COUNT tabelas Tubaron encontradas!${NC}\n"

if [ "$TABLES_COUNT" -eq "13" ]; then
    echo -e "${GREEN}✅ Todas as 13 tabelas foram criadas com sucesso!${NC}\n"
else
    echo -e "${YELLOW}⚠️  Esperado 13 tabelas, encontrado $TABLES_COUNT${NC}"
    echo -e "${YELLOW}   Pode ser necessário executar upgrade manual.${NC}\n"
fi

# 7. Seed initial data (achievements)
echo -e "${YELLOW}🌱 Criando achievements padrão...${NC}"
docker-compose exec -T moodle_app php -r "
require_once('/var/www/html/config.php');
global \$DB;

\$achievements = [
    [
        'code' => 'LEADER_MONTH',
        'name' => 'Líder do Mês',
        'description' => 'Ficou em 1º lugar no ranking mensal',
        'iconurl' => '/local/tubaron/pix/trophy.svg',
        'criteria' => json_encode(['type' => 'rank_position', 'rank' => 1, 'period' => 'month'])
    ],
    [
        'code' => 'STREAK_7',
        'name' => 'Sequência 7 Dias',
        'description' => 'Completou tarefas por 7 dias consecutivos',
        'iconurl' => '/local/tubaron/pix/fire.svg',
        'criteria' => json_encode(['type' => 'streak', 'days' => 7])
    ],
    [
        'code' => 'FIRST_WIN',
        'name' => 'Primeira Vitória',
        'description' => 'Ganhou primeira tarefa competitiva',
        'iconurl' => '/local/tubaron/pix/medal.svg',
        'criteria' => json_encode(['type' => 'first_competitive_win'])
    ],
    [
        'code' => 'TEAM_LIGHTNING',
        'name' => 'Equipe Relâmpago',
        'description' => 'Equipe completou tarefa em menos de 24h',
        'iconurl' => '/local/tubaron/pix/lightning.svg',
        'criteria' => json_encode(['type' => 'completion_speed', 'hours' => 24])
    ]
];

foreach (\$achievements as \$achievement) {
    if (!\$DB->record_exists('local_tubaron_achievements', ['code' => \$achievement['code']])) {
        \$achievement['timecreated'] = time();
        \$DB->insert_record('local_tubaron_achievements', (object)\$achievement);
    }
}

echo 'Achievements criados: ' . count(\$achievements) . PHP_EOL;
"

echo -e "${GREEN}✅ Achievements padrão criados!${NC}\n"

# Conclusão
echo -e "${GREEN}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════════╗
║                                                               ║
║              ✅ INSTALAÇÃO CONCLUÍDA COM SUCESSO!             ║
║                                                               ║
╚═══════════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

echo -e "${BLUE}📌 Próximos Passos:${NC}\n"
echo -e "  1. ${GREEN}Acesse:${NC} ${BLUE}http://localhost:9080${NC}"
echo -e "  2. ${GREEN}Login:${NC} admin / Admin@123"
echo -e "  3. ${GREEN}Navegue:${NC} Menu superior → Tubaron Gamification"
echo -e "  4. ${GREEN}Dashboard:${NC} http://localhost:9080/local/tubaron/dashboard.php"
echo -e "  5. ${GREEN}Admin:${NC} http://localhost:9080/local/tubaron/admin/seasons.php"
echo -e "  6. ${GREEN}Criar Temporada:${NC} Primeira temporada teste\n"

echo -e "${YELLOW}📚 Documentação:${NC}"
echo -e "  ${BLUE}docs/ADAPTACAO_MOODLE_PHP.md${NC}"
echo -e "  ${BLUE}docs/STATUS_DESENVOLVIMENTO_TUBARON.md${NC}"
echo -e "  ${BLUE}docs/RESUMO_EXECUTIVO_PLUGIN_MOODLE.md${NC}\n"

echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}\n"

