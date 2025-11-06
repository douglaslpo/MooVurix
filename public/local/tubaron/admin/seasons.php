<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Manage Seasons (Admin)
 *
 * @package    local_tubaron
 * @copyright  2025 Tubaron Telecomunicações
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__ . '/../lib.php'); // Include Tubaron functions

require_login();

$context = context_system::instance();
require_capability('local/tubaron:manageseasons', $context);

$action = optional_param('action', 'list', PARAM_ALPHA);
$seasonid = optional_param('id', 0, PARAM_INT);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/tubaron/admin/seasons.php'));
$PAGE->set_title(get_string('seasons', 'local_tubaron'));
$PAGE->set_heading(get_string('seasons', 'local_tubaron'));
$PAGE->set_pagelayout('admin');

// Handle actions
if ($action === 'create' || $action === 'edit') {
    require_once(__DIR__ . '/season_form.php');
    
    $mform = new season_edit_form(new moodle_url('/local/tubaron/admin/seasons.php', ['action' => $action, 'id' => $seasonid]));
    
    if ($mform->is_cancelled()) {
        redirect(new moodle_url('/local/tubaron/admin/seasons.php'));
    } else if ($data = $mform->get_data()) {
        try {
            if ($action === 'create') {
                $seasonid = \local_tubaron\season_manager::create_season($data);
                redirect(
                    new moodle_url('/local/tubaron/admin/seasons.php'),
                    get_string('season_created_success', 'local_tubaron'),
                    null,
                    \core\output\notification::NOTIFY_SUCCESS
                );
            } else {
                // Edit logic here
                redirect(new moodle_url('/local/tubaron/admin/seasons.php'));
            }
        } catch (\moodle_exception $e) {
            \core\notification::error($e->getMessage());
        }
    }
    
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
    exit;
}

if ($action === 'close' && $seasonid) {
    require_sesskey();
    
    try {
        \local_tubaron\season_manager::close_season($seasonid);
        redirect(
            new moodle_url('/local/tubaron/admin/seasons.php'),
            get_string('season_closed_success', 'local_tubaron'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    } catch (\moodle_exception $e) {
        \core\notification::error($e->getMessage());
    }
}

// List seasons
$seasons = \local_tubaron\season_manager::get_seasons();

echo $OUTPUT->header();
?>

<style>
.tubaron-admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.tubaron-seasons-grid {
    display: grid;
    gap: 1.5rem;
}

.tubaron-season-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    border-left: 4px solid #3b82f6;
}

.tubaron-season-card.active {
    border-left-color: #22c55e;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
}

.tubaron-season-card.closed {
    border-left-color: #737373;
    background: #fafafa;
}

.tubaron-season-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 1rem;
}

.tubaron-season-actions {
    display: flex;
    gap: 0.5rem;
}
</style>

<div class="tubaron-admin-seasons">
    <div class="tubaron-admin-header">
        <div>
            <h1 style="margin: 0; font-size: 2rem; font-weight: 700;">
                🏆 Gerenciar Temporadas
            </h1>
            <p style="margin-top: 0.5rem; color: #737373;">
                Criar e gerenciar temporadas de gamificação (6-12 meses)
            </p>
        </div>

        <a href="<?php echo new moodle_url('/local/tubaron/admin/seasons.php', ['action' => 'create']); ?>" class="btn btn-primary btn-lg">
            ➕ Nova Temporada
        </a>
    </div>

    <?php if (!empty($seasons)): ?>
        <div class="tubaron-seasons-grid">
            <?php foreach ($seasons as $season): 
                $isactive = $season->status === 'active';
                $isclosed = $season->status === 'closed';
                $duration = ceil(($season->enddate - $season->startdate) / (30 * 24 * 60 * 60));
            ?>
                <div class="tubaron-season-card <?php echo $season->status; ?>">
                    <div class="tubaron-season-header">
                        <div>
                            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; display: flex; align-items: center; gap: 0.75rem;">
                                <?php echo format_string($season->name); ?>
                                <span class="badge badge-<?php echo $isactive ? 'success' : ($isclosed ? 'secondary' : 'warning'); ?>">
                                    <?php echo strtoupper(get_string('status_' . $season->status, 'local_tubaron')); ?>
                                </span>
                            </h2>
                            <div style="display: flex; gap: 1.5rem; margin-top: 0.75rem; font-size: 0.875rem; color: #737373;">
                                <span>📅 <?php echo userdate($season->startdate, '%d %b %Y'); ?> → <?php echo userdate($season->enddate, '%d %b %Y'); ?></span>
                                <span>⏱️ <?php echo $duration; ?> meses</span>
                            </div>
                        </div>

                        <div class="tubaron-season-actions">
                            <?php if (!$isclosed): ?>
                                <a href="<?php echo new moodle_url('/local/tubaron/admin/seasons.php', ['action' => 'edit', 'id' => $season->id]); ?>" class="btn btn-sm btn-outline-primary">
                                    ✏️ Editar
                                </a>

                                <?php if ($isactive): ?>
                                    <form method="post" action="<?php echo new moodle_url('/local/tubaron/admin/seasons.php'); ?>" style="display: inline;">
                                        <input type="hidden" name="action" value="close">
                                        <input type="hidden" name="id" value="<?php echo $season->id; ?>">
                                        <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Encerrar temporada? Rankings serão congelados.');">
                                            🔒 Encerrar
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Stats -->
                    <?php
                    $teamcount = $DB->count_records('local_tubaron_teams', ['seasonid' => $season->id]);
                    $taskcount = $DB->count_records_sql(
                        "SELECT COUNT(DISTINCT t.id)
                           FROM {local_tubaron_tasks} t
                           JOIN {local_tubaron_missions} m ON m.id = t.missionid
                          WHERE m.seasonid = ?",
                        [$season->id]
                    );
                    $participantcount = $DB->count_records_sql(
                        "SELECT COUNT(DISTINCT userid)
                           FROM {local_tubaron_scores}
                          WHERE seasonid = ? AND entitytype = ?",
                        [$season->id, 'user']
                    );
                    ?>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e5e5e5;">
                        <div>
                            <div style="font-size: 0.75rem; color: #737373; margin-bottom: 0.25rem;">Equipes</div>
                            <div style="font-size: 1.5rem; font-weight: 700;">👥 <?php echo $teamcount; ?></div>
                        </div>

                        <div>
                            <div style="font-size: 0.75rem; color: #737373; margin-bottom: 0.25rem;">Tarefas</div>
                            <div style="font-size: 1.5rem; font-weight: 700;">📋 <?php echo $taskcount; ?></div>
                        </div>

                        <div>
                            <div style="font-size: 0.75rem; color: #737373; margin-bottom: 0.25rem;">Participantes</div>
                            <div style="font-size: 1.5rem; font-weight: 700;">👤 <?php echo $participantcount; ?></div>
                        </div>

                        <?php if ($participantcount > 0): ?>
                            <div>
                                <div style="font-size: 0.75rem; color: #737373; margin-bottom: 0.25rem;">Engajamento</div>
                                <div style="font-size: 1.5rem; font-weight: 700;">
                                    📊 <?php echo round(($participantcount / $DB->count_records('user', ['deleted' => 0, 'suspended' => 0])) * 100); ?>%
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- View Details -->
                    <div style="margin-top: 1.5rem;">
                        <a href="<?php echo new moodle_url('/local/tubaron/admin/season_details.php', ['id' => $season->id]); ?>" class="btn btn-sm btn-outline-secondary" style="width: 100%;">
                            Ver Detalhes Completos →
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <!-- Empty State -->
        <div style="text-align: center; padding: 4rem; background: white; border-radius: 12px;">
            <div style="font-size: 5rem; margin-bottom: 1.5rem;">🏆</div>
            <h2 style="color: #525252; margin-bottom: 1rem;">Nenhuma Temporada Criada</h2>
            <p style="color: #a3a3a3; max-width: 500px; margin: 0 auto 2rem;">
                Crie a primeira temporada de gamificação Tubaron! Temporadas duram entre 6 e 12 meses.
            </p>

            <a href="<?php echo new moodle_url('/local/tubaron/admin/seasons.php', ['action' => 'create']); ?>" class="btn btn-primary btn-lg">
                ➕ Criar Primeira Temporada
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
echo $OUTPUT->footer();

