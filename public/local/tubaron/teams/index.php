<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Teams Listing
 *
 * Lista todas as equipes com filtros, busca e paginação
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
require_capability('local/tubaron:viewteams', $context);

// Parâmetros
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 20, PARAM_INT);
$search = optional_param('search', '', PARAM_TEXT);
$status = optional_param('status', 'all', PARAM_ALPHA);
$seasonid = optional_param('seasonid', 0, PARAM_INT);

// Configurar página
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/tubaron/teams/index.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('teams', 'local_tubaron'));
$PAGE->set_heading(get_string('teams', 'local_tubaron'));
$PAGE->navbar->add(get_string('pluginname', 'local_tubaron'), new moodle_url('/local/tubaron/index.php'));
$PAGE->navbar->add(get_string('teams', 'local_tubaron'));

// Output
echo $OUTPUT->header();

// Hero Section
echo html_writer::start_div('tubaron-hero');
echo html_writer::tag('h1', '👥 ' . get_string('teams', 'local_tubaron'), ['class' => 'tubaron-hero-title']);
echo html_writer::tag('p', get_string('teams_description', 'local_tubaron'), ['class' => 'tubaron-hero-subtitle']);
echo html_writer::end_div();

// Actions Bar
echo html_writer::start_div('tubaron-actions-bar');

// Botão Nova Equipe
if (has_capability('local/tubaron:manageteams', $context)) {
    $createurl = new moodle_url('/local/tubaron/teams/edit.php');
    echo html_writer::link(
        $createurl,
        '➕ ' . get_string('createteam', 'local_tubaron'),
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
    'placeholder' => get_string('searchteams', 'local_tubaron'),
    'class' => 'form-control'
]);
echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => '🔍 ' . get_string('search'), 'class' => 'btn btn-secondary']);
echo html_writer::end_tag('form');

// Filtro Status
$statusoptions = [
    'all' => get_string('allstatuses', 'local_tubaron'),
    'active' => get_string('active'),
    'inactive' => get_string('inactive'),
];
echo html_writer::start_tag('form', ['method' => 'get', 'class' => 'tubaron-filter-form']);
if (!empty($search)) {
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'search', 'value' => $search]);
}
echo html_writer::select($statusoptions, 'status', $status, false, ['class' => 'form-control', 'onchange' => 'this.form.submit()']);
echo html_writer::end_tag('form');

echo html_writer::end_div(); // filters
echo html_writer::end_div(); // actions-bar

// Buscar equipes
global $DB;

$whereclauses = [];
$params = [];

if (!empty($search)) {
    $whereclauses[] = $DB->sql_like('name', '?', false);
    $params[] = '%' . $DB->sql_like_escape($search) . '%';
}

if ($status !== 'all') {
    $whereclauses[] = 'status = ?';
    $params[] = $status;
}

if ($seasonid > 0) {
    $whereclauses[] = 'seasonid = ?';
    $params[] = $seasonid;
}

$where = !empty($whereclauses) ? 'WHERE ' . implode(' AND ', $whereclauses) : '';
$sql = "SELECT * FROM {local_tubaron_teams} $where ORDER BY timecreated DESC";

$totalcount = $DB->count_records_sql("SELECT COUNT(*) FROM {local_tubaron_teams} $where", $params);
$teams = $DB->get_records_sql($sql, $params, $page * $perpage, $perpage);

// Stats rápidas
$totalteams = $DB->count_records('local_tubaron_teams');
$activeteams = $DB->count_records('local_tubaron_teams', ['status' => 'active']);

echo html_writer::start_div('tubaron-stats-mini');
echo html_writer::tag('div', 
    html_writer::tag('strong', $totalteams) . ' ' . get_string('totalteams', 'local_tubaron'),
    ['class' => 'tubaron-stat-item']
);
echo html_writer::tag('div',
    html_writer::tag('strong', $activeteams) . ' ' . get_string('activeteams', 'local_tubaron'),
    ['class' => 'tubaron-stat-item active']
);
echo html_writer::end_div();

// Tabela de equipes
if (empty($teams)) {
    echo $OUTPUT->notification(get_string('noteamsfound', 'local_tubaron'), 'info');
} else {
    echo html_writer::start_div('tubaron-teams-grid');
    
    foreach ($teams as $team) {
        // Buscar membros
        $membercount = $DB->count_records('local_tubaron_team_members', ['teamid' => $team->id, 'status' => 'active']);
        
        // Buscar líder
        $leader = $DB->get_record_sql(
            "SELECT u.firstname, u.lastname FROM {user} u
             JOIN {local_tubaron_team_members} tm ON tm.userid = u.id
             WHERE tm.teamid = ? AND tm.role = ?",
            [$team->id, 'leader']
        );
        
        // Card da equipe
        echo html_writer::start_div('tubaron-team-card ' . ($team->status === 'active' ? 'active' : 'inactive'));
        
        // Header
        echo html_writer::start_div('tubaron-team-card-header');
        echo html_writer::tag('h3', format_string($team->name), ['class' => 'tubaron-team-name']);
        echo html_writer::tag('span', 
            $team->status === 'active' ? '✅ ' . get_string('active') : '⏸️ ' . get_string('inactive'),
            ['class' => 'tubaron-team-status badge badge-' . ($team->status === 'active' ? 'success' : 'secondary')]
        );
        echo html_writer::end_div();
        
        // Descrição
        if (!empty($team->description)) {
            echo html_writer::tag('p', format_text($team->description, FORMAT_PLAIN), ['class' => 'tubaron-team-description']);
        }
        
        // Stats
        echo html_writer::start_div('tubaron-team-stats');
        echo html_writer::tag('div',
            '👥 ' . $membercount . ' ' . get_string('members', 'local_tubaron'),
            ['class' => 'tubaron-team-stat']
        );
        if ($leader) {
            echo html_writer::tag('div',
                '👑 ' . fullname($leader),
                ['class' => 'tubaron-team-stat']
            );
        }
        echo html_writer::tag('div',
            '📅 ' . userdate($team->timecreated, get_string('strftimedateshort')),
            ['class' => 'tubaron-team-stat']
        );
        echo html_writer::end_div();
        
        // Ações
        echo html_writer::start_div('tubaron-team-actions');
        
        $viewurl = new moodle_url('/local/tubaron/teams/view.php', ['id' => $team->id]);
        echo html_writer::link($viewurl, '👁️ ' . get_string('view'), ['class' => 'btn btn-sm btn-info']);
        
        if (has_capability('local/tubaron:manageteams', $context)) {
            $editurl = new moodle_url('/local/tubaron/teams/edit.php', ['id' => $team->id]);
            echo html_writer::link($editurl, '✏️ ' . get_string('edit'), ['class' => 'btn btn-sm btn-secondary']);
        }
        
        echo html_writer::end_div();
        echo html_writer::end_div(); // team-card
    }
    
    echo html_writer::end_div(); // teams-grid
    
    // Paginação
    $baseurl = new moodle_url('/local/tubaron/teams/index.php', [
        'search' => $search,
        'status' => $status,
        'seasonid' => $seasonid,
        'perpage' => $perpage
    ]);
    echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $baseurl);
}

// CSS Inline (será movido para arquivo separado)
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
    gap: 1rem;
    align-items: center;
}

.tubaron-search-form, .tubaron-filter-form {
    display: flex;
    gap: 0.5rem;
}

.tubaron-stats-mini {
    display: flex;
    gap: 2rem;
    margin-bottom: 2rem;
    padding: 1rem;
    background: #f3f4f6;
    border-radius: 8px;
}

.tubaron-stat-item {
    font-size: 1rem;
}

.tubaron-stat-item strong {
    font-size: 1.5rem;
    color: #1e3a8a;
    margin-right: 0.5rem;
}

.tubaron-teams-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.tubaron-team-card {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
}

.tubaron-team-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
    border-color: #3b82f6;
}

.tubaron-team-card.active {
    border-color: #10b981;
}

.tubaron-team-card-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 1rem;
}

.tubaron-team-name {
    margin: 0;
    font-size: 1.25rem;
    color: #1e3a8a;
}

.tubaron-team-description {
    color: #6b7280;
    font-size: 0.875rem;
    margin-bottom: 1rem;
}

.tubaron-team-stats {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
    font-size: 0.875rem;
    color: #4b5563;
}

.tubaron-team-actions {
    display: flex;
    gap: 0.5rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

@media (max-width: 768px) {
    .tubaron-teams-grid {
        grid-template-columns: 1fr;
    }
    
    .tubaron-actions-bar {
        flex-direction: column;
        align-items: stretch;
    }
}
');

echo $OUTPUT->footer();

