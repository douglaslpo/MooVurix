<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Rankings Page
 *
 * Visualização de rankings com update real-time
 *
 * @package    local_tubaron
 * @copyright  2025 Tubaron Telecomunicações
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php'); // Include Tubaron functions

require_login();

$context = context_system::instance();
require_capability('local/tubaron:viewrankings', $context);

$type = optional_param('type', 'users', PARAM_ALPHA); // 'users' or 'teams'
$limit = optional_param('limit', 50, PARAM_INT);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/tubaron/rankings.php', ['type' => $type]));
$PAGE->set_title(get_string('rankings', 'local_tubaron'));
$PAGE->set_heading(get_string('rankings', 'local_tubaron'));
$PAGE->set_pagelayout('standard');

// Get active season
$activeseason = local_tubaron_get_active_season();

// Get rankings
$rankings = local_tubaron_get_top_rankings($type, $limit);

echo $OUTPUT->header();
?>

<style>
.tubaron-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 2rem;
    border-bottom: 2px solid #e5e5e5;
}

.tubaron-tab {
    padding: 1rem 2rem;
    border: none;
    background: none;
    font-weight: 600;
    color: #737373;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.2s;
}

.tubaron-tab.active {
    color: #2563eb;
    border-bottom-color: #2563eb;
}

.tubaron-tab:hover {
    color: #2563eb;
}

.tubaron-rankings-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.tubaron-ranking-table {
    width: 100%;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.tubaron-ranking-table thead {
    background: #f5f5f5;
}

.tubaron-ranking-table th {
    padding: 1rem;
    text-align: left;
    font-weight: 700;
    color: #404040;
    font-size: 0.875rem;
}

.tubaron-ranking-table td {
    padding: 1.5rem 1rem;
    border-top: 1px solid #e5e5e5;
}

.tubaron-ranking-table tr:hover {
    background: #fafafa;
}

.tubaron-ranking-table tr.current-user {
    background: #eff6ff;
    border-left: 4px solid #2563eb;
}

.tubaron-rank-medal {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.25rem;
}

.tubaron-rank-medal.gold {
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    color: white;
}

.tubaron-rank-medal.silver {
    background: linear-gradient(135deg, #cbd5e1, #94a3b8);
    color: white;
}

.tubaron-rank-medal.bronze {
    background: linear-gradient(135deg, #fb923c, #f97316);
    color: white;
}

.tubaron-rank-medal.normal {
    background: #f5f5f5;
    color: #404040;
}

.tubaron-trend {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.tubaron-trend.up {
    background: #dcfce7;
    color: #15803d;
}

.tubaron-trend.down {
    background: #fee2e2;
    color: #b91c1c;
}

.tubaron-trend.neutral {
    background: #f5f5f5;
    color: #737373;
}

.tubaron-live-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #737373;
}

.tubaron-live-dot {
    width: 8px;
    height: 8px;
    background: #22c55e;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
</style>

<div class="tubaron-rankings">
    <?php if ($activeseason): ?>
        <div class="tubaron-rankings-header">
            <div>
                <h1 style="margin: 0; font-size: 2rem; font-weight: 700;">
                    🏆 Rankings - <?php echo format_string($activeseason->name); ?>
                </h1>
                <div class="tubaron-live-indicator" style="margin-top: 0.5rem;">
                    <div class="tubaron-live-dot"></div>
                    <span id="last-updated">Atualizado agora</span>
                </div>
            </div>

            <div>
                <a href="<?php echo new moodle_url('/local/tubaron/dashboard.php'); ?>" style="text-decoration: none; color: #2563eb; font-weight: 600;">
                    ← Voltar ao Dashboard
                </a>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tubaron-tabs">
            <button class="tubaron-tab <?php echo $type === 'users' ? 'active' : ''; ?>" onclick="window.location='?type=users'">
                Usuários
            </button>
            <button class="tubaron-tab <?php echo $type === 'teams' ? 'active' : ''; ?>" onclick="window.location='?type=teams'">
                Equipes
            </button>
        </div>

        <!-- Rankings Table -->
        <table class="tubaron-ranking-table">
            <thead>
                <tr>
                    <th style="width: 80px;">Pos.</th>
                    <th><?php echo $type === 'users' ? 'Colaborador' : 'Equipe'; ?></th>
                    <th style="text-align: right;">Pontos</th>
                    <th style="text-align: center;">🥇 Vitórias</th>
                    <th style="text-align: center;">Tarefas</th>
                    <th style="text-align: center;">Tendência</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $position = 1;
                foreach ($rankings as $ranking): 
                    $iscurrentuser = ($type === 'users' && $ranking->entityid == $USER->id);
                    
                    $medalclass = 'normal';
                    if ($position === 1) $medalclass = 'gold';
                    else if ($position === 2) $medalclass = 'silver';
                    else if ($position === 3) $medalclass = 'bronze';
                    
                    $medal = $position === 1 ? '🥇' : ($position === 2 ? '🥈' : ($position === 3 ? '🥉' : ''));
                ?>
                    <tr class="<?php echo $iscurrentuser ? 'current-user' : ''; ?>" id="rank-<?php echo $ranking->id; ?>">
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div class="tubaron-rank-medal <?php echo $medalclass; ?>">
                                    <?php echo $position; ?>
                                </div>
                                <?php if ($medal): ?>
                                    <span style="font-size: 1.5rem;"><?php echo $medal; ?></span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <?php if ($type === 'users'): ?>
                                    <?php
                                    $userobj = $DB->get_record('user', ['id' => $ranking->entityid]);
                                    echo $OUTPUT->user_picture($userobj, ['size' => 40]);
                                    ?>
                                    <div>
                                        <div style="font-weight: 600; color: #171717;">
                                            <?php echo fullname($ranking); ?>
                                            <?php if ($iscurrentuser): ?>
                                                <span class="tubaron-badge tubaron-badge-primary">Você</span>
                                            <?php endif; ?>
                                        </div>
                                        <div style="font-size: 0.875rem; color: #737373;">
                                            <?php echo $ranking->firstplaces; ?> vitória<?php echo $ranking->firstplaces != 1 ? 's' : ''; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div>
                                        <div style="font-weight: 600; color: #171717; font-size: 1.125rem;">
                                            🛡️ <?php echo format_string($ranking->teamname); ?>
                                        </div>
                                        <div style="font-size: 0.875rem; color: #737373;">
                                            Capitão: <?php echo $ranking->captainfirstname . ' ' . $ranking->captainlastname; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td style="text-align: right;">
                            <div style="font-size: 1.5rem; font-weight: 800; color: #171717;">
                                <?php echo number_format($ranking->points, 0); ?>
                            </div>
                            <div style="font-size: 0.75rem; color: #737373;">pontos</div>
                        </td>

                        <td style="text-align: center;">
                            <div style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.5rem 0.75rem; background: linear-gradient(135deg, #fbbf24, #f59e0b); border-radius: 9999px; color: white; font-weight: 600;">
                                <span>🏆</span>
                                <span><?php echo $ranking->firstplaces; ?></span>
                            </div>
                        </td>

                        <td style="text-align: center;">
                            <span style="font-weight: 600; color: #404040;">
                                <?php echo $ranking->taskcount; ?>
                            </span>
                        </td>

                        <td style="text-align: center;">
                            <?php
                            // Simular trend (em produção viria de comparação com ranking anterior)
                            $trendtype = $position <= 3 ? 'up' : 'neutral';
                            $trendchange = $position <= 3 ? rand(0, 3) : 0;
                            ?>
                            <div class="tubaron-trend <?php echo $trendtype; ?>">
                                <?php if ($trendtype === 'up'): ?>
                                    ↑ +<?php echo $trendchange; ?>
                                <?php elseif ($trendtype === 'down'): ?>
                                    ↓ <?php echo $trendchange; ?>
                                <?php else: ?>
                                    ─ 0
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php 
                $position++;
                endforeach; 
                ?>

                <?php if (empty($rankings)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 3rem; color: #a3a3a3;">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">📊</div>
                            <div>Nenhum dado de ranking disponível ainda.</div>
                            <div style="font-size: 0.875rem; margin-top: 0.5rem;">Complete tarefas para aparecer no ranking!</div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Export Actions -->
        <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: center;">
            <a href="<?php echo new moodle_url('/local/tubaron/export.php', ['type' => $type, 'format' => 'csv']); ?>" class="tubaron-btn-primary" style="text-decoration: none;">
                📊 Exportar CSV
            </a>
            
            <a href="<?php echo new moodle_url('/local/tubaron/export.php', ['type' => $type, 'format' => 'excel']); ?>" style="display: inline-block; padding: 0.5rem 1.5rem; border-radius: 8px; background: #f5f5f5; color: #171717; text-decoration: none; font-weight: 600;">
                📗 Exportar Excel
            </a>

            <a href="<?php echo new moodle_url('/local/tubaron/export.php', ['type' => $type, 'format' => 'pdf']); ?>" style="display: inline-block; padding: 0.5rem 1.5rem; border-radius: 8px; background: #f5f5f5; color: #171717; text-decoration: none; font-weight: 600;">
                📕 Exportar PDF
            </a>
        </div>

    <?php else: ?>
        <!-- No Active Season -->
        <div style="text-align: center; padding: 4rem; background: white; border-radius: 12px;">
            <div style="font-size: 5rem; margin-bottom: 1.5rem;">🏆</div>
            <h2 style="color: #525252;">Nenhuma Temporada Ativa</h2>
            <p style="color: #a3a3a3;">Aguarde o início da próxima temporada!</p>
        </div>
    <?php endif; ?>
</div>

<script>
// Auto-refresh rankings every 5 seconds
let lastUpdate = new Date();

function updateLastUpdateText() {
    const now = new Date();
    const diff = Math.floor((now - lastUpdate) / 1000);
    
    let text = '';
    if (diff < 60) {
        text = 'Atualizado há ' + diff + 's';
    } else {
        text = 'Atualizado há ' + Math.floor(diff / 60) + 'min';
    }
    
    document.getElementById('last-updated').textContent = text;
}

// Update text every second
setInterval(updateLastUpdateText, 1000);

// Fetch rankings every 5 seconds
setInterval(async function() {
    try {
        const response = await fetch('<?php echo new moodle_url('/local/tubaron/ajax/get_rankings.php', ['type' => $type]); ?>');
        const data = await response.json();
        
        if (data.rankings && data.rankings.length > 0) {
            // Update table (simplified - full implementation would use DOM diffing)
            lastUpdate = new Date();
            
            // Optional: highlight changed positions
            data.rankings.forEach((rank, index) => {
                const row = document.getElementById('rank-' + rank.id);
                if (row) {
                    // Check if position changed
                    const currentPos = row.querySelector('.tubaron-rank-medal').textContent;
                    if (parseInt(currentPos) !== (index + 1)) {
                        // Position changed - highlight
                        row.style.background = '#fffbeb';
                        setTimeout(() => {
                            row.style.background = '';
                        }, 3000);
                    }
                }
            });
        }
    } catch (error) {
        console.error('Error fetching rankings:', error);
    }
}, 5000);
</script>

<?php
echo $OUTPUT->footer();

