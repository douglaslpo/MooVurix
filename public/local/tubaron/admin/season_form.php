<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Season Edit Form
 *
 * @package    local_tubaron
 * @copyright  2025 Tubaron Telecomunicações
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form for creating/editing seasons
 */
class season_edit_form extends moodleform {

    /**
     * Define form
     */
    public function definition() {
        $mform = $this->_form;

        // Header
        $mform->addElement('header', 'generalheader', get_string('createseason', 'local_tubaron'));

        // Season name
        $mform->addElement('text', 'name', get_string('seasonname', 'local_tubaron'), ['size' => 60]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('name', 'seasonname', 'local_tubaron');

        // Start date
        $mform->addElement('date_time_selector', 'startdate', get_string('startdate', 'local_tubaron'));
        $mform->addRule('startdate', get_string('required'), 'required', null, 'client');
        $mform->setDefault('startdate', time());

        // End date
        $mform->addElement('date_time_selector', 'enddate', get_string('enddate', 'local_tubaron'));
        $mform->addRule('enddate', get_string('required'), 'required', null, 'client');
        $mform->setDefault('enddate', time() + (6 * 30 * 24 * 60 * 60)); // Default 6 months

        // Status
        $statuses = [
            'draft' => get_string('status_draft', 'local_tubaron'),
            'active' => get_string('status_active', 'local_tubaron'),
        ];
        $mform->addElement('select', 'status', get_string('seasonstatus', 'local_tubaron'), $statuses);
        $mform->setDefault('status', 'draft');

        // Rules (JSON) - Advanced
        $mform->addElement('header', 'rulesheader', get_string('seasonrules', 'local_tubaron'));
        $mform->setExpanded('rulesheader', false);

        $mform->addElement('text', 'individual_points', 'Pontos Tarefa Individual', ['size' => 10]);
        $mform->setType('individual_points', PARAM_INT);
        $mform->setDefault('individual_points', 10);

        $mform->addElement('text', 'team_points', 'Pontos Tarefa Equipe', ['size' => 10]);
        $mform->setType('team_points', PARAM_INT);
        $mform->setDefault('team_points', 20);

        $mform->addElement('text', 'competitive_first', 'Pontos 1º Lugar Competitiva', ['size' => 10]);
        $mform->setType('competitive_first', PARAM_INT);
        $mform->setDefault('competitive_first', 50);

        $mform->addElement('text', 'competitive_second', 'Pontos 2º Lugar Competitiva', ['size' => 10]);
        $mform->setType('competitive_second', PARAM_INT);
        $mform->setDefault('competitive_second', 30);

        $mform->addElement('text', 'competitive_third', 'Pontos 3º Lugar Competitiva', ['size' => 10]);
        $mform->setType('competitive_third', PARAM_INT);
        $mform->setDefault('competitive_third', 15);

        $mform->addElement('text', 'competitive_participation', 'Pontos Participação Competitiva', ['size' => 10]);
        $mform->setType('competitive_participation', PARAM_INT);
        $mform->setDefault('competitive_participation', 5);

        // Buttons
        $this->add_action_buttons();
    }

    /**
     * Validation
     *
     * @param array $data Form data
     * @param array $files Form files
     * @return array Errors
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Validate duration (6-12 months)
        if (!empty($data['startdate']) && !empty($data['enddate'])) {
            if (!\local_tubaron\season_manager::validate_duration($data['startdate'], $data['enddate'])) {
                $errors['enddate'] = get_string('seasonduration_error', 'local_tubaron');
            }

            // Start must be before end
            if ($data['startdate'] >= $data['enddate']) {
                $errors['enddate'] = 'Data fim deve ser posterior à data início';
            }
        }

        return $errors;
    }

    /**
     * Get data for form (when editing)
     *
     * @return object Form data
     */
    public function get_data() {
        $data = parent::get_data();

        if ($data) {
            // Build rules JSON from individual fields
            $data->rules = json_encode([
                'individual_points' => $data->individual_points,
                'team_points' => $data->team_points,
                'competitive_points' => [
                    '1' => $data->competitive_first,
                    '2' => $data->competitive_second,
                    '3' => $data->competitive_third,
                    'participation' => $data->competitive_participation
                ]
            ]);

            // Remove individual fields
            unset($data->individual_points);
            unset($data->team_points);
            unset($data->competitive_first);
            unset($data->competitive_second);
            unset($data->competitive_third);
            unset($data->competitive_participation);
        }

        return $data;
    }
}

