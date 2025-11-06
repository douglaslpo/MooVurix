<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Export Data
 *
 * Export relatórios em CSV/PDF
 * Integrado ao MooVurix LMS Platform
 *
 * @package    local_tubaron
 * @copyright  2025 Tubaron Telecomunicações
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../lib.php');

// Autenticação
require_login();
$context = context_system::instance();

// Verificar capability
require_capability('local/tubaron:viewreports', $context);

// Parâmetros
$seasonid = required_param('seasonid', PARAM_INT);
$format = required_param('format', PARAM_ALPHA); // csv ou pdf
$type = required_param('type', PARAM_ALPHA); // rankings, tasks, votes, full

// Buscar temporada
$season = $DB->get_record('local_tubaron_seasons', ['id' => $seasonid], '*', MUST_EXIST);

// Coletar dados baseado no tipo
$data = [];

switch ($type) {
    case 'rankings':
        // Export rankings
        $data['users'] = local_tubaron_get_top_rankings($seasonid, 'user', 100);
        $data['teams'] = local_tubaron_get_top_rankings($seasonid, 'team', 50);
        break;
        
    case 'tasks':
        // Export tarefas
        $data['tasks'] = $DB->get_records_sql(
            "SELECT t.*, m.name as missionname, u.firstname, u.lastname
             FROM {local_tubaron_tasks} t
             JOIN {local_tubaron_missions} m ON m.id = t.missionid
             JOIN {user} u ON u.id = t.creatorid
             WHERE m.seasonid = ?
             ORDER BY t.timecreated DESC",
            [$seasonid]
        );
        break;
        
    case 'votes':
        // Export votos
        $data['votes'] = $DB->get_records_sql(
            "SELECT v.*, t.title as tasktitle, u.firstname, u.lastname
             FROM {local_tubaron_votes} v
             JOIN {local_tubaron_tasks} t ON t.id = v.taskid
             JOIN {user} u ON u.id = v.voterid
             JOIN {local_tubaron_missions} m ON m.id = t.missionid
             WHERE m.seasonid = ?
             ORDER BY v.timevoted DESC",
            [$seasonid]
        );
        break;
        
    case 'full':
        // Export completo
        $data['users'] = local_tubaron_get_top_rankings($seasonid, 'user', 100);
        $data['teams'] = local_tubaron_get_top_rankings($seasonid, 'team', 50);
        $data['tasks'] = $DB->get_records_sql(
            "SELECT t.*, m.name as missionname
             FROM {local_tubaron_tasks} t
             JOIN {local_tubaron_missions} m ON m.id = t.missionid
             WHERE m.seasonid = ?
             ORDER BY t.timecreated DESC",
            [$seasonid]
        );
        break;
}

if ($format === 'csv') {
    // === EXPORT CSV ===
    
    $filename = 'tubaron_' . $type . '_' . $season->id . '_' . date('Y-m-d') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    
    // BOM UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    switch ($type) {
        case 'rankings':
        case 'full':
            // Rankings Usuários
            fputcsv($output, ['RANKING USUÁRIOS - ' . format_string($season->name)]);
            fputcsv($output, ['Posição', 'Nome', 'Pontos', 'Tarefas Concluídas']);
            
            foreach ($data['users'] as $user) {
                $userobj = $DB->get_record('user', ['id' => $user->entityid]);
                fputcsv($output, [
                    $user->rank,
                    fullname($userobj),
                    $user->totalpoints,
                    $user->taskscount
                ]);
            }
            
            fputcsv($output, []);
            
            // Rankings Equipes
            fputcsv($output, ['RANKING EQUIPES - ' . format_string($season->name)]);
            fputcsv($output, ['Posição', 'Equipe', 'Pontos', 'Tarefas']);
            
            foreach ($data['teams'] as $team) {
                $teamobj = $DB->get_record('local_tubaron_teams', ['id' => $team->entityid]);
                fputcsv($output, [
                    $team->rank,
                    format_string($teamobj->name),
                    $team->totalpoints,
                    $team->taskscount
                ]);
            }
            break;
            
        case 'tasks':
            fputcsv($output, ['TAREFAS - ' . format_string($season->name)]);
            fputcsv($output, ['ID', 'Título', 'Tipo', 'Status', 'Pontos', 'Criador', 'Missão', 'Criado Em']);
            
            foreach ($data['tasks'] as $task) {
                fputcsv($output, [
                    $task->id,
                    format_string($task->title),
                    $task->type,
                    $task->status,
                    $task->points,
                    isset($task->firstname) ? $task->firstname . ' ' . $task->lastname : '',
                    $task->missionname ?? '',
                    userdate($task->timecreated, '%Y-%m-%d %H:%M')
                ]);
            }
            break;
            
        case 'votes':
            fputcsv($output, ['VOTOS - ' . format_string($season->name)]);
            fputcsv($output, ['ID', 'Tarefa', 'Votante', 'Método', 'Valor', 'Data Voto']);
            
            foreach ($data['votes'] as $vote) {
                fputcsv($output, [
                    $vote->id,
                    $vote->tasktitle,
                    $vote->firstname . ' ' . $vote->lastname,
                    $vote->votingmethod,
                    $vote->votevalue,
                    userdate($vote->timevoted, '%Y-%m-%d %H:%M')
                ]);
            }
            break;
    }
    
    fclose($output);
    exit;
    
} else if ($format === 'pdf') {
    // === EXPORT PDF ===
    
    // Simplificado: gerar HTML e converter para PDF (requer TCPDF ou similar)
    // Por ora, redirect para página printable
    
    redirect(new moodle_url('/local/tubaron/admin/print_report.php', [
        'seasonid' => $seasonid,
        'type' => $type
    ]));
}

