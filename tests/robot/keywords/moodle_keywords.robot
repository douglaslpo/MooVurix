*** Settings ***
Documentation    Keywords específicas para testes do Moodle
Library          SeleniumLibrary
Resource         ../keywords/common_keywords.robot
Variables         ../resources/variables.robot

*** Keywords ***
Realizar Login Moodle
    [Documentation]    Realiza login no Moodle com credenciais fornecidas
    [Arguments]    ${usuario}=${ADMIN_USER}    ${senha}=${ADMIN_PASS}
    Go To    ${MOODLE_LOGIN_URL}
    Aguardar E Clicar    ${USERNAME_INPUT}
    Preencher Campo    ${USERNAME_INPUT}    ${usuario}
    Preencher Campo    ${PASSWORD_INPUT}    ${senha}
    Aguardar E Clicar    ${SUBMIT_BUTTON}
    Verificar Login Bem Sucedido

Verificar Login Bem Sucedido
    [Documentation]    Verifica se o login foi realizado com sucesso
    Wait Until Location Contains    ${DASHBOARD_URL}    timeout=10s
    Verificar Texto Na Página    Dashboard    timeout=5s

Realizar Logout Moodle
    [Documentation]    Realiza logout do Moodle
    ${user_menu}=    Set Variable    xpath=//div[contains(@class, 'usermenu')]//a
    ${logout_link}=    Set Variable    xpath=//a[contains(@href, 'logout')]
    Aguardar E Clicar    ${user_menu}
    Aguardar E Clicar    ${logout_link}
    Wait Until Location Contains    login    timeout=10s

Navegar Para Dashboard
    [Documentation]    Navega para o dashboard do Moodle
    Go To    ${DASHBOARD_URL}
    Verificar Texto Na Página    Dashboard

Navegar Para Cursos
    [Documentation]    Navega para a página de cursos
    Go To    ${MOODLE_URL}/course/
    Verificar Texto Na Página    Cursos

Buscar Curso
    [Documentation]    Busca um curso específico pelo nome
    [Arguments]    ${nome_curso}
    ${search_input}=    Set Variable    xpath=//input[@type='search' or contains(@placeholder, 'Buscar')]
    ${search_button}=    Set Variable    xpath=//button[@type='submit' or contains(@class, 'search')]
    Preencher Campo    ${search_input}    ${nome_curso}
    Aguardar E Clicar    ${search_button}
    Verificar Texto Na Página    ${nome_curso}

Acessar Curso
    [Documentation]    Acessa um curso específico pelo nome
    [Arguments]    ${nome_curso}
    Buscar Curso    ${nome_curso}
    ${course_link}=    Set Variable    xpath=//a[contains(text(), '${nome_curso}')]
    Aguardar E Clicar    ${course_link}
    Verificar URL Contém    course/view

Verificar Usuário Logado
    [Documentation]    Verifica se há um usuário logado
    ${user_menu}=    Set Variable    xpath=//div[contains(@class, 'usermenu')]
    Aguardar Elemento Visível    ${user_menu}

Verificar Mensagem De Erro
    [Documentation]    Verifica se há uma mensagem de erro na página
    [Arguments]    ${mensagem_esperada}
    ${error_locator}=    Set Variable    xpath=//div[contains(@class, 'alert') or contains(@class, 'error')]
    Aguardar Elemento Visível    ${error_locator}
    Verificar Texto Na Página    ${mensagem_esperada}

