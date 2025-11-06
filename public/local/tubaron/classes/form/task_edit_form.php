<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Task Edit Form
 *
 * Formulário dinâmico para criar/editar tarefas (3 tipos: individual/team/competitive)
 * Integrado ao MooVurix LMS Platform
 *
 * @package    local_tubaron
 * @copyright  2025 Tubaron Telecomunicações
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tubaron\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Task edit form class (formulário dinâmico para 3 tipos)
 */
class task_edit_form extends \moodleform {

    /**
     * Define the form
     */
    public function definition() {
        $mform = $this->_form;
        $task = $this->_customdata['task'] ?? null;
        $missionid = $this->_customdata['missionid'] ?? 0;

        // === BASIC DETAILS ===
        $mform->addElement('header', 'taskdetails', get_string('taskdetails', 'local_tubaron'));

        // Tipo de Tarefa (altera campos disponíveis)
        $typeoptions = [
            'individual' => get_string('type_individual', 'local_tubaron'),
            'team' => get_string('type_team', 'local_tubaron'),
            'competitive' => get_string('type_competitive', 'local_tubaron'),
        ];
        $mform->addElement('select', 'type', get_string('tasktype', 'local_tubaron'), $typeoptions);
        $mform->addHelpButton('type', 'tasktype', 'local_tubaron');

        // Título
        $mform->addElement('text', 'title', get_string('tasktitle', 'local_tubaron'), ['size' => 60]);
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', get_string('required'), 'required', null, 'client');
        $mform->addRule('title', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('title', 'tasktitle', 'local_tubaron');

        // Descrição
        $mform->addElement('editor', 'description_editor', get_string('description'), ['rows' => 8]);
        $mform->setType('description_editor', PARAM_RAW);
        $mform->addHelpButton('description_editor', 'taskdescription', 'local_tubaron');

        // Missão (agrupamento)
        $missions = $this->get_active_missions();
        if (empty($missions)) {
            $mform->addElement('static', 'nomission', '', 
                \html_writer::div(
                    get_string('noactivemissions', 'local_tubaron'),
                    'alert alert-warning'
                )
            );
        } else {
            $mform->addElement('select', 'missionid', get_string('mission', 'local_tubaron'), $missions);
            $mform->addRule('missionid', get_string('required'), 'required', null, 'client');
            $mform->addHelpButton('missionid', 'mission', 'local_tubaron');
            
            if ($missionid > 0) {
                $mform->setDefault('missionid', $missionid);
            }
        }

        // Pontos
        $mform->addElement('text', 'points', get_string('taskpoints', 'local_tubaron'), ['size' => 10]);
        $mform->setType('points', PARAM_INT);
        $mform->setDefault('points', 100);
        $mform->addRule('points', get_string('required'), 'required', null, 'client');
        $mform->addRule('points', get_string('numeric', 'local_tubaron'), 'numeric', null, 'client');
        $mform->addHelpButton('points', 'taskpoints', 'local_tubaron');

        // Deadline
        $mform->addElement('date_time_selector', 'deadline', get_string('deadline', 'local_tubaron'), ['optional' => true]);
        $mform->addHelpButton('deadline', 'deadline', 'local_tubaron');

        // === VOTING CONFIG ===
        $mform->addElement('header', 'votingconfig', get_string('votingconfiguration', 'local_tubaron'));

        // Método de Votação
        $votingmethods = [
            'majority' => get_string('method_majority', 'local_tubaron'),
            'rating' => get_string('method_rating', 'local_tubaron'),
            'ranking' => get_string('method_ranking', 'local_tubaron'),
        ];
        $mform->addElement('select', 'votingmethod', get_string('votingmethod', 'local_tubaron'), $votingmethods);
        $mform->setDefault('votingmethod', 'rating');
        $mform->addHelpButton('votingmethod', 'votingmethod', 'local_tubaron');

        // Critérios de Aprovação
        $mform->addElement('textarea', 'approvalcriteria', get_string('approvalcriteria', 'local_tubaron'), ['rows' => 4]);
        $mform->setType('approvalcriteria', PARAM_TEXT);
        $mform->addHelpButton('approvalcriteria', 'approvalcriteria', 'local_tubaron');

        // Votação Deadline
        $mform->addElement('date_time_selector', 'votingdeadline', get_string('votingdeadline', 'local_tubaron'), ['optional' => true]);
        $mform->addHelpButton('votingdeadline', 'votingdeadline', 'local_tubaron');

        // === ASSIGNMENTS (depende do tipo) ===
        $mform->addElement('header', 'assignments', get_string('taskassignments', 'local_tubaron'));

        // Individual: atribuir para usuário específico
        $users = $this->get_eligible_users();
        $mform->addElement('autocomplete', 'assignuser', get_string('assigntouser', 'local_tubaron'), $users, [
            'placeholder' => get_string('selectuser', 'local_tubaron')
        ]);
        $mform->hideIf('assignuser', 'type', 'neq', 'individual');
        $mform->addHelpButton('assignuser', 'assigntouser', 'local_tubaron');

        // Team: atribuir para equipe específica
        $teams = $this->get_active_teams();
        $mform->addElement('autocomplete', 'assignteam', get_string('assigntoteam', 'local_tubaron'), $teams, [
            'placeholder' => get_string('selectteam', 'local_tubaron')
        ]);
        $mform->hideIf('assignteam', 'type', 'neq', 'team');
        $mform->addHelpButton('assignteam', 'assigntoteam', 'local_tubaron');

        // Competitive: múltiplas atribuições
        $mform->addElement('autocomplete', 'assignmultiple', get_string('assignmultiple', 'local_tubaron'), 
            array_merge(['teams' => get_string('teams', 'local_tubaron')] + $teams, ['users' => get_string('users')] + $users), 
            [
                'multiple' => true,
                'placeholder' => get_string('selectmultiple', 'local_tubaron')
            ]
        );
        $mform->hideIf('assignmultiple', 'type', 'neq', 'competitive');
        $mform->addHelpButton('assignmultiple', 'assignmultiple', 'local_tubaron');

        // === CAMPOS HIDDEN ===
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        // === ACTION BUTTONS ===
        $this->add_action_buttons(true, get_string('savetask', 'local_tubaron'));
    }

    /**
     * Validação customizada
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Validar atribuição conforme tipo
        if ($data['type'] === 'individual' && empty($data['assignuser'])) {
            $errors['assignuser'] = get_string('assignuserrequired', 'local_tubaron');
        }

        if ($data['type'] === 'team' && empty($data['assignteam'])) {
            $errors['assignteam'] = get_string('assignteamrequired', 'local_tubaron');
        }

        if ($data['type'] === 'competitive' && empty($data['assignmultiple'])) {
            $errors['assignmultiple'] = get_string('assignmultiplerequired', 'local_tubaron');
        }

        // Validar deadline > votingdeadline
        if (!empty($data['deadline']) && !empty($data['votingdeadline'])) {
            if ($data['votingdeadline'] <= $data['deadline']) {
                $errors['votingdeadline'] = get_string('votingdeadlineaftererror', 'local_tubaron');
            }
        }

        // Validar pontos > 0
        if (!empty($data['points']) && $data['points'] <= 0) {
            $errors['points'] = get_string('pointspositiveerror', 'local_tubaron');
        }

        return $errors;
    }

    /**
     * Get active missions
     */
    private function get_active_missions() {
        global $DB;
        
        $season = local_tubaron_get_active_season();
        if (!$season) {
            return [];
        }

        $missions = $DB->get_records('local_tubaron_missions', ['seasonid' => $season->id], 'name ASC');

        $options = [];
        foreach ($missions as $mission) {
            $options[$mission->id] = format_string($mission->name) . ' (peso: ' . $mission->weight . 'x)';
        }

        return $options;
    }

    /**
     * Get eligible users
     */
    private function get_eligible_users() {
        global $DB;

        $users = $DB->get_records_select(
            'user',
            'deleted = ? AND suspended = ?',
            [0, 0],
            'lastname, firstname',
            'id, firstname, lastname, firstnamephonetic, lastnamephonetic, middlename, alternatename, email'
        );

        $options = [];
        foreach ($users as $user) {
            $options[$user->id] = fullname($user) . ' (' . $user->email . ')';
        }

        return $options;
    }

    /**
     * Get active teams
     */
    private function get_active_teams() {
        global $DB;

        $season = local_tubaron_get_active_season();
        if (!$season) {
            return [];
        }

        $teams = $DB->get_records('local_tubaron_teams', [
            'seasonid' => $season->id,
            'status' => 'active'
        ], 'name ASC');

        $options = [];
        foreach ($teams as $team) {
            $membercount = $DB->count_records('local_tubaron_team_members', [
                'teamid' => $team->id,
                'status' => 'active'
            ]);
            $options[$team->id] = format_string($team->name) . ' (' . $membercount . ' membros)';
        }

        return $options;
    }
}

