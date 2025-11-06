<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - LGPD Export User Data
 *
 * Exportação de dados pessoais conforme LGPD Art. 18
 * (Direito à Portabilidade de Dados)
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

// Parâmetros
$format = optional_param('format', 'view', PARAM_ALPHA); // view, json, csv

// Configurar página
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/tubaron/privacy/export_data.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('exportmydata', 'local_tubaron'));
$PAGE->set_heading(get_string('exportmydata', 'local_tubaron'));
$PAGE->navbar->add(get_string('pluginname', 'local_tubaron'), new moodle_url('/local/tubaron/index.php'));
$PAGE->navbar->add(get_string('privacy'), new moodle_url('/local/tubaron/privacy/export_data.php'));

// Coletar todos os dados do usuário
$userdata = [];

// 1. Perfil básico
$userdata['profile'] = [
    'userid' => $USER->id,
    'username' => $USER->username,
    'email' => $USER->email,
    'firstname' => $USER->firstname,
    'lastname' => $USER->lastname,
    'exportdate' => userdate(time(), get_string('strftimedatetime')),
];

// 2. Tasks criadas
$userdata['tasks_created'] = $DB->get_records('local_tubaron_tasks', ['createdby' => $USER->id]);

// 3. Submissions
$userdata['submissions'] = $DB->get_records('local_tubaron_submissions', ['userid' => $USER->id]);

// 4. Votos realizados
$userdata['votes'] = $DB->get_records_sql(
    "SELECT v.*, t.title as tasktitle
     FROM {local_tubaron_votes} v
     JOIN {local_tubaron_tasks} t ON t.id = v.taskid
     WHERE v.voterid = ?
     ORDER BY v.timecreated DESC",
    [$USER->id]
);

// 5. Achievements
$userdata['achievements'] = $DB->get_records_sql(
    "SELECT ua.timeunlocked, a.name, a.description, a.tier
     FROM {local_tubaron_user_achievements} ua
     JOIN {local_tubaron_achievements} a ON a.id = ua.achievementid
     WHERE ua.userid = ?
     ORDER BY ua.timeunlocked DESC",
    [$USER->id]
);

// 6. Rankings
$userdata['rankings'] = $DB->get_records_sql(
    "SELECT r.*, s.seasonname
     FROM {local_tubaron_rankings} r
     JOIN {local_tubaron_seasons} s ON s.id = r.seasonid
     WHERE r.userid = ?
     ORDER BY s.startdate DESC",
    [$USER->id]
);

// 7. Equipes
$userdata['teams'] = $DB->get_records_sql(
    "SELECT tm.role, tm.joineddate, t.name as teamname, t.status
     FROM {local_tubaron_team_members} tm
     JOIN {local_tubaron_teams} t ON t.id = tm.teamid
     WHERE tm.userid = ?
     ORDER BY tm.joineddate DESC",
    [$USER->id]
);

// Export JSON
if ($format === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="tubaron_mydata_' . $USER->id . '_' . time() . '.json"');
    echo json_encode($userdata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Export CSV (todas tabelas em um ZIP)
if ($format === 'csv') {
    require_once($CFG->libdir . '/filelib.php');
    
    $tempdir = make_temp_directory('tubaron_export');
    $timestamp = time();
    
    foreach ($userdata as $tablename => $records) {
        if (empty($records)) continue;
        
        $filename = $tempdir . '/tubaron_' . $tablename . '.csv';
        $fp = fopen($filename, 'w');
        
        // UTF-8 BOM
        fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Headers
        if ($tablename === 'profile') {
            fputcsv($fp, array_keys($records));
            fputcsv($fp, array_values($records));
        } else {
            $first = true;
            foreach ($records as $record) {
                $row = (array)$record;
                if ($first) {
                    fputcsv($fp, array_keys($row));
                    $first = false;
                }
                fputcsv($fp, array_values($row));
            }
        }
        
        fclose($fp);
    }
    
    // Criar ZIP
    $zipfile = $tempdir . '/tubaron_mydata_' . $USER->id . '_' . $timestamp . '.zip';
    $zip = new ZipArchive();
    $zip->open($zipfile, ZipArchive::CREATE);
    
    foreach (glob($tempdir . '/*.csv') as $csvfile) {
        $zip->addFile($csvfile, basename($csvfile));
    }
    
    $zip->close();
    
    // Download
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($zipfile) . '"');
    header('Content-Length: ' . filesize($zipfile));
    readfile($zipfile);
    
    // Cleanup
    foreach (glob($tempdir . '/*') as $file) {
        unlink($file);
    }
    
    exit;
}

// View HTML
echo $OUTPUT->header();

// Hero
echo html_writer::start_div('tubaron-privacy-hero');
echo html_writer::tag('h1', '🔒 ' . get_string('exportmydata', 'local_tubaron'));
echo html_writer::tag('p', get_string('exportmydatadesc', 'local_tubaron'));
echo html_writer::end_div();

// Info LGPD
echo $OUTPUT->notification(
    get_string('lgpdinfo', 'local_tubaron'),
    'info'
);

// Export buttons
echo html_writer::start_div('tubaron-export-buttons');
echo html_writer::link(
    new moodle_url('/local/tubaron/privacy/export_data.php', ['format' => 'json']),
    '📄 ' . get_string('exportjson', 'local_tubaron'),
    ['class' => 'btn btn-primary btn-lg']
);
echo html_writer::link(
    new moodle_url('/local/tubaron/privacy/export_data.php', ['format' => 'csv']),
    '📊 ' . get_string('exportcsv', 'local_tubaron'),
    ['class' => 'btn btn-secondary btn-lg']
);
echo html_writer::end_div();

// Preview dos dados
echo html_writer::tag('h2', get_string('datapreview', 'local_tubaron'));

$tabs = [
    'profile' => get_string('profile'),
    'tasks_created' => get_string('taskscreated', 'local_tubaron'),
    'submissions' => get_string('submissions', 'local_tubaron'),
    'votes' => get_string('votes', 'local_tubaron'),
    'achievements' => get_string('achievements', 'local_tubaron'),
    'rankings' => get_string('rankings', 'local_tubaron'),
    'teams' => get_string('teams', 'local_tubaron'),
];

echo html_writer::start_div('tubaron-data-tabs');
foreach ($tabs as $key => $label) {
    $count = is_array($userdata[$key]) ? count($userdata[$key]) : 1;
    $active = $key === 'profile' ? 'active' : '';
    echo html_writer::tag('button',
        $label . ' (' . $count . ')',
        ['class' => 'tubaron-tab-btn ' . $active, 'data-tab' => $key]
    );
}
echo html_writer::end_div();

echo html_writer::start_div('tubaron-data-content');
foreach ($tabs as $key => $label) {
    $active = $key === 'profile' ? 'active' : '';
    echo html_writer::start_div('tubaron-tab-panel ' . $active, ['data-panel' => $key]);
    
    echo html_writer::tag('h3', $label);
    
    if ($key === 'profile') {
        echo html_writer::start_tag('table', ['class' => 'table table-bordered']);
        foreach ($userdata[$key] as $k => $v) {
            echo html_writer::start_tag('tr');
            echo html_writer::tag('th', ucfirst($k));
            echo html_writer::tag('td', $v);
            echo html_writer::end_tag('tr');
        }
        echo html_writer::end_tag('table');
    } else {
        $records = $userdata[$key];
        if (empty($records)) {
            echo html_writer::tag('p', get_string('nodatafound', 'local_tubaron'));
        } else {
            echo html_writer::tag('pre', json_encode(array_values($records), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
    
    echo html_writer::end_div();
}
echo html_writer::end_div();

// JavaScript
echo html_writer::tag('script', "
document.addEventListener('DOMContentLoaded', function() {
    const tabBtns = document.querySelectorAll('.tubaron-tab-btn');
    const panels = document.querySelectorAll('.tubaron-tab-panel');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tab = this.dataset.tab;
            
            tabBtns.forEach(b => b.classList.remove('active'));
            panels.forEach(p => p.classList.remove('active'));
            
            this.classList.add('active');
            document.querySelector('[data-panel=\"' + tab + '\"]').classList.add('active');
        });
    });
});
");

// CSS
echo html_writer::tag('style', '
.tubaron-privacy-hero {
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
    color: white;
    padding: 3rem 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    text-align: center;
}

.tubaron-privacy-hero h1 {
    margin: 0 0 1rem 0;
}

.tubaron-export-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin: 2rem 0;
}

.tubaron-data-tabs {
    display: flex;
    gap: 0.5rem;
    border-bottom: 2px solid #e5e7eb;
    margin: 2rem 0 1rem 0;
    flex-wrap: wrap;
}

.tubaron-tab-btn {
    padding: 0.75rem 1.5rem;
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

.tubaron-tab-btn.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
}

.tubaron-tab-panel {
    display: none;
}

.tubaron-tab-panel.active {
    display: block;
}

pre {
    background: #f3f4f6;
    padding: 1rem;
    border-radius: 8px;
    overflow-x: auto;
    font-size: 0.875rem;
}
');

echo $OUTPUT->footer();

