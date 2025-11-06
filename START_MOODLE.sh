#!/bin/bash

# Script para iniciar o Moodle no Docker
# Autor: DevOps Team
# Data: 05/11/2025

set -e

# Cores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${BLUE}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════════╗
║                                                               ║
║               🚀 MOODLE - AMBIENTE DE TESTES                  ║
║                                                               ║
║               Script de Inicialização Docker                  ║
║                                                               ║
╚═══════════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

# Verificar se Docker está instalado
if ! command -v docker &> /dev/null; then
    echo -e "${RED}❌ Docker não está instalado!${NC}"
    echo "Por favor, instale o Docker primeiro: https://docs.docker.com/get-docker/"
    exit 1
fi

# Verificar se Docker Compose está instalado
if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
    echo -e "${RED}❌ Docker Compose não está instalado!${NC}"
    echo "Por favor, instale o Docker Compose primeiro: https://docs.docker.com/compose/install/"
    exit 1
fi

echo -e "${GREEN}✅ Docker e Docker Compose encontrados!${NC}\n"

# Criar .env se não existir
if [ ! -f .env ]; then
    echo -e "${YELLOW}⚠️  Arquivo .env não encontrado. Criando a partir do .env.example...${NC}"
    cp .env.example .env
    echo -e "${GREEN}✅ Arquivo .env criado!${NC}\n"
fi

# Limpar containers antigos (opcional)
read -p "$(echo -e ${YELLOW}Deseja limpar containers e volumes antigos? [s/N]:${NC} )" -n 1 -r
echo
if [[ $REPLY =~ ^[SsYy]$ ]]; then
    echo -e "${YELLOW}🧹 Limpando ambiente antigo...${NC}"
    docker-compose down -v 2>/dev/null || true
    echo -e "${GREEN}✅ Ambiente limpo!${NC}\n"
fi

# Build das imagens
echo -e "${BLUE}🔨 Construindo imagens Docker...${NC}"
docker-compose build

# Subir containers
echo -e "\n${BLUE}🚀 Iniciando containers...${NC}"
docker-compose up -d

# Aguardar containers estarem prontos
echo -e "\n${YELLOW}⏳ Aguardando containers estarem prontos...${NC}"
sleep 5

# Verificar status
echo -e "\n${BLUE}📊 Status dos containers:${NC}\n"
docker-compose ps

# Aguardar Moodle estar completamente instalado
echo -e "\n${YELLOW}⏳ Aguardando instalação do Moodle (isso pode levar alguns minutos)...${NC}"
echo -e "${YELLOW}   Acompanhe os logs em outra janela: docker-compose logs -f moodle${NC}\n"

# Monitorar logs até instalação completa
TIMEOUT=300
ELAPSED=0
while [ $ELAPSED -lt $TIMEOUT ]; do
    if docker-compose logs moodle 2>/dev/null | grep -q "Moodle está pronto"; then
        break
    fi
    
    # Mostrar progresso
    echo -ne "${YELLOW}   Aguardando... ${ELAPSED}s/${TIMEOUT}s\r${NC}"
    sleep 5
    ELAPSED=$((ELAPSED + 5))
done

echo -e "\n"

# Verificar se instalação foi concluída
if docker-compose logs moodle 2>/dev/null | grep -q "Moodle está pronto"; then
    echo -e "${GREEN}"
    cat << "EOF"
╔═══════════════════════════════════════════════════════════════╗
║                                                               ║
║                  ✅ MOODLE INSTALADO COM SUCESSO!             ║
║                                                               ║
╚═══════════════════════════════════════════════════════════════╝
EOF
    echo -e "${NC}"
    
    echo -e "${BLUE}📌 Informações de Acesso:${NC}\n"
    
    echo -e "  ${GREEN}🌐 Moodle:${NC}"
    echo -e "     URL:     ${BLUE}http://localhost:8080${NC}"
    echo -e "     Usuário: ${BLUE}admin${NC}"
    echo -e "     Senha:   ${BLUE}Admin@123${NC}\n"
    
    echo -e "  ${GREEN}🗄️  PgAdmin (Gerenciador PostgreSQL):${NC}"
    echo -e "     URL:     ${BLUE}http://localhost:5050${NC}"
    echo -e "     Email:   ${BLUE}admin@moodle.local${NC}"
    echo -e "     Senha:   ${BLUE}admin123${NC}\n"
    
    echo -e "  ${GREEN}💾 PostgreSQL:${NC}"
    echo -e "     Host:    ${BLUE}localhost:5432${NC}"
    echo -e "     DB:      ${BLUE}moodle${NC}"
    echo -e "     Usuário: ${BLUE}moodleuser${NC}"
    echo -e "     Senha:   ${BLUE}moodlepass123${NC}\n"
    
    echo -e "${BLUE}📝 Comandos Úteis:${NC}\n"
    echo -e "  ${YELLOW}Ver logs:${NC}           docker-compose logs -f"
    echo -e "  ${YELLOW}Parar containers:${NC}   docker-compose stop"
    echo -e "  ${YELLOW}Iniciar:${NC}            docker-compose start"
    echo -e "  ${YELLOW}Reiniciar:${NC}          docker-compose restart"
    echo -e "  ${YELLOW}Parar e remover:${NC}    docker-compose down"
    echo -e "  ${YELLOW}Shell no container:${NC} docker-compose exec moodle bash"
    
    echo -e "\n${GREEN}✨ Acesse agora: ${BLUE}http://localhost:8080${NC}\n"
    
    # Abrir navegador automaticamente (opcional)
    if command -v xdg-open &> /dev/null; then
        read -p "$(echo -e ${YELLOW}Deseja abrir o Moodle no navegador agora? [S/n]:${NC} )" -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Nn]$ ]]; then
            xdg-open http://localhost:8080 2>/dev/null
        fi
    fi
    
else
    echo -e "${RED}"
    cat << "EOF"
╔═══════════════════════════════════════════════════════════════╗
║                                                               ║
║              ⚠️  TEMPO LIMITE ATINGIDO                        ║
║                                                               ║
╚═══════════════════════════════════════════════════════════════╝
EOF
    echo -e "${NC}"
    
    echo -e "${YELLOW}A instalação está demorando mais que o esperado.${NC}"
    echo -e "${YELLOW}Verifique os logs para mais detalhes:${NC}\n"
    echo -e "  ${BLUE}docker-compose logs -f moodle${NC}\n"
    
    echo -e "${YELLOW}Os containers continuarão rodando em background.${NC}"
    echo -e "${YELLOW}Aguarde a mensagem 'Moodle está pronto!' nos logs.${NC}\n"
fi

echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}\n"


