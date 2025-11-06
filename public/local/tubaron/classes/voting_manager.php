<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Voting Manager
 *
 * Gerencia sistema de votação com 3 métodos + anti-fraude
 * Integrado ao MooVurix LMS Platform
 *
 * @package    local_tubaron
 * @copyright  2025 Tubaron Telecomunicações
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tubaron;

defined('MOODLE_INTERNAL') || die();

/**
 * Voting Manager Class
 *
 * Métodos de votação:
 * - majority: Maioria simples (aprovado/rejeitado)
 * - rating: Notas 0-10 (média ponderada)
 * - ranking: Top 3 (peso diferenciado)
 */
class voting_manager {

    /** @var int Rate limit: máximo votos por janela */
    const RATE_LIMIT_VOTES = 10;

    /** @var int Rate limit: janela em segundos */
    const RATE_LIMIT_WINDOW = 60;

    /** @var int Mínimo de votos para validar resultado */
    const MIN_VOTES_REQUIRED = 3;

    /**
     * Registrar voto em uma tarefa
     *
     * @param int $taskid ID da tarefa
     * @param int $userid ID do votante
     * @param mixed $votevalue Valor do voto (depende do método)
     * @param int $submissionid ID da submissão (opcional, para ranking)
     * @return bool|int Vote ID ou false em erro
     * @throws \moodle_exception
     */
    public static function cast_vote($taskid, $userid, $votevalue, $submissionid = null) {
        global $DB;

        // Validações
        $task = $DB->get_record('local_tubaron_tasks', ['id' => $taskid], '*', MUST_EXIST);
        
        // 1. Verificar se votação está aberta
        if ($task->status !== 'voting') {
            throw new \moodle_exception('votingnotopen', 'local_tubaron');
        }

        // 2. Verificar elegibilidade
        if (!self::check_eligibility($taskid, $userid)) {
            throw new \moodle_exception('noteligible', 'local_tubaron');
        }

        // 3. Verificar se já votou
        if (self::has_voted($taskid, $userid, $submissionid)) {
            throw new \moodle_exception('alreadyvoted', 'local_tubaron');
        }

        // 4. Verificar rate limit
        if (!self::check_rate_limit($userid)) {
            throw new \moodle_exception('ratelimit', 'local_tubaron');
        }

        // 5. Validar voto conforme método
        if (!self::validate_vote_value($task->votingmethod, $votevalue)) {
            throw new \moodle_exception('invalidvote', 'local_tubaron');
        }

        // Registrar voto
        $vote = new \stdClass();
        $vote->taskid = $taskid;
        $vote->submissionid = $submissionid;
        $vote->voterid = $userid;
        $vote->votevalue = self::serialize_vote_value($task->votingmethod, $votevalue);
        $vote->votingmethod = $task->votingmethod;
        $vote->timevoted = time();

        $voteid = $DB->insert_record('local_tubaron_votes', $vote);

        // Log de auditoria
        local_tubaron_log_action('vote_cast', 'vote', $voteid, [
            'taskid' => $taskid,
            'method' => $task->votingmethod
        ]);

        // Verificar se atingiu votos mínimos para calcular resultado
        $votecount = $DB->count_records('local_tubaron_votes', ['taskid' => $taskid]);
        if ($votecount >= self::MIN_VOTES_REQUIRED) {
            // Pode calcular resultado preliminar
            self::calculate_preliminary_results($taskid);
        }

        return $voteid;
    }

    /**
     * Verificar elegibilidade para votar
     *
     * Pode votar SE:
     * - Participou da tarefa (criador, atribuído, membro equipe)
     * - OU tarefa é tipo "competitive" (todos elegíveis)
     * - E não é o próprio executor (em alguns casos)
     *
     * @param int $taskid
     * @param int $userid
     * @return bool
     */
    public static function check_eligibility($taskid, $userid) {
        global $DB;

        $task = $DB->get_record('local_tubaron_tasks', ['id' => $taskid], '*', MUST_EXIST);

        // Competitive: todos podem votar
        if ($task->type === 'competitive') {
            return true;
        }

        // Verificar se participou da tarefa
        $participated = $DB->record_exists_sql(
            "SELECT 1 FROM (
                SELECT t.creatorid as userid FROM {local_tubaron_tasks} t WHERE t.id = ?
                UNION
                SELECT ta.assigneeid as userid FROM {local_tubaron_task_assignments} ta WHERE ta.taskid = ?
                UNION
                SELECT tm.userid FROM {local_tubaron_task_assignments} ta
                JOIN {local_tubaron_team_members} tm ON tm.teamid = ta.assigneeid
                WHERE ta.taskid = ? AND ta.assigneetype = ?
            ) participants WHERE userid = ?",
            [$taskid, $taskid, $taskid, 'team', $userid]
        );

        return $participated;
    }

    /**
     * Verificar rate limit de votos
     *
     * @param int $userid
     * @return bool
     */
    public static function check_rate_limit($userid) {
        global $DB;

        $window = time() - self::RATE_LIMIT_WINDOW;
        
        $recentvotes = $DB->count_records_select(
            'local_tubaron_votes',
            'voterid = ? AND timevoted > ?',
            [$userid, $window]
        );

        return ($recentvotes < self::RATE_LIMIT_VOTES);
    }

    /**
     * Verificar se usuário já votou
     *
     * @param int $taskid
     * @param int $userid
     * @param int|null $submissionid
     * @return bool
     */
    public static function has_voted($taskid, $userid, $submissionid = null) {
        global $DB;

        $conditions = [
            'taskid' => $taskid,
            'voterid' => $userid
        ];

        if ($submissionid !== null) {
            $conditions['submissionid'] = $submissionid;
        }

        return $DB->record_exists('local_tubaron_votes', $conditions);
    }

    /**
     * Validar valor do voto conforme método
     *
     * @param string $method majority|rating|ranking
     * @param mixed $value
     * @return bool
     */
    private static function validate_vote_value($method, $value) {
        switch ($method) {
            case 'majority':
                // Boolean ou 0/1
                return is_bool($value) || $value === 0 || $value === 1;

            case 'rating':
                // Inteiro 0-10
                return is_numeric($value) && $value >= 0 && $value <= 10;

            case 'ranking':
                // Array com 3 posições
                if (!is_array($value) || count($value) !== 3) {
                    return false;
                }
                // Verificar se são IDs válidos
                foreach ($value as $pos => $submissionid) {
                    if (!is_numeric($submissionid) || $submissionid <= 0) {
                        return false;
                    }
                }
                return true;

            default:
                return false;
        }
    }

    /**
     * Serializar valor do voto para armazenamento
     *
     * @param string $method
     * @param mixed $value
     * @return string
     */
    private static function serialize_vote_value($method, $value) {
        if ($method === 'ranking') {
            return json_encode($value);
        }
        return (string)$value;
    }

    /**
     * Deserializar valor do voto
     *
     * @param string $method
     * @param string $value
     * @return mixed
     */
    private static function deserialize_vote_value($method, $value) {
        if ($method === 'ranking') {
            return json_decode($value, true);
        }
        if ($method === 'rating') {
            return (int)$value;
        }
        return (bool)$value;
    }

    /**
     * Calcular resultados preliminares (durante votação)
     *
     * @param int $taskid
     * @return object
     */
    public static function calculate_preliminary_results($taskid) {
        global $DB;

        $task = $DB->get_record('local_tubaron_tasks', ['id' => $taskid], '*', MUST_EXIST);
        $votes = $DB->get_records('local_tubaron_votes', ['taskid' => $taskid]);

        $results = new \stdClass();
        $results->taskid = $taskid;
        $results->method = $task->votingmethod;
        $results->votecount = count($votes);
        $results->timestamp = time();

        switch ($task->votingmethod) {
            case 'majority':
                $results->data = self::calculate_majority($votes);
                break;

            case 'rating':
                $results->data = self::calculate_rating($votes);
                break;

            case 'ranking':
                $results->data = self::calculate_ranking($votes);
                break;
        }

        return $results;
    }

    /**
     * Calcular resultado maioria simples
     *
     * @param array $votes
     * @return object
     */
    private static function calculate_majority($votes) {
        $approved = 0;
        $rejected = 0;

        foreach ($votes as $vote) {
            $value = self::deserialize_vote_value('majority', $vote->votevalue);
            if ($value) {
                $approved++;
            } else {
                $rejected++;
            }
        }

        $total = $approved + $rejected;
        $approval_rate = $total > 0 ? ($approved / $total) * 100 : 0;

        $result = new \stdClass();
        $result->approved = $approved;
        $result->rejected = $rejected;
        $result->total = $total;
        $result->approval_rate = $approval_rate;
        $result->status = $approval_rate > 50 ? 'approved' : 'rejected';

        return $result;
    }

    /**
     * Calcular resultado notas 0-10
     *
     * @param array $votes
     * @return object
     */
    private static function calculate_rating($votes) {
        $sum = 0;
        $count = 0;
        $distribution = array_fill(0, 11, 0); // 0-10

        foreach ($votes as $vote) {
            $value = self::deserialize_vote_value('rating', $vote->votevalue);
            $sum += $value;
            $count++;
            $distribution[$value]++;
        }

        $average = $count > 0 ? $sum / $count : 0;

        $result = new \stdClass();
        $result->average = round($average, 2);
        $result->total_votes = $count;
        $result->sum = $sum;
        $result->distribution = $distribution;
        $result->percentage = ($average / 10) * 100;

        return $result;
    }

    /**
     * Calcular resultado ranking top 3
     *
     * @param array $votes
     * @return object
     */
    private static function calculate_ranking($votes) {
        $scores = []; // submissionid => pontos

        // Peso: 1º=3pts, 2º=2pts, 3º=1pt
        $weights = [1 => 3, 2 => 2, 3 => 1];

        foreach ($votes as $vote) {
            $ranking = self::deserialize_vote_value('ranking', $vote->votevalue);
            
            foreach ($ranking as $position => $submissionid) {
                if (!isset($scores[$submissionid])) {
                    $scores[$submissionid] = 0;
                }
                $scores[$submissionid] += $weights[$position];
            }
        }

        // Ordenar por pontuação
        arsort($scores);

        $result = new \stdClass();
        $result->scores = $scores;
        $result->total_votes = count($votes);
        $result->ranking = array_keys($scores); // IDs ordenados
        $result->weights = $weights;

        return $result;
    }

    /**
     * Obter estatísticas de votação de uma tarefa
     *
     * @param int $taskid
     * @return object
     */
    public static function get_voting_stats($taskid) {
        global $DB;

        $task = $DB->get_record('local_tubaron_tasks', ['id' => $taskid], '*', MUST_EXIST);
        
        $stats = new \stdClass();
        $stats->taskid = $taskid;
        $stats->method = $task->votingmethod;
        $stats->status = $task->status;
        
        // Contar votos
        $stats->votes_received = $DB->count_records('local_tubaron_votes', ['taskid' => $taskid]);
        
        // Contar elegíveis
        $stats->eligible_voters = self::count_eligible_voters($taskid);
        
        // Taxa participação
        $stats->participation_rate = $stats->eligible_voters > 0 
            ? ($stats->votes_received / $stats->eligible_voters) * 100 
            : 0;
        
        // Resultado atual (se houver votos)
        if ($stats->votes_received > 0) {
            $stats->current_results = self::calculate_preliminary_results($taskid);
        }
        
        return $stats;
    }

    /**
     * Contar votantes elegíveis
     *
     * @param int $taskid
     * @return int
     */
    private static function count_eligible_voters($taskid) {
        global $DB;

        $task = $DB->get_record('local_tubaron_tasks', ['id' => $taskid], '*', MUST_EXIST);

        if ($task->type === 'competitive') {
            // Todos usuários ativos
            return $DB->count_records_select('user', 'deleted = ? AND suspended = ?', [0, 0]);
        }

        // Participantes da tarefa
        $count = $DB->count_records_sql(
            "SELECT COUNT(DISTINCT userid) FROM (
                SELECT t.creatorid as userid FROM {local_tubaron_tasks} t WHERE t.id = ?
                UNION
                SELECT ta.assigneeid as userid FROM {local_tubaron_task_assignments} ta WHERE ta.taskid = ?
                UNION
                SELECT tm.userid FROM {local_tubaron_task_assignments} ta
                JOIN {local_tubaron_team_members} tm ON tm.teamid = ta.assigneeid
                WHERE ta.taskid = ? AND ta.assigneetype = ?
            ) participants",
            [$taskid, $taskid, $taskid, 'team']
        );

        return $count;
    }
}

