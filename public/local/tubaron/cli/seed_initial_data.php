<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Seed Initial Data
 *
 * CLI script para inserir dados iniciais (achievements padrão)
 *
 * @package    local_tubaron
 * @copyright  2025 Tubaron Telecomunicações
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/clilib.php');

// Ensure errors are well explained
$CFG->debug = DEBUG_NORMAL;

cli_heading('Tubaron Gamification - Seed Initial Data');

// Insert achievements
$achievements = [
    [
        'code' => 'LEADER_MONTH',
        'name' => 'Líder do Mês',
        'description' => 'Ficou em 1º lugar no ranking mensal',
        'iconurl' => '/local/tubaron/pix/trophy.svg',
        'criteria' => json_encode(['type' => 'rank_position', 'rank' => 1, 'period' => 'month']),
        'timecreated' => time()
    ],
    [
        'code' => 'STREAK_7',
        'name' => 'Sequência 7 Dias',
        'description' => 'Completou tarefas por 7 dias consecutivos',
        'iconurl' => '/local/tubaron/pix/fire.svg',
        'criteria' => json_encode(['type' => 'streak', 'days' => 7]),
        'timecreated' => time()
    ],
    [
        'code' => 'FIRST_WIN',
        'name' => 'Primeira Vitória',
        'description' => 'Ganhou primeira tarefa competitiva',
        'iconurl' => '/local/tubaron/pix/medal.svg',
        'criteria' => json_encode(['type' => 'first_competitive_win']),
        'timecreated' => time()
    ],
    [
        'code' => 'TEAM_LIGHTNING',
        'name' => 'Equipe Relâmpago',
        'description' => 'Equipe completou tarefa em menos de 24h',
        'iconurl' => '/local/tubaron/pix/lightning.svg',
        'criteria' => json_encode(['type' => 'completion_speed', 'hours' => 24]),
        'timecreated' => time()
    ],
    [
        'code' => 'PERFECT_SCORE',
        'name' => 'Nota Perfeita',
        'description' => 'Recebeu nota 10.0 em votação competitiva',
        'iconurl' => '/local/tubaron/pix/star.svg',
        'criteria' => json_encode(['type' => 'perfect_score']),
        'timecreated' => time()
    ]
];

cli_writeln('Inserindo achievements padrão...');

$inserted = 0;
foreach ($achievements as $achievement) {
    if (!$DB->record_exists('local_tubaron_achievements', ['code' => $achievement['code']])) {
        $DB->insert_record('local_tubaron_achievements', (object)$achievement);
        $inserted++;
        cli_writeln("  ✅ {$achievement['name']} ({$achievement['code']})");
    } else {
        cli_writeln("  ⏭️  {$achievement['name']} (já existe)");
    }
}

cli_writeln("\n✅ Total inserido: {$inserted} achievements");
cli_writeln("✅ Seed concluído com sucesso!\n");

exit(0);

