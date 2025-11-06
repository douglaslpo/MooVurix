<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Analytics Dashboard
 *
 * Dashboard administrativo com analytics avançado e gráficos interativos
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
require_capability('local/tubaron:viewadmindashboard', $context);

// Parâmetros filtros
$seasonid = optional_param('seasonid', 0, PARAM_INT);
$from = optional_param('from', strtotime('-30 days'), PARAM_INT);
$to = optional_param('to', time(), PARAM_INT);

// Buscar temporada
if (!$seasonid) {
    $season = local_tubaron_get_active_season();
    $seasonid = $season ? $season->id : 0;
} else {
    $season = $DB->get_record('local_tubaron_seasons', ['id' => $seasonid]);
}

// Configurar página
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/tubaron/admin/analytics.php', ['seasonid' => $seasonid]));
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('analytics', 'local_tubaron'));
$PAGE->set_heading(get_string('analytics', 'local_tubaron'));
$PAGE->navbar->add(get_string('pluginname', 'local_tubaron'), new moodle_url('/local/tubaron/index.php'));
$PAGE->navbar->add(get_string('admin', 'local_tubaron'));
$PAGE->navbar->add(get_string('analytics', 'local_tubaron'));

// Incluir Chart.js
$PAGE->requires->js(new moodle_url('https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js'), true);

// Coletar dados analytics
if (!$season) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('noactiveseason', 'local_tubaron'), 'warning');
    echo $OUTPUT->footer();
    exit;
}

// === KPIs ===
$kpis = new \stdClass();

// Total tarefas
$kpis->total_tasks = $DB->count_records_sql(
    "SELECT COUNT(t.id) FROM {local_tubaron_tasks} t
     JOIN {local_tubaron_missions} m ON m.id = t.missionid
     WHERE m.seasonid = ?",
    [$seasonid]
);

// Total votos
$kpis->total_votes = $DB->count_records_sql(
    "SELECT COUNT(v.id) FROM {local_tubaron_votes} v
     JOIN {local_tubaron_tasks} t ON t.id = v.taskid
     JOIN {local_tubaron_missions} m ON m.id = t.missionid
     WHERE m.seasonid = ?",
    [$seasonid]
);

// Taxa participação
$eligible_total = $DB->count_records_sql(
    "SELECT COUNT(DISTINCT voterid) FROM {local_tubaron_votes} v
     JOIN {local_tubaron_tasks} t ON t.id = v.taskid
     JOIN {local_tubaron_missions} m ON m.id = t.missionid
     WHERE m.seasonid = ?",
    [$seasonid]
);
$active_users = $DB->count_records('user', ['deleted' => 0, 'suspended' => 0]);
$kpis->participation_rate = $active_users > 0 ? ($eligible_total / $active_users) * 100 : 0;

// Equipes ativas
$kpis->active_teams = $DB->count_records('local_tubaron_teams', [
    'seasonid' => $seasonid,
    'status' => 'active'
]);

// Dias restantes
$kpis->days_remaining = max(0, ceil(($season->enddate - time()) / 86400));

// === Dados Charts ===

// 1. Pontuação ao longo do tempo (últimos 30 dias)
$timelinedata = $DB->get_records_sql(
    "SELECT DATE(to_timestamp(s.lastupdated)) as date, 
            SUM(s.totalpoints) as points
     FROM {local_tubaron_scores} s
     WHERE s.seasonid = ? AND s.entitytype = ? 
     AND s.lastupdated >= ? AND s.lastupdated <= ?
     GROUP BY DATE(to_timestamp(s.lastupdated))
     ORDER BY date ASC",
    [$seasonid, 'user', $from, $to]
);

// 2. Distribuição tipos tarefas
$typedist = $DB->get_records_sql(
    "SELECT t.type, COUNT(t.id) as count
     FROM {local_tubaron_tasks} t
     JOIN {local_tubaron_missions} m ON m.id = t.missionid
     WHERE m.seasonid = ?
     GROUP BY t.type",
    [$seasonid]
);

// 3. Top 10 performers
$topperformers = local_tubaron_get_top_rankings($seasonid, 'user', 10);

// 4. Distribuição status tarefas
$statusdist = $DB->get_records_sql(
    "SELECT t.status, COUNT(t.id) as count
     FROM {local_tubaron_tasks} t
     JOIN {local_tubaron_missions} m ON m.id = t.missionid
     WHERE m.seasonid = ?
     GROUP BY t.status",
    [$seasonid]
);

// Output
echo $OUTPUT->header();

// Hero
echo html_writer::start_div('tubaron-analytics-hero');
echo html_writer::tag('h1', '📊 ' . get_string('analytics', 'local_tubaron'), ['class' => 'tubaron-analytics-title']);
echo html_writer::tag('p', format_string($season->name), ['class' => 'tubaron-analytics-season']);

// Actions bar
echo html_writer::start_div('tubaron-analytics-actions');

// Export buttons
$exportcsvurl = new moodle_url('/local/tubaron/admin/export.php', [
    'seasonid' => $seasonid,
    'format' => 'csv',
    'type' => 'full'
]);
echo html_writer::link($exportcsvurl, '📥 ' . get_string('exportcsv', 'local_tubaron'), [
    'class' => 'btn btn-secondary'
]);

$exportpdfurl = new moodle_url('/local/tubaron/admin/export.php', [
    'seasonid' => $seasonid,
    'format' => 'pdf',
    'type' => 'full'
]);
echo html_writer::link($exportpdfurl, '📄 ' . get_string('exportpdf', 'local_tubaron'), [
    'class' => 'btn btn-secondary'
]);

echo html_writer::end_div(); // actions
echo html_writer::end_div(); // hero

// KPIs Cards
echo html_writer::start_div('tubaron-kpis-grid');

// Card 1: Total Tarefas
echo html_writer::start_div('tubaron-kpi-card blue');
echo html_writer::tag('div', '📋', ['class' => 'tubaron-kpi-icon']);
echo html_writer::tag('div', $kpis->total_tasks, ['class' => 'tubaron-kpi-value']);
echo html_writer::tag('div', get_string('totaltasks', 'local_tubaron'), ['class' => 'tubaron-kpi-label']);
echo html_writer::end_div();

// Card 2: Participação
echo html_writer::start_div('tubaron-kpi-card green');
echo html_writer::tag('div', '📈', ['class' => 'tubaron-kpi-icon']);
echo html_writer::tag('div', round($kpis->participation_rate, 1) . '%', ['class' => 'tubaron-kpi-value']);
echo html_writer::tag('div', get_string('participation', 'local_tubaron'), ['class' => 'tubaron-kpi-label']);
echo html_writer::end_div();

// Card 3: Total Votos
echo html_writer::start_div('tubaron-kpi-card purple');
echo html_writer::tag('div', '🗳️', ['class' => 'tubaron-kpi-icon']);
echo html_writer::tag('div', number_format($kpis->total_votes), ['class' => 'tubaron-kpi-value']);
echo html_writer::tag('div', get_string('totalvotes', 'local_tubaron'), ['class' => 'tubaron-kpi-label']);
echo html_writer::end_div();

// Card 4: Equipes Ativas
echo html_writer::start_div('tubaron-kpi-card orange');
echo html_writer::tag('div', '👥', ['class' => 'tubaron-kpi-icon']);
echo html_writer::tag('div', $kpis->active_teams, ['class' => 'tubaron-kpi-value']);
echo html_writer::tag('div', get_string('activeteams', 'local_tubaron'), ['class' => 'tubaron-kpi-label']);
echo html_writer::end_div();

// Card 5: Dias Restantes
echo html_writer::start_div('tubaron-kpi-card red');
echo html_writer::tag('div', '⏰', ['class' => 'tubaron-kpi-icon']);
echo html_writer::tag('div', $kpis->days_remaining, ['class' => 'tubaron-kpi-value']);
echo html_writer::tag('div', get_string('daysremaining', 'local_tubaron'), ['class' => 'tubaron-kpi-label']);
echo html_writer::end_div();

echo html_writer::end_div(); // kpis-grid

// Charts Grid
echo html_writer::start_div('tubaron-charts-grid');

// Chart 1: Distribuição Tipos (Pie)
echo html_writer::start_div('tubaron-chart-container');
echo html_writer::tag('h3', '📊 ' . get_string('tasktypesdistribution', 'local_tubaron'), ['class' => 'tubaron-chart-title']);
echo html_writer::tag('canvas', '', ['id' => 'chartTaskTypes', 'height' => '300']);
echo html_writer::end_div();

// Chart 2: Status Tarefas (Doughnut)
echo html_writer::start_div('tubaron-chart-container');
echo html_writer::tag('h3', '📈 ' . get_string('taskstatusdistribution', 'local_tubaron'), ['class' => 'tubaron-chart-title']);
echo html_writer::tag('canvas', '', ['id' => 'chartTaskStatus', 'height' => '300']);
echo html_writer::end_div();

// Chart 3: Top Performers (Bar)
echo html_writer::start_div('tubaron-chart-container full-width');
echo html_writer::tag('h3', '🏆 ' . get_string('topperformers', 'local_tubaron'), ['class' => 'tubaron-chart-title']);
echo html_writer::tag('canvas', '', ['id' => 'chartTopPerformers', 'height' => '400']);
echo html_writer::end_div();

echo html_writer::end_div(); // charts-grid

// Preparar dados para JavaScript
$chartdata = [
    'typeDistribution' => array_values(array_map(function($item) {
        return [
            'type' => $item->type,
            'count' => $item->count
        ];
    }, $typedist)),
    'statusDistribution' => array_values(array_map(function($item) {
        return [
            'status' => $item->status,
            'count' => $item->count
        ];
    }, $statusdist)),
    'topPerformers' => array_values(array_map(function($item) use ($DB) {
        $user = $DB->get_record('user', ['id' => $item->entityid]);
        return [
            'name' => fullname($user),
            'points' => $item->totalpoints
        ];
    }, $topperformers))
];

// JavaScript para renderizar charts
echo html_writer::tag('script', "
document.addEventListener('DOMContentLoaded', function() {
    const chartData = " . json_encode($chartdata) . ";
    
    // Chart 1: Task Types (Pie)
    if (document.getElementById('chartTaskTypes')) {
        new Chart(document.getElementById('chartTaskTypes'), {
            type: 'pie',
            data: {
                labels: chartData.typeDistribution.map(d => d.type === 'individual' ? 'Individual' : d.type === 'team' ? 'Equipe' : 'Competitiva'),
                datasets: [{
                    data: chartData.typeDistribution.map(d => d.count),
                    backgroundColor: ['#3b82f6', '#10b981', '#8b5cf6'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { size: 14 } }
                    }
                }
            }
        });
    }
    
    // Chart 2: Task Status (Doughnut)
    if (document.getElementById('chartTaskStatus')) {
        new Chart(document.getElementById('chartTaskStatus'), {
            type: 'doughnut',
            data: {
                labels: chartData.statusDistribution.map(d => d.status === 'open' ? 'Abertas' : d.status === 'voting' ? 'Votação' : d.status === 'completed' ? 'Concluídas' : 'Em Andamento'),
                datasets: [{
                    data: chartData.statusDistribution.map(d => d.count),
                    backgroundColor: ['#3b82f6', '#8b5cf6', '#10b981', '#f59e0b'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { size: 14 } }
                    }
                }
            }
        });
    }
    
    // Chart 3: Top Performers (Bar)
    if (document.getElementById('chartTopPerformers')) {
        new Chart(document.getElementById('chartTopPerformers'), {
            type: 'bar',
            data: {
                labels: chartData.topPerformers.map(d => d.name),
                datasets: [{
                    label: 'Pontos',
                    data: chartData.topPerformers.map(d => d.points),
                    backgroundColor: '#3b82f6',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { font: { size: 12 } }
                    },
                    y: {
                        ticks: { font: { size: 12 } }
                    }
                }
            }
        });
    }
});
");

// CSS
echo html_writer::tag('style', '
.tubaron-analytics-hero {
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    color: white;
    padding: 2.5rem 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
}

.tubaron-analytics-title {
    font-size: 2.5rem;
    margin: 0 0 0.5rem 0;
}

.tubaron-analytics-season {
    font-size: 1.25rem;
    opacity: 0.9;
    margin: 0 0 1.5rem 0;
}

.tubaron-analytics-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.tubaron-kpis-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.tubaron-kpi-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border-top: 4px solid;
    transition: transform 0.3s;
}

.tubaron-kpi-card:hover {
    transform: translateY(-4px);
}

.tubaron-kpi-card.blue { border-top-color: #3b82f6; }
.tubaron-kpi-card.green { border-top-color: #10b981; }
.tubaron-kpi-card.purple { border-top-color: #8b5cf6; }
.tubaron-kpi-card.orange { border-top-color: #f59e0b; }
.tubaron-kpi-card.red { border-top-color: #ef4444; }

.tubaron-kpi-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.tubaron-kpi-value {
    font-size: 3rem;
    font-weight: 700;
    color: #1e3a8a;
}

.tubaron-kpi-label {
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.tubaron-charts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 2rem;
    margin-bottom: 2rem;
}

.tubaron-chart-container {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.tubaron-chart-container.full-width {
    grid-column: 1 / -1;
}

.tubaron-chart-title {
    font-size: 1.25rem;
    color: #1e3a8a;
    margin: 0 0 1.5rem 0;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #e5e7eb;
}

@media (max-width: 968px) {
    .tubaron-charts-grid {
        grid-template-columns: 1fr;
    }
    
    .tubaron-kpis-grid {
        grid-template-columns: 1fr;
    }
}
');

echo $OUTPUT->footer();

