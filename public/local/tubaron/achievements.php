<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Achievements Showcase
 *
 * Página de conquistas do usuário com progresso e badges
 * Integrado ao MooVurix LMS Platform
 *
 * @package    local_tubaron
 * @copyright  2025 Tubaron Telecomunicações
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

// Autenticação
require_login();
$context = context_system::instance();

// Parâmetros
$userid = optional_param('userid', $USER->id, PARAM_INT);

// Se visualizando outro usuário, verificar permissão
if ($userid != $USER->id) {
    require_capability('local/tubaron:viewfullrankings', $context);
}

// Buscar usuário
$user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

// Configurar página
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/tubaron/achievements.php', ['userid' => $userid]));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('achievements', 'local_tubaron'));
$PAGE->set_heading(fullname($user) . ' - ' . get_string('achievements', 'local_tubaron'));
$PAGE->navbar->add(get_string('pluginname', 'local_tubaron'), new moodle_url('/local/tubaron/index.php'));
$PAGE->navbar->add(get_string('achievements', 'local_tubaron'));

// Buscar achievements
$achievements = \local_tubaron\achievements_manager::get_user_achievements($userid);

// Estatísticas
$totalach = count($achievements);
$unlockedach = count(array_filter($achievements, function($a) { return $a->unlocked; }));
$percentage = $totalach > 0 ? ($unlockedach / $totalach) * 100 : 0;

// Output
echo $OUTPUT->header();

// Hero
echo html_writer::start_div('tubaron-achievements-hero');
echo $OUTPUT->user_picture($user, ['size' => 100, 'class' => 'tubaron-user-avatar-large']);
echo html_writer::start_div('tubaron-achievements-hero-content');
echo html_writer::tag('h1', fullname($user), ['class' => 'tubaron-achievements-title']);
echo html_writer::tag('p', 
    get_string('achievementscount', 'local_tubaron', ['unlocked' => $unlockedach, 'total' => $totalach]),
    ['class' => 'tubaron-achievements-count']
);

// Progress bar
echo html_writer::start_div('tubaron-achievements-progress-bar');
echo html_writer::div('', 'tubaron-achievements-progress-fill', ['style' => 'width: ' . $percentage . '%;']);
echo html_writer::div(round($percentage, 1) . '%', 'tubaron-achievements-progress-text');
echo html_writer::end_div();

echo html_writer::end_div(); // hero-content
echo html_writer::end_div(); // hero

// Filtros
echo html_writer::start_div('tubaron-achievements-filters');
echo html_writer::tag('button', get_string('all'), [
    'class' => 'tubaron-filter-btn active',
    'data-filter' => 'all'
]);
echo html_writer::tag('button', get_string('unlocked', 'local_tubaron'), [
    'class' => 'tubaron-filter-btn',
    'data-filter' => 'unlocked'
]);
echo html_writer::tag('button', get_string('locked', 'local_tubaron'), [
    'class' => 'tubaron-filter-btn',
    'data-filter' => 'locked'
]);
echo html_writer::end_div();

// Grid de achievements
echo html_writer::start_div('tubaron-achievements-grid');

foreach ($achievements as $achievement) {
    $locked = !$achievement->unlocked;
    
    echo html_writer::start_div('tubaron-achievement-card' . ($locked ? ' locked' : ' unlocked'));
    
    // Badge/Icon
    $badgeclass = 'tubaron-achievement-badge ' . ($achievement->tier ?? 'bronze');
    if ($locked) {
        $badgeclass .= ' grayscale';
    }
    
    echo html_writer::start_div($badgeclass);
    
    if (!empty($achievement->iconurl)) {
        echo html_writer::empty_tag('img', [
            'src' => $achievement->iconurl,
            'alt' => format_string($achievement->name)
        ]);
    } else {
        // Emoji default baseado em tier
        $emojis = [
            'bronze' => '🥉',
            'silver' => '🥈',
            'gold' => '🥇',
            'platinum' => '💎'
        ];
        $emoji = $emojis[$achievement->tier ?? 'bronze'] ?? '🏆';
        echo html_writer::tag('div', $emoji, ['class' => 'tubaron-achievement-emoji']);
    }
    
    echo html_writer::end_div(); // badge
    
    // Info
    echo html_writer::start_div('tubaron-achievement-info');
    echo html_writer::tag('h3', format_string($achievement->name), ['class' => 'tubaron-achievement-name']);
    echo html_writer::tag('p', format_text($achievement->description, FORMAT_PLAIN), ['class' => 'tubaron-achievement-description']);
    
    if ($locked && isset($achievement->progress)) {
        // Mostrar progresso se locked
        $prog = $achievement->progress;
        echo html_writer::start_div('tubaron-achievement-progress');
        echo html_writer::tag('div',
            get_string('progress', 'local_tubaron') . ': ' . $prog->current . '/' . $prog->required,
            ['class' => 'tubaron-progress-label']
        );
        echo html_writer::start_div('tubaron-progress-bar-small');
        echo html_writer::div('', 'tubaron-progress-fill', ['style' => 'width: ' . $prog->percentage . '%;']);
        echo html_writer::end_div();
        echo html_writer::end_div(); // progress
    } else if (!$locked) {
        // Mostrar data unlock
        echo html_writer::tag('div',
            '🎉 ' . get_string('unlockedon', 'local_tubaron') . ': ' . userdate($achievement->timeunlocked, get_string('strftimedateshort')),
            ['class' => 'tubaron-achievement-unlocked-date']
        );
    }
    
    echo html_writer::end_div(); // info
    echo html_writer::end_div(); // card
}

echo html_writer::end_div(); // grid

// JavaScript filtros
echo html_writer::tag('script', "
document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.tubaron-filter-btn');
    const cards = document.querySelectorAll('.tubaron-achievement-card');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.dataset.filter;
            
            // Update active button
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Filter cards
            cards.forEach(card => {
                if (filter === 'all') {
                    card.style.display = 'flex';
                } else if (filter === 'unlocked' && card.classList.contains('unlocked')) {
                    card.style.display = 'flex';
                } else if (filter === 'locked' && card.classList.contains('locked')) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
});
");

// CSS
echo html_writer::tag('style', '
.tubaron-achievements-hero {
    background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
    color: white;
    padding: 3rem 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 2rem;
}

.tubaron-user-avatar-large {
    border: 4px solid white;
    border-radius: 50%;
}

.tubaron-achievements-title {
    font-size: 2.5rem;
    margin: 0 0 0.5rem 0;
}

.tubaron-achievements-count {
    font-size: 1.25rem;
    opacity: 0.9;
    margin: 0 0 1.5rem 0;
}

.tubaron-achievements-progress-bar {
    position: relative;
    width: 100%;
    max-width: 500px;
    height: 40px;
    background: rgba(255,255,255,0.2);
    border-radius: 20px;
    overflow: hidden;
}

.tubaron-achievements-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981, #34d399);
    transition: width 0.5s;
}

.tubaron-achievements-progress-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-weight: 700;
    font-size: 1.125rem;
}

.tubaron-achievements-filters {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    justify-content: center;
}

.tubaron-filter-btn {
    padding: 0.75rem 2rem;
    border: 2px solid #e5e7eb;
    background: white;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
    font-weight: 600;
}

.tubaron-filter-btn.active {
    background: #7c3aed;
    color: white;
    border-color: #7c3aed;
}

.tubaron-achievements-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.tubaron-achievement-card {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.5rem;
    display: flex;
    gap: 1.5rem;
    transition: all 0.3s;
}

.tubaron-achievement-card.unlocked {
    border-color: #10b981;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
}

.tubaron-achievement-card.locked {
    opacity: 0.6;
}

.tubaron-achievement-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.1);
}

.tubaron-achievement-badge {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.tubaron-achievement-badge.bronze {
    background: linear-gradient(135deg, #cd7f32, #e5a670);
}

.tubaron-achievement-badge.silver {
    background: linear-gradient(135deg, #c0c0c0, #e0e0e0);
}

.tubaron-achievement-badge.gold {
    background: linear-gradient(135deg, #ffd700, #ffed4e);
}

.tubaron-achievement-badge.platinum {
    background: linear-gradient(135deg, #e5e4e2, #ffffff);
}

.tubaron-achievement-badge.grayscale {
    filter: grayscale(100%);
}

.tubaron-achievement-emoji {
    font-size: 3rem;
}

.tubaron-achievement-name {
    font-size: 1.25rem;
    color: #1e3a8a;
    margin: 0 0 0.5rem 0;
}

.tubaron-achievement-description {
    color: #6b7280;
    font-size: 0.875rem;
    margin: 0 0 1rem 0;
}

.tubaron-achievement-unlocked-date {
    font-size: 0.875rem;
    color: #10b981;
    font-weight: 600;
}

.tubaron-progress-bar-small {
    width: 100%;
    height: 8px;
    background: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
    margin-top: 0.5rem;
}

@media (max-width: 768px) {
    .tubaron-achievements-hero {
        flex-direction: column;
        text-align: center;
    }
    
    .tubaron-achievements-grid {
        grid-template-columns: 1fr;
    }
}
');

echo $OUTPUT->footer();

