<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Team View Page
 *
 * Visualizar detalhes completos da equipe
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
require_capability('local/tubaron:viewteams', $context);

// Parâmetros
$id = required_param('id', PARAM_INT);

// Buscar equipe
$team = $DB->get_record('local_tubaron_teams', ['id' => $id], '*', MUST_EXIST);

// Configurar página
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/tubaron/teams/view.php', ['id' => $id]));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(format_string($team->name));
$PAGE->set_heading(format_string($team->name));

// Navegação
$PAGE->navbar->add(get_string('pluginname', 'local_tubaron'), new moodle_url('/local/tubaron/index.php'));
$PAGE->navbar->add(get_string('teams', 'local_tubaron'), new moodle_url('/local/tubaron/teams/index.php'));
$PAGE->navbar->add(format_string($team->name));

// Buscar dados adicionais
$season = $DB->get_record('local_tubaron_seasons', ['id' => $team->seasonid]);
$members = $DB->get_records_sql(
    "SELECT tm.*, u.firstname, u.lastname, u.email, u.picture, u.imagealt
     FROM {local_tubaron_team_members} tm
     JOIN {user} u ON u.id = tm.userid
     WHERE tm.teamid = ? AND tm.status = ?
     ORDER BY tm.role DESC, u.lastname, u.firstname",
    [$team->id, 'active']
);

// Buscar pontuação da equipe
$score = $DB->get_record('local_tubaron_scores', [
    'entitytype' => 'team',
    'entityid' => $team->id,
    'seasonid' => $team->seasonid
]);

// Buscar tarefas da equipe
$tasks = $DB->get_records_sql(
    "SELECT t.*, COUNT(ta.id) as assignmentcount
     FROM {local_tubaron_tasks} t
     LEFT JOIN {local_tubaron_task_assignments} ta ON ta.taskid = t.id AND ta.assigneetype = ? AND ta.assigneeid = ?
     JOIN {local_tubaron_missions} m ON m.id = t.missionid
     WHERE m.seasonid = ? AND t.type IN (?, ?)
     GROUP BY t.id
     ORDER BY t.timecreated DESC",
    ['team', $team->id, $team->seasonid, 'team', 'competitive'],
    0, 10 // Últimas 10
);

// Output
echo $OUTPUT->header();

// Hero
echo html_writer::start_div('tubaron-team-hero');

// Avatar
if (!empty($team->avatarurl)) {
    echo html_writer::empty_tag('img', [
        'src' => $team->avatarurl,
        'alt' => format_string($team->name),
        'class' => 'tubaron-team-avatar'
    ]);
}

echo html_writer::start_div('tubaron-team-hero-content');
echo html_writer::tag('h1', format_string($team->name), ['class' => 'tubaron-team-hero-title']);

// Status badge
$statusclass = $team->status === 'active' ? 'success' : 'secondary';
$statustext = $team->status === 'active' ? '✅ ' . get_string('active') : '⏸️ ' . get_string('inactive');
echo html_writer::tag('span', $statustext, ['class' => 'badge badge-' . $statusclass . ' tubaron-team-status-badge']);

// Descrição
if (!empty($team->description)) {
    echo html_writer::tag('p', format_text($team->description, FORMAT_PLAIN), ['class' => 'tubaron-team-description-hero']);
}

// Actions
if (has_capability('local/tubaron:manageteams', $context)) {
    echo html_writer::start_div('tubaron-team-actions-hero');
    $editurl = new moodle_url('/local/tubaron/teams/edit.php', ['id' => $team->id]);
    echo html_writer::link($editurl, '✏️ ' . get_string('edit'), ['class' => 'btn btn-secondary']);
    echo html_writer::end_div();
}

echo html_writer::end_div(); // hero-content
echo html_writer::end_div(); // hero

// Stats Cards
echo html_writer::start_div('tubaron-team-stats-cards');

// Card Membros
echo html_writer::start_div('tubaron-stat-card');
echo html_writer::tag('div', '👥', ['class' => 'tubaron-stat-icon']);
echo html_writer::tag('div', count($members), ['class' => 'tubaron-stat-value']);
echo html_writer::tag('div', get_string('members', 'local_tubaron'), ['class' => 'tubaron-stat-label']);
echo html_writer::end_div();

// Card Pontuação
$totalpoints = $score ? $score->totalpoints : 0;
echo html_writer::start_div('tubaron-stat-card');
echo html_writer::tag('div', '🏆', ['class' => 'tubaron-stat-icon']);
echo html_writer::tag('div', number_format($totalpoints), ['class' => 'tubaron-stat-value']);
echo html_writer::tag('div', get_string('points', 'local_tubaron'), ['class' => 'tubaron-stat-label']);
echo html_writer::end_div();

// Card Tarefas
echo html_writer::start_div('tubaron-stat-card');
echo html_writer::tag('div', '📋', ['class' => 'tubaron-stat-icon']);
echo html_writer::tag('div', count($tasks), ['class' => 'tubaron-stat-value']);
echo html_writer::tag('div', get_string('tasks', 'local_tubaron'), ['class' => 'tubaron-stat-label']);
echo html_writer::end_div();

// Card Temporada
echo html_writer::start_div('tubaron-stat-card');
echo html_writer::tag('div', '🗓️', ['class' => 'tubaron-stat-icon']);
echo html_writer::tag('div', format_string($season->name), ['class' => 'tubaron-stat-value small']);
echo html_writer::tag('div', get_string('season', 'local_tubaron'), ['class' => 'tubaron-stat-label']);
echo html_writer::end_div();

echo html_writer::end_div(); // stats-cards

// Layout 2 colunas
echo html_writer::start_div('tubaron-team-layout');

// Coluna Esquerda - Membros
echo html_writer::start_div('tubaron-team-column');
echo html_writer::tag('h2', '👥 ' . get_string('teammembers', 'local_tubaron'), ['class' => 'tubaron-section-title']);

if (empty($members)) {
    echo $OUTPUT->notification(get_string('nomembers', 'local_tubaron'), 'info');
} else {
    echo html_writer::start_div('tubaron-members-list');
    
    foreach ($members as $member) {
        $user = (object)[
            'id' => $member->userid,
            'firstname' => $member->firstname,
            'lastname' => $member->lastname,
            'email' => $member->email,
            'picture' => $member->picture,
            'imagealt' => $member->imagealt,
        ];
        
        echo html_writer::start_div('tubaron-member-card' . ($member->role === 'leader' ? ' leader' : ''));
        
        // Avatar
        echo $OUTPUT->user_picture($user, ['size' => 50, 'class' => 'tubaron-member-avatar']);
        
        // Info
        echo html_writer::start_div('tubaron-member-info');
        echo html_writer::tag('div', fullname($user), ['class' => 'tubaron-member-name']);
        
        if ($member->role === 'leader') {
            echo html_writer::tag('span', '👑 ' . get_string('leader', 'local_tubaron'), ['class' => 'badge badge-warning']);
        }
        
        echo html_writer::tag('div', $member->email, ['class' => 'tubaron-member-email']);
        echo html_writer::tag('div', 
            get_string('joineddate', 'local_tubaron') . ': ' . userdate($member->timejoined, get_string('strftimedateshort')),
            ['class' => 'tubaron-member-joined']
        );
        echo html_writer::end_div();
        
        echo html_writer::end_div(); // member-card
    }
    
    echo html_writer::end_div(); // members-list
}

echo html_writer::end_div(); // column

// Coluna Direita - Tarefas
echo html_writer::start_div('tubaron-team-column');
echo html_writer::tag('h2', '📋 ' . get_string('recenttasks', 'local_tubaron'), ['class' => 'tubaron-section-title']);

if (empty($tasks)) {
    echo $OUTPUT->notification(get_string('notasksfound', 'local_tubaron'), 'info');
} else {
    echo html_writer::start_tag('ul', ['class' => 'tubaron-tasks-list']);
    
    foreach ($tasks as $task) {
        $statusclass = $task->status === 'completed' ? 'success' : ($task->status === 'voting' ? 'warning' : 'info');
        $statusicon = $task->status === 'completed' ? '✅' : ($task->status === 'voting' ? '🗳️' : '🔵');
        
        echo html_writer::start_tag('li', ['class' => 'tubaron-task-item']);
        echo html_writer::tag('span', $statusicon, ['class' => 'tubaron-task-icon']);
        echo html_writer::tag('strong', format_string($task->title));
        echo html_writer::tag('span', 
            get_string('type_' . $task->type, 'local_tubaron'),
            ['class' => 'badge badge-' . $statusclass . ' ml-2']
        );
        echo html_writer::tag('div',
            userdate($task->timecreated, get_string('strftimedateshort')),
            ['class' => 'tubaron-task-date']
        );
        echo html_writer::end_tag('li');
    }
    
    echo html_writer::end_tag('ul');
}

echo html_writer::end_div(); // column
echo html_writer::end_div(); // layout

// CSS
echo html_writer::tag('style', '
.tubaron-team-hero {
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    color: white;
    padding: 3rem 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    display: flex;
    gap: 2rem;
    align-items: center;
}

.tubaron-team-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 4px solid white;
    object-fit: cover;
}

.tubaron-team-hero-content {
    flex: 1;
}

.tubaron-team-hero-title {
    font-size: 2.5rem;
    margin: 0 0 0.5rem 0;
}

.tubaron-team-status-badge {
    font-size: 1rem;
    padding: 0.5rem 1rem;
    margin-bottom: 1rem;
}

.tubaron-team-description-hero {
    font-size: 1.125rem;
    opacity: 0.9;
    margin: 1rem 0;
}

.tubaron-team-actions-hero {
    margin-top: 1rem;
}

.tubaron-team-stats-cards {
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
    transition: all 0.3s;
}

.tubaron-stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    border-color: #3b82f6;
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

.tubaron-team-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.tubaron-team-column {
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

.tubaron-members-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.tubaron-member-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    transition: all 0.2s;
}

.tubaron-member-card:hover {
    background: #f9fafb;
    border-color: #3b82f6;
}

.tubaron-member-card.leader {
    border: 2px solid #fbbf24;
    background: #fffbeb;
}

.tubaron-member-info {
    flex: 1;
}

.tubaron-member-name {
    font-weight: 600;
    font-size: 1.125rem;
    color: #1e3a8a;
}

.tubaron-member-email {
    color: #6b7280;
    font-size: 0.875rem;
}

.tubaron-member-joined {
    color: #9ca3af;
    font-size: 0.75rem;
    margin-top: 0.25rem;
}

.tubaron-tasks-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.tubaron-task-item {
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.tubaron-task-item:last-child {
    border-bottom: none;
}

.tubaron-task-icon {
    font-size: 1.25rem;
}

.tubaron-task-date {
    width: 100%;
    color: #9ca3af;
    font-size: 0.75rem;
    margin-top: 0.25rem;
}

@media (max-width: 968px) {
    .tubaron-team-layout {
        grid-template-columns: 1fr;
    }
    
    .tubaron-team-hero {
        flex-direction: column;
        text-align: center;
    }
}
');

echo $OUTPUT->footer();

