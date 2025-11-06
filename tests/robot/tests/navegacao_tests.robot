*** Settings ***
Documentation    Testes de navegação do Moodle
Resource         ../keywords/moodle_keywords.robot
Resource         ../keywords/common_keywords.robot
Variables         ../resources/variables.robot
Suite Setup      Abrir Navegador Moodle
Suite Teardown   Fechar Navegador

*** Test Cases ***
Teste Navegação Dashboard
    [Documentation]    Testa navegação para o dashboard após login
    [Tags]    navegacao    dashboard    smoke
    Realizar Login Moodle
    Navegar Para Dashboard
    Verificar Texto Na Página    Dashboard
    Verificar URL Contém    my

Teste Navegação Para Cursos
    [Documentation]    Testa navegação para a página de cursos
    [Tags]    navegacao    cursos
    Realizar Login Moodle
    Navegar Para Cursos
    Verificar Texto Na Página    Cursos
    Verificar URL Contém    course

Teste Navegação Sem Login
    [Documentation]    Testa se usuário não logado é redirecionado para login
    [Tags]    navegacao    segurança
    Go To    ${DASHBOARD_URL}
    Wait Until Location Contains    login    timeout=10s
    Verificar Texto Na Página    Login

