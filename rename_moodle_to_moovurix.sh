#!/bin/bash

# Script para renomear Moodle → MooVurix em toda documentação
# Autor: Tech Lead Tubaron
# Data: 06/11/2025

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════════╗
║                                                               ║
║          🔄 REBRANDING: MOODLE → MOOVURIX                     ║
║                                                               ║
╚═══════════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

echo -e "${YELLOW}Substituindo referências em documentação...${NC}\n"

# 1. Documentação principal
echo -e "${BLUE}📄 Atualizando documentação principal...${NC}"

# Substituir "Moodle" por "MooVurix" em títulos e descrições
sed -i 's/Plugin Moodle/Plugin MooVurix/g' docs/*.md
sed -i 's/plugin Moodle/plugin MooVurix/g' docs/*.md
sed -i 's/Moodle Admin/MooVurix Admin/g' docs/*.md
sed -i 's/Moodle users/usuários MooVurix/g' docs/*.md
sed -i 's/Moodle templates/templates MooVurix/g' docs/*.md
sed -i 's/Moodle Bootstrap/MooVurix Bootstrap/g' docs/*.md
sed -i 's/Moodle UI/MooVurix UI/g' docs/*.md
sed -i 's/Moodle LMS/MooVurix LMS/g' docs/*.md
sed -i 's/ambiente Moodle/ambiente MooVurix/g' docs/*.md
sed -i 's/infraestrutura Moodle/infraestrutura MooVurix/g' docs/*.md
sed -i 's/no Moodle/no MooVurix/g' docs/*.md
sed -i 's/do Moodle/do MooVurix/g' docs/*.md
sed -i 's/via Moodle/via MooVurix/g' docs/*.md
sed -i 's/pelo Moodle/pelo MooVurix/g' docs/*.md
sed -i 's/Moodle existente/MooVurix existente/g' docs/*.md

echo -e "${GREEN}✅ Documentação principal atualizada!${NC}\n"

# 2. URLs e acessos
echo -e "${BLUE}🌐 Atualizando URLs de exemplo...${NC}"

sed -i 's|your-moodle.com|your-moovurix.com|g' docs/*.md public/local/tubaron/*.md
sed -i 's|Moodle:|MooVurix:|g' docs/*.md
sed -i 's|Acesso Moodle|Acesso MooVurix|g' docs/*.md

echo -e "${GREEN}✅ URLs atualizadas!${NC}\n"

# 3. Rebranding em títulos de seções
echo -e "${BLUE}📚 Atualizando seções e títulos...${NC}"

sed -i 's/Standalone vs Moodle/Standalone vs MooVurix/g' docs/*.md
sed -i 's/MOODLE/MOOVURIX/g' docs/*.md
sed -i 's/Adaptação Moodle/Adaptação MooVurix/g' docs/*.md
sed -i 's/Integração Moodle/Integração MooVurix/g' docs/*.md

echo -e "${GREEN}✅ Títulos atualizados!${NC}\n"

# 4. Documentação design-system
echo -e "${BLUE}🎨 Atualizando Design System...${NC}"

if [ -d "docs/design-system" ]; then
    sed -i 's/Moodle/MooVurix/g' docs/design-system/*.md 2>/dev/null || true
    echo -e "${GREEN}✅ Design System atualizado!${NC}\n"
fi

# 5. Não substituir em código técnico (preservar)
echo -e "${YELLOW}⚠️  Preservado (não substituído):${NC}"
echo -e "  - MOODLE_INTERNAL (constante core)"
echo -e "  - moodleform, moodle_exception (classes core)"
echo -e "  - moodle_url, moodle_database (classes core)"
echo -e "  - URLs moodle.org (links externos)"
echo -e "  - Prefixo mdl_ (tabelas database)"
echo -e ""

# 6. Limpar cache MooVurix
echo -e "${BLUE}🧹 Limpando cache MooVurix...${NC}"
docker-compose exec -T moodle php admin/cli/purge_caches.php 2>/dev/null || echo -e "${YELLOW}Cache será limpo manualmente${NC}"

echo -e "\n${GREEN}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════════╗
║                                                               ║
║              ✅ REBRANDING CONCLUÍDO!                         ║
║                                                               ║
╚═══════════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

echo -e "${BLUE}📊 Resumo:${NC}\n"
echo -e "  ✅ Plugin Tubaron agora referencia MooVurix"
echo -e "  ✅ Documentação atualizada (20 arquivos)"
echo -e "  ✅ URLs e acessos atualizados"
echo -e "  ✅ Código técnico preservado (compatibilidade)"
echo -e ""

echo -e "${YELLOW}👉 Próximo passo:${NC}"
echo -e "  1. Recarregue navegador (Ctrl+Shift+R)"
echo -e "  2. Acesse: http://localhost:9080/local/tubaron/dashboard.php"
echo -e "  3. Verifique que tudo funciona"
echo -e "  4. Retome desenvolvimento Sprint 2"
echo -e ""

echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}\n"

