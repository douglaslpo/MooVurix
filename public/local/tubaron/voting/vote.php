<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Voting Interface
 *
 * Interface para votar em tarefas (3 métodos: maioria/rating/ranking)
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
require_capability('local/tubaron:vote', $context);

// Parâmetros
$id = required_param('id', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);

// Buscar tarefa
$task = $DB->get_record('local_tubaron_tasks', ['id' => $id], '*', MUST_EXIST);

// Verificar se votação está aberta
if ($task->status !== 'voting') {
    throw new moodle_exception('votingnotopen', 'local_tubaron');
}

// Verificar elegibilidade
if (!\local_tubaron\voting_manager::check_eligibility($id, $USER->id)) {
    throw new moodle_exception('noteligible', 'local_tubaron');
}

// Verificar se já votou
if (\local_tubaron\voting_manager::has_voted($id, $USER->id)) {
    redirect(
        new moodle_url('/local/tubaron/voting/results.php', ['id' => $id]),
        get_string('alreadyvoted', 'local_tubaron'),
        null,
        \core\output\notification::NOTIFY_INFO
    );
}

// Configurar página
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/tubaron/voting/vote.php', ['id' => $id]));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('vote', 'local_tubaron'));
$PAGE->set_heading(format_string($task->title));
$PAGE->navbar->add(get_string('pluginname', 'local_tubaron'), new moodle_url('/local/tubaron/index.php'));
$PAGE->navbar->add(get_string('voting', 'local_tubaron'), new moodle_url('/local/tubaron/voting/index.php'));
$PAGE->navbar->add(format_string($task->title));

// Processar voto
if ($confirm && confirm_sesskey()) {
    try {
        $votevalue = null;
        
        switch ($task->votingmethod) {
            case 'majority':
                $votevalue = required_param('vote_majority', PARAM_BOOL);
                break;
                
            case 'rating':
                $votevalue = required_param('vote_rating', PARAM_INT);
                break;
                
            case 'ranking':
                $first = required_param('vote_rank_1', PARAM_INT);
                $second = required_param('vote_rank_2', PARAM_INT);
                $third = required_param('vote_rank_3', PARAM_INT);
                $votevalue = [1 => $first, 2 => $second, 3 => $third];
                break;
        }
        
        // Registrar voto
        $voteid = \local_tubaron\voting_manager::cast_vote($id, $USER->id, $votevalue);
        
        if ($voteid) {
            redirect(
                new moodle_url('/local/tubaron/voting/results.php', ['id' => $id]),
                get_string('votesuccess', 'local_tubaron'),
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );
        }
        
    } catch (\moodle_exception $e) {
        \core\notification::error($e->getMessage());
    }
}

// Buscar dados adicionais
$mission = $DB->get_record('local_tubaron_missions', ['id' => $task->missionid]);
$creator = $DB->get_record('user', ['id' => $task->creatorid]);
$stats = \local_tubaron\voting_manager::get_voting_stats($id);

// Buscar submissões (para ranking)
$submissions = [];
if ($task->votingmethod === 'ranking') {
    $submissions = $DB->get_records_sql(
        "SELECT s.*, u.firstname, u.lastname
         FROM {local_tubaron_submissions} s
         JOIN {user} u ON u.id = s.userid
         WHERE s.taskid = ?
         ORDER BY s.timecreated DESC",
        [$id]
    );
}

// Output
echo $OUTPUT->header();

// Hero
echo html_writer::start_div('tubaron-voting-hero');
echo html_writer::tag('h1', '🗳️ ' . format_string($task->title), ['class' => 'tubaron-voting-hero-title']);

// Meta info
echo html_writer::start_div('tubaron-voting-hero-meta');
echo html_writer::tag('span', 
    get_string('type_' . $task->type, 'local_tubaron'),
    ['class' => 'badge badge-light']
);
echo html_writer::tag('span',
    get_string('method_' . $task->votingmethod, 'local_tubaron'),
    ['class' => 'badge badge-warning']
);
echo html_writer::tag('span',
    '🏆 ' . $task->points . ' pts',
    ['class' => 'tubaron-voting-hero-points']
);
echo html_writer::end_div();

echo html_writer::end_div();

// Layout 2 colunas
echo html_writer::start_div('tubaron-voting-layout');

// Coluna esquerda - Detalhes da tarefa
echo html_writer::start_div('tubaron-voting-column-left');

// Card Tarefa
echo html_writer::start_div('tubaron-card');
echo html_writer::tag('h2', '📋 ' . get_string('taskdetails', 'local_tubaron'), ['class' => 'tubaron-card-title']);

if (!empty($task->description)) {
    echo html_writer::tag('p', format_text($task->description, FORMAT_HTML), ['class' => 'tubaron-task-description']);
}

echo html_writer::start_tag('ul', ['class' => 'tubaron-task-meta-list']);
echo html_writer::tag('li', '👤 ' . get_string('creator', 'local_tubaron') . ': ' . fullname($creator));
echo html_writer::tag('li', '📂 ' . get_string('mission', 'local_tubaron') . ': ' . format_string($mission->name));
echo html_writer::tag('li', '📅 ' . get_string('created', 'local_tubaron') . ': ' . userdate($task->timecreated));

if ($task->votingdeadline > 0) {
    $remaining = $task->votingdeadline - time();
    $hours = floor($remaining / 3600);
    $minutes = floor(($remaining % 3600) / 60);
    
    echo html_writer::tag('li', 
        '⏰ ' . get_string('votingdeadline', 'local_tubaron') . ': ' . 
        userdate($task->votingdeadline) . 
        ($remaining > 0 ? " ({$hours}h {$minutes}min restantes)" : ' ' . get_string('expired', 'local_tubaron'))
    );
}
echo html_writer::end_tag('ul');

echo html_writer::end_div(); // card

// Stats votação
echo html_writer::start_div('tubaron-card');
echo html_writer::tag('h2', '📊 ' . get_string('votingstats', 'local_tubaron'), ['class' => 'tubaron-card-title']);

$progress = $stats->eligible_voters > 0 ? ($stats->votes_received / $stats->eligible_voters) * 100 : 0;

echo html_writer::tag('div',
    get_string('votesprogress', 'local_tubaron', [
        'received' => $stats->votes_received,
        'eligible' => $stats->eligible_voters
    ]),
    ['class' => 'tubaron-stats-label']
);

echo html_writer::start_div('tubaron-progress-bar-large');
echo html_writer::div('', 'tubaron-progress-fill', ['style' => 'width: ' . $progress . '%']);
echo html_writer::div(round($progress, 1) . '%', 'tubaron-progress-text');
echo html_writer::end_div();

echo html_writer::end_div(); // card

echo html_writer::end_div(); // column-left

// Coluna direita - Formulário de votação
echo html_writer::start_div('tubaron-voting-column-right');

echo html_writer::start_div('tubaron-card voting-form');
echo html_writer::tag('h2', '🗳️ ' . get_string('castvote', 'local_tubaron'), ['class' => 'tubaron-card-title']);

// Formulário baseado no método
echo html_writer::start_tag('form', [
    'method' => 'post',
    'action' => new moodle_url('/local/tubaron/voting/vote.php', ['id' => $id]),
    'class' => 'tubaron-vote-form',
    'id' => 'tubaron-vote-form'
]);

echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'confirm', 'value' => '1']);

switch ($task->votingmethod) {
    case 'majority':
        // Maioria Simples: Aprovar/Rejeitar
        echo html_writer::tag('p', get_string('majority_question', 'local_tubaron'), ['class' => 'tubaron-vote-question']);
        
        echo html_writer::start_div('tubaron-vote-options-majority');
        
        echo html_writer::start_tag('label', ['class' => 'tubaron-vote-option approve']);
        echo html_writer::empty_tag('input', ['type' => 'radio', 'name' => 'vote_majority', 'value' => '1', 'required' => 'required']);
        echo html_writer::tag('div', '✅', ['class' => 'tubaron-vote-icon']);
        echo html_writer::tag('div', get_string('approve', 'local_tubaron'), ['class' => 'tubaron-vote-label']);
        echo html_writer::tag('div', get_string('approve_description', 'local_tubaron'), ['class' => 'tubaron-vote-description']);
        echo html_writer::end_tag('label');
        
        echo html_writer::start_tag('label', ['class' => 'tubaron-vote-option reject']);
        echo html_writer::empty_tag('input', ['type' => 'radio', 'name' => 'vote_majority', 'value' => '0', 'required' => 'required']);
        echo html_writer::tag('div', '❌', ['class' => 'tubaron-vote-icon']);
        echo html_writer::tag('div', get_string('reject', 'local_tubaron'), ['class' => 'tubaron-vote-label']);
        echo html_writer::tag('div', get_string('reject_description', 'local_tubaron'), ['class' => 'tubaron-vote-description']);
        echo html_writer::end_tag('label');
        
        echo html_writer::end_div();
        break;
        
    case 'rating':
        // Notas 0-10
        echo html_writer::tag('p', get_string('rating_question', 'local_tubaron'), ['class' => 'tubaron-vote-question']);
        
        echo html_writer::start_div('tubaron-vote-rating');
        
        // Slider 0-10
        echo html_writer::start_div('tubaron-rating-slider-container');
        echo html_writer::empty_tag('input', [
            'type' => 'range',
            'name' => 'vote_rating',
            'id' => 'vote_rating',
            'min' => '0',
            'max' => '10',
            'value' => '5',
            'class' => 'tubaron-rating-slider',
            'required' => 'required',
            'oninput' => 'document.getElementById("rating-display").textContent = this.value'
        ]);
        echo html_writer::end_div();
        
        // Display nota
        echo html_writer::start_div('tubaron-rating-display');
        echo html_writer::tag('div', '5', ['id' => 'rating-display', 'class' => 'tubaron-rating-value']);
        echo html_writer::tag('div', get_string('outof10', 'local_tubaron'), ['class' => 'tubaron-rating-label']);
        echo html_writer::end_div();
        
        // Escala visual
        echo html_writer::start_div('tubaron-rating-scale');
        for ($i = 0; $i <= 10; $i++) {
            echo html_writer::tag('span', $i, ['class' => 'tubaron-rating-number']);
        }
        echo html_writer::end_div();
        
        // Descrições qualitativas
        echo html_writer::start_div('tubaron-rating-descriptions');
        echo html_writer::tag('small', '0-3: ' . get_string('rating_poor', 'local_tubaron'));
        echo html_writer::tag('small', '4-6: ' . get_string('rating_average', 'local_tubaron'));
        echo html_writer::tag('small', '7-8: ' . get_string('rating_good', 'local_tubaron'));
        echo html_writer::tag('small', '9-10: ' . get_string('rating_excellent', 'local_tubaron'));
        echo html_writer::end_div();
        
        echo html_writer::end_div();
        break;
        
    case 'ranking':
        // Ranking Top 3
        echo html_writer::tag('p', get_string('ranking_question', 'local_tubaron'), ['class' => 'tubaron-vote-question']);
        
        if (count($submissions) < 3) {
            echo $OUTPUT->notification(
                get_string('notenoughsubmissions', 'local_tubaron'),
                'warning'
            );
        } else {
            // Criar opções para os selects
            $options = [];
            foreach ($submissions as $sub) {
                $user = (object)['firstname' => $sub->firstname, 'lastname' => $sub->lastname];
                $options[$sub->id] = fullname($user) . ' - ' . userdate($sub->timecreated, get_string('strftimedateshort'));
            }
            
            echo html_writer::start_div('tubaron-vote-ranking');
            
            // 1º Lugar
            echo html_writer::start_div('tubaron-ranking-position first');
            echo html_writer::tag('div', '🥇', ['class' => 'tubaron-ranking-medal']);
            echo html_writer::tag('label', get_string('firstplace', 'local_tubaron'), ['class' => 'tubaron-ranking-label']);
            echo html_writer::select($options, 'vote_rank_1', '', ['' => get_string('selectsubmission', 'local_tubaron')], [
                'class' => 'form-control tubaron-ranking-select',
                'required' => 'required'
            ]);
            echo html_writer::end_div();
            
            // 2º Lugar
            echo html_writer::start_div('tubaron-ranking-position second');
            echo html_writer::tag('div', '🥈', ['class' => 'tubaron-ranking-medal']);
            echo html_writer::tag('label', get_string('secondplace', 'local_tubaron'), ['class' => 'tubaron-ranking-label']);
            echo html_writer::select($options, 'vote_rank_2', '', ['' => get_string('selectsubmission', 'local_tubaron')], [
                'class' => 'form-control tubaron-ranking-select',
                'required' => 'required'
            ]);
            echo html_writer::end_div();
            
            // 3º Lugar
            echo html_writer::start_div('tubaron-ranking-position third');
            echo html_writer::tag('div', '🥉', ['class' => 'tubaron-ranking-medal']);
            echo html_writer::tag('label', get_string('thirdplace', 'local_tubaron'), ['class' => 'tubaron-ranking-label']);
            echo html_writer::select($options, 'vote_rank_3', '', ['' => get_string('selectsubmission', 'local_tubaron')], [
                'class' => 'form-control tubaron-ranking-select',
                'required' => 'required'
            ]);
            echo html_writer::end_div();
            
            echo html_writer::end_div(); // ranking
        }
        break;
}

// Botões
echo html_writer::start_div('tubaron-vote-buttons');
echo html_writer::tag('button', '🗳️ ' . get_string('confirmandsend', 'local_tubaron'), [
    'type' => 'submit',
    'class' => 'btn btn-primary btn-lg',
    'id' => 'submit-vote-btn'
]);

$cancelurl = new moodle_url('/local/tubaron/voting/index.php');
echo html_writer::link($cancelurl, get_string('cancel'), ['class' => 'btn btn-secondary']);
echo html_writer::end_div();

echo html_writer::end_tag('form');

echo html_writer::end_div(); // card
echo html_writer::end_div(); // column-right
echo html_writer::end_div(); // layout

// CSS
echo html_writer::tag('style', '
.tubaron-voting-hero {
    background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
    color: white;
    padding: 2.5rem 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
}

.tubaron-voting-hero-title {
    font-size: 2rem;
    margin: 0 0 1rem 0;
}

.tubaron-voting-hero-meta {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.tubaron-voting-hero-points {
    font-weight: 600;
    font-size: 1.25rem;
}

.tubaron-voting-layout {
    display: grid;
    grid-template-columns: 1fr 1.2fr;
    gap: 2rem;
}

.tubaron-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
}

.tubaron-card.voting-form {
    border: 3px solid #8b5cf6;
}

.tubaron-card-title {
    font-size: 1.5rem;
    color: #1e3a8a;
    margin: 0 0 1.5rem 0;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #e5e7eb;
}

.tubaron-vote-question {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1e3a8a;
    margin-bottom: 2rem;
    text-align: center;
}

/* Maioria */
.tubaron-vote-options-majority {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.tubaron-vote-option {
    border: 3px solid #e5e7eb;
    border-radius: 12px;
    padding: 2rem;
    cursor: pointer;
    transition: all 0.3s;
    text-align: center;
}

.tubaron-vote-option:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}

.tubaron-vote-option input[type="radio"] {
    display: none;
}

.tubaron-vote-option input:checked + .tubaron-vote-icon {
    transform: scale(1.2);
}

.tubaron-vote-option.approve:has(input:checked) {
    border-color: #10b981;
    background: #d1fae5;
}

.tubaron-vote-option.reject:has(input:checked) {
    border-color: #ef4444;
    background: #fee2e2;
}

.tubaron-vote-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
    transition: transform 0.3s;
}

.tubaron-vote-label {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.tubaron-vote-description {
    font-size: 0.875rem;
    color: #6b7280;
}

/* Rating */
.tubaron-rating-slider-container {
    margin: 2rem 0;
}

.tubaron-rating-slider {
    width: 100%;
    height: 12px;
    border-radius: 6px;
    background: linear-gradient(to right, #ef4444, #f59e0b, #10b981);
    outline: none;
}

.tubaron-rating-slider::-webkit-slider-thumb {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #8b5cf6;
    cursor: pointer;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.tubaron-rating-display {
    text-align: center;
    margin: 2rem 0;
}

.tubaron-rating-value {
    font-size: 5rem;
    font-weight: 700;
    color: #8b5cf6;
}

.tubaron-rating-label {
    font-size: 1.25rem;
    color: #6b7280;
}

.tubaron-rating-scale {
    display: flex;
    justify-content: space-between;
    margin: 1rem 0 2rem 0;
}

.tubaron-rating-number {
    font-size: 0.875rem;
    color: #9ca3af;
}

.tubaron-rating-descriptions {
    display: flex;
    justify-content: space-around;
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 1rem;
}

/* Ranking */
.tubaron-vote-ranking {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.tubaron-ranking-position {
    background: #f9fafb;
    border-radius: 12px;
    padding: 1.5rem;
    border: 2px solid #e5e7eb;
}

.tubaron-ranking-position.first {
    border-color: #fbbf24;
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.tubaron-ranking-position.second {
    border-color: #94a3b8;
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
}

.tubaron-ranking-position.third {
    border-color: #cd7f32;
    background: linear-gradient(135deg, #fef3c7, #fde68a);
}

.tubaron-ranking-medal {
    font-size: 3rem;
    text-align: center;
    margin-bottom: 1rem;
}

.tubaron-ranking-label {
    display: block;
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    color: #1e3a8a;
}

.tubaron-ranking-select {
    width: 100%;
    font-size: 1.125rem;
}

/* Botões */
.tubaron-vote-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 2px solid #e5e7eb;
}

.tubaron-progress-bar-large {
    position: relative;
    width: 100%;
    height: 40px;
    background: #e5e7eb;
    border-radius: 20px;
    overflow: hidden;
    margin: 1rem 0;
}

.tubaron-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #8b5cf6, #6366f1);
    transition: width 0.5s ease;
}

.tubaron-progress-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-weight: 700;
    color: #1e3a8a;
}

@media (max-width: 968px) {
    .tubaron-voting-layout {
        grid-template-columns: 1fr;
    }
    
    .tubaron-vote-options-majority {
        grid-template-columns: 1fr;
    }
}
');

// JavaScript para validação ranking (não duplicar)
echo html_writer::tag('script', "
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('tubaron-vote-form');
    if (!form) return;
    
    // Validação ranking (não permitir duplicatas)
    const rank1 = document.querySelector('select[name=\"vote_rank_1\"]');
    const rank2 = document.querySelector('select[name=\"vote_rank_2\"]');
    const rank3 = document.querySelector('select[name=\"vote_rank_3\"]');
    
    if (rank1 && rank2 && rank3) {
        form.addEventListener('submit', function(e) {
            const val1 = rank1.value;
            const val2 = rank2.value;
            const val3 = rank3.value;
            
            if (val1 === val2 || val2 === val3 || val1 === val3) {
                e.preventDefault();
                alert('" . get_string('rankingduplicateerror', 'local_tubaron') . "');
                return false;
            }
        });
    }
    
    // Confirmação antes de enviar
    form.addEventListener('submit', function(e) {
        if (!confirm('" . get_string('confirmvote', 'local_tubaron') . "')) {
            e.preventDefault();
            return false;
        }
    });
});
");

echo $OUTPUT->footer();

