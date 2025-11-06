<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Main library functions
 *
 * @package    local_tubaron
 * @copyright  2025 Tubaron Telecomunicações
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Extends navigation to add Tubaron links
 *
 * @param global_navigation $navigation Navigation node
 */
function local_tubaron_extend_navigation(global_navigation $navigation) {
    global $USER, $PAGE;

    // Only add for authenticated users
    if (!isloggedin() || isguestuser()) {
        return;
    }

    // Create main Tubaron node
    $tubaronnode = $navigation->add(
        get_string('pluginname', 'local_tubaron'),
        new moodle_url('/local/tubaron/index.php'),
        navigation_node::TYPE_CUSTOM,
        null,
        'tubaron',
        new pix_icon('i/trophy', get_string('pluginname', 'local_tubaron'))
    );

    // Dashboard
    if (has_capability('local/tubaron:viewdashboard', context_system::instance())) {
        $tubaronnode->add(
            get_string('dashboard', 'local_tubaron'),
            new moodle_url('/local/tubaron/dashboard.php'),
            navigation_node::TYPE_CUSTOM,
            null,
            'tubaron_dashboard'
        );
    }

    // Tasks
    if (has_capability('local/tubaron:viewtasks', context_system::instance())) {
        $tasksnode = $tubaronnode->add(
            get_string('tasks', 'local_tubaron'),
            new moodle_url('/local/tubaron/tasks/index.php'),
            navigation_node::TYPE_CUSTOM,
            null,
            'tubaron_tasks'
        );

        if (has_capability('local/tubaron:createtask', context_system::instance())) {
            $tasksnode->add(
                get_string('createtask', 'local_tubaron'),
                new moodle_url('/local/tubaron/tasks/edit.php'),
                navigation_node::TYPE_CUSTOM
            );
        }
    }

    // Teams
    if (has_capability('local/tubaron:viewteams', context_system::instance())) {
        $teamsnode = $tubaronnode->add(
            get_string('teams', 'local_tubaron'),
            new moodle_url('/local/tubaron/teams/index.php'),
            navigation_node::TYPE_CUSTOM,
            null,
            'tubaron_teams'
        );
    }

    // Rankings
    if (has_capability('local/tubaron:viewrankings', context_system::instance())) {
        $tubaronnode->add(
            get_string('rankings', 'local_tubaron'),
            new moodle_url('/local/tubaron/rankings.php'),
            navigation_node::TYPE_CUSTOM,
            null,
            'tubaron_rankings'
        );
    }

    // Calendar
    $tubaronnode->add(
        get_string('calendar', 'local_tubaron'),
        new moodle_url('/local/tubaron/calendar.php'),
        navigation_node::TYPE_CUSTOM,
        null,
        'tubaron_calendar'
    );

    // Admin (apenas para managers)
    if (has_capability('local/tubaron:administrate', context_system::instance())) {
        $adminnode = $tubaronnode->add(
            get_string('admin', 'local_tubaron'),
            new moodle_url('/local/tubaron/admin/index.php'),
            navigation_node::TYPE_CUSTOM,
            null,
            'tubaron_admin'
        );

        $adminnode->add(
            get_string('seasons', 'local_tubaron'),
            new moodle_url('/local/tubaron/admin/seasons.php')
        );

        $adminnode->add(
            get_string('reports', 'local_tubaron'),
            new moodle_url('/local/tubaron/admin/reports.php')
        );
    }

    // Set active node based on current page
    if (strpos($PAGE->url->get_path(), '/local/tubaron/') !== false) {
        $tubaronnode->make_active();
    }
}

/**
 * Extends settings navigation to add Tubaron user menu items
 *
 * @param settings_navigation $navigation Settings navigation
 * @param context $context Context
 */
function local_tubaron_extend_settings_navigation(settings_navigation $navigation, context $context) {
    global $USER;

    if (!isloggedin() || isguestuser()) {
        return;
    }

    // Add to user menu
    if ($usernode = $navigation->get('usercurrentsettings')) {
        $usernode->add(
            get_string('myachievements', 'local_tubaron'),
            new moodle_url('/local/tubaron/achievements.php'),
            navigation_node::TYPE_SETTING,
            null,
            'tubaron_achievements'
        );

        // LGPD data export
        $usernode->add(
            get_string('lgpd_export', 'local_tubaron'),
            new moodle_url('/local/tubaron/lgpd/export.php'),
            navigation_node::TYPE_SETTING,
            null,
            'tubaron_lgpd'
        );
    }
}

/**
 * Get active season
 *
 * @return object|false Active season object or false
 */
function local_tubaron_get_active_season() {
    global $DB;

    $now = time();

    // Use get_records_sql with LIMIT and get first record
    $seasons = $DB->get_records_sql(
        "SELECT *
           FROM {local_tubaron_seasons}
          WHERE status = ?
            AND startdate <= ?
            AND enddate >= ?
       ORDER BY startdate DESC",
        ['active', $now, $now],
        0,  // limitfrom
        1   // limitnum (LIMIT 1)
    );

    // Return first record or false
    return !empty($seasons) ? reset($seasons) : false;
}

/**
 * Check if user can vote on submission
 *
 * @param int $taskid Task ID
 * @param int $submissionid Submission ID
 * @param int $userid User ID
 * @return bool True if can vote
 */
function local_tubaron_can_vote($taskid, $submissionid, $userid) {
    global $DB;

    // Check if already voted
    if ($DB->record_exists('local_tubaron_votes', ['taskid' => $taskid, 'voterid' => $userid])) {
        return false;
    }

    // Get task
    $task = $DB->get_record('local_tubaron_tasks', ['id' => $taskid], '*', MUST_EXIST);

    // Check voting window
    $now = time();
    if ($task->votingopenedtime > $now || $task->votingclosedtime < $now) {
        return false;
    }

    // Get submission
    $submission = $DB->get_record('local_tubaron_submissions', ['id' => $submissionid], '*', MUST_EXIST);

    // Check if voting own team
    if ($submission->submittertype === 'team') {
        $ismember = $DB->record_exists('local_tubaron_team_members', [
            'teamid' => $submission->submitterid,
            'userid' => $userid,
            'status' => 'active'
        ]);

        if ($ismember) {
            return false; // Cannot vote own team
        }
    }

    // Check eligibility based on voting_config
    $votingconfig = json_decode($task->votingconfig, true);
    $eligible = $votingconfig['eligible'] ?? 'all';

    if ($eligible === 'all_except_participants') {
        // Check if user participated in task
        $participated = $DB->record_exists_sql(
            "SELECT 1
               FROM {local_tubaron_task_assignments} ta
              WHERE ta.taskid = ?
                AND (
                    (ta.assigneetype = 'user' AND ta.assigneeid = ?)
                    OR
                    (ta.assigneetype = 'team' AND ta.assigneeid IN (
                        SELECT teamid
                          FROM {local_tubaron_team_members}
                         WHERE userid = ? AND status = ?
                    ))
                )",
            [$taskid, $userid, $userid, 'active']
        );

        if ($participated) {
            return false;
        }
    }

    return true;
}

/**
 * Calculate and update scores after task completion or voting
 *
 * @param int $taskid Task ID
 * @param array $ranking Array of submission rankings (if competitive)
 * @return bool Success
 */
function local_tubaron_update_scores($taskid, $ranking = null) {
    global $DB;

    $task = $DB->get_record('local_tubaron_tasks', ['id' => $taskid], '*', MUST_EXIST);

    // Get season
    $assignments = $DB->get_records('local_tubaron_task_assignments', ['taskid' => $taskid]);
    if (empty($assignments)) {
        return false;
    }

    // Get season from task's mission or active season
    $seasonid = null;
    if ($task->missionid) {
        $mission = $DB->get_record('local_tubaron_missions', ['id' => $task->missionid]);
        $seasonid = $mission->seasonid;
    } else {
        $activeseason = local_tubaron_get_active_season();
        $seasonid = $activeseason ? $activeseason->id : null;
    }

    if (!$seasonid) {
        return false;
    }

    // Get mission weight
    $weight = 1.0;
    if ($task->missionid) {
        $mission = $DB->get_record('local_tubaron_missions', ['id' => $task->missionid]);
        $weight = $mission->weight;
    }

    // Calculate points based on task type
    if ($task->type === 'competitive' && $ranking) {
        // Competitive task - points based on ranking
        $pointsmap = json_decode($task->votingconfig, true)['points'] ?? [
            '1' => 50,
            '2' => 30,
            '3' => 15,
            'participation' => 5
        ];

        foreach ($ranking as $position => $submission) {
            $points = ($pointsmap[(string)($position + 1)] ?? $pointsmap['participation']) * $weight;

            local_tubaron_add_points(
                $submission->submittertype,
                $submission->submitterid,
                $seasonid,
                $points,
                $position === 0 ? 1 : 0, // First place
                $position === 1 ? 1 : 0, // Second place
                $position === 2 ? 1 : 0  // Third place
            );
        }
    } else {
        // Individual or team task - fixed points
        $points = $task->points * $weight;

        foreach ($assignments as $assignment) {
            local_tubaron_add_points(
                $assignment->assigneetype,
                $assignment->assigneeid,
                $seasonid,
                $points,
                0, 0, 0 // No podium places
            );
        }
    }

    // Refresh rankings
    local_tubaron_refresh_rankings($seasonid);

    return true;
}

/**
 * Add points to user or team
 *
 * @param string $entitytype 'user' or 'team'
 * @param int $entityid Entity ID
 * @param int $seasonid Season ID
 * @param float $points Points to add
 * @param int $firstplaces First places increment
 * @param int $secondplaces Second places increment
 * @param int $thirdplaces Third places increment
 */
function local_tubaron_add_points($entitytype, $entityid, $seasonid, $points, $firstplaces = 0, $secondplaces = 0, $thirdplaces = 0) {
    global $DB;

    $score = $DB->get_record('local_tubaron_scores', [
        'entitytype' => $entitytype,
        'entityid' => $entityid,
        'seasonid' => $seasonid
    ]);

    if ($score) {
        // Update existing
        $score->points += $points;
        $score->taskcount += 1;
        $score->firstplaces += $firstplaces;
        $score->secondplaces += $secondplaces;
        $score->thirdplaces += $thirdplaces;
        $score->timemodified = time();
        $DB->update_record('local_tubaron_scores', $score);
    } else {
        // Create new
        $score = new stdClass();
        $score->entitytype = $entitytype;
        $score->entityid = $entityid;
        $score->seasonid = $seasonid;
        $score->points = $points;
        $score->taskcount = 1;
        $score->firstplaces = $firstplaces;
        $score->secondplaces = $secondplaces;
        $score->thirdplaces = $thirdplaces;
        $score->timemodified = time();
        $DB->insert_record('local_tubaron_scores', $score);
    }

    // If team, also add points to members (50%)
    if ($entitytype === 'team') {
        $members = $DB->get_records('local_tubaron_team_members', [
            'teamid' => $entityid,
            'status' => 'active'
        ]);

        foreach ($members as $member) {
            local_tubaron_add_points(
                'user',
                $member->userid,
                $seasonid,
                $points / 2, // Members get 50% of team points
                $firstplaces,
                $secondplaces,
                $thirdplaces
            );
        }
    }
}

/**
 * Refresh rankings for a season
 *
 * @param int $seasonid Season ID
 */
function local_tubaron_refresh_rankings($seasonid) {
    global $DB;

    // Calculate ranks for users
    $userscores = $DB->get_records(
        'local_tubaron_scores',
        ['seasonid' => $seasonid, 'entitytype' => 'user'],
        'points DESC, firstplaces DESC, taskcount DESC'
    );

    $rank = 1;
    foreach ($userscores as $score) {
        $score->rank = $rank++;
        $score->timemodified = time();
        $DB->update_record('local_tubaron_scores', $score);
    }

    // Calculate ranks for teams
    $teamscores = $DB->get_records(
        'local_tubaron_scores',
        ['seasonid' => $seasonid, 'entitytype' => 'team'],
        'points DESC, firstplaces DESC, taskcount DESC'
    );

    $rank = 1;
    foreach ($teamscores as $score) {
        $score->rank = $rank++;
        $score->timemodified = time();
        $DB->update_record('local_tubaron_scores', $score);
    }
}

/**
 * Get user's ranking in current season
 *
 * @param int $userid User ID
 * @return object|false Score record or false
 */
function local_tubaron_get_user_score($userid) {
    global $DB;

    $season = local_tubaron_get_active_season();
    if (!$season) {
        return false;
    }

    return $DB->get_record('local_tubaron_scores', [
        'entitytype' => 'user',
        'entityid' => $userid,
        'seasonid' => $season->id
    ]);
}

/**
 * Get top rankings
 *
 * @param string $entitytype 'user' or 'team'
 * @param int $limit Number of top entities (default 10)
 * @param int $seasonid Season ID (default active season)
 * @return array Array of score records with entity details
 */
function local_tubaron_get_top_rankings($entitytype = 'user', $limit = 10, $seasonid = null) {
    global $DB;

    if (!$seasonid) {
        $season = local_tubaron_get_active_season();
        $seasonid = $season ? $season->id : 0;
    }

    if (!$seasonid) {
        return [];
    }

    if ($entitytype === 'user') {
        $sql = "SELECT s.*, u.firstname, u.lastname, u.email, u.picture, u.imagealt
                  FROM {local_tubaron_scores} s
                  JOIN {user} u ON u.id = s.entityid
                 WHERE s.seasonid = ?
                   AND s.entitytype = ?
                   AND u.deleted = 0
                   AND u.suspended = 0
              ORDER BY s.points DESC, s.firstplaces DESC, s.taskcount DESC";
    } else {
        $sql = "SELECT s.*, t.name as teamname, t.captainid,
                       u.firstname as captainfirstname, u.lastname as captainlastname
                  FROM {local_tubaron_scores} s
                  JOIN {local_tubaron_teams} t ON t.id = s.entityid
                  JOIN {user} u ON u.id = t.captainid
                 WHERE s.seasonid = ?
                   AND s.entitytype = ?
              ORDER BY s.points DESC, s.firstplaces DESC, s.taskcount DESC";
    }

    // Use positional parameters and limitnum parameter for LIMIT
    return $DB->get_records_sql(
        $sql,
        [$seasonid, $entitytype],
        0,      // limitfrom (offset)
        $limit  // limitnum (LIMIT)
    );
}

/**
 * Create audit log entry (LGPD compliance)
 *
 * @param string $entity Entity type (task, vote, season, etc)
 * @param int $entityid Entity ID
 * @param string $action Action (created, updated, deleted)
 * @param int $actorid User ID performing action
 * @param mixed $before Before state (will be JSON encoded)
 * @param mixed $after After state (will be JSON encoded)
 */
function local_tubaron_audit_log($entity, $entityid, $action, $actorid, $before = null, $after = null) {
    global $DB;

    $log = new stdClass();
    $log->entity = $entity;
    $log->entityid = $entityid;
    $log->action = $action;
    $log->actorid = $actorid;
    $log->before = $before ? json_encode($before) : null;
    $log->after = $after ? json_encode($after) : null;
    $log->iphash = hash('sha256', getremoteaddr());
    $log->timecreated = time();

    $DB->insert_record('local_tubaron_audit_logs', $log);
}

/**
 * Check rate limit for voting (Redis-based)
 *
 * @param int $userid User ID
 * @param int $maxvotes Max votes per window (default 10)
 * @param int $window Window in seconds (default 60)
 * @return bool True if allowed, false if exceeded
 */
function local_tubaron_check_vote_ratelimit($userid, $maxvotes = 10, $window = 60) {
    global $DB;

    // Fallback to database if Redis not available
    // Count recent votes
    $recentvotes = $DB->count_records_select(
        'local_tubaron_votes',
        'voterid = ? AND timevoted > ?',
        [$userid, time() - $window]
    );

    return $recentvotes < $maxvotes;
}

/**
 * Get user's pending tasks
 *
 * @param int $userid User ID
 * @param string $urgency Filter by urgency (urgent, due_soon, all)
 * @return array Array of task records
 */
function local_tubaron_get_user_pending_tasks($userid, $urgency = 'all') {
    global $DB;

    $now = time();

    $whereclauses = [];
    $params = [];

    // Urgency filter
    if ($urgency === 'urgent') {
        $whereclauses[] = 't.deadline < ?';
        $params[] = $now + (24 * 60 * 60); // Next 24h
    } elseif ($urgency === 'due_soon') {
        $whereclauses[] = 't.deadline BETWEEN ? AND ?';
        $params[] = $now + (24 * 60 * 60);
        $params[] = $now + (48 * 60 * 60);
    }

    $whereclauses[] = "t.status IN ('open', 'in_progress')";

    $where = implode(' AND ', $whereclauses);

    // Add userid parameters (used twice in query)
    $params[] = $userid; // for ta.assigneeid
    $params[] = $userid; // for tm.userid

    $sql = "SELECT DISTINCT t.*
              FROM {local_tubaron_tasks} t
              JOIN {local_tubaron_task_assignments} ta ON ta.taskid = t.id
         LEFT JOIN {local_tubaron_team_members} tm ON tm.teamid = ta.assigneeid AND ta.assigneetype = 'team'
             WHERE $where
               AND (
                   (ta.assigneetype = 'user' AND ta.assigneeid = ?)
                   OR
                   (ta.assigneetype = 'team' AND tm.userid = ? AND tm.status = 'active')
               )
          ORDER BY t.deadline ASC";

    return $DB->get_records_sql($sql, $params);
}

/**
 * Send notification to user
 *
 * @param int $userid User ID
 * @param string $type Notification type
 * @param string $title Title
 * @param string $message Message
 * @return int Notification ID
 */
function local_tubaron_send_notification($userid, $type, $title, $message) {
    global $DB;

    $notification = new stdClass();
    $notification->userid = $userid;
    $notification->type = $type;
    $notification->title = $title;
    $notification->message = $message;
    $notification->read = 0;
    $notification->timecreated = time();

    $notificationid = $DB->insert_record('local_tubaron_notifications', $notification);

    // Also use Moodle's message API for email/popup
    $userto = $DB->get_record('user', ['id' => $userid]);
    if ($userto) {
        $message = new \core\message\message();
        $message->component = 'local_tubaron';
        $message->name = 'notification';
        $message->userfrom = core_user::get_noreply_user();
        $message->userto = $userto;
        $message->subject = $title;
        $message->fullmessage = $message;
        $message->fullmessageformat = FORMAT_HTML;
        $message->fullmessagehtml = $message;
        $message->smallmessage = $title;
        $message->notification = 1;

        message_send($message);
    }

    return $notificationid;
}

