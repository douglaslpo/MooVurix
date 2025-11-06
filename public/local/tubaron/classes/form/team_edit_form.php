<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Team Edit Form
 *
 * Formulário para criar/editar equipes com validação mínimo 3 membros
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
 * Team edit form class
 */
class team_edit_form extends \moodleform {

    /**
     * Define the form
     */
    public function definition() {
        $mform = $this->_form;
        $team = $this->_customdata['team'] ?? null;
        $seasonid = $this->_customdata['seasonid'] ?? 0;

        // Header
        $mform->addElement('header', 'teamdetails', get_string('teamdetails', 'local_tubaron'));

        // Nome da Equipe
        $mform->addElement('text', 'name', get_string('teamname', 'local_tubaron'), ['size' => 50]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 100), 'maxlength', 100, 'client');
        $mform->addHelpButton('name', 'teamname', 'local_tubaron');

        // Descrição
        $mform->addElement('textarea', 'description', get_string('description'), ['rows' => 4, 'cols' => 50]);
        $mform->setType('description', PARAM_TEXT);
        $mform->addHelpButton('description', 'description', 'local_tubaron');

        // Temporada
        $seasons = $this->get_active_seasons();
        if (empty($seasons)) {
            $mform->addElement('static', 'noseason', '', 
                \html_writer::div(
                    get_string('noactiveseasons', 'local_tubaron'),
                    'alert alert-warning'
                )
            );
        } else {
            $mform->addElement('select', 'seasonid', get_string('season', 'local_tubaron'), $seasons);
            $mform->addRule('seasonid', get_string('required'), 'required', null, 'client');
            $mform->addHelpButton('seasonid', 'season', 'local_tubaron');
            
            if ($seasonid > 0) {
                $mform->setDefault('seasonid', $seasonid);
            }
        }

        // Status
        $statusoptions = [
            'active' => get_string('active'),
            'inactive' => get_string('inactive'),
        ];
        $mform->addElement('select', 'status', get_string('status'), $statusoptions);
        $mform->setDefault('status', 'active');
        $mform->addHelpButton('status', 'teamstatus', 'local_tubaron');

        // Máximo de Membros
        $mform->addElement('text', 'maxmembers', get_string('maxmembers', 'local_tubaron'), ['size' => 10]);
        $mform->setType('maxmembers', PARAM_INT);
        $mform->setDefault('maxmembers', 10);
        $mform->addRule('maxmembers', get_string('required'), 'required', null, 'client');
        $mform->addRule('maxmembers', get_string('numeric', 'local_tubaron'), 'numeric', null, 'client');
        $mform->addHelpButton('maxmembers', 'maxmembers', 'local_tubaron');

        // Avatar URL (opcional)
        $mform->addElement('text', 'avatarurl', get_string('avatarurl', 'local_tubaron'), ['size' => 50]);
        $mform->setType('avatarurl', PARAM_URL);
        $mform->addHelpButton('avatarurl', 'avatarurl', 'local_tubaron');

        // --- Seção Membros ---
        $mform->addElement('header', 'membersheader', get_string('teammembers', 'local_tubaron'));

        // Líder da Equipe
        $users = $this->get_eligible_users();
        $mform->addElement('autocomplete', 'leaderid', get_string('teamleader', 'local_tubaron'), $users, [
            'placeholder' => get_string('selectleader', 'local_tubaron')
        ]);
        $mform->addRule('leaderid', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('leaderid', 'teamleader', 'local_tubaron');

        // Membros (mínimo 2 além do líder = 3 total)
        $mform->addElement('autocomplete', 'memberids', get_string('members', 'local_tubaron'), $users, [
            'multiple' => true,
            'placeholder' => get_string('selectmembers', 'local_tubaron'),
            'noselectionstring' => get_string('noselection', 'local_tubaron')
        ]);
        $mform->addRule('memberids', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('memberids', 'teammembers', 'local_tubaron');
        
        // Info mínimo 3 membros
        $mform->addElement('static', 'minmembersinfo', '', 
            \html_writer::div(
                '⚠️ ' . get_string('minmemberswarning', 'local_tubaron'),
                'alert alert-info'
            )
        );

        // --- Campos Hidden ---
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        // --- Action Buttons ---
        $this->add_action_buttons(true, get_string('saveteam', 'local_tubaron'));
    }

    /**
     * Validação customizada
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Validar mínimo 3 membros total (líder + 2 membros)
        $totalmembers = 1; // Líder
        if (!empty($data['memberids'])) {
            $totalmembers += count($data['memberids']);
        }

        if ($totalmembers < 3) {
            $errors['memberids'] = get_string('minmemberserror', 'local_tubaron', 3);
        }

        // Validar máximo de membros
        if (!empty($data['maxmembers']) && $totalmembers > $data['maxmembers']) {
            $errors['memberids'] = get_string('maxmemberserror', 'local_tubaron', $data['maxmembers']);
        }

        // Validar líder não está na lista de membros
        if (!empty($data['leaderid']) && !empty($data['memberids'])) {
            if (in_array($data['leaderid'], $data['memberids'])) {
                $errors['memberids'] = get_string('leaderduplicateerror', 'local_tubaron');
            }
        }

        // Validar nome único na temporada
        if (!empty($data['name']) && !empty($data['seasonid'])) {
            global $DB;
            $params = ['name' => $data['name'], 'seasonid' => $data['seasonid']];
            
            if (!empty($data['id'])) {
                // Editando - excluir próprio ID
                $sql = "SELECT id FROM {local_tubaron_teams} WHERE name = ? AND seasonid = ? AND id <> ?";
                $params[] = $data['id'];
                $exists = $DB->get_record_sql($sql, $params);
            } else {
                // Criando
                $exists = $DB->get_record('local_tubaron_teams', $params);
            }

            if ($exists) {
                $errors['name'] = get_string('teamnamexists', 'local_tubaron');
            }
        }

        return $errors;
    }

    /**
     * Get active seasons for dropdown
     */
    private function get_active_seasons() {
        global $DB;
        
        $seasons = $DB->get_records_select(
            'local_tubaron_seasons',
            'status = ?',
            ['active'],
            'startdate DESC'
        );

        $options = [];
        foreach ($seasons as $season) {
            $options[$season->id] = format_string($season->name) . ' (' . 
                userdate($season->startdate, get_string('strftimedateshort')) . ' - ' .
                userdate($season->enddate, get_string('strftimedateshort')) . ')';
        }

        return $options;
    }

    /**
     * Get eligible users for team
     */
    private function get_eligible_users() {
        global $DB;

        // Buscar todos usuários ativos do sistema com todos os campos necessários
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
}

