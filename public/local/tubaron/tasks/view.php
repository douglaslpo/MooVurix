<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Task View Page
 *
 * Visualizar detalhes completos da tarefa
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
require_capability('local/tubaron:viewtasks', $context);

// Parâmetros
$id = required_param('id', PARAM_INT);

// Buscar tarefa
$task = $DB->get_record('local_tubaron_tasks', ['id' => $id], '*', MUST_EXIST);

// Configurar página
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/tubaron/tasks/view.php', ['id' => $id]));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(format_string($task->title));
$PAGE->set_heading(format_string($task->title));

// Navegação
$PAGE->navbar->add(get_string('pluginname', 'local_tubaron'), new moodle_url('/local/tubaron/index.php'));
$PAGE->navbar->add(get_string('tasks', 'local_tubaron'), new moodle_url('/local/tubaron/tasks/index.php'));
$PAGE->navbar->add(format_string($task->title));

// Buscar dados adicionais
$mission = $DB->get_record('local_tubaron_missions', ['id' => $task->missionid]);
$creator = $DB->get_record('user', ['id' => $task->creatorid]);
$assignments = $DB->get_records('local_tubaron_task_assignments', ['taskid' => $id]);
$submissions = $DB->get_records('local_tubaron_submissions', ['taskid' => $id], 'timecreated DESC');

// Ícones por tipo
$typeicons = [
    'individual' => '👤',
    'team' => '👥',
    'competitive' => '⚔️'
];
$typeicon = $typeicons[$task->type] ?? '📋';

// Cores por status
$statuscolors = [
    'open' => 'info',
    'in_progress' => 'warning',
    'voting' => 'primary',
    'completed' => 'success'
];
$statuscolor = $statuscolors[$task->status] ?? 'secondary';

// Output
echo $OUTPUT->header();

// Hero
echo html_writer::start_div('tubaron-task-hero');
echo html_writer::tag('h1', $typeicon . ' ' . format_string($task->title), ['class' => 'tubaron-task-hero-title']);

// Meta badges
echo html_writer::start_div('tubaron-task-hero-meta');
echo html_writer::tag('span', 
    get_string('type_' . $task->type, 'local_tubaron'),
    ['class' => 'badge badge-dark badge-lg']
);
echo html_writer::tag('span',
    get_string('status_' . $task->status, 'local_tubaron'),
    ['class' => 'badge badge-' . $statuscolor . ' badge-lg']
);
echo html_writer::tag('span',
    '🏆 ' . $task->points . ' pts',
    ['class' => 'tubaron-task-hero-points']
);
echo html_writer::end_div();

echo html_writer::end_div();

// Stats Cards
echo html_writer::start_div('tubaron-task-stats-cards');

// Criador
echo html_writer::start_div('tubaron-stat-card');
echo html_writer::tag('div', '👤', ['class' => 'tubaron-stat-icon']);
echo html_writer::tag('div', fullname($creator), ['class' => 'tubaron-stat-value small']);
echo html_writer::tag('div', get_string('creator', 'local_tubaron'), ['class' => 'tubaron-stat-label']);
echo html_writer::end_div();

// Atribuições
echo html_writer::start_div('tubaron-stat-card');
echo html_writer::tag('div', '📌', ['class' => 'tubaron-stat-icon']);
echo html_writer::tag('div', count($assignments), ['class' => 'tubaron-stat-value']);
echo html_writer::tag('div', get_string('assignments', 'local_tubaron'), ['class' => 'tubaron-stat-label']);
echo html_writer::end_div();

// Submissões
echo html_writer::start_div('tubaron-stat-card');
echo html_writer::tag('div', '📤', ['class' => 'tubaron-stat-icon']);
echo html_writer::tag('div', count($submissions), ['class' => 'tubaron-stat-value']);
echo html_writer::tag('div', get_string('submissions', 'local_tubaron'), ['class' => 'tubaron-stat-label']);
echo html_writer::end_div();

// Missão
echo html_writer::start_div('tubaron-stat-card');
echo html_writer::tag('div', '📂', ['class' => 'tubaron-stat-icon']);
echo html_writer::tag('div', format_string($mission->name), ['class' => 'tubaron-stat-value small']);
echo html_writer::tag('div', get_string('mission', 'local_tubaron'), ['class' => 'tubaron-stat-label']);
echo html_writer::end_div();

echo html_writer::end_div(); // stats-cards

// Layout 2 colunas
echo html_writer::start_div('tubaron-task-layout');

// Coluna Esquerda - Detalhes
echo html_writer::start_div('tubaron-task-column');

// Descrição
echo html_writer::tag('h2', '📋 ' . get_string('description'), ['class' => 'tubaron-section-title']);
echo html_writer::div(format_text($task->description, FORMAT_HTML), 'tubaron-task-description-content');

// Critérios Aprovação
if (!empty($task->approvalcriteria)) {
    echo html_writer::tag('h2', '✅ ' . get_string('approvalcriteria', 'local_tubaron'), ['class' => 'tubaron-section-title mt-4']);
    echo html_writer::div(format_text($task->approvalcriteria, FORMAT_PLAIN), 'tubaron-task-criteria');
}

// Info adicional
echo html_writer::tag('h2', 'ℹ️ ' . get_string('additionalinfo', 'local_tubaron'), ['class' => 'tubaron-section-title mt-4']);
echo html_writer::start_tag('ul', ['class' => 'tubaron-info-list']);
echo html_writer::tag('li', '📅 ' . get_string('created', 'local_tubaron') . ': ' . userdate($task->timecreated));

if ($task->deadline > 0) {
    $isoverdue = $task->deadline < time() && $task->status !== 'completed';
    echo html_writer::tag('li', 
        '⏰ ' . get_string('deadline', 'local_tubaron') . ': ' . 
        userdate($task->deadline) .
        ($isoverdue ? ' (' . get_string('overdue', 'local_tubaron') . ')' : ''),
        ['class' => $isoverdue ? 'text-danger' : '']
    );
}

echo html_writer::tag('li', '🗳️ ' . get_string('votingmethod', 'local_tubaron') . ': ' . get_string('method_' . $task->votingmethod, 'local_tubaron'));

if ($task->votingdeadline > 0) {
    echo html_writer::tag('li', '⏱️ ' . get_string('votingdeadline', 'local_tubaron') . ': ' . userdate($task->votingdeadline));
}

echo html_writer::end_tag('ul');

echo html_writer::end_div(); // column

// Coluna Direita - Atribuições e Submissões
echo html_writer::start_div('tubaron-task-column');

// Atribuições
echo html_writer::tag('h2', '📌 ' . get_string('assignments', 'local_tubaron'), ['class' => 'tubaron-section-title']);

if (empty($assignments)) {
    echo $OUTPUT->notification(get_string('noassignments', 'local_tubaron'), 'info');
} else {
    echo html_writer::start_tag('ul', ['class' => 'tubaron-assignments-list']);
    
    foreach ($assignments as $assignment) {
        if ($assignment->assigneetype === 'user') {
            $user = $DB->get_record('user', ['id' => $assignment->assigneeid]);
            $assigneename = '👤 ' . fullname($user);
        } else {
            $team = $DB->get_record('local_tubaron_teams', ['id' => $assignment->assigneeid]);
            $assigneename = '👥 ' . format_string($team->name);
        }
        
        echo html_writer::tag('li', $assigneename);
    }
    
    echo html_writer::end_tag('ul');
}

// Submissões
if ($task->status !== 'open') {
    echo html_writer::tag('h2', '📤 ' . get_string('submissions', 'local_tubaron'), ['class' => 'tubaron-section-title mt-4']);
    
    if (empty($submissions)) {
        echo $OUTPUT->notification(get_string('nosubmissions', 'local_tubaron'), 'info');
    } else {
        echo html_writer::start_div('tubaron-submissions-list');
        
        foreach ($submissions as $submission) {
            $user = $DB->get_record('user', ['id' => $submission->userid]);
            
            echo html_writer::start_div('tubaron-submission-card');
            echo html_writer::tag('div',
                fullname($user),
                ['class' => 'tubaron-submission-user']
            );
            echo html_writer::tag('div',
                userdate($submission->timecreated, get_string('strftimedatetime')),
                ['class' => 'tubaron-submission-date']
            );
            
            if (!empty($submission->comments)) {
                echo html_writer::tag('p', format_text($submission->comments, FORMAT_PLAIN), ['class' => 'tubaron-submission-comments']);
            }
            
            echo html_writer::end_div();
        }
        
        echo html_writer::end_div();
    }
}

// Ações
echo html_writer::tag('h2', '⚡ ' . get_string('actions'), ['class' => 'tubaron-section-title mt-4']);
echo html_writer::start_div('tubaron-task-view-actions');

if (has_capability('local/tubaron:edittask', $context) || $task->creatorid == $USER->id) {
    $editurl = new moodle_url('/local/tubaron/tasks/edit.php', ['id' => $task->id]);
    echo html_writer::link($editurl, '✏️ ' . get_string('edit'), ['class' => 'btn btn-secondary']);
}

if ($task->status === 'voting') {
    if (\local_tubaron\voting_manager::check_eligibility($id, $USER->id) &&
        !\local_tubaron\voting_manager::has_voted($id, $USER->id)) {
        $voteurl = new moodle_url('/local/tubaron/voting/vote.php', ['id' => $id]);
        echo html_writer::link($voteurl, '🗳️ ' . get_string('vote', 'local_tubaron'), ['class' => 'btn btn-primary']);
    }
    
    $resultsurl = new moodle_url('/local/tubaron/voting/results.php', ['id' => $id]);
    echo html_writer::link($resultsurl, '📊 ' . get_string('viewresults', 'local_tubaron'), ['class' => 'btn btn-info']);
}

$backurl = new moodle_url('/local/tubaron/tasks/index.php');
echo html_writer::link($backurl, '« ' . get_string('backtotasks', 'local_tubaron'), ['class' => 'btn btn-secondary']);

echo html_writer::end_div();

echo html_writer::end_div(); // column
echo html_writer::end_div(); // layout

// CSS
echo html_writer::tag('style', '
.tubaron-task-hero {
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    color: white;
    padding: 3rem 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    text-align: center;
}

.tubaron-task-hero-title {
    font-size: 2.5rem;
    margin: 0 0 1.5rem 0;
}

.tubaron-task-hero-meta {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.tubaron-task-hero-points {
    font-weight: 700;
    font-size: 1.5rem;
}

.tubaron-task-stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.tubaron-stat-card {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
}

.tubaron-stat-icon {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.tubaron-stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #1e3a8a;
}

.tubaron-stat-value.small {
    font-size: 1.25rem;
}

.tubaron-stat-label {
    color: #6b7280;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.tubaron-task-layout {
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: 2rem;
}

.tubaron-task-column {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.tubaron-section-title {
    font-size: 1.5rem;
    color: #1e3a8a;
    margin: 0 0 1.5rem 0;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #e5e7eb;
}

.tubaron-task-description-content {
    color: #374151;
    line-height: 1.6;
}

.tubaron-task-criteria {
    background: #fffbeb;
    border-left: 4px solid #fbbf24;
    padding: 1rem;
    border-radius: 8px;
    color: #78350f;
}

.tubaron-info-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.tubaron-info-list li {
    padding: 0.75rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.tubaron-info-list li:last-child {
    border-bottom: none;
}

.tubaron-assignments-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.tubaron-assignments-list li {
    padding: 0.75rem 1rem;
    background: #f9fafb;
    border-radius: 8px;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.tubaron-submissions-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.tubaron-submission-card {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1rem;
    background: #fafafa;
}

.tubaron-submission-user {
    font-weight: 600;
    color: #1e3a8a;
    margin-bottom: 0.25rem;
}

.tubaron-submission-date {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 0.5rem;
}

.tubaron-submission-comments {
    color: #374151;
    font-size: 0.875rem;
    margin: 0.5rem 0 0 0;
}

.tubaron-task-view-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

@media (max-width: 968px) {
    .tubaron-task-layout {
        grid-template-columns: 1fr;
    }
}
');

echo $OUTPUT->footer();

