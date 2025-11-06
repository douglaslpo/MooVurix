*** Settings ***
Documentation    Testes de login do Moodle
Resource         ../keywords/moodle_keywords.robot
Resource         ../keywords/common_keywords.robot
Variables         ../resources/variables.robot
Suite Setup      Abrir Navegador Moodle
Suite Teardown   Fechar Navegador

*** Test Cases ***
Teste Login Admin Com Sucesso
    [Documentation]    Testa login com credenciais de administrador válidas
    [Tags]    login    admin    smoke
    Realizar Login Moodle    ${ADMIN_USER}    ${ADMIN_PASS}
    Verificar Usuário Logado
    Realizar Logout Moodle

Teste Login Com Credenciais Inválidas
    [Documentation]    Testa login com credenciais inválidas
    [Tags]    login    negativo
    Go To    ${MOODLE_LOGIN_URL}
    Preencher Campo    ${USERNAME_INPUT}    usuario_invalido
    Preencher Campo    ${PASSWORD_INPUT}    senha_invalida
    Aguardar E Clicar    ${SUBMIT_BUTTON}
    Verificar Mensagem De Erro    inválido
    Tirar Screenshot    login_falhou

Teste Campos Obrigatórios Login
    [Documentation]    Testa se os campos de login são obrigatórios
    [Tags]    login    validacao
    Go To    ${MOODLE_LOGIN_URL}
    Aguardar E Clicar    ${SUBMIT_BUTTON}
    # Verificar se há mensagem de erro de campos obrigatórios
    ${error_present}=    Run Keyword And Return Status    Verificar Mensagem De Erro    obrigatório
    Should Be True    ${error_present}    Mensagem de erro de campo obrigatório não encontrada

