*** Settings ***
Documentation    Variáveis globais de configuração para testes Moodle
# Este arquivo pode ser importado nos testes usando: Variables  ../resources/variables.robot

*** Variables ***
# URLs
${MOODLE_URL}          http://localhost
${MOODLE_LOGIN_URL}    ${MOODLE_URL}/login/index.php
${DASHBOARD_URL}       ${MOODLE_URL}/my/
${COURSES_URL}         ${MOODLE_URL}/course/

# Configurações do navegador
${BROWSER}             chrome
${BROWSER_OPTIONS}     --headless --disable-gpu --no-sandbox
${TIMEOUT}             30s
${DELAY}               0.5s

# Credenciais de teste (ajustar conforme necessário)
${ADMIN_USER}          admin
${ADMIN_PASS}          admin123
${STUDENT_USER}        student
${STUDENT_PASS}        student123
${TEACHER_USER}        teacher
${TEACHER_PASS}        teacher123

# Seletores comuns do Moodle
${USERNAME_INPUT}      id=username
${PASSWORD_INPUT}      id=password
${SUBMIT_BUTTON}       xpath=//button[@type='submit']
${LOGIN_BUTTON}        xpath=//button[contains(@class, 'btn-login')]
${USER_MENU}           xpath=//div[contains(@class, 'usermenu')]//a
${LOGOUT_LINK}         xpath=//a[contains(@href, 'logout')]

# Mensagens esperadas
${LOGIN_SUCCESS_TEXT}  Dashboard
${LOGIN_ERROR_TEXT}    inválido
${COURSE_TEXT}         Cursos

# Diretórios de resultados
${RESULTS_DIR}         ${CURDIR}/../results

