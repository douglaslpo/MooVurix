*** Settings ***
Documentation    Keywords comuns para testes do Moodle
Library          SeleniumLibrary
Library          Collections
Library          String
Variables         ../resources/variables.robot

*** Keywords ***
Abrir Navegador Moodle
    [Documentation]    Abre o navegador e navega para a URL do Moodle
    [Arguments]    ${url}=${MOODLE_URL}
    Open Browser    ${url}    ${BROWSER}    options=${BROWSER_OPTIONS}
    Maximize Browser Window
    Set Selenium Timeout    ${TIMEOUT}
    Set Selenium Implicit Wait    ${TIMEOUT}

Fechar Navegador
    [Documentation]    Fecha o navegador atual
    Close All Browsers

Aguardar Elemento Visível
    [Documentation]    Aguarda um elemento ficar visível na página
    [Arguments]    ${locator}    ${timeout}=${TIMEOUT}
    Wait Until Element Is Visible    ${locator}    ${timeout}

Aguardar E Clicar
    [Documentation]    Aguarda elemento ficar visível e clica nele
    [Arguments]    ${locator}    ${timeout}=${TIMEOUT}
    Wait Until Element Is Visible    ${locator}    ${timeout}
    Click Element    ${locator}
    Sleep    ${DELAY}

Preencher Campo
    [Documentation]    Preenche um campo de formulário
    [Arguments]    ${locator}    ${texto}    ${timeout}=${TIMEOUT}
    Wait Until Element Is Visible    ${locator}    ${timeout}
    Clear Element Text    ${locator}
    Input Text    ${locator}    ${texto}
    Sleep    ${DELAY}

Verificar Texto Na Página
    [Documentation]    Verifica se um texto está presente na página
    [Arguments]    ${texto}    ${timeout}=${TIMEOUT}
    Wait Until Page Contains    ${texto}    ${timeout}

Tirar Screenshot
    [Documentation]    Tira um screenshot da página atual
    [Arguments]    ${nome_arquivo}=screenshot
    Capture Page Screenshot    ${nome_arquivo}.png

Scroll Para Elemento
    [Documentation]    Faz scroll até um elemento específico
    [Arguments]    ${locator}
    Scroll Element Into View    ${locator}

Verificar URL Contém
    [Documentation]    Verifica se a URL atual contém um texto específico
    [Arguments]    ${texto}
    ${current_url}=    Get Location
    Should Contain    ${current_url}    ${texto}

