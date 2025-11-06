<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - AJAX Vote Submit
 *
 * Endpoint AJAX para processar votos em tempo real
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
require_sesskey();

$context = context_system::instance();
require_capability('local/tubaron:vote', $context);

try {
    // Parâmetros
    $taskid = required_param('taskid', PARAM_INT);
    $votevalue = required_param('votevalue', PARAM_RAW);
    
    // Processar voto
    $task = $DB->get_record('local_tubaron_tasks', ['id' => $taskid], '*', MUST_EXIST);
    
    // Deserializar votevalue baseado no método
    $processedvalue = null;
    switch ($task->votingmethod) {
        case 'majority':
            $processedvalue = (bool)json_decode($votevalue);
            break;
            
        case 'rating':
            $processedvalue = (int)$votevalue;
            break;
            
        case 'ranking':
            $processedvalue = json_decode($votevalue, true);
            break;
    }
    
    // Registrar voto
    $voteid = \local_tubaron\voting_manager::cast_vote($taskid, $USER->id, $processedvalue);
    
    if (!$voteid) {
        throw new \moodle_exception('errorcastingvote', 'local_tubaron');
    }
    
    // Buscar stats atualizadas
    $stats = \local_tubaron\voting_manager::get_voting_stats($taskid);
    
    // Resposta sucesso
    echo json_encode([
        'success' => true,
        'voteid' => $voteid,
        'message' => get_string('votesuccess', 'local_tubaron'),
        'stats' => [
            'votes_received' => $stats->votes_received,
            'eligible_voters' => $stats->eligible_voters,
            'participation_rate' => round($stats->participation_rate, 1),
        ],
        'current_results' => $stats->current_results ?? null
    ]);

} catch (\moodle_exception $e) {
    // Erro
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->errorcode,
        'message' => $e->getMessage()
    ]);
    
} catch (\Exception $e) {
    // Erro genérico
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'unknown',
        'message' => $e->getMessage()
    ]);
}

