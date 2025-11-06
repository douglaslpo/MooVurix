<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Team Edit Page
 *
 * Criar/editar equipes com validação e integração DB
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
require_capability('local/tubaron:manageteams', $context);

// Parâmetros
$id = optional_param('id', 0, PARAM_INT);
$seasonid = optional_param('seasonid', 0, PARAM_INT);

// Configurar página
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/tubaron/teams/edit.php', ['id' => $id]));
$PAGE->set_pagelayout('standard');

// Buscar equipe se editando
$team = null;
if ($id) {
    $team = $DB->get_record('local_tubaron_teams', ['id' => $id], '*', MUST_EXIST);
    $PAGE->set_title(get_string('editteam', 'local_tubaron'));
    $PAGE->set_heading(get_string('editteam', 'local_tubaron'));
} else {
    $PAGE->set_title(get_string('createteam', 'local_tubaron'));
    $PAGE->set_heading(get_string('createteam', 'local_tubaron'));
}

// Navegação
$PAGE->navbar->add(get_string('pluginname', 'local_tubaron'), new moodle_url('/local/tubaron/index.php'));
$PAGE->navbar->add(get_string('teams', 'local_tubaron'), new moodle_url('/local/tubaron/teams/index.php'));
$PAGE->navbar->add($id ? get_string('edit') : get_string('create'));

// Instanciar formulário
$formdata = [
    'team' => $team,
    'seasonid' => $seasonid
];
$mform = new \local_tubaron\form\team_edit_form(null, $formdata);

// Se editando, carregar dados existentes
if ($team) {
    // Buscar membros atuais
    $members = $DB->get_records('local_tubaron_team_members', ['teamid' => $team->id, 'status' => 'active']);
    
    $leaderid = null;
    $memberids = [];
    
    foreach ($members as $member) {
        if ($member->role === 'leader') {
            $leaderid = $member->userid;
        } else {
            $memberids[] = $member->userid;
        }
    }
    
    $team->id = $team->id;
    $team->leaderid = $leaderid;
    $team->memberids = $memberids;
    
    $mform->set_data($team);
}

// Processar formulário
if ($mform->is_cancelled()) {
    // Cancelou
    redirect(new moodle_url('/local/tubaron/teams/index.php'));
    
} else if ($data = $mform->get_data()) {
    // Salvar
    
    try {
        $transaction = $DB->start_delegated_transaction();
        
        $now = time();
        
        if ($data->id) {
            // Atualizar equipe existente
            $teamrecord = new \stdClass();
            $teamrecord->id = $data->id;
            $teamrecord->name = $data->name;
            $teamrecord->description = $data->description ?? '';
            $teamrecord->seasonid = $data->seasonid;
            $teamrecord->status = $data->status;
            $teamrecord->maxmembers = $data->maxmembers;
            $teamrecord->avatarurl = $data->avatarurl ?? '';
            $teamrecord->timemodified = $now;
            
            $DB->update_record('local_tubaron_teams', $teamrecord);
            $teamid = $data->id;
            
            // Remover membros antigos
            $DB->delete_records('local_tubaron_team_members', ['teamid' => $teamid]);
            
            $message = get_string('teamupdated', 'local_tubaron');
            
        } else {
            // Criar nova equipe
            $teamrecord = new \stdClass();
            $teamrecord->name = $data->name;
            $teamrecord->description = $data->description ?? '';
            $teamrecord->seasonid = $data->seasonid;
            $teamrecord->status = $data->status;
            $teamrecord->maxmembers = $data->maxmembers;
            $teamrecord->avatarurl = $data->avatarurl ?? '';
            $teamrecord->timecreated = $now;
            $teamrecord->timemodified = $now;
            
            $teamid = $DB->insert_record('local_tubaron_teams', $teamrecord);
            
            $message = get_string('teamcreated', 'local_tubaron');
        }
        
        // Adicionar líder
        $leader = new \stdClass();
        $leader->teamid = $teamid;
        $leader->userid = $data->leaderid;
        $leader->role = 'leader';
        $leader->status = 'active';
        $leader->timejoined = $now;
        $DB->insert_record('local_tubaron_team_members', $leader);
        
        // Adicionar membros
        if (!empty($data->memberids)) {
            foreach ($data->memberids as $userid) {
                $member = new \stdClass();
                $member->teamid = $teamid;
                $member->userid = $userid;
                $member->role = 'member';
                $member->status = 'active';
                $member->timejoined = $now;
                $DB->insert_record('local_tubaron_team_members', $member);
            }
        }
        
        // Audit log
        local_tubaron_log_action(
            $data->id ? 'team_updated' : 'team_created',
            'team',
            $teamid,
            ['name' => $data->name, 'members' => count($data->memberids) + 1]
        );
        
        $transaction->allow_commit();
        
        // Redirecionar com sucesso
        redirect(
            new moodle_url('/local/tubaron/teams/view.php', ['id' => $teamid]),
            $message,
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
        
    } catch (\Exception $e) {
        $transaction->rollback($e);
        
        \core\notification::error(get_string('errorsavingteam', 'local_tubaron') . ': ' . $e->getMessage());
    }
}

// Output
echo $OUTPUT->header();

// Hero
echo html_writer::start_div('tubaron-hero-small');
echo html_writer::tag('h1', 
    ($id ? '✏️ ' . get_string('editteam', 'local_tubaron') : '➕ ' . get_string('createteam', 'local_tubaron')),
    ['class' => 'tubaron-hero-title']
);
echo html_writer::tag('p',
    get_string('teamform_description', 'local_tubaron'),
    ['class' => 'tubaron-hero-subtitle']
);
echo html_writer::end_div();

// Formulário
echo html_writer::start_div('tubaron-form-container');
$mform->display();
echo html_writer::end_div();

// CSS
echo html_writer::tag('style', '
.tubaron-hero-small {
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 2rem;
}

.tubaron-form-container {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.tubaron-form-container .mform {
    max-width: 800px;
    margin: 0 auto;
}

.tubaron-form-container fieldset {
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.tubaron-form-container legend {
    background: #1e3a8a;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-weight: 600;
}

.tubaron-form-container .alert {
    padding: 1rem;
    border-radius: 8px;
    margin: 1rem 0;
}

.tubaron-form-container .alert-info {
    background: #dbeafe;
    border: 1px solid #3b82f6;
    color: #1e3a8a;
}

.tubaron-form-container .alert-warning {
    background: #fef3c7;
    border: 1px solid #f59e0b;
    color: #92400e;
}
');

echo $OUTPUT->footer();

