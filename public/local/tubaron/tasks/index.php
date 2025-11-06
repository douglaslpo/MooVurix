<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Tasks Listing
 *
 * Lista todas as tarefas com filtros por tipo, status e temporada
 * Integrado ao MooVurix LMS Platform
 *
 * @package    local_tubaron
 * @copyright  2025 Tubaron Telecomunicações
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/tablelib.php');
require_once(__DIR__ . '/../lib.php');

// Autenticação e contexto
require_login();
$context = context_system::instance();

// Verificar capability
require_capability('local/tubaron:viewtasks', $context);

// Parâmetros
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 20, PARAM_INT);
$search = optional_param('search', '', PARAM_TEXT);
$type = optional_param('type', 'all', PARAM_ALPHA);
$status = optional_param('status', 'all', PARAM_ALPHA);
$seasonid = optional_param('seasonid', 0, PARAM_INT);

// Configurar página
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/tubaron/tasks/index.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('tasks', 'local_tubaron'));
$PAGE->set_heading(get_string('tasks', 'local_tubaron'));
$PAGE->navbar->add(get_string('pluginname', 'local_tubaron'), new moodle_url('/local/tubaron/index.php'));
$PAGE->navbar->add(get_string('tasks', 'local_tubaron'));

// Output
echo $OUTPUT->header();

// Hero Section
echo html_writer::start_div('tubaron-hero');
echo html_writer::tag('h1', '📋 ' . get_string('tasks', 'local_tubaron'), ['class' => 'tubaron-hero-title']);
echo html_writer::tag('p', get_string('tasks_description', 'local_tubaron'), ['class' => 'tubaron-hero-subtitle']);
echo html_writer::end_div();

// Actions Bar
echo html_writer::start_div('tubaron-actions-bar');

// Botão Nova Tarefa
if (has_capability('local/tubaron:createtask', $context)) {
    $createurl = new moodle_url('/local/tubaron/tasks/edit.php');
    echo html_writer::link(
        $createurl,
        '➕ ' . get_string('createtask', 'local_tubaron'),
        ['class' => 'btn btn-primary btn-lg tubaron-btn-create']
    );
}

// Filtros
echo html_writer::start_div('tubaron-filters');

// Busca
echo html_writer::start_tag('form', ['method' => 'get', 'class' => 'tubaron-search-form']);
echo html_writer::empty_tag('input', [
    'type' => 'text',
    'name' => 'search',
    'value' => $search,
    'placeholder' => get_string('searchtasks', 'local_tubaron'),
    'class' => 'form-control'
]);
echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => '🔍 ' . get_string('search'), 'class' => 'btn btn-secondary']);
echo html_writer::end_tag('form');

// Filtro Tipo
$typeoptions = [
    'all' => get_string('alltypes', 'local_tubaron'),
    'individual' => get_string('type_individual', 'local_tubaron'),
    'team' => get_string('type_team', 'local_tubaron'),
    'competitive' => get_string('type_competitive', 'local_tubaron'),
];
echo html_writer::start_tag('form', ['method' => 'get', 'class' => 'tubaron-filter-form']);
if (!empty($search)) {
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'search', 'value' => $search]);
}
if ($status !== 'all') {
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'status', 'value' => $status]);
}
echo html_writer::select($typeoptions, 'type', $type, false, ['class' => 'form-control', 'onchange' => 'this.form.submit()']);
echo html_writer::end_tag('form');

// Filtro Status
$statusoptions = [
    'all' => get_string('allstatuses', 'local_tubaron'),
    'open' => get_string('status_open', 'local_tubaron'),
    'in_progress' => get_string('status_in_progress', 'local_tubaron'),
    'voting' => get_string('status_voting', 'local_tubaron'),
    'completed' => get_string('status_completed', 'local_tubaron'),
];
echo html_writer::start_tag('form', ['method' => 'get', 'class' => 'tubaron-filter-form']);
if (!empty($search)) {
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'search', 'value' => $search]);
}
if ($type !== 'all') {
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'type', 'value' => $type]);
}
echo html_writer::select($statusoptions, 'status', $status, false, ['class' => 'form-control', 'onchange' => 'this.form.submit()']);
echo html_writer::end_tag('form');

echo html_writer::end_div(); // filters
echo html_writer::end_div(); // actions-bar

// Buscar tarefas
global $DB;

$whereclauses = ['1=1'];
$params = [];

if (!empty($search)) {
    $whereclauses[] = $DB->sql_like('t.title', '?', false);
    $params[] = '%' . $DB->sql_like_escape($search) . '%';
}

if ($type !== 'all') {
    $whereclauses[] = 't.type = ?';
    $params[] = $type;
}

if ($status !== 'all') {
    $whereclauses[] = 't.status = ?';
    $params[] = $status;
}

if ($seasonid > 0) {
    $whereclauses[] = 'm.seasonid = ?';
    $params[] = $seasonid;
}

$where = implode(' AND ', $whereclauses);

$sql = "SELECT t.*, m.name as missionname, m.seasonid
        FROM {local_tubaron_tasks} t
        JOIN {local_tubaron_missions} m ON m.id = t.missionid
        WHERE $where
        ORDER BY t.timecreated DESC";

$countsql = "SELECT COUNT(t.id)
             FROM {local_tubaron_tasks} t
             JOIN {local_tubaron_missions} m ON m.id = t.missionid
             WHERE $where";

$totalcount = $DB->count_records_sql($countsql, $params);
$tasks = $DB->get_records_sql($sql, $params, $page * $perpage, $perpage);

// Stats rápidas
$totaltasks = $DB->count_records('local_tubaron_tasks');
$opentasks = $DB->count_records('local_tubaron_tasks', ['status' => 'open']);
$votingtasks = $DB->count_records('local_tubaron_tasks', ['status' => 'voting']);
$completedtasks = $DB->count_records('local_tubaron_tasks', ['status' => 'completed']);

echo html_writer::start_div('tubaron-stats-mini');
echo html_writer::tag('div', 
    html_writer::tag('strong', $totaltasks) . ' ' . get_string('totaltasks', 'local_tubaron'),
    ['class' => 'tubaron-stat-item']
);
echo html_writer::tag('div',
    html_writer::tag('strong', $opentasks) . ' ' . get_string('opentasks', 'local_tubaron'),
    ['class' => 'tubaron-stat-item open']
);
echo html_writer::tag('div',
    html_writer::tag('strong', $votingtasks) . ' ' . get_string('votingtasks', 'local_tubaron'),
    ['class' => 'tubaron-stat-item voting']
);
echo html_writer::tag('div',
    html_writer::tag('strong', $completedtasks) . ' ' . get_string('completedtasks', 'local_tubaron'),
    ['class' => 'tubaron-stat-item completed']
);
echo html_writer::end_div();

// Tabela de tarefas
if (empty($tasks)) {
    echo $OUTPUT->notification(get_string('notasksfound', 'local_tubaron'), 'info');
} else {
    echo html_writer::start_div('tubaron-tasks-grid');
    
    foreach ($tasks as $task) {
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
        
        // Card da tarefa
        echo html_writer::start_div('tubaron-task-card ' . $task->status);
        
        // Header
        echo html_writer::start_div('tubaron-task-card-header');
        echo html_writer::tag('h3', 
            $typeicon . ' ' . format_string($task->title),
            ['class' => 'tubaron-task-title']
        );
        echo html_writer::tag('span', 
            get_string('status_' . $task->status, 'local_tubaron'),
            ['class' => 'badge badge-' . $statuscolor]
        );
        echo html_writer::end_div();
        
        // Tipo e Pontos
        echo html_writer::start_div('tubaron-task-meta');
        echo html_writer::tag('span',
            get_string('type_' . $task->type, 'local_tubaron'),
            ['class' => 'badge badge-dark']
        );
        echo html_writer::tag('span',
            '🏆 ' . $task->points . ' pts',
            ['class' => 'tubaron-task-points']
        );
        echo html_writer::end_div();
        
        // Descrição
        if (!empty($task->description)) {
            $shortdesc = strlen($task->description) > 120 
                ? substr($task->description, 0, 120) . '...' 
                : $task->description;
            echo html_writer::tag('p', format_text($shortdesc, FORMAT_PLAIN), ['class' => 'tubaron-task-description']);
        }
        
        // Mission & Deadline
        echo html_writer::start_div('tubaron-task-info');
        echo html_writer::tag('div',
            '📂 ' . format_string($task->missionname),
            ['class' => 'tubaron-task-info-item']
        );
        if ($task->deadline > 0) {
            $isoverdue = $task->deadline < time() && $task->status !== 'completed';
            echo html_writer::tag('div',
                '⏰ ' . userdate($task->deadline, get_string('strftimedateshort')),
                ['class' => 'tubaron-task-info-item' . ($isoverdue ? ' overdue' : '')]
            );
        }
        echo html_writer::end_div();
        
        // Ações
        echo html_writer::start_div('tubaron-task-actions');
        
        $viewurl = new moodle_url('/local/tubaron/tasks/view.php', ['id' => $task->id]);
        echo html_writer::link($viewurl, '👁️ ' . get_string('view'), ['class' => 'btn btn-sm btn-info']);
        
        if (has_capability('local/tubaron:edittask', $context)) {
            $editurl = new moodle_url('/local/tubaron/tasks/edit.php', ['id' => $task->id]);
            echo html_writer::link($editurl, '✏️ ' . get_string('edit'), ['class' => 'btn btn-sm btn-secondary']);
        }
        
        echo html_writer::end_div();
        echo html_writer::end_div(); // task-card
    }
    
    echo html_writer::end_div(); // tasks-grid
    
    // Paginação
    $baseurl = new moodle_url('/local/tubaron/tasks/index.php', [
        'search' => $search,
        'type' => $type,
        'status' => $status,
        'seasonid' => $seasonid,
        'perpage' => $perpage
    ]);
    echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $baseurl);
}

// CSS Inline
echo html_writer::tag('style', '
.tubaron-hero {
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    color: white;
    padding: 3rem 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(30, 58, 138, 0.3);
}

.tubaron-hero-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
}

.tubaron-hero-subtitle {
    font-size: 1.125rem;
    opacity: 0.9;
    margin: 0;
}

.tubaron-actions-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.tubaron-btn-create {
    font-size: 1.125rem;
    padding: 0.75rem 2rem;
    border-radius: 8px;
    font-weight: 600;
}

.tubaron-filters {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    flex-wrap: wrap;
}

.tubaron-search-form, .tubaron-filter-form {
    display: flex;
    gap: 0.5rem;
}

.tubaron-stats-mini {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 2rem;
    padding: 1rem;
    background: #f3f4f6;
    border-radius: 8px;
    flex-wrap: wrap;
}

.tubaron-stat-item {
    font-size: 1rem;
}

.tubaron-stat-item strong {
    font-size: 1.5rem;
    color: #1e3a8a;
    margin-right: 0.5rem;
}

.tubaron-tasks-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.tubaron-task-card {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
}

.tubaron-task-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
    border-color: #3b82f6;
}

.tubaron-task-card.open {
    border-left: 4px solid #3b82f6;
}

.tubaron-task-card.voting {
    border-left: 4px solid #8b5cf6;
}

.tubaron-task-card.completed {
    border-left: 4px solid #10b981;
}

.tubaron-task-card-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 1rem;
    gap: 1rem;
}

.tubaron-task-title {
    margin: 0;
    font-size: 1.25rem;
    color: #1e3a8a;
    flex: 1;
}

.tubaron-task-meta {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.tubaron-task-points {
    font-weight: 600;
    color: #b45309;
}

.tubaron-task-description {
    color: #6b7280;
    font-size: 0.875rem;
    margin-bottom: 1rem;
    line-height: 1.5;
}

.tubaron-task-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
    font-size: 0.875rem;
    color: #4b5563;
}

.tubaron-task-info-item.overdue {
    color: #dc2626;
    font-weight: 600;
}

.tubaron-task-actions {
    display: flex;
    gap: 0.5rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

@media (max-width: 768px) {
    .tubaron-tasks-grid {
        grid-template-columns: 1fr;
    }
    
    .tubaron-actions-bar {
        flex-direction: column;
        align-items: stretch;
    }
    
    .tubaron-filters {
        width: 100%;
    }
    
    .tubaron-search-form,
    .tubaron-filter-form {
        flex: 1;
    }
}
');

echo $OUTPUT->footer();

