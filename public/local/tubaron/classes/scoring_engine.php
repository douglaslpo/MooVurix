<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Scoring Engine
 *
 * Calcula pontos finais com bônus, penalidades e atualiza rankings
 * Integrado ao MooVurix LMS Platform
 *
 * @package    local_tubaron
 * @copyright  2025 Tubaron Telecomunicações
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tubaron;

defined('MOODLE_INTERNAL') || die();

/**
 * Scoring Engine Class
 *
 * Fórmula: Pontos Finais = (Pontos Base * Votação%) + Bônus - Penalidades
 */
class scoring_engine {

    /** Bônus */
    const BONUS_FIRST_COMPLETE = 0.20;    // +20% primeira submissão aprovada
    const BONUS_PERFECT_SCORE = 0.15;     // +15% nota 10/10 ou 100% aprovação
    const BONUS_STREAK_3 = 0.10;          // +10% 3 tarefas seguidas
    const BONUS_STREAK_5 = 0.20;          // +20% 5 tarefas seguidas
    const BONUS_EARLY_SUBMIT = 0.10;      // +10% antes de 50% deadline
    const BONUS_TEAM_COMPLETE = 0.15;     // +15% todos membros contribuíram

    /** Penalidades */
    const PENALTY_LATE_SUBMIT = 0.20;     // -20% depois deadline
    const PENALTY_REJECTED = 0.50;        // -50% maioria rejeitou
    const PENALTY_LOW_QUALITY = 0.30;     // -30% nota < 5/10
    const PENALTY_INCOMPLETE = 0.40;      // -40% critérios não atendidos

    /**
     * Calcular pontos finais de uma tarefa após votação
     *
     * @param int $taskid
     * @param object $votingresults Resultados da votação
     * @return object Score details
     */
    public static function calculate_final_score($taskid, $votingresults) {
        global $DB;

        $task = $DB->get_record('local_tubaron_tasks', ['id' => $taskid], '*', MUST_EXIST);
        $submission = $DB->get_record('local_tubaron_submissions', ['taskid' => $taskid], '*', IGNORE_MISSING);

        $score = new \stdClass();
        $score->taskid = $taskid;
        $score->base_points = $task->points;
        $score->voting_percentage = 0;
        $score->bonuses = [];
        $score->penalties = [];
        $score->bonus_total = 0;
        $score->penalty_total = 0;
        $score->final_points = 0;

        // Calcular percentual votação
        switch ($task->votingmethod) {
            case 'majority':
                $score->voting_percentage = $votingresults->data->status === 'approved' ? 100 : 0;
                break;

            case 'rating':
                $score->voting_percentage = $votingresults->data->percentage;
                break;

            case 'ranking':
                // Para ranking, percentual depende da posição
                // 1º = 100%, 2º = 85%, 3º = 70%, demais = 50%
                $position = array_search($submission->id ?? 0, $votingresults->data->ranking) + 1;
                $percentages = [1 => 100, 2 => 85, 3 => 70];
                $score->voting_percentage = $percentages[$position] ?? 50;
                break;
        }

        // Pontos base com votação
        $base_with_voting = ($score->base_points * $score->voting_percentage) / 100;

        // === BÔNUS ===

        // 1. Primeira submissão aprovada
        if (self::is_first_submission($taskid) && $score->voting_percentage >= 70) {
            $bonus = $base_with_voting * self::BONUS_FIRST_COMPLETE;
            $score->bonuses[] = [
                'type' => 'first_complete',
                'description' => get_string('bonus_first_complete', 'local_tubaron'),
                'value' => $bonus,
                'percentage' => self::BONUS_FIRST_COMPLETE * 100
            ];
            $score->bonus_total += $bonus;
        }

        // 2. Perfect score
        if (($task->votingmethod === 'rating' && $votingresults->data->average >= 10) ||
            ($task->votingmethod === 'majority' && $votingresults->data->approval_rate >= 100)) {
            $bonus = $base_with_voting * self::BONUS_PERFECT_SCORE;
            $score->bonuses[] = [
                'type' => 'perfect_score',
                'description' => get_string('bonus_perfect_score', 'local_tubaron'),
                'value' => $bonus,
                'percentage' => self::BONUS_PERFECT_SCORE * 100
            ];
            $score->bonus_total += $bonus;
        }

        // 3. Streak
        if ($submission) {
            $streak = self::get_user_streak($submission->userid);
            if ($streak >= 5) {
                $bonus = $base_with_voting * self::BONUS_STREAK_5;
                $score->bonuses[] = [
                    'type' => 'streak_5',
                    'description' => get_string('bonus_streak_5', 'local_tubaron'),
                    'value' => $bonus,
                    'percentage' => self::BONUS_STREAK_5 * 100
                ];
                $score->bonus_total += $bonus;
            } else if ($streak >= 3) {
                $bonus = $base_with_voting * self::BONUS_STREAK_3;
                $score->bonuses[] = [
                    'type' => 'streak_3',
                    'description' => get_string('bonus_streak_3', 'local_tubaron'),
                    'value' => $bonus,
                    'percentage' => self::BONUS_STREAK_3 * 100
                ];
                $score->bonus_total += $bonus;
            }
        }

        // 4. Early submit
        if ($submission && $task->deadline > 0) {
            $submittime = $submission->timecreated;
            $taskstart = $task->timecreated;
            $taskdeadline = $task->deadline;
            $midpoint = $taskstart + (($taskdeadline - $taskstart) / 2);
            
            if ($submittime < $midpoint) {
                $bonus = $base_with_voting * self::BONUS_EARLY_SUBMIT;
                $score->bonuses[] = [
                    'type' => 'early_submit',
                    'description' => get_string('bonus_early_submit', 'local_tubaron'),
                    'value' => $bonus,
                    'percentage' => self::BONUS_EARLY_SUBMIT * 100
                ];
                $score->bonus_total += $bonus;
            }
        }

        // === PENALIDADES ===

        // 1. Late submit
        if ($submission && $task->deadline > 0 && $submission->timecreated > $task->deadline) {
            $penalty = $base_with_voting * self::PENALTY_LATE_SUBMIT;
            $score->penalties[] = [
                'type' => 'late_submit',
                'description' => get_string('penalty_late_submit', 'local_tubaron'),
                'value' => $penalty,
                'percentage' => self::PENALTY_LATE_SUBMIT * 100
            ];
            $score->penalty_total += $penalty;
        }

        // 2. Rejected
        if ($task->votingmethod === 'majority' && $votingresults->data->status === 'rejected') {
            $penalty = $base_with_voting * self::PENALTY_REJECTED;
            $score->penalties[] = [
                'type' => 'rejected',
                'description' => get_string('penalty_rejected', 'local_tubaron'),
                'value' => $penalty,
                'percentage' => self::PENALTY_REJECTED * 100
            ];
            $score->penalty_total += $penalty;
        }

        // 3. Low quality
        if ($task->votingmethod === 'rating' && $votingresults->data->average < 5) {
            $penalty = $base_with_voting * self::PENALTY_LOW_QUALITY;
            $score->penalties[] = [
                'type' => 'low_quality',
                'description' => get_string('penalty_low_quality', 'local_tubaron'),
                'value' => $penalty,
                'percentage' => self::PENALTY_LOW_QUALITY * 100
            ];
            $score->penalty_total += $penalty;
        }

        // Cálculo final
        $score->final_points = max(0, $base_with_voting + $score->bonus_total - $score->penalty_total);
        $score->calculated_at = time();

        return $score;
    }

    /**
     * Aplicar pontuação e atualizar rankings
     *
     * @param int $taskid
     * @param object $scoredetails
     * @return bool
     */
    public static function apply_score_to_task($taskid, $scoredetails) {
        global $DB;

        try {
            $transaction = $DB->start_delegated_transaction();

            $task = $DB->get_record('local_tubaron_tasks', ['id' => $taskid], '*', MUST_EXIST);
            
            // Determinar quem recebe os pontos
            $recipients = self::get_score_recipients($task);

            foreach ($recipients as $recipient) {
                // Atualizar ou criar registro de pontuação
                $scorerecord = $DB->get_record('local_tubaron_scores', [
                    'seasonid' => $recipient['seasonid'],
                    'entitytype' => $recipient['type'],
                    'entityid' => $recipient['id']
                ]);

                if ($scorerecord) {
                    // Atualizar existente
                    $scorerecord->totalpoints += $scoredetails->final_points;
                    $scorerecord->taskscount++;
                    $scorerecord->lastupdated = time();
                    $DB->update_record('local_tubaron_scores', $scorerecord);
                } else {
                    // Criar novo
                    $scorerecord = new \stdClass();
                    $scorerecord->seasonid = $recipient['seasonid'];
                    $scorerecord->entitytype = $recipient['type'];
                    $scorerecord->entityid = $recipient['id'];
                    $scorerecord->totalpoints = $scoredetails->final_points;
                    $scorerecord->taskscount = 1;
                    $scorerecord->lastupdated = time();
                    $DB->insert_record('local_tubaron_scores', $scorerecord);
                }
            }

            // Atualizar rankings
            self::update_rankings($task->missionid);

            // Audit log
            local_tubaron_log_action('score_applied', 'task', $taskid, [
                'points' => $scoredetails->final_points,
                'recipients' => count($recipients)
            ]);

            $transaction->allow_commit();
            return true;

        } catch (\Exception $e) {
            $transaction->rollback($e);
            debugging('Error applying score: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualizar rankings de uma temporada
     *
     * @param int $missionid
     * @return bool
     */
    private static function update_rankings($missionid) {
        global $DB;

        $mission = $DB->get_record('local_tubaron_missions', ['id' => $missionid]);

        // Atualizar rank users
        $DB->execute("
            WITH ranked_users AS (
                SELECT id, entityid, 
                       ROW_NUMBER() OVER (ORDER BY totalpoints DESC) as newrank
                FROM {local_tubaron_scores}
                WHERE seasonid = ? AND entitytype = ?
            )
            UPDATE {local_tubaron_scores} s
            SET rank = r.newrank
            FROM ranked_users r
            WHERE s.id = r.id
        ", [$mission->seasonid, 'user']);

        // Atualizar rank teams
        $DB->execute("
            WITH ranked_teams AS (
                SELECT id, entityid,
                       ROW_NUMBER() OVER (ORDER BY totalpoints DESC) as newrank
                FROM {local_tubaron_scores}
                WHERE seasonid = ? AND entitytype = ?
            )
            UPDATE {local_tubaron_scores} s
            SET rank = r.newrank
            FROM ranked_teams r
            WHERE s.id = r.id
        ", [$mission->seasonid, 'team']);

        return true;
    }

    /**
     * Obter destinatários da pontuação
     *
     * @param object $task
     * @return array
     */
    private static function get_score_recipients($task) {
        global $DB;

        $recipients = [];
        $mission = $DB->get_record('local_tubaron_missions', ['id' => $task->missionid]);

        // Buscar atribuições
        $assignments = $DB->get_records('local_tubaron_task_assignments', ['taskid' => $task->id]);

        foreach ($assignments as $assignment) {
            if ($assignment->assigneetype === 'user') {
                $recipients[] = [
                    'type' => 'user',
                    'id' => $assignment->assigneeid,
                    'seasonid' => $mission->seasonid
                ];
            } else if ($assignment->assigneetype === 'team') {
                // Pontos para equipe
                $recipients[] = [
                    'type' => 'team',
                    'id' => $assignment->assigneeid,
                    'seasonid' => $mission->seasonid
                ];
                
                // Pontos individuais para membros
                $members = $DB->get_records('local_tubaron_team_members', [
                    'teamid' => $assignment->assigneeid,
                    'status' => 'active'
                ]);
                
                foreach ($members as $member) {
                    $recipients[] = [
                        'type' => 'user',
                        'id' => $member->userid,
                        'seasonid' => $mission->seasonid
                    ];
                }
            }
        }

        return $recipients;
    }

    /**
     * Verificar se é primeira submissão aprovada da tarefa
     *
     * @param int $taskid
     * @return bool
     */
    private static function is_first_submission($taskid) {
        global $DB;

        $count = $DB->count_records_select(
            'local_tubaron_submissions',
            'taskid = ? AND status = ?',
            [$taskid, 'approved']
        );

        return ($count === 1);
    }

    /**
     * Obter streak atual do usuário
     *
     * @param int $userid
     * @return int
     */
    private static function get_user_streak($userid) {
        global $DB;

        $streak = $DB->get_record('local_tubaron_streaks', [
            'userid' => $userid,
            'streaktype' => 'task_completion'
        ]);

        return $streak ? $streak->currentcount : 0;
    }

    /**
     * Atualizar streak do usuário
     *
     * @param int $userid
     * @param bool $success Tarefa foi aprovada?
     * @return int Novo streak count
     */
    public static function update_user_streak($userid, $success) {
        global $DB;

        $streak = $DB->get_record('local_tubaron_streaks', [
            'userid' => $userid,
            'streaktype' => 'task_completion'
        ]);

        if (!$streak) {
            // Criar novo streak
            $streak = new \stdClass();
            $streak->userid = $userid;
            $streak->streaktype = 'task_completion';
            $streak->currentcount = $success ? 1 : 0;
            $streak->maxcount = $success ? 1 : 0;
            $streak->lastactivity = time();
            $DB->insert_record('local_tubaron_streaks', $streak);
            return $streak->currentcount;
        }

        if ($success) {
            // Incrementar streak
            $streak->currentcount++;
            if ($streak->currentcount > $streak->maxcount) {
                $streak->maxcount = $streak->currentcount;
            }
        } else {
            // Resetar streak
            $streak->currentcount = 0;
        }

        $streak->lastactivity = time();
        $DB->update_record('local_tubaron_streaks', $streak);

        return $streak->currentcount;
    }
}

