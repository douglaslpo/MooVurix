<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Voting Index
 *
 * Lista tarefas em votação com filtros e estatísticas
 * Integrado ao MooVurix LMS Platform
 *
 * @package    local_tubaron
 * @copyright  2025 Tubaron Telecomunicações
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/tablelib.php');
require_once(__DIR__ . '/../lib.php');

// Autenticação
require_login();
$context = context_system::instance();

// Verificar capability
require_capability('local/tubaron:vote', $context);

// Parâmetros
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 20, PARAM_INT);
$type = optional_param('type', 'all', PARAM_ALPHA);

// Configurar página
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/tubaron/voting/index.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('voting', 'local_tubaron'));
$PAGE->set_heading(get_string('voting', 'local_tubaron'));
$PAGE->navbar->add(get_string('pluginname', 'local_tubaron'), new moodle_url('/local/tubaron/index.php'));
$PAGE->navbar->add(get_string('voting', 'local_tubaron'));

// Output
echo $OUTPUT->header();

// Hero Section
echo html_writer::start_div('tubaron-hero');
echo html_writer::tag('h1', '🗳️ ' . get_string('voting', 'local_tubaron'), ['class' => 'tubaron-hero-title']);
echo html_writer::tag('p', get_string('voting_description', 'local_tubaron'), ['class' => 'tubaron-hero-subtitle']);
echo html_writer::end_div();

// Buscar tarefas em votação
global $DB, $USER;

$whereclauses = ['t.status = ?'];
$params = ['voting'];

if ($type !== 'all') {
    $whereclauses[] = 't.type = ?';
    $params[] = $type;
}

$where = implode(' AND ', $whereclauses);

$sql = "SELECT t.*, m.name as missionname, COUNT(v.id) as votecount
        FROM {local_tubaron_tasks} t
        JOIN {local_tubaron_missions} m ON m.id = t.missionid
        LEFT JOIN {local_tubaron_votes} v ON v.taskid = t.id
        WHERE $where
        GROUP BY t.id, m.name
        ORDER BY t.votingdeadline ASC, t.timecreated DESC";

$totalcount = $DB->count_records('local_tubaron_tasks', ['status' => 'voting']);
$tasks = $DB->get_records_sql($sql, $params, $page * $perpage, $perpage);

// Stats globais
$totalvoting = $DB->count_records('local_tubaron_tasks', ['status' => 'voting']);
$uservotes = $DB->count_records('local_tubaron_votes', ['voterid' => $USER->id]);

// Votos pendentes (elegível mas não votou)
$pendingtasks = 0;
foreach ($tasks as $task) {
    if (\local_tubaron\voting_manager::check_eligibility($task->id, $USER->id) &&
        !\local_tubaron\voting_manager::has_voted($task->id, $USER->id)) {
        $pendingtasks++;
    }
}

echo html_writer::start_div('tubaron-stats-mini');
echo html_writer::tag('div', 
    html_writer::tag('strong', $totalvoting) . ' ' . get_string('tasksinvoting', 'local_tubaron'),
    ['class' => 'tubaron-stat-item']
);
echo html_writer::tag('div',
    html_writer::tag('strong', $uservotes) . ' ' . get_string('yourvotes', 'local_tubaron'),
    ['class' => 'tubaron-stat-item']
);
echo html_writer::tag('div',
    html_writer::tag('strong', $pendingtasks) . ' ' . get_string('pendingyourvotes', 'local_tubaron'),
    ['class' => 'tubaron-stat-item pending']
);
echo html_writer::end_div();

// Filtro por tipo
echo html_writer::start_div('tubaron-filters');
$typeoptions = [
    'all' => get_string('alltypes', 'local_tubaron'),
    'individual' => get_string('type_individual', 'local_tubaron'),
    'team' => get_string('type_team', 'local_tubaron'),
    'competitive' => get_string('type_competitive', 'local_tubaron'),
];
echo html_writer::start_tag('form', ['method' => 'get', 'class' => 'tubaron-filter-form']);
echo html_writer::select($typeoptions, 'type', $type, false, ['class' => 'form-control', 'onchange' => 'this.form.submit()']);
echo html_writer::end_tag('form');
echo html_writer::end_div();

// Lista de tarefas em votação
if (empty($tasks)) {
    echo $OUTPUT->notification(get_string('notasksinvoting', 'local_tubaron'), 'info');
} else {
    echo html_writer::start_div('tubaron-voting-grid');
    
    foreach ($tasks as $task) {
        // Verificar elegibilidade
        $eligible = \local_tubaron\voting_manager::check_eligibility($task->id, $USER->id);
        $hasvoted = \local_tubaron\voting_manager::has_voted($task->id, $USER->id);
        
        // Obter stats
        $stats = \local_tubaron\voting_manager::get_voting_stats($task->id);
        
        // Ícones método
        $methodicons = [
            'majority' => '✅',
            'rating' => '⭐',
            'ranking' => '🏆'
        ];
        $methodicon = $methodicons[$task->votingmethod] ?? '🗳️';
        
        // Card tarefa
        echo html_writer::start_div('tubaron-voting-card ' . ($hasvoted ? 'voted' : ($eligible ? 'eligible' : '')));
        
        // Header
        echo html_writer::start_div('tubaron-voting-card-header');
        echo html_writer::tag('h3', 
            format_string($task->title),
            ['class' => 'tubaron-voting-title']
        );
        
        if ($hasvoted) {
            echo html_writer::tag('span', '✓ ' . get_string('voted', 'local_tubaron'), ['class' => 'badge badge-success']);
        } else if ($eligible) {
            echo html_writer::tag('span', '⏳ ' . get_string('pending', 'local_tubaron'), ['class' => 'badge badge-warning']);
        } else {
            echo html_writer::tag('span', '🔒 ' . get_string('noteligible', 'local_tubaron'), ['class' => 'badge badge-secondary']);
        }
        echo html_writer::end_div();
        
        // Meta info
        echo html_writer::start_div('tubaron-voting-meta');
        echo html_writer::tag('span',
            get_string('type_' . $task->type, 'local_tubaron'),
            ['class' => 'badge badge-dark']
        );
        echo html_writer::tag('span',
            $methodicon . ' ' . get_string('method_' . $task->votingmethod, 'local_tubaron'),
            ['class' => 'tubaron-voting-method']
        );
        echo html_writer::tag('span',
            '🏆 ' . $task->points . ' pts',
            ['class' => 'tubaron-voting-points']
        );
        echo html_writer::end_div();
        
        // Progress bar votação
        $progress = $stats->eligible_voters > 0 
            ? ($stats->votes_received / $stats->eligible_voters) * 100 
            : 0;
        
        echo html_writer::start_div('tubaron-voting-progress');
        echo html_writer::tag('div',
            get_string('votesprogress', 'local_tubaron', [
                'received' => $stats->votes_received,
                'eligible' => $stats->eligible_voters
            ]),
            ['class' => 'tubaron-voting-progress-label']
        );
        echo html_writer::start_div('tubaron-progress-bar');
        echo html_writer::div('', 'tubaron-progress-fill', ['style' => 'width: ' . $progress . '%']);
        echo html_writer::end_div();
        echo html_writer::end_div();
        
        // Deadline
        if ($task->votingdeadline > 0) {
            $remaining = $task->votingdeadline - time();
            $isurgent = $remaining < 3600 * 24; // < 24h
            
            echo html_writer::tag('div',
                '⏰ ' . get_string('deadline', 'local_tubaron') . ': ' . 
                userdate($task->votingdeadline, get_string('strftimedatetime')),
                ['class' => 'tubaron-voting-deadline' . ($isurgent ? ' urgent' : '')]
            );
        }
        
        // Ações
        echo html_writer::start_div('tubaron-voting-actions');
        
        if ($eligible && !$hasvoted) {
            $voteurl = new moodle_url('/local/tubaron/voting/vote.php', ['id' => $task->id]);
            echo html_writer::link($voteurl, '🗳️ ' . get_string('vote', 'local_tubaron'), [
                'class' => 'btn btn-primary btn-lg'
            ]);
        }
        
        $resultsurl = new moodle_url('/local/tubaron/voting/results.php', ['id' => $task->id]);
        echo html_writer::link($resultsurl, '📊 ' . get_string('viewresults', 'local_tubaron'), [
            'class' => 'btn btn-secondary'
        ]);
        
        echo html_writer::end_div();
        echo html_writer::end_div(); // voting-card
    }
    
    echo html_writer::end_div(); // voting-grid
    
    // Paginação
    $baseurl = new moodle_url('/local/tubaron/voting/index.php', [
        'type' => $type,
        'perpage' => $perpage
    ]);
    echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $baseurl);
}

// CSS
echo html_writer::tag('style', '
.tubaron-hero {
    background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
    color: white;
    padding: 3rem 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
}

.tubaron-stats-mini {
    display: flex;
    gap: 2rem;
    margin-bottom: 2rem;
    padding: 1rem;
    background: #f3f4f6;
    border-radius: 8px;
    flex-wrap: wrap;
}

.tubaron-stat-item.pending strong {
    color: #f59e0b;
}

.tubaron-voting-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.tubaron-voting-card {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s;
}

.tubaron-voting-card.eligible {
    border-left: 4px solid #f59e0b;
}

.tubaron-voting-card.voted {
    border-left: 4px solid #10b981;
    opacity: 0.8;
}

.tubaron-voting-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.1);
}

.tubaron-voting-card-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 1rem;
    gap: 1rem;
}

.tubaron-voting-title {
    margin: 0;
    font-size: 1.25rem;
    color: #1e3a8a;
    flex: 1;
}

.tubaron-voting-meta {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.tubaron-voting-progress {
    margin: 1rem 0;
}

.tubaron-voting-progress-label {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 0.5rem;
}

.tubaron-progress-bar {
    width: 100%;
    height: 8px;
    background: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
}

.tubaron-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #8b5cf6, #6366f1);
    transition: width 0.3s;
}

.tubaron-voting-deadline {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 1rem 0;
}

.tubaron-voting-deadline.urgent {
    color: #dc2626;
    font-weight: 600;
}

.tubaron-voting-actions {
    display: flex;
    gap: 0.5rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

@media (max-width: 768px) {
    .tubaron-voting-grid {
        grid-template-columns: 1fr;
    }
}
');

echo $OUTPUT->footer();

