<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Task Edit Page
 *
 * Criar/editar tarefas (3 tipos) com validação e integração DB
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
require_capability('local/tubaron:createtask', $context);

// Parâmetros
$id = optional_param('id', 0, PARAM_INT);
$missionid = optional_param('missionid', 0, PARAM_INT);

// Configurar página
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/tubaron/tasks/edit.php', ['id' => $id]));
$PAGE->set_pagelayout('standard');

// Buscar tarefa se editando
$task = null;
if ($id) {
    $task = $DB->get_record('local_tubaron_tasks', ['id' => $id], '*', MUST_EXIST);
    
    // Verificar permissão para editar
    if (!has_capability('local/tubaron:edittask', $context) && $task->creatorid != $USER->id) {
        throw new moodle_exception('nopermissiontoedit', 'local_tubaron');
    }
    
    $PAGE->set_title(get_string('edittask', 'local_tubaron'));
    $PAGE->set_heading(get_string('edittask', 'local_tubaron'));
} else {
    $PAGE->set_title(get_string('createtask', 'local_tubaron'));
    $PAGE->set_heading(get_string('createtask', 'local_tubaron'));
}

// Navegação
$PAGE->navbar->add(get_string('pluginname', 'local_tubaron'), new moodle_url('/local/tubaron/index.php'));
$PAGE->navbar->add(get_string('tasks', 'local_tubaron'), new moodle_url('/local/tubaron/tasks/index.php'));
$PAGE->navbar->add($id ? get_string('edit') : get_string('create'));

// Instanciar formulário
$formdata = [
    'task' => $task,
    'missionid' => $missionid
];
$mform = new \local_tubaron\form\task_edit_form(null, $formdata);

// Se editando, carregar dados existentes
if ($task) {
    // Preparar editor
    $task->description_editor = [
        'text' => $task->description,
        'format' => FORMAT_HTML
    ];
    
    // Buscar atribuições
    $assignments = $DB->get_records('local_tubaron_task_assignments', ['taskid' => $task->id]);
    
    foreach ($assignments as $assignment) {
        if ($task->type === 'individual') {
            $task->assignuser = $assignment->assigneeid;
        } else if ($task->type === 'team') {
            $task->assignteam = $assignment->assigneeid;
        }
    }
    
    $mform->set_data($task);
}

// Processar formulário
if ($mform->is_cancelled()) {
    // Cancelou
    redirect(new moodle_url('/local/tubaron/tasks/index.php'));
    
} else if ($data = $mform->get_data()) {
    // Salvar
    
    try {
        $transaction = $DB->start_delegated_transaction();
        
        $now = time();
        
        if ($data->id) {
            // Atualizar tarefa existente
            $taskrecord = new \stdClass();
            $taskrecord->id = $data->id;
            $taskrecord->type = $data->type;
            $taskrecord->title = $data->title;
            $taskrecord->description = $data->description_editor['text'];
            $taskrecord->missionid = $data->missionid;
            $taskrecord->points = $data->points;
            $taskrecord->deadline = $data->deadline ?? 0;
            $taskrecord->votingmethod = $data->votingmethod;
            $taskrecord->approvalcriteria = $data->approvalcriteria ?? '';
            $taskrecord->votingdeadline = $data->votingdeadline ?? 0;
            
            $DB->update_record('local_tubaron_tasks', $taskrecord);
            $taskid = $data->id;
            
            // Remover atribuições antigas
            $DB->delete_records('local_tubaron_task_assignments', ['taskid' => $taskid]);
            
            $message = get_string('taskupdated', 'local_tubaron');
            
        } else {
            // Criar nova tarefa
            $taskrecord = new \stdClass();
            $taskrecord->type = $data->type;
            $taskrecord->title = $data->title;
            $taskrecord->description = $data->description_editor['text'];
            $taskrecord->creatorid = $USER->id;
            $taskrecord->missionid = $data->missionid;
            $taskrecord->points = $data->points;
            $taskrecord->deadline = $data->deadline ?? 0;
            $taskrecord->votingmethod = $data->votingmethod;
            $taskrecord->approvalcriteria = $data->approvalcriteria ?? '';
            $taskrecord->votingdeadline = $data->votingdeadline ?? 0;
            $taskrecord->status = 'open';
            $taskrecord->timecreated = $now;
            
            $taskid = $DB->insert_record('local_tubaron_tasks', $taskrecord);
            
            $message = get_string('taskcreated', 'local_tubaron');
        }
        
        // Criar atribuições
        if ($data->type === 'individual' && !empty($data->assignuser)) {
            $assignment = new \stdClass();
            $assignment->taskid = $taskid;
            $assignment->assigneetype = 'user';
            $assignment->assigneeid = $data->assignuser;
            $assignment->timeassigned = $now;
            $DB->insert_record('local_tubaron_task_assignments', $assignment);
            
        } else if ($data->type === 'team' && !empty($data->assignteam)) {
            $assignment = new \stdClass();
            $assignment->taskid = $taskid;
            $assignment->assigneetype = 'team';
            $assignment->assigneeid = $data->assignteam;
            $assignment->timeassigned = $now;
            $DB->insert_record('local_tubaron_task_assignments', $assignment);
            
        } else if ($data->type === 'competitive' && !empty($data->assignmultiple)) {
            foreach ($data->assignmultiple as $assigneeid) {
                // Determinar se é user ou team pelo valor
                $assigneetype = $assigneeid > 10000 ? 'user' : 'team'; // Simplificado
                
                $assignment = new \stdClass();
                $assignment->taskid = $taskid;
                $assignment->assigneetype = $assigneetype;
                $assignment->assigneeid = $assigneeid;
                $assignment->timeassigned = $now;
                $DB->insert_record('local_tubaron_task_assignments', $assignment);
            }
        }
        
        // Audit log
        local_tubaron_log_action(
            $data->id ? 'task_updated' : 'task_created',
            'task',
            $taskid,
            ['type' => $data->type, 'points' => $data->points]
        );
        
        $transaction->allow_commit();
        
        // Redirecionar com sucesso
        redirect(
            new moodle_url('/local/tubaron/tasks/view.php', ['id' => $taskid]),
            $message,
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
        
    } catch (\Exception $e) {
        $transaction->rollback($e);
        \core\notification::error(get_string('errorsavingtask', 'local_tubaron') . ': ' . $e->getMessage());
    }
}

// Output
echo $OUTPUT->header();

// Hero
echo html_writer::start_div('tubaron-hero-small');
echo html_writer::tag('h1', 
    ($id ? '✏️ ' . get_string('edittask', 'local_tubaron') : '➕ ' . get_string('createtask', 'local_tubaron')),
    ['class' => 'tubaron-hero-title']
);
echo html_writer::tag('p',
    get_string('taskform_description', 'local_tubaron'),
    ['class' => 'tubaron-hero-subtitle']
);
echo html_writer::end_div();

// Info sobre tipos
echo html_writer::start_div('tubaron-task-types-info');
echo html_writer::tag('h3', get_string('tasktypes', 'local_tubaron'), ['class' => 'mb-3']);

echo html_writer::start_div('tubaron-types-cards');

// Individual
echo html_writer::start_div('tubaron-type-card');
echo html_writer::tag('div', '👤', ['class' => 'tubaron-type-icon']);
echo html_writer::tag('h4', get_string('type_individual', 'local_tubaron'));
echo html_writer::tag('p', get_string('type_individual_desc', 'local_tubaron'));
echo html_writer::end_div();

// Team
echo html_writer::start_div('tubaron-type-card');
echo html_writer::tag('div', '👥', ['class' => 'tubaron-type-icon']);
echo html_writer::tag('h4', get_string('type_team', 'local_tubaron'));
echo html_writer::tag('p', get_string('type_team_desc', 'local_tubaron'));
echo html_writer::end_div();

// Competitive
echo html_writer::start_div('tubaron-type-card');
echo html_writer::tag('div', '⚔️', ['class' => 'tubaron-type-icon']);
echo html_writer::tag('h4', get_string('type_competitive', 'local_tubaron'));
echo html_writer::tag('p', get_string('type_competitive_desc', 'local_tubaron'));
echo html_writer::end_div();

echo html_writer::end_div(); // types-cards
echo html_writer::end_div(); // task-types-info

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

.tubaron-task-types-info {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.tubaron-types-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
}

.tubaron-type-card {
    background: #f9fafb;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s;
}

.tubaron-type-card:hover {
    transform: translateY(-4px);
    border-color: #3b82f6;
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}

.tubaron-type-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.tubaron-type-card h4 {
    color: #1e3a8a;
    margin-bottom: 0.5rem;
}

.tubaron-type-card p {
    color: #6b7280;
    font-size: 0.875rem;
    margin: 0;
}

.tubaron-form-container {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.tubaron-form-container .mform {
    max-width: 900px;
    margin: 0 auto;
}

@media (max-width: 968px) {
    .tubaron-types-cards {
        grid-template-columns: 1fr;
    }
}
');

echo $OUTPUT->footer();

