<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Dashboard
 *
 * Dashboard principal do colaborador com KPIs, tarefas urgentes e mini ranking
 * Integrado ao MooVurix LMS Platform
 *
 * @package    local_tubaron
 * @copyright  2025 Tubaron Telecomunicações
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/tablelib.php');
require_once(__DIR__ . '/lib.php'); // Include Tubaron functions

require_login();

$context = context_system::instance();
require_capability('local/tubaron:viewdashboard', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/tubaron/dashboard.php'));
$PAGE->set_title(get_string('mydashboard', 'local_tubaron'));
$PAGE->set_heading(get_string('mydashboard', 'local_tubaron'));
$PAGE->set_pagelayout('standard');

// Get active season
$activeseason = local_tubaron_get_active_season();

// Get user score
$userscore = local_tubaron_get_user_score($USER->id);

// Get user's team(s)
$userteams = $DB->get_records_sql(
    "SELECT t.*, tm.timejoined
       FROM {local_tubaron_teams} t
       JOIN {local_tubaron_team_members} tm ON tm.teamid = t.id
      WHERE tm.userid = ?
        AND tm.status = ?
   ORDER BY tm.timejoined DESC",
    [$USER->id, 'active']
);

// Get pending tasks
$pendingtasks = local_tubaron_get_user_pending_tasks($USER->id, 'all');
$urgenttasks = local_tubaron_get_user_pending_tasks($USER->id, 'urgent');

// Get user streak
$streak = $DB->get_record('local_tubaron_streaks', [
    'userid' => $USER->id,
    'type' => 'daily'
]);

// Get top 5 rankings
$toprankings = local_tubaron_get_top_rankings('user', 5);

// Get recent achievements
$recentachievements = $DB->get_records_sql(
    "SELECT ua.*, a.name, a.description, a.iconurl
       FROM {local_tubaron_user_achievements} ua
       JOIN {local_tubaron_achievements} a ON a.id = ua.achievementid
      WHERE ua.userid = ?
   ORDER BY ua.timeunlocked DESC",
    [$USER->id],
    0,  // limitfrom
    3   // limitnum (LIMIT 3)
);

echo $OUTPUT->header();

// Add custom CSS
?>
<style>
/* Tubaron Design System - Inline Styles */
.tubaron-hero {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    border-radius: 16px;
    padding: 2rem;
    color: white;
    margin-bottom: 2rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.tubaron-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.tubaron-kpi-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
}

.tubaron-kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.tubaron-kpi-value {
    font-size: 2.5rem;
    font-weight: 800;
    margin: 0.5rem 0;
}

.tubaron-kpi-label {
    font-size: 0.875rem;
    opacity: 0.8;
}

.tubaron-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.tubaron-task-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    border-left: 4px solid #3b82f6;
    transition: all 0.3s ease;
    cursor: pointer;
}

.tubaron-task-card:hover {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    transform: translateX(4px);
}

.tubaron-task-card.urgent {
    border-left-color: #ef4444;
    background: #fef2f2;
}

.tubaron-task-card.due-soon {
    border-left-color: #f59e0b;
    background: #fffbeb;
}

.tubaron-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.tubaron-badge-primary {
    background: #dbeafe;
    color: #1d4ed8;
}

.tubaron-badge-success {
    background: #dcfce7;
    color: #15803d;
}

.tubaron-badge-warning {
    background: #fef3c7;
    color: #b45309;
}

.tubaron-badge-error {
    background: #fee2e2;
    color: #b91c1c;
}

.tubaron-ranking-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    border-radius: 8px;
    transition: background 0.2s;
}

.tubaron-ranking-item:hover {
    background: #f5f5f5;
}

.tubaron-ranking-item.current-user {
    background: #eff6ff;
    border: 2px solid #3b82f6;
}

.tubaron-btn-primary {
    background: #2563eb;
    color: white;
    padding: 0.5rem 1.5rem;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.tubaron-btn-primary:hover {
    background: #1d4ed8;
    transform: scale(1.02);
}

.tubaron-medal {
    font-size: 1.5rem;
}
</style>

<div class="tubaron-dashboard">
    <?php if ($activeseason): ?>
        <!-- Hero Section -->
        <div class="tubaron-hero">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1.5rem;">
                <div>
                    <h1 style="margin: 0; font-size: 2rem; font-weight: 700;">
                        👋 Olá, <?php echo $USER->firstname; ?>!
                    </h1>
                    <p style="margin-top: 0.5rem; opacity: 0.9; font-size: 1.125rem;">
                        <?php if ($userscore): ?>
                            Você está em <?php echo $userscore->rank; ?>º lugar. Continue assim! 🚀
                        <?php else: ?>
                            Bem-vindo ao sistema de gamificação! Comece completando tarefas.
                        <?php endif; ?>
                    </p>
                </div>
                <span class="tubaron-badge tubaron-badge-primary" style="background: rgba(255,255,255,0.2); color: white;">
                    <?php echo $activeseason->name; ?>
                </span>
            </div>

            <!-- KPI Grid -->
            <div class="tubaron-kpi-grid">
                <!-- Total Points -->
                <div class="tubaron-kpi-card">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <span style="font-size: 2rem;">🏆</span>
                        <?php if ($userscore && $userscore->points > 0): ?>
                            <span class="tubaron-badge tubaron-badge-success" style="background: rgba(34, 197, 94, 0.2); color: #fff;">
                                +<?php echo number_format($userscore->points - ($userscore->prevpoints ?? 0), 0); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="tubaron-kpi-label">Total Pontos</div>
                    <div class="tubaron-kpi-value">
                        <?php echo $userscore ? number_format($userscore->points, 0) : '0'; ?>
                    </div>
                </div>

                <!-- Rank Position -->
                <div class="tubaron-kpi-card">
                    <span style="font-size: 2rem;">📊</span>
                    <div class="tubaron-kpi-label">Posição Geral</div>
                    <div class="tubaron-kpi-value">
                        <?php echo $userscore ? $userscore->rank . 'º' : '-'; ?>
                    </div>
                </div>

                <!-- Tasks Completed -->
                <div class="tubaron-kpi-card">
                    <span style="font-size: 2rem;">✅</span>
                    <div class="tubaron-kpi-label">Tarefas Completas</div>
                    <div class="tubaron-kpi-value">
                        <?php echo $userscore ? $userscore->taskcount : '0'; ?>
                    </div>
                    <div class="tubaron-kpi-label">
                        <?php echo count($pendingtasks); ?> pendentes
                    </div>
                </div>

                <!-- Streak -->
                <div class="tubaron-kpi-card">
                    <span style="font-size: 2rem;">🔥</span>
                    <div class="tubaron-kpi-label">Sequência Dias</div>
                    <div class="tubaron-kpi-value">
                        <?php echo $streak ? $streak->currentcount : '0'; ?>
                    </div>
                    <div style="font-size: 1.5rem;">
                        <?php echo str_repeat('🔥', min($streak->currentcount ?? 0, 7)); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            <!-- Left Column -->
            <div>
                <!-- Urgent Tasks -->
                <?php if (!empty($urgenttasks)): ?>
                    <div class="tubaron-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">
                                ⚡ Urgente (<24h)
                            </h2>
                            <a href="<?php echo new moodle_url('/local/tubaron/tasks/index.php'); ?>" style="text-decoration: none;">
                                Ver Todas →
                            </a>
                        </div>

                        <?php foreach ($urgenttasks as $task): ?>
                            <div class="tubaron-task-card urgent" onclick="window.location='<?php echo new moodle_url('/local/tubaron/tasks/view.php', ['id' => $task->id]); ?>'">
                                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                                    <span><?php echo $task->type === 'competitive' ? '🎯' : ($task->type === 'team' ? '👥' : '📋'); ?></span>
                                    <span class="tubaron-badge tubaron-badge-error">🔴 URGENTE</span>
                                    <?php if ($task->type === 'competitive'): ?>
                                        <span class="tubaron-badge tubaron-badge-primary">COMPETITIVA</span>
                                    <?php endif; ?>
                                </div>

                                <h3 style="margin: 0 0 0.75rem 0; font-size: 1.25rem; font-weight: 600;">
                                    <?php echo format_string($task->title); ?>
                                </h3>

                                <div style="display: flex; gap: 1.5rem; font-size: 0.875rem; color: #525252;">
                                    <span>📅 <?php echo userdate($task->deadline, '%d %b %H:%M'); ?></span>
                                    <span>🏆 <?php echo $task->points; ?> pontos</span>
                                </div>

                                <?php if ($task->type === 'competitive'): ?>
                                    <?php
                                    $progress = \local_tubaron\task_manager::get_task_progress($task->id);
                                    ?>
                                    <div style="margin-top: 1rem; background: rgba(0,0,0,0.05); border-radius: 8px; padding: 0.75rem;">
                                        <div style="font-size: 0.75rem; color: #525252; margin-bottom: 0.5rem;">
                                            Progresso: <?php echo $progress->completed; ?>/<?php echo $progress->total; ?> submissões
                                        </div>
                                        <div style="width: 100%; height: 8px; background: #e5e5e5; border-radius: 4px; overflow: hidden;">
                                            <div style="width: <?php echo $progress->percentage; ?>%; height: 100%; background: linear-gradient(90deg, #3b82f6, #1d4ed8); transition: width 0.5s;"></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Pending Tasks Summary -->
                <?php if (!empty($pendingtasks)): ?>
                    <div class="tubaron-card">
                        <h2 style="margin: 0 0 1rem 0; font-size: 1.5rem; font-weight: 700;">
                            📋 Suas Tarefas (<?php echo count($pendingtasks); ?>)
                        </h2>

                        <?php 
                        $displaytasks = array_slice($pendingtasks, 0, 5);
                        foreach ($displaytasks as $task): 
                            $now = time();
                            $diffhours = ($task->deadline - $now) / 3600;
                            $urgencyclass = $diffhours < 24 ? 'urgent' : ($diffhours < 48 ? 'due-soon' : '');
                        ?>
                            <div class="tubaron-task-card <?php echo $urgencyclass; ?>" onclick="window.location='<?php echo new moodle_url('/local/tubaron/tasks/view.php', ['id' => $task->id]); ?>'">
                                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                                    <span><?php echo $task->type === 'competitive' ? '🎯' : ($task->type === 'team' ? '👥' : '📋'); ?></span>
                                    <span class="tubaron-badge tubaron-badge-primary">
                                        <?php echo strtoupper(get_string('tasktype_' . $task->type, 'local_tubaron')); ?>
                                    </span>
                                </div>

                                <h3 style="margin: 0 0 0.75rem 0; font-size: 1.125rem; font-weight: 600;">
                                    <?php echo format_string($task->title); ?>
                                </h3>

                                <div style="display: flex; gap: 1.5rem; font-size: 0.875rem; color: #525252;">
                                    <span>📅 <?php echo userdate($task->deadline, '%d %b'); ?></span>
                                    <span>🏆 <?php echo $task->points; ?> pts</span>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if (count($pendingtasks) > 5): ?>
                            <a href="<?php echo new moodle_url('/local/tubaron/tasks/index.php'); ?>" class="tubaron-btn-primary" style="width: 100%; text-align: center; display: block; margin-top: 1rem;">
                                Ver Todas as Tarefas (<?php echo count($pendingtasks); ?>)
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="tubaron-card" style="text-align: center; padding: 3rem;">
                        <div style="font-size: 4rem; margin-bottom: 1rem;">🎯</div>
                        <h3 style="color: #737373; margin-bottom: 0.5rem;">Nenhuma Tarefa Pendente</h3>
                        <p style="color: #a3a3a3;">Você está em dia! Continue assim.</p>
                    </div>
                <?php endif; ?>

                <!-- Recent Achievements -->
                <?php if (!empty($recentachievements)): ?>
                    <div class="tubaron-card">
                        <h2 style="margin: 0 0 1rem 0; font-size: 1.5rem; font-weight: 700;">
                            🏅 Conquistas Recentes
                        </h2>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                            <?php foreach ($recentachievements as $achievement): ?>
                                <div style="text-align: center; padding: 1rem; background: linear-gradient(135deg, #a855f7, #9333ea); border-radius: 12px; color: white;">
                                    <div style="font-size: 3rem; margin-bottom: 0.5rem;">
                                        <?php echo $achievement->iconurl ? "🏆" : "🏅"; ?>
                                    </div>
                                    <div style="font-size: 0.875rem; font-weight: 600;">
                                        <?php echo format_string($achievement->name); ?>
                                    </div>
                                    <div style="font-size: 0.75rem; opacity: 0.8; margin-top: 0.25rem;">
                                        <?php echo userdate($achievement->timeunlocked, '%d %b'); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Column - Sidebar -->
            <div>
                <!-- Top 5 Ranking -->
                <div class="tubaron-card">
                    <h2 style="margin: 0 0 1rem 0; font-size: 1.25rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                        <span>🏆</span>
                        Top 5 Geral
                    </h2>

                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <?php 
                        $position = 1;
                        foreach ($toprankings as $rankeduser): 
                            $iscurrentuser = $rankeduser->entityid == $USER->id;
                        ?>
                            <div class="tubaron-ranking-item <?php echo $iscurrentuser ? 'current-user' : ''; ?>">
                                <div class="tubaron-medal">
                                    <?php
                                    echo $position === 1 ? '🥇' : ($position === 2 ? '🥈' : ($position === 3 ? '🥉' : '📍'));
                                    ?>
                                </div>

                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-weight: 600; color: #171717; display: flex; align-items: center; gap: 0.5rem;">
                                        <?php echo fullname($rankeduser); ?>
                                        <?php if ($iscurrentuser): ?>
                                            <span style="font-size: 0.75rem; color: #2563eb;">← Você</span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size: 0.875rem; color: #737373;">
                                        <?php echo $rankeduser->firstplaces; ?> vitória<?php echo $rankeduser->firstplaces != 1 ? 's' : ''; ?>
                                    </div>
                                </div>

                                <div style="text-align: right;">
                                    <div style="font-weight: 700; color: #171717;">
                                        <?php echo number_format($rankeduser->points, 0); ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #737373;">pts</div>
                                </div>
                            </div>
                        <?php 
                        $position++;
                        endforeach; 
                        ?>
                    </div>

                    <a href="<?php echo new moodle_url('/local/tubaron/rankings.php'); ?>" class="tubaron-btn-primary" style="width: 100%; text-align: center; display: block; margin-top: 1rem; text-decoration: none;">
                        Ver Ranking Completo
                    </a>
                </div>

                <!-- My Team(s) -->
                <?php if (!empty($userteams)): ?>
                    <div class="tubaron-card">
                        <h2 style="margin: 0 0 1rem 0; font-size: 1.25rem; font-weight: 700;">
                            👥 Minha<?php echo count($userteams) > 1 ? 's' : ''; ?> Equipe<?php echo count($userteams) > 1 ? 's' : ''; ?>
                        </h2>

                        <?php foreach ($userteams as $team): 
                            $teamscore = $DB->get_record('local_tubaron_scores', [
                                'entitytype' => 'team',
                                'entityid' => $team->id,
                                'seasonid' => $activeseason->id
                            ]);
                        ?>
                            <div style="padding: 1rem; background: #f5f5f5; border-radius: 8px; margin-bottom: 0.75rem;">
                                <h3 style="margin: 0 0 0.5rem 0; font-weight: 600;">
                                    <?php echo format_string($team->name); ?>
                                </h3>
                                <div style="font-size: 0.875rem; color: #737373;">
                                    Capitão: <?php 
                                    $captain = $DB->get_record('user', ['id' => $team->captainid]);
                                    echo fullname($captain);
                                    ?>
                                </div>
                                <div style="display: flex; gap: 1rem; margin-top: 0.5rem; font-size: 0.875rem;">
                                    <span>👥 <?php echo $team->memberscount; ?> membros</span>
                                    <?php if ($teamscore): ?>
                                        <span>🏆 <?php echo number_format($teamscore->points, 0); ?> pts</span>
                                        <span>📊 <?php echo $teamscore->rank; ?>º lugar</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Quick Actions -->
                <div class="tubaron-card">
                    <h2 style="margin: 0 0 1rem 0; font-size: 1.25rem; font-weight: 700;">
                        ⚡ Ações Rápidas
                    </h2>

                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <a href="<?php echo new moodle_url('/local/tubaron/tasks/edit.php'); ?>" class="tubaron-btn-primary" style="text-decoration: none;">
                            ➕ Nova Tarefa
                        </a>

                        <a href="<?php echo new moodle_url('/local/tubaron/rankings.php'); ?>" style="display: block; padding: 0.5rem 1.5rem; border-radius: 8px; text-align: center; background: #f5f5f5; color: #171717; text-decoration: none; font-weight: 600; transition: all 0.2s;">
                            🏆 Ver Rankings
                        </a>

                        <a href="<?php echo new moodle_url('/local/tubaron/teams/index.php'); ?>" style="display: block; padding: 0.5rem 1.5rem; border-radius: 8px; text-align: center; background: #f5f5f5; color: #171717; text-decoration: none; font-weight: 600; transition: all 0.2s;">
                            👥 Minhas Equipes
                        </a>

                        <a href="<?php echo new moodle_url('/local/tubaron/achievements.php'); ?>" style="display: block; padding: 0.5rem 1.5rem; border-radius: 8px; text-align: center; background: #f5f5f5; color: #171717; text-decoration: none; font-weight: 600; transition: all 0.2s;">
                            🏅 Conquistas
                        </a>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- No Active Season -->
        <div class="tubaron-card" style="text-align: center; padding: 4rem;">
            <div style="font-size: 5rem; margin-bottom: 1.5rem;">🏆</div>
            <h2 style="color: #525252; margin-bottom: 1rem;">Nenhuma Temporada Ativa</h2>
            <p style="color: #a3a3a3; max-width: 500px; margin: 0 auto 2rem;">
                Aguarde o início da próxima temporada de gamificação Tubaron!
            </p>

            <?php if (has_capability('local/tubaron:manageseasons', $context)): ?>
                <a href="<?php echo new moodle_url('/local/tubaron/admin/seasons.php'); ?>" class="tubaron-btn-primary">
                    Criar Nova Temporada
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
// Real-time updates (WebSocket simulation via AJAX polling)
setInterval(function() {
    // Poll for ranking updates every 5 seconds
    // This would be replaced with WebSocket in production
    fetch('<?php echo new moodle_url('/local/tubaron/ajax/get_rankings.php'); ?>')
        .then(response => response.json())
        .then(data => {
            // Update ranking display if changed
            // console.log('Rankings updated', data);
        })
        .catch(error => console.error('Error fetching rankings:', error));
}, 5000);
</script>

<?php
echo $OUTPUT->footer();

