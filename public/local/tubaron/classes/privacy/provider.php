<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Privacy Provider (LGPD/GDPR)
 *
 * Implementa direitos de portabilidade e esquecimento
 * Conforme Lei Geral de Proteção de Dados (Brasil)
 *
 * @package    local_tubaron
 * @copyright  2025 Tubaron Telecomunicações
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tubaron\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\writer;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Provider Class
 *
 * Implementa interfaces LGPD/GDPR compliance
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Retorna metadados sobre dados pessoais armazenados
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        
        // Tabela tasks
        $collection->add_database_table('local_tubaron_tasks', [
            'createdby' => 'privacy:metadata:tasks:createdby',
            'title' => 'privacy:metadata:tasks:title',
            'description' => 'privacy:metadata:tasks:description',
            'timecreated' => 'privacy:metadata:tasks:timecreated',
        ], 'privacy:metadata:tasks');

        // Tabela submissions
        $collection->add_database_table('local_tubaron_submissions', [
            'userid' => 'privacy:metadata:submissions:userid',
            'taskid' => 'privacy:metadata:submissions:taskid',
            'submissiontext' => 'privacy:metadata:submissions:submissiontext',
            'status' => 'privacy:metadata:submissions:status',
            'timecreated' => 'privacy:metadata:submissions:timecreated',
        ], 'privacy:metadata:submissions');

        // Tabela votes
        $collection->add_database_table('local_tubaron_votes', [
            'voterid' => 'privacy:metadata:votes:voterid',
            'taskid' => 'privacy:metadata:votes:taskid',
            'votevalue' => 'privacy:metadata:votes:votevalue',
            'timecreated' => 'privacy:metadata:votes:timecreated',
        ], 'privacy:metadata:votes');

        // Tabela achievements
        $collection->add_database_table('local_tubaron_user_achievements', [
            'userid' => 'privacy:metadata:achievements:userid',
            'achievementid' => 'privacy:metadata:achievements:achievementid',
            'timeunlocked' => 'privacy:metadata:achievements:timeunlocked',
        ], 'privacy:metadata:achievements');

        // Tabela rankings
        $collection->add_database_table('local_tubaron_rankings', [
            'userid' => 'privacy:metadata:rankings:userid',
            'seasonid' => 'privacy:metadata:rankings:seasonid',
            'totalpoints' => 'privacy:metadata:rankings:totalpoints',
            'rank' => 'privacy:metadata:rankings:rank',
        ], 'privacy:metadata:rankings');

        // Tabela teams
        $collection->add_database_table('local_tubaron_team_members', [
            'userid' => 'privacy:metadata:teammembers:userid',
            'teamid' => 'privacy:metadata:teammembers:teamid',
            'role' => 'privacy:metadata:teammembers:role',
            'joineddate' => 'privacy:metadata:teammembers:joineddate',
        ], 'privacy:metadata:teammembers');

        return $collection;
    }

    /**
     * Obter contextos que contêm dados do usuário
     *
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        // Sistema context para dados locais
        $contextlist->add_system_context();

        return $contextlist;
    }

    /**
     * Exportar dados do usuário (LGPD Art. 18)
     *
     * @param approved_contextlist $contextlist
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;
        $context = \context_system::instance();

        // 1. Tasks criadas
        $tasks = $DB->get_records('local_tubaron_tasks', ['createdby' => $userid]);
        if ($tasks) {
            writer::with_context($context)->export_data(
                [get_string('privacy:path:tasks', 'local_tubaron')],
                (object)['tasks' => array_values($tasks)]
            );
        }

        // 2. Submissions
        $submissions = $DB->get_records('local_tubaron_submissions', ['userid' => $userid]);
        if ($submissions) {
            writer::with_context($context)->export_data(
                [get_string('privacy:path:submissions', 'local_tubaron')],
                (object)['submissions' => array_values($submissions)]
            );
        }

        // 3. Votos realizados
        $votes = $DB->get_records('local_tubaron_votes', ['voterid' => $userid]);
        if ($votes) {
            writer::with_context($context)->export_data(
                [get_string('privacy:path:votes', 'local_tubaron')],
                (object)['votes' => array_values($votes)]
            );
        }

        // 4. Achievements desbloqueados
        $achievements = $DB->get_records_sql(
            "SELECT ua.*, a.name, a.description
             FROM {local_tubaron_user_achievements} ua
             JOIN {local_tubaron_achievements} a ON a.id = ua.achievementid
             WHERE ua.userid = ?",
            [$userid]
        );
        if ($achievements) {
            writer::with_context($context)->export_data(
                [get_string('privacy:path:achievements', 'local_tubaron')],
                (object)['achievements' => array_values($achievements)]
            );
        }

        // 5. Rankings
        $rankings = $DB->get_records_sql(
            "SELECT r.*, s.seasonname
             FROM {local_tubaron_rankings} r
             JOIN {local_tubaron_seasons} s ON s.id = r.seasonid
             WHERE r.userid = ?",
            [$userid]
        );
        if ($rankings) {
            writer::with_context($context)->export_data(
                [get_string('privacy:path:rankings', 'local_tubaron')],
                (object)['rankings' => array_values($rankings)]
            );
        }

        // 6. Equipes
        $teams = $DB->get_records_sql(
            "SELECT tm.*, t.name as teamname
             FROM {local_tubaron_team_members} tm
             JOIN {local_tubaron_teams} t ON t.id = tm.teamid
             WHERE tm.userid = ?",
            [$userid]
        );
        if ($teams) {
            writer::with_context($context)->export_data(
                [get_string('privacy:path:teams', 'local_tubaron')],
                (object)['teams' => array_values($teams)]
            );
        }
    }

    /**
     * Deletar dados do usuário (LGPD Art. 16 - Direito ao Esquecimento)
     *
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;

        // LGPD: Anonimizar ao invés de deletar para manter integridade
        
        // 1. Anonimizar tasks criadas
        $DB->set_field('local_tubaron_tasks', 'createdby', 0, ['createdby' => $userid]);
        $DB->set_field('local_tubaron_tasks', 'title', 'Deleted User Task', ['createdby' => $userid]);
        $DB->set_field('local_tubaron_tasks', 'description', '', ['createdby' => $userid]);

        // 2. Deletar submissions (conteúdo pessoal)
        $DB->delete_records('local_tubaron_submissions', ['userid' => $userid]);

        // 3. Manter votos mas anonimizar (importante para estatísticas)
        // Não deletar para manter integridade das votações
        
        // 4. Deletar achievements pessoais
        $DB->delete_records('local_tubaron_user_achievements', ['userid' => $userid]);

        // 5. Deletar rankings (dados pessoais)
        $DB->delete_records('local_tubaron_rankings', ['userid' => $userid]);

        // 6. Remover de equipes
        $DB->delete_records('local_tubaron_team_members', ['userid' => $userid]);

        // 7. Log da anonimização (LGPD compliance)
        local_tubaron_log_action('user_data_deleted', 'user', $userid, [
            'reason' => 'LGPD Art. 16 - Right to be forgotten',
            'timestamp' => time()
        ]);
    }

    /**
     * Deletar dados de múltiplos usuários
     *
     * @param approved_userlist $userlist
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();
        if (!$context instanceof \context_system) {
            return;
        }

        $userids = $userlist->get_userids();

        foreach ($userids as $userid) {
            $contextlist = new approved_contextlist(
                \core_user::get_user($userid),
                'local_tubaron',
                [$context->id]
            );
            self::delete_data_for_user($contextlist);
        }
    }

    /**
     * Obter usuários em um contexto
     *
     * @param userlist $userlist
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        
        if (!$context instanceof \context_system) {
            return;
        }

        // Adicionar usuários com dados no sistema
        $sql = "SELECT DISTINCT createdby as userid
                FROM {local_tubaron_tasks}
                WHERE createdby > 0";
        $userlist->add_from_sql('userid', $sql, []);

        $sql = "SELECT DISTINCT userid
                FROM {local_tubaron_submissions}";
        $userlist->add_from_sql('userid', $sql, []);

        $sql = "SELECT DISTINCT voterid as userid
                FROM {local_tubaron_votes}";
        $userlist->add_from_sql('userid', $sql, []);
    }
}

