#!/bin/bash

# Script para executar testes Robot Framework do Moodle
# Uso: ./run_tests.sh [opções]

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Diretórios
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TESTS_DIR="${SCRIPT_DIR}/tests"
RESULTS_DIR="${SCRIPT_DIR}/results"

# Verificar se Python está instalado
if ! command -v python3 &> /dev/null; then
    echo -e "${RED}Erro: Python3 não encontrado. Por favor, instale Python 3.8 ou superior.${NC}"
    exit 1
fi

# Verificar se pip está instalado
if ! command -v pip3 &> /dev/null; then
    echo -e "${RED}Erro: pip3 não encontrado. Por favor, instale pip.${NC}"
    exit 1
fi

# Verificar se as dependências estão instaladas
echo -e "${YELLOW}Verificando dependências...${NC}"
if ! python3 -c "import robot" 2>/dev/null; then
    echo -e "${YELLOW}Instalando dependências do Robot Framework...${NC}"
    pip3 install -r "${SCRIPT_DIR}/requirements.txt"
fi

# Criar diretório de resultados se não existir
mkdir -p "${RESULTS_DIR}"

# Opções padrão
ROBOT_OPTS="--outputdir ${RESULTS_DIR} --loglevel INFO"

# Processar argumentos
TAGS=""
INCLUDE=""
EXCLUDE=""
TEST_FILE=""

while [[ $# -gt 0 ]]; do
    case $1 in
        --tags)
            TAGS="$2"
            shift 2
            ;;
        --include)
            INCLUDE="$2"
            shift 2
            ;;
        --exclude)
            EXCLUDE="$2"
            shift 2
            ;;
        --debug)
            ROBOT_OPTS="${ROBOT_OPTS} --loglevel DEBUG"
            shift
            ;;
        --file)
            TEST_FILE="$2"
            shift 2
            ;;
        --help|-h)
            echo "Uso: $0 [opções]"
            echo ""
            echo "Opções:"
            echo "  --tags TAG1,TAG2     Executar apenas testes com essas tags"
            echo "  --include TAG        Executar testes que incluem esta tag"
            echo "  --exclude TAG        Excluir testes com esta tag"
            echo "  --debug              Executar com log nível DEBUG"
            echo "  --file ARQUIVO       Executar arquivo específico"
            echo "  --help               Mostrar esta ajuda"
            exit 0
            ;;
        *)
            echo -e "${RED}Opção desconhecida: $1${NC}"
            echo "Use --help para ver opções disponíveis"
            exit 1
            ;;
    esac
done

# Construir comando robot
ROBOT_CMD="robot ${ROBOT_OPTS}"

if [ -n "$TAGS" ]; then
    ROBOT_CMD="${ROBOT_CMD} --test \"*\" --include \"${TAGS}\""
fi

if [ -n "$INCLUDE" ]; then
    ROBOT_CMD="${ROBOT_CMD} --include \"${INCLUDE}\""
fi

if [ -n "$EXCLUDE" ]; then
    ROBOT_CMD="${ROBOT_CMD} --exclude \"${EXCLUDE}\""
fi

# Determinar arquivo/diretório de teste
if [ -n "$TEST_FILE" ]; then
    TEST_PATH="${TESTS_DIR}/${TEST_FILE}"
    if [ ! -f "$TEST_PATH" ]; then
        echo -e "${RED}Erro: Arquivo não encontrado: ${TEST_PATH}${NC}"
        exit 1
    fi
else
    TEST_PATH="${TESTS_DIR}"
fi

ROBOT_CMD="${ROBOT_CMD} ${TEST_PATH}"

# Executar testes
echo -e "${GREEN}Executando testes Robot Framework...${NC}"
echo -e "${YELLOW}Comando: ${ROBOT_CMD}${NC}"
echo ""

eval $ROBOT_CMD

# Verificar resultado
EXIT_CODE=$?

if [ $EXIT_CODE -eq 0 ]; then
    echo ""
    echo -e "${GREEN}✓ Testes executados com sucesso!${NC}"
    echo -e "${GREEN}Relatórios disponíveis em: ${RESULTS_DIR}${NC}"
    if [ -f "${RESULTS_DIR}/report.html" ]; then
        echo -e "${YELLOW}Abra o relatório em: file://${RESULTS_DIR}/report.html${NC}"
    fi
else
    echo ""
    echo -e "${RED}✗ Alguns testes falharam. Verifique os relatórios em: ${RESULTS_DIR}${NC}"
fi

exit $EXIT_CODE

