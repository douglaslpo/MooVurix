#!/bin/bash

# Script para parar o Moodle no Docker
# Autor: DevOps Team
# Data: 05/11/2025

set -e

# Cores
RED='\033[0;31m'
YELLOW='\033[1;33m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${RED}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════════╗
║                                                               ║
║               🛑 MOODLE - PARAR CONTAINERS                    ║
║                                                               ║
╚═══════════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}\n"

# Verificar se há containers rodando
if ! docker-compose ps | grep -q "Up"; then
    echo -e "${YELLOW}⚠️  Nenhum container do Moodle está rodando.${NC}\n"
    exit 0
fi

# Perguntar se deseja remover volumes
echo -e "${YELLOW}Como deseja parar o Moodle?${NC}\n"
echo -e "  ${BLUE}1)${NC} Apenas parar containers (manter dados)"
echo -e "  ${BLUE}2)${NC} Parar e remover containers (manter volumes de dados)"
echo -e "  ${BLUE}3)${NC} Parar e remover TUDO (incluindo banco de dados) ${RED}⚠️  CUIDADO!${NC}\n"

read -p "$(echo -e ${YELLOW}Escolha uma opção [1-3]:${NC} )" -n 1 -r
echo -e "\n"

case $REPLY in
    1)
        echo -e "${BLUE}🛑 Parando containers...${NC}"
        docker-compose stop
        echo -e "${GREEN}✅ Containers parados!${NC}"
        echo -e "${YELLOW}Para iniciar novamente: ./START_MOODLE.sh ou docker-compose start${NC}\n"
        ;;
    2)
        echo -e "${BLUE}🛑 Parando e removendo containers...${NC}"
        docker-compose down
        echo -e "${GREEN}✅ Containers removidos!${NC}"
        echo -e "${YELLOW}ℹ️  Os dados foram preservados nos volumes Docker.${NC}"
        echo -e "${YELLOW}Para iniciar novamente: ./START_MOODLE.sh${NC}\n"
        ;;
    3)
        echo -e "${RED}⚠️  ATENÇÃO: Isso irá remover TODOS os dados!${NC}"
        read -p "$(echo -e ${RED}Tem certeza? Digite 'CONFIRMAR' para continuar:${NC} )" -r
        echo
        if [ "$REPLY" = "CONFIRMAR" ]; then
            echo -e "${RED}🗑️  Removendo containers e volumes...${NC}"
            docker-compose down -v
            echo -e "${GREEN}✅ Tudo removido!${NC}"
            echo -e "${YELLOW}Para recriar o ambiente: ./START_MOODLE.sh${NC}\n"
        else
            echo -e "${YELLOW}❌ Operação cancelada.${NC}\n"
        fi
        ;;
    *)
        echo -e "${RED}❌ Opção inválida!${NC}\n"
        exit 1
        ;;
esac


