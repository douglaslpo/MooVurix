<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - AJAX Voting Stats
 *
 * Endpoint AJAX para estatísticas de votação em tempo real
 * Integrado ao MooVurix LMS Platform
 *
 * @package    local_tubaron
 * @copyright  2025 Tubaron Telecomunicações
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../lib.php');

// Headers JSON
header('Content-Type: application/json');

// Autenticação
require_login();

$context = context_system::instance();
require_capability('local/tubaron:viewrankings', $context);

try {
    // Parâmetros
    $taskid = optional_param('taskid', 0, PARAM_INT);
    $seasonid = optional_param('seasonid', 0, PARAM_INT);
    $action = required_param('action', PARAM_ALPHA);
    
    $response = ['success' => true];
    
    switch ($action) {
        case 'task_stats':
            // Stats de uma tarefa específica
            if (!$taskid) {
                throw new \moodle_exception('missingtaskid', 'local_tubaron');
            }
            
            $stats = \local_tubaron\voting_manager::get_voting_stats($taskid);
            
            $response['stats'] = [
                'taskid' => $taskid,
                'votes_received' => $stats->votes_received,
                'eligible_voters' => $stats->eligible_voters,
                'participation_rate' => round($stats->participation_rate, 1),
                'status' => $stats->status,
                'current_results' => $stats->current_results ?? null
            ];
            break;
            
        case 'season_rankings':
            // Rankings atualizados de uma temporada
            if (!$seasonid) {
                $season = local_tubaron_get_active_season();
                $seasonid = $season ? $season->id : 0;
            }
            
            if (!$seasonid) {
                throw new \moodle_exception('noactiveseason', 'local_tubaron');
            }
            
            // Top 10 usuários
            $userrankings = local_tubaron_get_top_rankings($seasonid, 'user', 10);
            
            // Top 5 equipes
            $teamrankings = local_tubaron_get_top_rankings($seasonid, 'team', 5);
            
            $response['rankings'] = [
                'seasonid' => $seasonid,
                'users' => array_values($userrankings),
                'teams' => array_values($teamrankings),
                'updated_at' => time()
            ];
            break;
            
        case 'user_pending_votes':
            // Votos pendentes do usuário
            global $USER;
            
            $pendingtasks = $DB->get_records_sql(
                "SELECT t.id, t.title, t.points, t.votingmethod, t.votingdeadline,
                        COUNT(v.id) as votecount
                 FROM {local_tubaron_tasks} t
                 LEFT JOIN {local_tubaron_votes} v ON v.taskid = t.id
                 WHERE t.status = ?
                 GROUP BY t.id, t.title, t.points, t.votingmethod, t.votingdeadline
                 ORDER BY t.votingdeadline ASC",
                ['voting'],
                0,
                50
            );
            
            $pending = [];
            foreach ($pendingtasks as $task) {
                if (\local_tubaron\voting_manager::check_eligibility($task->id, $USER->id) &&
                    !\local_tubaron\voting_manager::has_voted($task->id, $USER->id)) {
                    $pending[] = [
                        'id' => $task->id,
                        'title' => format_string($task->title),
                        'points' => $task->points,
                        'method' => $task->votingmethod,
                        'deadline' => $task->votingdeadline,
                        'votes' => $task->votecount
                    ];
                }
            }
            
            $response['pending'] = $pending;
            $response['count'] = count($pending);
            break;
            
        case 'live_update':
            // Update completo para dashboard
            $season = local_tubaron_get_active_season();
            
            if ($season) {
                $response['active_season'] = [
                    'id' => $season->id,
                    'name' => format_string($season->name)
                ];
                
                // Tarefas em votação
                $votingtasks = $DB->count_records('local_tubaron_tasks', ['status' => 'voting']);
                $response['voting_tasks'] = $votingtasks;
                
                // User stats
                global $USER;
                $userscore = $DB->get_record('local_tubaron_scores', [
                    'seasonid' => $season->id,
                    'entitytype' => 'user',
                    'entityid' => $USER->id
                ]);
                
                $response['user_stats'] = [
                    'points' => $userscore ? $userscore->totalpoints : 0,
                    'rank' => $userscore ? $userscore->rank : null,
                    'tasks' => $userscore ? $userscore->taskscount : 0
                ];
            }
            break;
            
        default:
            throw new \moodle_exception('invalidaction', 'local_tubaron');
    }
    
    echo json_encode($response);

} catch (\moodle_exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->errorcode,
        'message' => $e->getMessage()
    ]);
    
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'unknown',
        'message' => $e->getMessage()
    ]);
}

