<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Language strings (English)
 *
 * @package    local_tubaron
 * @copyright  2025 Tubaron Telecomunicações
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Plugin name
$string['pluginname'] = 'Tubaron Gamification System';
$string['tubaron'] = 'Gamificação Tubaron';

// Capabilities
$string['tubaron:viewseasons'] = 'Visualizar temporadas';
$string['tubaron:manageseasons'] = 'Gerenciar temporadas';
$string['tubaron:closeseason'] = 'Encerrar temporada';
$string['tubaron:viewteams'] = 'Visualizar equipes';
$string['tubaron:createteam'] = 'Criar equipe';
$string['tubaron:manageteam'] = 'Gerenciar equipe';
$string['tubaron:viewtasks'] = 'Visualizar tarefas';
$string['tubaron:createtask'] = 'Criar tarefa';
$string['tubaron:edittask'] = 'Editar tarefa';
$string['tubaron:deletetask'] = 'Excluir tarefa';
$string['tubaron:submittask'] = 'Submeter tarefa';
$string['tubaron:completetask'] = 'Completar tarefa';
$string['tubaron:vote'] = 'Votar em submissões';
$string['tubaron:managevoting'] = 'Gerenciar votações';
$string['tubaron:viewrankings'] = 'Visualizar rankings';
$string['tubaron:viewfullrankings'] = 'Visualizar rankings completos';
$string['tubaron:viewdashboard'] = 'Visualizar dashboard';
$string['tubaron:viewadmindashboard'] = 'Visualizar dashboard admin';
$string['tubaron:viewreports'] = 'Visualizar relatórios';
$string['tubaron:exportdata'] = 'Exportar dados (LGPD)';
$string['tubaron:administrate'] = 'Administrar sistema';

// Navigation
$string['dashboard'] = 'Dashboard';
$string['tasks'] = 'Tarefas';
$string['teams'] = 'Equipes';
$string['rankings'] = 'Rankings';
$string['calendar'] = 'Calendário';
$string['admin'] = 'Administração';

// Seasons
$string['seasons'] = 'Temporadas';
$string['season'] = 'Temporada';
$string['seasonname'] = 'Nome da Temporada';
$string['seasonname_help'] = 'Nome descritivo da temporada. Exemplo: "Temporada Inaugural 2025" ou "Campeonato Verão 2025". Duração: 6 a 12 meses.';
$string['startdate'] = 'Data Início';
$string['startdate_help'] = 'Data de início da temporada. A temporada deve durar entre 6 e 12 meses.';
$string['enddate'] = 'Data Fim';
$string['enddate_help'] = 'Data de encerramento da temporada. Deve ser entre 6 e 12 meses após a data de início.';
$string['seasonrules'] = 'Regras de Pontuação';
$string['seasonrules_help'] = 'Configure os pontos para cada tipo de tarefa e colocações em tarefas competitivas.';
$string['seasonstatus'] = 'Status';
$string['status_draft'] = 'Rascunho';
$string['status_active'] = 'Ativa';
$string['status_closed'] = 'Encerrada';
$string['createseason'] = 'Criar Temporada';
$string['editseason'] = 'Editar Temporada';
$string['closeseason'] = 'Encerrar Temporada';
$string['seasonduration_error'] = 'Temporada deve durar entre 6 e 12 meses';
$string['season_closed_success'] = 'Temporada encerrada com sucesso. Rankings congelados.';
$string['season_overlap_error'] = 'Já existe uma temporada ativa no período selecionado';
$string['season_already_closed'] = 'Esta temporada já está encerrada';
$string['season_created_success'] = 'Temporada criada com sucesso!';

// Teams
$string['team'] = 'Equipe';
$string['teamname'] = 'Nome da Equipe';
$string['captain'] = 'Capitão';
$string['members'] = 'Membros';
$string['memberscount'] = 'Número de Membros';
$string['createteam'] = 'Criar Equipe';
$string['editteam'] = 'Editar Equipe';
$string['deleteteam'] = 'Excluir Equipe';
$string['jointeam'] = 'Entrar na Equipe';
$string['leaveteam'] = 'Sair da Equipe';
$string['team_minmembers_error'] = 'Equipe {$a} possui apenas {$a->count} membros (mínimo 3 para tarefas competitivas)';
$string['team_created_success'] = 'Equipe criada com sucesso!';

// Tasks
$string['task'] = 'Tarefa';
$string['tasktitle'] = 'Título da Tarefa';
$string['taskdescription'] = 'Descrição';
$string['tasktype'] = 'Tipo';
$string['tasktype_individual'] = 'Individual';
$string['tasktype_team'] = 'Equipe';
$string['tasktype_competitive'] = 'Competitiva';
$string['deadline'] = 'Prazo';
$string['points'] = 'Pontos';
$string['mission'] = 'Missão';
$string['createtask'] = 'Criar Tarefa';
$string['edittask'] = 'Editar Tarefa';
$string['deletetask'] = 'Excluir Tarefa';
$string['viewtask'] = 'Ver Detalhes';
$string['submittask'] = 'Submeter';
$string['completetask'] = 'Completar';
$string['task_status_open'] = 'Aberta';
$string['task_status_in_progress'] = 'Em Andamento';
$string['task_status_voting'] = 'Em Votação';
$string['task_status_completed'] = 'Completa';
$string['task_created_success'] = 'Tarefa criada com sucesso!';
$string['task_submitted_success'] = 'Submissão enviada com sucesso!';
$string['task_completed_success'] = 'Tarefa completada! +{$a} pontos';

// Submissions
$string['submission'] = 'Submissão';
$string['submissions'] = 'Submissões';
$string['content'] = 'Conteúdo';
$string['attachments'] = 'Anexos';
$string['submit'] = 'Enviar Submissão';

// Voting
$string['voting'] = 'Votação';
$string['vote'] = 'Votar';
$string['votevalue'] = 'Nota';
$string['votingmethod'] = 'Método de Votação';
$string['votingmethod_majority'] = 'Maioria Simples';
$string['votingmethod_grades'] = 'Notas (0-10)';
$string['votingmethod_ranking'] = 'Ranking (1º/2º/3º)';
$string['votingwindow'] = 'Janela de Votação';
$string['votingopened'] = 'Votação Aberta';
$string['votingclosed'] = 'Votação Encerrada';
$string['voteeligible'] = 'Votantes Elegíveis';
$string['vote_success'] = 'Voto registrado com sucesso!';
$string['vote_ownteam_error'] = 'Você não pode votar na própria equipe';
$string['vote_duplicate_error'] = 'Você já votou nesta tarefa';
$string['vote_ratelimit_error'] = 'Limite de 10 votos por minuto excedido. Aguarde.';
$string['vote_noteligible_error'] = 'Você não está elegível para votar nesta tarefa';
$string['vote_closed_error'] = 'Votação encerrada';

// Rankings
$string['ranking'] = 'Ranking';
$string['rank'] = 'Posição';
$string['totalpoints'] = 'Total de Pontos';
$string['firstplaces'] = 'Primeiros Lugares';
$string['taskscount'] = 'Tarefas Completas';
$string['trend'] = 'Tendência';
$string['trend_up'] = 'Subindo';
$string['trend_down'] = 'Descendo';
$string['trend_neutral'] = 'Estável';
$string['userranking'] = 'Ranking Usuários';
$string['teamranking'] = 'Ranking Equipes';
$string['top10'] = 'Top 10';
$string['yourposition'] = 'Sua Posição';
$string['lastupdated'] = 'Atualizado há {$a}';

// Achievements
$string['achievement'] = 'Conquista';
$string['achievements'] = 'Conquistas';
$string['achievementunlocked'] = 'Conquista Desbloqueada!';
$string['achievementcode'] = 'Código';
$string['achievementname'] = 'Nome';
$string['achievementdescription'] = 'Descrição';
$string['achievementcriteria'] = 'Critérios';
$string['myachievements'] = 'Minhas Conquistas';

// Streaks
$string['streak'] = 'Sequência';
$string['streakdaily'] = 'Sequência Diária';
$string['streakweekly'] = 'Sequência Semanal';
$string['currentstreak'] = 'Sequência Atual';
$string['beststreak'] = 'Melhor Sequência';
$string['streak_days'] = '{$a} dias';

// Dashboard
$string['mydashboard'] = 'Meu Dashboard';
$string['teamdashboard'] = 'Dashboard da Equipe';
$string['admindashboard'] = 'Dashboard Admin';
$string['pendingtasks'] = 'Tarefas Pendentes';
$string['urgenttasks'] = 'Urgente (<24h)';
$string['upcomingevents'] = 'Próximos Eventos';
$string['myperformance'] = 'Meu Desempenho';
$string['teamperformance'] = 'Desempenho da Equipe';

// Reports
$string['reports'] = 'Relatórios';
$string['participationreport'] = 'Relatório de Participação';
$string['auditreport'] = 'Relatório de Auditoria';
$string['exportcsv'] = 'Exportar CSV';
$string['exportexcel'] = 'Exportar Excel';
$string['exportpdf'] = 'Exportar PDF';

// LGPD
$string['lgpd'] = 'LGPD';
$string['lgpd_export'] = 'Exportar Meus Dados (LGPD Art. 18)';
$string['lgpd_export_success'] = 'Dados exportados com sucesso';
$string['lgpd_anonymize'] = 'Anonimizar Usuário';
$string['lgpd_anonymize_confirm'] = 'Tem certeza? Esta ação é irreversível.';
$string['lgpd_anonymize_success'] = 'Usuário anonimizado com sucesso';

// Notifications
$string['notifications'] = 'Notificações';
$string['notification_taskassigned'] = 'Você foi designado para a tarefa "{$a}"';
$string['notification_votingopened'] = 'Votação aberta: "{$a}"';
$string['notification_votingclosed'] = 'Votação encerrada: "{$a}"';
$string['notification_deadline24h'] = 'Prazo em 24h: "{$a}"';
$string['notification_resultspublished'] = 'Resultados publicados: "{$a}"';
$string['notification_achievementunlocked'] = 'Conquista desbloqueada: "{$a}"';

// Errors & Warnings
$string['error'] = 'Erro';
$string['warning'] = 'Aviso';
$string['success'] = 'Sucesso';
$string['confirm'] = 'Confirmar';
$string['cancel'] = 'Cancelar';
$string['save'] = 'Salvar';
$string['delete'] = 'Excluir';
$string['edit'] = 'Editar';
$string['view'] = 'Visualizar';
$string['back'] = 'Voltar';
$string['nodata'] = 'Nenhum dado disponível';
$string['loading'] = 'Carregando...';

// Settings
$string['settings'] = 'Configurações';
$string['enablegamification'] = 'Habilitar Gamificação';
$string['enablegamification_desc'] = 'Ativar sistema de gamificação Tubaron';
$string['votingratelimit'] = 'Limite Votos por Minuto';
$string['votingratelimit_desc'] = 'Máximo de votos permitidos por usuário por minuto (padrão: 10)';
$string['taskcompletion_policy'] = 'Política Conclusão Tarefas';
$string['taskcompletion_policy_desc'] = 'free = qualquer assignee pode completar, approval = apenas líder/admin';
$string['dataretention_months'] = 'Retenção de Dados (Meses)';
$string['dataretention_months_desc'] = 'Meses para reter dados após encerramento de temporada (LGPD)';

// Events
$string['event_season_created'] = 'Temporada criada';
$string['event_season_closed'] = 'Temporada encerrada';
$string['event_team_created'] = 'Equipe criada';
$string['event_task_created'] = 'Tarefa criada';
$string['event_task_submitted'] = 'Tarefa submetida';
$string['event_task_completed'] = 'Tarefa completada';
$string['event_vote_submitted'] = 'Voto submetido';
$string['event_achievement_unlocked'] = 'Conquista desbloqueada';

// Privacy
$string['privacy:metadata:local_tubaron_votes'] = 'Informações sobre votos em tarefas competitivas';
$string['privacy:metadata:local_tubaron_votes:userid'] = 'ID do usuário que votou';
$string['privacy:metadata:local_tubaron_votes:votevalue'] = 'Valor do voto';
$string['privacy:metadata:local_tubaron_votes:timevoted'] = 'Data/hora do voto';
$string['privacy:metadata:local_tubaron_scores'] = 'Pontuações de usuários e equipes';
$string['privacy:metadata:local_tubaron_scores:points'] = 'Total de pontos acumulados';
$string['privacy:metadata:local_tubaron_scores:rank'] = 'Posição no ranking';
$string['privacy:metadata:local_tubaron_audit_logs'] = 'Logs de auditoria (LGPD compliance)';
$string['privacy:metadata:local_tubaron_audit_logs:action'] = 'Ação realizada';
$string['privacy:metadata:local_tubaron_audit_logs:timecreated'] = 'Data/hora da ação';

// Teams CRUD
$string['teams_description'] = 'Gerencie equipes, membros e colaboração';
$string['createteam'] = 'Criar Equipe';
$string['editteam'] = 'Editar Equipe';
$string['viewteam'] = 'Visualizar Equipe';
$string['saveteam'] = 'Salvar Equipe';
$string['teamname'] = 'Nome da Equipe';
$string['teamdetails'] = 'Detalhes da Equipe';
$string['teammembers'] = 'Membros da Equipe';
$string['teamleader'] = 'Líder da Equipe';
$string['teamstatus'] = 'Status da Equipe';
$string['maxmembers'] = 'Máximo de Membros';
$string['avatarurl'] = 'URL do Avatar';
$string['members'] = 'Membros';
$string['leader'] = 'Líder';
$string['searchteams'] = 'Buscar equipes...';
$string['allstatuses'] = 'Todos os Status';
$string['totalteams'] = 'Total de Equipes';
$string['activeteams'] = 'Equipes Ativas';
$string['noteamsfound'] = 'Nenhuma equipe encontrada';
$string['nomembers'] = 'Esta equipe ainda não possui membros';
$string['selectleader'] = 'Selecione o líder...';
$string['selectmembers'] = 'Selecione os membros...';
$string['noselection'] = 'Nenhuma seleção';
$string['minmemberswarning'] = 'Uma equipe deve ter no mínimo 3 membros (1 líder + 2 membros).';
$string['minmemberserror'] = 'A equipe deve ter no mínimo {$a} membros (incluindo o líder).';
$string['maxmemberserror'] = 'A equipe não pode ter mais de {$a} membros.';
$string['leaderduplicateerror'] = 'O líder não pode estar na lista de membros.';
$string['teamnamexists'] = 'Já existe uma equipe com este nome nesta temporada.';
$string['teamcreated'] = 'Equipe criada com sucesso!';
$string['teamupdated'] = 'Equipe atualizada com sucesso!';
$string['errorsavingteam'] = 'Erro ao salvar equipe';
$string['teamform_description'] = 'Preencha os dados abaixo para criar ou editar uma equipe';
$string['noactiveseasons'] = 'Não há temporadas ativas. Crie uma temporada antes de criar equipes.';
$string['joineddate'] = 'Entrou em';
$string['recenttasks'] = 'Tarefas Recentes';
$string['notasksfound'] = 'Nenhuma tarefa encontrada para esta equipe';
$string['numeric'] = 'Este campo deve conter apenas números';
$string['points'] = 'Pontos';
$string['type_individual'] = 'Individual';
$string['type_team'] = 'Equipe';
$string['type_competitive'] = 'Competitiva';

// Help strings for team form
$string['teamname_help'] = 'Nome único para identificar a equipe (ex: "Vendas Norte", "Tech Squad A").';
$string['description'] = 'Descrição';
$string['description_help'] = 'Breve descrição sobre a equipe e seus objetivos.';
$string['season_help'] = 'Temporada em que esta equipe irá competir.';
$string['teamstatus_help'] = 'Equipes ativas podem participar de tarefas. Equipes inativas são mantidas apenas para histórico.';
$string['maxmembers_help'] = 'Número máximo de membros permitidos nesta equipe (mínimo: 3).';
$string['avatarurl_help'] = 'URL de uma imagem para representar a equipe (opcional).';
$string['teamleader_help'] = 'Usuário responsável por liderar e coordenar a equipe.';
$string['teammembers_help'] = 'Membros da equipe além do líder. Mínimo: 2 membros + 1 líder = 3 total.';

// Voting System
$string['voting'] = 'Votação';
$string['voting_description'] = 'Vote em tarefas submetidas e ajude a definir os pontos';
$string['vote'] = 'Votar';
$string['castvote'] = 'Registrar Voto';
$string['confirmandsend'] = 'Confirmar e Enviar Voto';
$string['openvoting'] = 'Em Votação';
$string['votingclosed'] = 'Votação Encerrada';
$string['votingresults'] = 'Resultados da Votação';
$string['results'] = 'Resultados';
$string['votingmethod'] = 'Método de Votação';
$string['votingdeadline'] = 'Prazo Votação';
$string['taskdetails'] = 'Detalhes da Tarefa';
$string['votingstats'] = 'Estatísticas de Votação';
$string['tasksinvoting'] = 'Tarefas em Votação';
$string['yourvotes'] = 'Seus Votos';
$string['pendingyourvotes'] = 'Pendentes';
$string['voted'] = 'Votado';
$string['pending'] = 'Pendente';
$string['votesreceived'] = 'Votos Recebidos';
$string['participation'] = 'Participação';
$string['notasksinvoting'] = 'Não há tarefas em votação no momento';
$string['viewresults'] = 'Ver Resultados';
$string['backtovoting'] = 'Voltar para Votação';
$string['viewtask'] = 'Ver Tarefa';
$string['creator'] = 'Criador';
$string['mission'] = 'Missão';
$string['created'] = 'Criado em';
$string['expired'] = 'Expirado';

// Voting Methods
$string['method_majority'] = 'Maioria Simples';
$string['method_rating'] = 'Notas 0-10';
$string['method_ranking'] = 'Ranking Top 3';
$string['majority_question'] = 'Esta tarefa atende aos critérios e deve ser aprovada?';
$string['rating_question'] = 'Qual nota você dá para a qualidade desta entrega?';
$string['ranking_question'] = 'Ordene as 3 melhores submissões:';

// Voting Actions
$string['approve'] = 'Aprovar';
$string['reject'] = 'Rejeitar';
$string['approve_description'] = 'A tarefa atende aos critérios de qualidade';
$string['reject_description'] = 'A tarefa precisa de melhorias';
$string['outof10'] = 'de 10';
$string['firstplace'] = '1º Lugar';
$string['secondplace'] = '2º Lugar';
$string['thirdplace'] = '3º Lugar';
$string['selectsubmission'] = 'Selecione uma submissão...';

// Rating Scale
$string['rating_poor'] = 'Inadequado';
$string['rating_average'] = 'Satisfatório';
$string['rating_good'] = 'Bom';
$string['rating_excellent'] = 'Excelente';

// Voting Errors
$string['alreadyvoted'] = 'Você já votou nesta tarefa';
$string['noteligible'] = 'Você não é elegível para votar nesta tarefa';
$string['ratelimit'] = 'Limite de votos excedido. Aguarde um momento';
$string['votingnotopen'] = 'A votação desta tarefa não está aberta';
$string['invalidvote'] = 'Valor de voto inválido';
$string['rankingduplicateerror'] = 'Você não pode selecionar a mesma submissão em posições diferentes';
$string['confirmvote'] = 'Tem certeza que deseja registrar este voto? Não será possível alterar depois.';
$string['votesuccess'] = 'Voto registrado com sucesso!';
$string['errorcastingvote'] = 'Erro ao registrar voto';
$string['notenoughsubmissions'] = 'Não há submissões suficientes para votação por ranking (mínimo 3)';
$string['novotesyet'] = 'Ainda não há votos registrados para esta tarefa';

// Results
$string['approved'] = 'Aprovado';
$string['rejected'] = 'Rejeitado';
$string['averagescore'] = 'Nota Média';
$string['finalranking'] = 'Ranking Final';
$string['distribution'] = 'Distribuição de Notas';
$string['votesprogress'] = '{$a->received} de {$a->eligible} votos';

// Scoring
$string['bonus_first_complete'] = 'Primeira submissão aprovada';
$string['bonus_perfect_score'] = 'Pontuação perfeita';
$string['bonus_streak_3'] = 'Sequência de 3 tarefas';
$string['bonus_streak_5'] = 'Sequência de 5 tarefas';
$string['bonus_early_submit'] = 'Submissão antecipada';
$string['penalty_late_submit'] = 'Submissão atrasada';
$string['penalty_rejected'] = 'Tarefa rejeitada';
$string['penalty_low_quality'] = 'Baixa qualidade';

// Tasks CRUD
$string['tasks_description'] = 'Crie e gerencie tarefas gamificadas (3 tipos)';
$string['createtask'] = 'Criar Tarefa';
$string['edittask'] = 'Editar Tarefa';
$string['savetask'] = 'Salvar Tarefa';
$string['tasktype'] = 'Tipo de Tarefa';
$string['tasktitle'] = 'Título da Tarefa';
$string['taskpoints'] = 'Pontos';
$string['taskform_description'] = 'Preencha os dados abaixo para criar ou editar uma tarefa';
$string['tasktypes'] = 'Tipos de Tarefas';
$string['type_individual_desc'] = 'Tarefa atribuída a um colaborador específico';
$string['type_team_desc'] = 'Tarefa colaborativa para uma equipe completa';
$string['type_competitive_desc'] = 'Competição aberta com múltiplas submissões e votação';
$string['votingconfiguration'] = 'Configuração de Votação';
$string['taskassignments'] = 'Atribuições';
$string['assigntouser'] = 'Atribuir para Usuário';
$string['assigntoteam'] = 'Atribuir para Equipe';
$string['assignmultiple'] = 'Atribuir para Múltiplos';
$string['selectuser'] = 'Selecione um usuário...';
$string['selectteam'] = 'Selecione uma equipe...';
$string['selectmultiple'] = 'Selecione participantes...';
$string['users'] = 'Usuários';
$string['approvalcriteria'] = 'Critérios de Aprovação';
$string['assignments'] = 'Atribuições';
$string['submissions'] = 'Submissões';
$string['additionalinfo'] = 'Informações Adicionais';
$string['overdue'] = 'ATRASADO';
$string['noassignments'] = 'Esta tarefa ainda não possui atribuições';
$string['nosubmissions'] = 'Ainda não há submissões para esta tarefa';
$string['backtotasks'] = 'Voltar para Tarefas';
$string['taskcreated'] = 'Tarefa criada com sucesso!';
$string['taskupdated'] = 'Tarefa atualizada com sucesso!';
$string['errorsavingtask'] = 'Erro ao salvar tarefa';
$string['assignuserrequired'] = 'Tarefas individuais requerem atribuição de usuário';
$string['assignteamrequired'] = 'Tarefas de equipe requerem atribuição de equipe';
$string['assignmultiplerequired'] = 'Tarefas competitivas requerem múltiplas atribuições';
$string['votingdeadlineaftererror'] = 'Prazo de votação deve ser após o prazo da tarefa';
$string['pointspositiveerror'] = 'Pontos devem ser maiores que zero';
$string['noactivemissions'] = 'Não há missões ativas. Crie uma missão antes de criar tarefas.';
$string['nopermissiontoedit'] = 'Você não tem permissão para editar esta tarefa';
$string['searchtasks'] = 'Buscar tarefas...';
$string['alltypes'] = 'Todos os Tipos';
$string['totaltasks'] = 'Total de Tarefas';
$string['opentasks'] = 'Tarefas Abertas';
$string['votingtasks'] = 'Em Votação';
$string['completedtasks'] = 'Concluídas';
$string['status_open'] = 'Aberta';
$string['status_in_progress'] = 'Em Andamento';
$string['status_voting'] = 'Em Votação';
$string['status_completed'] = 'Concluída';

// Help strings for task form
$string['tasktype_help'] = 'Tipo de tarefa determina como será atribuída e executada:<br>
• <strong>Individual</strong>: Atribuída a um usuário específico<br>
• <strong>Equipe</strong>: Toda equipe colabora na execução<br>
• <strong>Competitiva</strong>: Múltiplas submissões competem por pontos';
$string['tasktitle_help'] = 'Título claro e objetivo da tarefa (ex: "Implementar Feature X", "Melhorar Performance Y")';
$string['taskdescription_help'] = 'Descrição detalhada incluindo: objetivos, requisitos, recursos necessários e critérios de sucesso';
$string['mission_help'] = 'Missão (agrupamento) à qual esta tarefa pertence. Missões têm peso multiplicador de pontos';
$string['taskpoints_help'] = 'Pontos base da tarefa. Pontos finais serão calculados após votação com bônus/penalidades';
$string['deadline_help'] = 'Prazo para conclusão da tarefa. Submissões após este prazo sofrem penalidade de -20%';
$string['votingmethod_help'] = 'Método que será usado para avaliar esta tarefa:<br>
• <strong>Maioria Simples</strong>: Aprovar/Rejeitar (>50% aprovado)<br>
• <strong>Notas 0-10</strong>: Média de notas (pontos proporcionais)<br>
• <strong>Ranking Top 3</strong>: Para competições com múltiplas submissões';
$string['approvalcriteria_help'] = 'Critérios específicos que devem ser atendidos para aprovação na votação';
$string['votingdeadline_help'] = 'Prazo para encerrar votação. Deve ser após o deadline da tarefa';
$string['assigntouser_help'] = 'Usuário responsável por executar esta tarefa individual';
$string['assigntoteam_help'] = 'Equipe que executará esta tarefa colaborativa';
$string['assignmultiple_help'] = 'Para tarefas competitivas, selecione múltiplas equipes ou usuários que poderão submeter soluções';

// Sprint 4 - Analytics & Dashboards
$string['analytics'] = 'Analytics';
$string['exportcsv'] = 'Exportar CSV';
$string['exportpdf'] = 'Exportar PDF';
$string['totalvotes'] = 'Total de Votos';
$string['daysremaining'] = 'Dias Restantes';
$string['tasktypesdistribution'] = 'Distribuição por Tipo de Tarefa';
$string['taskstatusdistribution'] = 'Distribuição por Status';
$string['topperformers'] = 'Top Performers';
$string['noactiveseason'] = 'Não há temporada ativa';

