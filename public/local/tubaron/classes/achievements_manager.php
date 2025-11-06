<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Achievements Manager
 *
 * Gerencia sistema de conquistas com unlock automático
 * Integrado ao MooVurix LMS Platform
 *
 * @package    local_tubaron
 * @copyright  2025 Tubaron Telecomunicações
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tubaron;

defined('MOODLE_INTERNAL') || die();

/**
 * Achievements Manager Class
 *
 * Sistema de conquistas com regras automáticas e badges
 */
class achievements_manager {

    /**
     * Verificar e desbloquear achievements para um usuário
     *
     * @param int $userid
     * @param string $trigger Gatilho (task_completed, vote_cast, etc)
     * @param array $data Dados contextuais
     * @return array Achievements desbloqueados
     */
    public static function check_and_unlock($userid, $trigger, $data = []) {
        global $DB;

        $unlocked = [];

        // Buscar achievements elegíveis para este trigger
        $achievements = $DB->get_records('local_tubaron_achievements', [
            'triggertype' => $trigger,
            'active' => 1
        ]);

        foreach ($achievements as $achievement) {
            // Verificar se já possui
            if (self::user_has_achievement($userid, $achievement->id)) {
                continue;
            }

            // Verificar regra
            if (self::check_achievement_rule($userid, $achievement, $data)) {
                self::unlock_achievement($userid, $achievement->id);
                $unlocked[] = $achievement;
            }
        }

        return $unlocked;
    }

    /**
     * Verificar se usuário possui achievement
     *
     * @param int $userid
     * @param int $achievementid
     * @return bool
     */
    public static function user_has_achievement($userid, $achievementid) {
        global $DB;
        
        return $DB->record_exists('local_tubaron_user_achievements', [
            'userid' => $userid,
            'achievementid' => $achievementid
        ]);
    }

    /**
     * Verificar regra de achievement
     *
     * @param int $userid
     * @param object $achievement
     * @param array $data
     * @return bool
     */
    private static function check_achievement_rule($userid, $achievement, $data) {
        global $DB;

        $rules = json_decode($achievement->criteriarules, true);
        if (!$rules) {
            return false;
        }

        switch ($achievement->criteriatype) {
            case 'task_count':
                // Completar N tarefas
                $count = $DB->count_records_sql(
                    "SELECT COUNT(DISTINCT t.id)
                     FROM {local_tubaron_tasks} t
                     JOIN {local_tubaron_submissions} s ON s.taskid = t.id
                     WHERE s.userid = ? AND s.status = ?",
                    [$userid, 'approved']
                );
                return $count >= ($rules['count'] ?? 0);

            case 'vote_count':
                // Realizar N votos
                $count = $DB->count_records('local_tubaron_votes', ['voterid' => $userid]);
                return $count >= ($rules['count'] ?? 0);

            case 'perfect_score':
                // Receber nota perfeita
                if (isset($data['voting_result']) && isset($data['voting_result']->data)) {
                    $result = $data['voting_result']->data;
                    if (isset($result->average)) {
                        return $result->average >= 10;
                    }
                    if (isset($result->approval_rate)) {
                        return $result->approval_rate >= 100;
                    }
                }
                return false;

            case 'streak':
                // Sequência de N tarefas
                $streak = $DB->get_record('local_tubaron_streaks', [
                    'userid' => $userid,
                    'streaktype' => 'task_completion'
                ]);
                return $streak && $streak->currentcount >= ($rules['count'] ?? 0);

            case 'first_submission':
                // Primeira submissão aprovada da tarefa
                if (isset($data['taskid'])) {
                    $count = $DB->count_records_select(
                        'local_tubaron_submissions',
                        'taskid = ? AND status = ?',
                        [$data['taskid'], 'approved']
                    );
                    return $count === 1;
                }
                return false;

            case 'team_leader':
                // Ser líder de equipe
                $isleader = $DB->record_exists('local_tubaron_team_members', [
                    'userid' => $userid,
                    'role' => 'leader',
                    'status' => 'active'
                ]);
                return $isleader;

            case 'quality_average':
                // Manter média ≥ X em N tarefas
                $mincount = $rules['task_count'] ?? 20;
                $minaverage = $rules['min_average'] ?? 9;
                
                $stats = $DB->get_record_sql(
                    "SELECT COUNT(v.id) as votecount, AVG(CAST(v.votevalue AS INTEGER)) as average
                     FROM {local_tubaron_votes} v
                     JOIN {local_tubaron_tasks} t ON t.id = v.taskid
                     JOIN {local_tubaron_submissions} s ON s.taskid = t.id
                     WHERE s.userid = ? AND t.votingmethod = ?",
                    [$userid, 'rating']
                );
                
                return $stats && $stats->votecount >= $mincount && $stats->average >= $minaverage;

            default:
                return false;
        }
    }

    /**
     * Desbloquear achievement
     *
     * @param int $userid
     * @param int $achievementid
     * @return int|bool User achievement ID
     */
    private static function unlock_achievement($userid, $achievementid) {
        global $DB;

        $userachievement = new \stdClass();
        $userachievement->userid = $userid;
        $userachievement->achievementid = $achievementid;
        $userachievement->timeunlocked = time();

        $id = $DB->insert_record('local_tubaron_user_achievements', $userachievement);

        // Enviar notificação
        self::send_achievement_notification($userid, $achievementid);

        // Audit log
        local_tubaron_log_action('achievement_unlocked', 'achievement', $achievementid, [
            'userid' => $userid
        ]);

        return $id;
    }

    /**
     * Enviar notificação achievement unlock
     *
     * @param int $userid
     * @param int $achievementid
     */
    private static function send_achievement_notification($userid, $achievementid) {
        global $DB;

        $achievement = $DB->get_record('local_tubaron_achievements', ['id' => $achievementid]);
        $user = $DB->get_record('user', ['id' => $userid]);

        if (!$achievement || !$user) {
            return;
        }

        $message = new \core\message\message();
        $message->component = 'local_tubaron';
        $message->name = 'achievement_unlocked';
        $message->userfrom = \core_user::get_noreply_user();
        $message->userto = $user;
        $message->subject = get_string('achievementunlocked', 'local_tubaron');
        $message->fullmessage = get_string('achievementunlockedmessage', 'local_tubaron', [
            'name' => $achievement->name,
            'description' => $achievement->description
        ]);
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml = '<h3>🏆 ' . $achievement->name . '</h3><p>' . $achievement->description . '</p>';
        $message->smallmessage = '🏆 ' . $achievement->name;
        $message->notification = 1;

        message_send($message);
    }

    /**
     * Obter progresso de um achievement
     *
     * @param int $userid
     * @param int $achievementid
     * @return object Progress data
     */
    public static function get_achievement_progress($userid, $achievementid) {
        global $DB;

        $achievement = $DB->get_record('local_tubaron_achievements', ['id' => $achievementid]);
        
        $progress = new \stdClass();
        $progress->achievementid = $achievementid;
        $progress->unlocked = self::user_has_achievement($userid, $achievementid);
        $progress->current = 0;
        $progress->required = 0;
        $progress->percentage = 0;

        if ($progress->unlocked) {
            $progress->percentage = 100;
            return $progress;
        }

        $rules = json_decode($achievement->criteriarules, true);
        if (!$rules) {
            return $progress;
        }

        switch ($achievement->criteriatype) {
            case 'task_count':
            case 'vote_count':
                $tablename = $achievement->criteriatype === 'task_count' 
                    ? 'local_tubaron_submissions' 
                    : 'local_tubaron_votes';
                $field = $achievement->criteriatype === 'task_count' ? 'userid' : 'voterid';
                
                $progress->current = $DB->count_records($tablename, [$field => $userid]);
                $progress->required = $rules['count'] ?? 0;
                break;

            case 'streak':
                $streak = $DB->get_record('local_tubaron_streaks', [
                    'userid' => $userid,
                    'streaktype' => 'task_completion'
                ]);
                $progress->current = $streak ? $streak->currentcount : 0;
                $progress->required = $rules['count'] ?? 0;
                break;
        }

        if ($progress->required > 0) {
            $progress->percentage = min(100, ($progress->current / $progress->required) * 100);
        }

        return $progress;
    }

    /**
     * Obter todos achievements do usuário
     *
     * @param int $userid
     * @return array
     */
    public static function get_user_achievements($userid) {
        global $DB;

        $sql = "SELECT a.*, ua.timeunlocked
                FROM {local_tubaron_achievements} a
                LEFT JOIN {local_tubaron_user_achievements} ua 
                    ON ua.achievementid = a.id AND ua.userid = ?
                WHERE a.active = ?
                ORDER BY ua.timeunlocked DESC NULLS LAST, a.displayorder ASC";

        $achievements = $DB->get_records_sql($sql, [$userid, 1]);

        $result = [];
        foreach ($achievements as $achievement) {
            $achievement->unlocked = !empty($achievement->timeunlocked);
            $achievement->progress = self::get_achievement_progress($userid, $achievement->id);
            $result[] = $achievement;
        }

        return $result;
    }
}

