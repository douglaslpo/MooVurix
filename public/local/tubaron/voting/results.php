<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Voting Results
 *
 * Exibe resultados da votação com gráficos e estatísticas
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
require_capability('local/tubaron:viewrankings', $context);

// Parâmetros
$id = required_param('id', PARAM_INT);

// Buscar tarefa
$task = $DB->get_record('local_tubaron_tasks', ['id' => $id], '*', MUST_EXIST);

// Configurar página
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/tubaron/voting/results.php', ['id' => $id]));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('votingresults', 'local_tubaron'));
$PAGE->set_heading(format_string($task->title));
$PAGE->navbar->add(get_string('pluginname', 'local_tubaron'), new moodle_url('/local/tubaron/index.php'));
$PAGE->navbar->add(get_string('voting', 'local_tubaron'), new moodle_url('/local/tubaron/voting/index.php'));
$PAGE->navbar->add(get_string('results', 'local_tubaron'));

// Buscar stats e resultados
$stats = \local_tubaron\voting_manager::get_voting_stats($id);
$results = $stats->votes_received > 0 ? $stats->current_results : null;

// Output
echo $OUTPUT->header();

// Hero
echo html_writer::start_div('tubaron-results-hero');
echo html_writer::tag('h1', '📊 ' . format_string($task->title), ['class' => 'tubaron-results-hero-title']);

$statusicon = $task->status === 'completed' ? '✅' : '🗳️';
$statustext = $task->status === 'completed' ? get_string('votingclosed', 'local_tubaron') : get_string('openvoting', 'local_tubaron');
echo html_writer::tag('span', $statusicon . ' ' . $statustext, ['class' => 'badge badge-' . ($task->status === 'completed' ? 'success' : 'warning') . ' badge-lg']);

echo html_writer::end_div();

// Stats cards
echo html_writer::start_div('tubaron-results-stats-cards');

// Total votos
echo html_writer::start_div('tubaron-stat-card');
echo html_writer::tag('div', '🗳️', ['class' => 'tubaron-stat-icon']);
echo html_writer::tag('div', $stats->votes_received, ['class' => 'tubaron-stat-value']);
echo html_writer::tag('div', get_string('votesreceived', 'local_tubaron'), ['class' => 'tubaron-stat-label']);
echo html_writer::end_div();

// Taxa participação
echo html_writer::start_div('tubaron-stat-card');
echo html_writer::tag('div', '📈', ['class' => 'tubaron-stat-icon']);
echo html_writer::tag('div', round($stats->participation_rate, 1) . '%', ['class' => 'tubaron-stat-value']);
echo html_writer::tag('div', get_string('participation', 'local_tubaron'), ['class' => 'tubaron-stat-label']);
echo html_writer::end_div();

// Método
echo html_writer::start_div('tubaron-stat-card');
echo html_writer::tag('div', '⚙️', ['class' => 'tubaron-stat-icon']);
echo html_writer::tag('div', get_string('method_' . $task->votingmethod, 'local_tubaron'), ['class' => 'tubaron-stat-value small']);
echo html_writer::tag('div', get_string('votingmethod', 'local_tubaron'), ['class' => 'tubaron-stat-label']);
echo html_writer::end_div();

echo html_writer::end_div(); // stats-cards

// Resultados
if (!$results) {
    echo $OUTPUT->notification(get_string('novotesyet', 'local_tubaron'), 'info');
} else {
    echo html_writer::start_div('tubaron-results-content');
    
    switch ($task->votingmethod) {
        case 'majority':
            // Resultado Maioria
            $data = $results->data;
            
            echo html_writer::start_div('tubaron-results-majority');
            
            // Status final
            $finalstatus = $data->status === 'approved' ? 'success' : 'danger';
            $finalicon = $data->status === 'approved' ? '✅' : '❌';
            $finaltext = $data->status === 'approved' 
                ? get_string('approved', 'local_tubaron') 
                : get_string('rejected', 'local_tubaron');
            
            echo html_writer::tag('div',
                $finalicon . ' ' . strtoupper($finaltext),
                ['class' => 'tubaron-final-status ' . $finalstatus]
            );
            
            // Gráfico pizza
            echo html_writer::start_div('tubaron-majority-chart');
            
            $approvedpct = $data->total > 0 ? ($data->approved / $data->total) * 100 : 0;
            $rejectedpct = $data->total > 0 ? ($data->rejected / $data->total) * 100 : 0;
            
            echo html_writer::start_div('tubaron-pie-visual');
            echo html_writer::tag('div', 
                round($approvedpct, 1) . '%',
                [
                    'class' => 'tubaron-pie-segment approved',
                    'style' => 'width: ' . $approvedpct . '%;'
                ]
            );
            echo html_writer::tag('div',
                round($rejectedpct, 1) . '%',
                [
                    'class' => 'tubaron-pie-segment rejected',
                    'style' => 'width: ' . $rejectedpct . '%;'
                ]
            );
            echo html_writer::end_div();
            
            // Legenda
            echo html_writer::start_div('tubaron-majority-legend');
            echo html_writer::tag('div',
                '✅ ' . get_string('approved', 'local_tubaron') . ': ' . $data->approved . ' (' . round($approvedpct, 1) . '%)',
                ['class' => 'tubaron-legend-item approved']
            );
            echo html_writer::tag('div',
                '❌ ' . get_string('rejected', 'local_tubaron') . ': ' . $data->rejected . ' (' . round($rejectedpct, 1) . '%)',
                ['class' => 'tubaron-legend-item rejected']
            );
            echo html_writer::end_div();
            
            echo html_writer::end_div(); // chart
            echo html_writer::end_div(); // majority
            break;
            
        case 'rating':
            // Resultado Rating
            $data = $results->data;
            
            echo html_writer::start_div('tubaron-results-rating');
            
            // Média grande
            echo html_writer::tag('div',
                number_format($data->average, 1),
                ['class' => 'tubaron-rating-average']
            );
            echo html_writer::tag('div',
                get_string('outof10', 'local_tubaron'),
                ['class' => 'tubaron-rating-outof']
            );
            
            // Distribuição
            echo html_writer::tag('h3', get_string('distribution', 'local_tubaron'), ['class' => 'tubaron-section-title']);
            
            echo html_writer::start_div('tubaron-rating-distribution');
            $maxcount = max($data->distribution);
            
            for ($i = 10; $i >= 0; $i--) {
                $count = $data->distribution[$i];
                $percentage = $maxcount > 0 ? ($count / $maxcount) * 100 : 0;
                
                echo html_writer::start_div('tubaron-distribution-row');
                echo html_writer::tag('span', $i, ['class' => 'tubaron-distribution-label']);
                echo html_writer::start_div('tubaron-distribution-bar');
                echo html_writer::div('', 'tubaron-distribution-fill', ['style' => 'width: ' . $percentage . '%;']);
                echo html_writer::end_div();
                echo html_writer::tag('span', $count, ['class' => 'tubaron-distribution-count']);
                echo html_writer::end_div();
            }
            
            echo html_writer::end_div(); // distribution
            echo html_writer::end_div(); // rating
            break;
            
        case 'ranking':
            // Resultado Ranking
            $data = $results->data;
            
            echo html_writer::start_div('tubaron-results-ranking');
            
            echo html_writer::tag('h3', get_string('finalranking', 'local_tubaron'), ['class' => 'tubaron-section-title']);
            
            $position = 1;
            foreach ($data->scores as $submissionid => $score) {
                $submission = $DB->get_record('local_tubaron_submissions', ['id' => $submissionid]);
                $user = $DB->get_record('user', ['id' => $submission->userid]);
                
                $medals = ['🥇', '🥈', '🥉'];
                $medal = $medals[$position - 1] ?? '🏅';
                
                echo html_writer::start_div('tubaron-ranking-result-item position-' . $position);
                echo html_writer::tag('div', $medal, ['class' => 'tubaron-ranking-result-medal']);
                echo html_writer::tag('div',
                    $position . 'º ' . fullname($user),
                    ['class' => 'tubaron-ranking-result-name']
                );
                echo html_writer::tag('div',
                    $score . ' pts',
                    ['class' => 'tubaron-ranking-result-score']
                );
                echo html_writer::end_div();
                
                $position++;
                if ($position > 3) break; // Mostrar apenas top 3
            }
            
            echo html_writer::end_div(); // ranking
            break;
    }
    
    echo html_writer::end_div(); // results-content
}

// Ações
echo html_writer::start_div('tubaron-results-actions');

$backurl = new moodle_url('/local/tubaron/voting/index.php');
echo html_writer::link($backurl, '« ' . get_string('backtovoting', 'local_tubaron'), ['class' => 'btn btn-secondary']);

$taskurl = new moodle_url('/local/tubaron/tasks/view.php', ['id' => $id]);
echo html_writer::link($taskurl, get_string('viewtask', 'local_tubaron'), ['class' => 'btn btn-info']);

echo html_writer::end_div();

// CSS
echo html_writer::tag('style', '
.tubaron-results-hero {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    color: white;
    padding: 2.5rem 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    text-align: center;
}

.tubaron-results-hero-title {
    font-size: 2rem;
    margin: 0 0 1rem 0;
}

.tubaron-results-stats-cards {
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

.tubaron-stat-value.small {
    font-size: 1.25rem;
}

.tubaron-results-content {
    background: white;
    border-radius: 12px;
    padding: 3rem 2rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* Maioria */
.tubaron-final-status {
    font-size: 3rem;
    font-weight: 700;
    text-align: center;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 2rem;
}

.tubaron-final-status.success {
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    color: #047857;
}

.tubaron-final-status.danger {
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    color: #b91c1c;
}

.tubaron-pie-visual {
    display: flex;
    height: 60px;
    border-radius: 30px;
    overflow: hidden;
    margin: 2rem 0;
}

.tubaron-pie-segment {
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: white;
    font-size: 1.25rem;
}

.tubaron-pie-segment.approved {
    background: #10b981;
}

.tubaron-pie-segment.rejected {
    background: #ef4444;
}

.tubaron-majority-legend {
    display: flex;
    justify-content: center;
    gap: 3rem;
    font-size: 1.125rem;
}

.tubaron-legend-item.approved {
    color: #047857;
    font-weight: 600;
}

.tubaron-legend-item.rejected {
    color: #b91c1c;
    font-weight: 600;
}

/* Rating */
.tubaron-rating-average {
    font-size: 6rem;
    font-weight: 700;
    color: #8b5cf6;
    text-align: center;
    margin: 2rem 0 0.5rem 0;
}

.tubaron-rating-outof {
    text-align: center;
    font-size: 1.5rem;
    color: #6b7280;
    margin-bottom: 3rem;
}

.tubaron-section-title {
    font-size: 1.25rem;
    color: #1e3a8a;
    margin: 2rem 0 1.5rem 0;
    text-align: center;
}

.tubaron-rating-distribution {
    max-width: 600px;
    margin: 0 auto;
}

.tubaron-distribution-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.75rem;
}

.tubaron-distribution-label {
    font-weight: 600;
    color: #1e3a8a;
    width: 30px;
    text-align: right;
}

.tubaron-distribution-bar {
    flex: 1;
    height: 32px;
    background: #e5e7eb;
    border-radius: 16px;
    overflow: hidden;
}

.tubaron-distribution-fill {
    height: 100%;
    background: linear-gradient(90deg, #8b5cf6, #6366f1);
    transition: width 0.5s;
}

.tubaron-distribution-count {
    font-weight: 600;
    color: #6b7280;
    width: 40px;
    text-align: center;
}

/* Ranking */
.tubaron-results-ranking {
    max-width: 600px;
    margin: 0 auto;
}

.tubaron-ranking-result-item {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.5rem;
    border-radius: 12px;
    margin-bottom: 1rem;
}

.tubaron-ranking-result-item.position-1 {
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
    border: 3px solid #fbbf24;
}

.tubaron-ranking-result-item.position-2 {
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
    border: 3px solid #94a3b8;
}

.tubaron-ranking-result-item.position-3 {
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    border: 3px solid #cd7f32;
}

.tubaron-ranking-result-medal {
    font-size: 3rem;
}

.tubaron-ranking-result-name {
    flex: 1;
    font-size: 1.5rem;
    font-weight: 600;
    color: #1e3a8a;
}

.tubaron-ranking-result-score {
    font-size: 1.25rem;
    font-weight: 700;
    color: #b45309;
}

.tubaron-results-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 3rem;
}

@media (max-width: 768px) {
    .tubaron-results-stats-cards {
        grid-template-columns: 1fr;
    }
}
');

echo $OUTPUT->footer();

