# Robot Framework - Testes Automatizados Moodle

Este diretório contém os testes automatizados usando Robot Framework para o projeto Moodle.

## 📋 Pré-requisitos

- Python 3.8 ou superior
- pip (gerenciador de pacotes Python)
- Chrome/Chromium instalado (para testes Selenium)
- Moodle configurado e rodando localmente ou em ambiente de teste

## 🚀 Instalação

1. **Instale as dependências Python:**

```bash
pip install -r tests/robot/requirements.txt
```

2. **Configure as variáveis de ambiente:**

Edite o arquivo `resources/variables.robot` com as configurações:

```bash
MOODLE_URL=http://localhost
BROWSER=chrome
ADMIN_USER=admin
ADMIN_PASS=admin123
```

## 📁 Estrutura de Diretórios

```
tests/robot/
├── tests/              # Casos de teste
│   ├── login_tests.robot
│   └── navegacao_tests.robot
├── keywords/           # Keywords reutilizáveis
│   ├── common_keywords.robot
│   └── moodle_keywords.robot
├── resources/          # Recursos compartilhados
├── results/            # Resultados dos testes (gerado automaticamente)
├── robot.yml          # Configuração principal
├── requirements.txt   # Dependências Python
└── README.md          # Este arquivo
```

## 🧪 Executando Testes

### Executar todos os testes:

```bash
robot tests/robot/tests/
```

### Executar testes específicos por tag:

```bash
robot --include smoke tests/robot/tests/
robot --include login tests/robot/tests/
```

### Executar um arquivo específico:

```bash
robot tests/robot/tests/login_tests.robot
```

### Executar com opções personalizadas:

```bash
robot --loglevel DEBUG --outputdir tests/robot/results tests/robot/tests/
```

### Executar em modo headless (sem abrir navegador):

O modo headless já está configurado por padrão. Para executar com navegador visível, edite `resources/variables.robot`:

```robot
${BROWSER_OPTIONS}    # Remova ou deixe vazio
```

## 📊 Relatórios

Após executar os testes, os relatórios serão gerados em `tests/robot/results/`:

- `log.html` - Log detalhado dos testes
- `report.html` - Relatório de execução
- `output.xml` - Saída XML para integração CI/CD

## 🔧 Configuração

### Variáveis de Ambiente

As principais variáveis podem ser configuradas no arquivo `resources/variables.robot`:

- `MOODLE_URL` - URL base do Moodle
- `BROWSER` - Navegador a ser usado (chrome, firefox, etc.)
- `TIMEOUT` - Timeout padrão para esperas
- `ADMIN_USER` / `ADMIN_PASS` - Credenciais de administrador

### Seletores

Os seletores CSS/XPath podem ser ajustados conforme necessário no arquivo `resources/variables.robot` ou nas keywords específicas.

## 📝 Escrevendo Novos Testes

### Exemplo de teste básico:

```robot
*** Settings ***
Resource         ../keywords/moodle_keywords.robot
Resource         ../keywords/common_keywords.robot
Suite Setup      Abrir Navegador Moodle
Suite Teardown   Fechar Navegador

*** Test Cases ***
Meu Novo Teste
    [Documentation]    Descrição do teste
    [Tags]    minha_tag
    Realizar Login Moodle
    # Suas ações aqui
    Verificar Texto Na Página    Texto esperado
```

### Criando novas keywords:

Adicione suas keywords personalizadas em `keywords/common_keywords.robot` ou crie um novo arquivo em `keywords/`.

## 🔍 Debugging

### Logs detalhados:

```bash
robot --loglevel DEBUG tests/robot/tests/
```

### Screenshots:

As keywords já incluem suporte para screenshots. Use:

```robot
Tirar Screenshot    nome_do_arquivo
```

Os screenshots serão salvos em `tests/robot/results/`.

## 🐛 Troubleshooting

### Erro: "ChromeDriver não encontrado"

```bash
pip install --upgrade chromedriver-binary
```

### Erro: "Selenium não consegue encontrar elemento"

- Verifique se os seletores estão corretos
- Aumente o timeout em `robot.yml`
- Verifique se o elemento está realmente visível na página

### Erro: "Navegador não abre"

- Verifique se o Chrome/Chromium está instalado
- Para modo headless, certifique-se de que está funcionando
- Verifique permissões de execução

## 📚 Recursos Adicionais

- [Documentação Robot Framework](https://robotframework.org/)
- [SeleniumLibrary](https://robotframework.org/SeleniumLibrary/)
- [Documentação Moodle](https://docs.moodle.org/)

## 🤝 Contribuindo

Ao adicionar novos testes:

1. Mantenha os testes organizados por funcionalidade
2. Use tags apropriadas para organização
3. Documente adequadamente suas keywords
4. Siga o padrão de nomenclatura existente

## 📄 Licença

Os testes seguem a mesma licença do projeto Moodle (GPL v3).

