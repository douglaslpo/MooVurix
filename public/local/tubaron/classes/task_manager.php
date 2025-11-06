<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Task Manager
 *
 * @package    local_tubaron
 * @copyright  2025 Tubaron Telecomunicações
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tubaron;

defined('MOODLE_INTERNAL') || die();

/**
 * Task manager class
 *
 * Handles task creation, submission, completion, and competitive voting
 */
class task_manager {

    /** Task types */
    const TYPE_INDIVIDUAL = 'individual';
    const TYPE_TEAM = 'team';
    const TYPE_COMPETITIVE = 'competitive';

    /** Task statuses */
    const STATUS_OPEN = 'open';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_VOTING = 'voting';
    const STATUS_COMPLETED = 'completed';

    /**
     * Create a new task
     *
     * @param object $data Task data
     * @return int Task ID
     * @throws \moodle_exception
     */
    public static function create_task($data) {
        global $DB, $USER;

        // Validate required fields
        if (empty($data->type) || empty($data->title) || empty($data->deadline)) {
            throw new \moodle_exception('invalid_task_data', 'local_tubaron');
        }

        // Validate competitive requirements
        if ($data->type === self::TYPE_COMPETITIVE) {
            if (empty($data->votingconfig)) {
                throw new \moodle_exception('competitive_voting_required', 'local_tubaron');
            }

            // Validate teams have min 3 members
            if (!empty($data->assignees)) {
                foreach ($data->assignees as $assignee) {
                    if ($assignee->type === 'team') {
                        $team = $DB->get_record('local_tubaron_teams', ['id' => $assignee->id], '*', MUST_EXIST);
                        if ($team->memberscount < 3) {
                            throw new \moodle_exception(
                                'team_minmembers_error',
                                'local_tubaron',
                                '',
                                ['name' => $team->name, 'count' => $team->memberscount]
                            );
                        }
                    }
                }
            }
        }

        // Create task
        $task = new \stdClass();
        $task->type = $data->type;
        $task->title = $data->title;
        $task->description = $data->description ?? '';
        $task->creatorid = $USER->id;
        $task->missionid = $data->missionid ?? null;
        $task->courseid = $data->courseid ?? null;
        $task->deadline = $data->deadline;
        $task->points = $data->points ?? 10;
        $task->status = self::STATUS_OPEN;
        $task->votingconfig = !empty($data->votingconfig) ? json_encode($data->votingconfig) : null;
        $task->timecreated = time();
        $task->timemodified = time();

        $taskid = $DB->insert_record('local_tubaron_tasks', $task);
        $task->id = $taskid;

        // Create assignments
        if (!empty($data->assignees)) {
            foreach ($data->assignees as $assignee) {
                $assignment = new \stdClass();
                $assignment->taskid = $taskid;
                $assignment->assigneetype = $assignee->type;
                $assignment->assigneeid = $assignee->id;
                $assignment->timecreated = time();
                $DB->insert_record('local_tubaron_task_assignments', $assignment);

                // Send notification to assignees
                if ($assignee->type === 'user') {
                    \local_tubaron_send_notification(
                        $assignee->id,
                        'task_assigned',
                        get_string('notification_taskassigned', 'local_tubaron', $task->title),
                        $task->description
                    );
                } else if ($assignee->type === 'team') {
                    // Notify all team members
                    $members = $DB->get_records('local_tubaron_team_members', [
                        'teamid' => $assignee->id,
                        'status' => 'active'
                    ]);
                    foreach ($members as $member) {
                        \local_tubaron_send_notification(
                            $member->userid,
                            'task_assigned',
                            get_string('notification_taskassigned', 'local_tubaron', $task->title),
                            $task->description
                        );
                    }
                }
            }
        }

        // Audit log
        \local_tubaron_audit_log('task', $taskid, 'created', $USER->id, null, $task);

        // Trigger event
        $event = \local_tubaron\event\task_created::create([
            'objectid' => $taskid,
            'context' => \context_system::instance(),
            'other' => [
                'type' => $task->type,
                'title' => $task->title
            ]
        ]);
        $event->trigger();

        return $taskid;
    }

    /**
     * Submit a task solution
     *
     * @param int $taskid Task ID
     * @param string $content Submission content
     * @param array $files Array of file paths
     * @param string $submittertype 'user' or 'team'
     * @param int $submitterid Submitter ID
     * @return int Submission ID
     * @throws \moodle_exception
     */
    public static function submit_task($taskid, $content, $files = [], $submittertype = 'user', $submitterid = null) {
        global $DB, $USER;

        $submitterid = $submitterid ?? $USER->id;

        $task = $DB->get_record('local_tubaron_tasks', ['id' => $taskid], '*', MUST_EXIST);

        // Validate task is open or in_progress
        if (!in_array($task->status, [self::STATUS_OPEN, self::STATUS_IN_PROGRESS])) {
            throw new \moodle_exception('task_not_open', 'local_tubaron');
        }

        // Check if already submitted
        $existing = $DB->get_record('local_tubaron_submissions', [
            'taskid' => $taskid,
            'submittertype' => $submittertype,
            'submitterid' => $submitterid
        ]);

        if ($existing) {
            throw new \moodle_exception('already_submitted', 'local_tubaron');
        }

        // Create submission
        $submission = new \stdClass();
        $submission->taskid = $taskid;
        $submission->submittertype = $submittertype;
        $submission->submitterid = $submitterid;
        $submission->content = $content;
        $submission->files = json_encode($files);
        $submission->votescount = 0;
        $submission->avgscore = null;
        $submission->timesubmitted = time();

        $submissionid = $DB->insert_record('local_tubaron_submissions', $submission);

        // Update task status to in_progress if first submission
        if ($task->status === self::STATUS_OPEN) {
            $task->status = self::STATUS_IN_PROGRESS;
            $task->timemodified = time();
            $DB->update_record('local_tubaron_tasks', $task);
        }

        // Audit log
        \local_tubaron_audit_log('submission', $submissionid, 'created', $USER->id, null, $submission);

        // Trigger event
        $event = \local_tubaron\event\task_submitted::create([
            'objectid' => $submissionid,
            'context' => \context_system::instance(),
            'relateduserid' => $submittertype === 'user' ? $submitterid : null,
            'other' => [
                'taskid' => $taskid,
                'submittertype' => $submittertype
            ]
        ]);
        $event->trigger();

        return $submissionid;
    }

    /**
     * Complete a task (non-competitive)
     *
     * @param int $taskid Task ID
     * @return bool Success
     * @throws \moodle_exception
     */
    public static function complete_task($taskid) {
        global $DB, $USER;

        $task = $DB->get_record('local_tubaron_tasks', ['id' => $taskid], '*', MUST_EXIST);

        // Competitive tasks use voting workflow
        if ($task->type === self::TYPE_COMPETITIVE) {
            throw new \moodle_exception('competitive_task_voting', 'local_tubaron');
        }

        // Check permission based on policy
        $policy = get_config('local_tubaron', 'taskcompletion_policy') ?? 'free';
        
        if ($policy === 'approval') {
            // Only captain/leader/admin can complete
            if (!has_capability('local/tubaron:edittask', \context_system::instance())) {
                throw new \moodle_exception('permission_denied', 'local_tubaron');
            }
        }

        $before = clone $task;

        // Update status
        $task->status = self::STATUS_COMPLETED;
        $task->timemodified = time();
        $DB->update_record('local_tubaron_tasks', $task);

        // Update scores
        \local_tubaron_update_scores($taskid);

        // Audit log
        \local_tubaron_audit_log('task', $taskid, 'completed', $USER->id, $before, $task);

        // Trigger event
        $event = \local_tubaron\event\task_completed::create([
            'objectid' => $taskid,
            'context' => \context_system::instance(),
            'other' => ['title' => $task->title]
        ]);
        $event->trigger();

        // Check achievements
        self::check_achievements($USER->id);

        return true;
    }

    /**
     * Open voting for competitive task
     *
     * @param int $taskid Task ID
     * @param int $windowhours Voting window in hours
     * @return bool Success
     * @throws \moodle_exception
     */
    public static function open_voting($taskid, $windowhours = 48) {
        global $DB, $USER;

        $task = $DB->get_record('local_tubaron_tasks', ['id' => $taskid], '*', MUST_EXIST);

        if ($task->type !== self::TYPE_COMPETITIVE) {
            throw new \moodle_exception('not_competitive_task', 'local_tubaron');
        }

        if ($task->status !== self::STATUS_IN_PROGRESS) {
            throw new \moodle_exception('invalid_task_status', 'local_tubaron');
        }

        // Check if all teams submitted
        $expectedsubmissions = $DB->count_records('local_tubaron_task_assignments', [
            'taskid' => $taskid,
            'assigneetype' => 'team'
        ]);

        $actualsubmissions = $DB->count_records('local_tubaron_submissions', [
            'taskid' => $taskid,
            'submittertype' => 'team'
        ]);

        if ($actualsubmissions < $expectedsubmissions) {
            throw new \moodle_exception('incomplete_submissions', 'local_tubaron');
        }

        $before = clone $task;

        // Open voting
        $task->status = self::STATUS_VOTING;
        $task->votingopenedtime = time();
        $task->votingclosedtime = time() + ($windowhours * 60 * 60);
        $task->timemodified = time();

        $DB->update_record('local_tubaron_tasks', $task);

        // Notify eligible voters
        self::notify_voting_opened($taskid);

        // Audit log
        \local_tubaron_audit_log('task', $taskid, 'voting_opened', $USER->id, $before, $task);

        // Schedule voting close (scheduled task ou cron)
        self::schedule_voting_close($taskid, $task->votingclosedtime);

        return true;
    }

    /**
     * Close voting and calculate results
     *
     * @param int $taskid Task ID
     * @return array Ranking results
     * @throws \moodle_exception
     */
    public static function close_voting($taskid) {
        global $DB, $USER;

        $task = $DB->get_record('local_tubaron_tasks', ['id' => $taskid], '*', MUST_EXIST);

        if ($task->status !== self::STATUS_VOTING) {
            throw new \moodle_exception('not_in_voting', 'local_tubaron');
        }

        // Get all submissions
        $submissions = $DB->get_records('local_tubaron_submissions', ['taskid' => $taskid]);

        // Calculate ranking based on voting method
        $votingconfig = json_decode($task->votingconfig, true);
        $method = $votingconfig['method'] ?? 'grades';

        $ranking = [];
        switch ($method) {
            case 'majority':
                $ranking = self::rank_by_majority($submissions);
                break;
            case 'grades':
                $ranking = self::rank_by_grades($submissions);
                break;
            case 'ranking':
                $ranking = self::rank_by_weighted_ranking($submissions);
                break;
        }

        $before = clone $task;

        // Update task status
        $task->status = self::STATUS_COMPLETED;
        $task->timemodified = time();
        $DB->update_record('local_tubaron_tasks', $task);

        // Update scores based on ranking
        \local_tubaron_update_scores($taskid, $ranking);

        // Audit log
        \local_tubaron_audit_log('task', $taskid, 'voting_closed', $USER->id, $before, $task);

        // Notify participants about results
        self::notify_voting_results($taskid, $ranking);

        return $ranking;
    }

    /**
     * Rank submissions by majority votes
     *
     * @param array $submissions Array of submission records
     * @return array Sorted array by vote count
     */
    private static function rank_by_majority($submissions) {
        global $DB;

        foreach ($submissions as $submission) {
            $submission->votecount = $DB->count_records('local_tubaron_votes', [
                'submissionid' => $submission->id
            ]);
        }

        usort($submissions, function($a, $b) {
            return $b->votecount <=> $a->votecount;
        });

        return $submissions;
    }

    /**
     * Rank submissions by average grade
     *
     * @param array $submissions Array of submission records
     * @return array Sorted array by average score
     */
    private static function rank_by_grades($submissions) {
        global $DB;

        foreach ($submissions as $submission) {
            $avgscore = $DB->get_field_sql(
                "SELECT AVG(votevalue)
                   FROM {local_tubaron_votes}
                  WHERE submissionid = :submissionid",
                ['submissionid' => $submission->id]
            );
            $submission->avgscore = $avgscore ?: 0;
        }

        usort($submissions, function($a, $b) {
            return $b->avgscore <=> $a->avgscore;
        });

        return $submissions;
    }

    /**
     * Rank submissions by weighted ranking positions
     *
     * @param array $submissions Array of submission records
     * @return array Sorted array by weighted score
     */
    private static function rank_by_weighted_ranking($submissions) {
        global $DB;

        // Points: 1st=3pts, 2nd=2pts, 3rd=1pt
        foreach ($submissions as $submission) {
            $weightedscore = $DB->get_field_sql(
                "SELECT SUM(
                            CASE votevalue
                                WHEN 1 THEN 3
                                WHEN 2 THEN 2
                                WHEN 3 THEN 1
                                ELSE 0
                            END
                        )
                   FROM {local_tubaron_votes}
                  WHERE submissionid = :submissionid",
                ['submissionid' => $submission->id]
            );
            $submission->weightedscore = $weightedscore ?: 0;
        }

        usort($submissions, function($a, $b) {
            return $b->weightedscore <=> $a->weightedscore;
        });

        return $submissions;
    }

    /**
     * Submit vote on submission
     *
     * @param int $taskid Task ID
     * @param int $submissionid Submission ID
     * @param float $votevalue Vote value (0-10)
     * @param int $voterid Voter ID
     * @return int Vote ID
     * @throws \moodle_exception
     */
    public static function submit_vote($taskid, $submissionid, $votevalue, $voterid = null) {
        global $DB, $USER;

        $voterid = $voterid ?? $USER->id;

        // Check rate limit
        $ratelimit = get_config('local_tubaron', 'votingratelimit') ?? 10;
        if (!\local_tubaron_check_vote_ratelimit($voterid, $ratelimit, 60)) {
            throw new \moodle_exception('vote_ratelimit_error', 'local_tubaron');
        }

        // Check if can vote
        if (!\local_tubaron_can_vote($taskid, $submissionid, $voterid)) {
            // Determine specific error
            if ($DB->record_exists('local_tubaron_votes', ['taskid' => $taskid, 'voterid' => $voterid])) {
                throw new \moodle_exception('vote_duplicate_error', 'local_tubaron');
            }

            $submission = $DB->get_record('local_tubaron_submissions', ['id' => $submissionid]);
            if ($submission->submittertype === 'team') {
                $ismember = $DB->record_exists('local_tubaron_team_members', [
                    'teamid' => $submission->submitterid,
                    'userid' => $voterid,
                    'status' => 'active'
                ]);
                if ($ismember) {
                    throw new \moodle_exception('vote_ownteam_error', 'local_tubaron');
                }
            }

            throw new \moodle_exception('vote_noteligible_error', 'local_tubaron');
        }

        // Validate vote value
        if ($votevalue < 0 || $votevalue > 10) {
            throw new \moodle_exception('invalid_vote_value', 'local_tubaron');
        }

        // Create vote
        $vote = new \stdClass();
        $vote->taskid = $taskid;
        $vote->voterid = $voterid;
        $vote->submissionid = $submissionid;
        $vote->votevalue = $votevalue;
        $vote->iphash = hash('sha256', getremoteaddr());
        $vote->timevoted = time();

        $voteid = $DB->insert_record('local_tubaron_votes', $vote);

        // Update submission avg_score
        $avgscore = $DB->get_field_sql(
            "SELECT AVG(votevalue)
               FROM {local_tubaron_votes}
              WHERE submissionid = :submissionid",
            ['submissionid' => $submissionid]
        );

        $DB->set_field('local_tubaron_submissions', 'avgscore', $avgscore, ['id' => $submissionid]);
        $DB->set_field('local_tubaron_submissions', 'votescount',
            $DB->count_records('local_tubaron_votes', ['submissionid' => $submissionid]),
            ['id' => $submissionid]
        );

        // Audit log
        \local_tubaron_audit_log('vote', $voteid, 'created', $voterid, null, [
            'taskid' => $taskid,
            'votevalue' => $votevalue
        ]);

        return $voteid;
    }

    /**
     * Get task progress (for competitive tasks)
     *
     * @param int $taskid Task ID
     * @return object Progress object with completed, total, percentage
     */
    public static function get_task_progress($taskid) {
        global $DB;

        $total = $DB->count_records('local_tubaron_task_assignments', [
            'taskid' => $taskid,
            'assigneetype' => 'team'
        ]);

        $completed = $DB->count_records('local_tubaron_submissions', [
            'taskid' => $taskid,
            'submittertype' => 'team'
        ]);

        return (object)[
            'completed' => $completed,
            'total' => $total,
            'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0
        ];
    }

    /**
     * Check and unlock achievements for user
     *
     * @param int $userid User ID
     */
    private static function check_achievements($userid) {
        global $DB;

        $achievements = $DB->get_records('local_tubaron_achievements');

        foreach ($achievements as $achievement) {
            // Check if already unlocked
            if ($DB->record_exists('local_tubaron_user_achievements', [
                'userid' => $userid,
                'achievementid' => $achievement->id
            ])) {
                continue;
            }

            $criteria = json_decode($achievement->criteria, true);

            // Check criteria
            if (self::check_achievement_criteria($userid, $criteria)) {
                // Unlock achievement
                $userachievement = new \stdClass();
                $userachievement->userid = $userid;
                $userachievement->achievementid = $achievement->id;
                $userachievement->timeunlocked = time();
                $DB->insert_record('local_tubaron_user_achievements', $userachievement);

                // Notify user
                \local_tubaron_send_notification(
                    $userid,
                    'achievement_unlocked',
                    get_string('notification_achievementunlocked', 'local_tubaron', $achievement->name),
                    $achievement->description
                );
            }
        }
    }

    /**
     * Check if user meets achievement criteria
     *
     * @param int $userid User ID
     * @param array $criteria Achievement criteria
     * @return bool True if criteria met
     */
    private static function check_achievement_criteria($userid, $criteria) {
        global $DB;

        $type = $criteria['type'] ?? null;

        switch ($type) {
            case 'rank_position':
                $score = \local_tubaron_get_user_score($userid);
                return $score && $score->rank <= ($criteria['rank'] ?? 1);

            case 'streak':
                $streak = $DB->get_record('local_tubaron_streaks', [
                    'userid' => $userid,
                    'type' => 'daily'
                ]);
                return $streak && $streak->currentcount >= ($criteria['days'] ?? 7);

            case 'first_competitive_win':
                $score = \local_tubaron_get_user_score($userid);
                return $score && $score->firstplaces > 0;

            default:
                return false;
        }
    }

    /**
     * Notify eligible voters that voting has opened
     *
     * @param int $taskid Task ID
     */
    private static function notify_voting_opened($taskid) {
        global $DB;

        $task = $DB->get_record('local_tubaron_tasks', ['id' => $taskid], '*', MUST_EXIST);

        // Get all users except participants
        $sql = "SELECT u.id
                  FROM {user} u
                 WHERE u.deleted = 0
                   AND u.suspended = 0
                   AND u.id NOT IN (
                       SELECT tm.userid
                         FROM {local_tubaron_task_assignments} ta
                         JOIN {local_tubaron_team_members} tm ON tm.teamid = ta.assigneeid
                        WHERE ta.taskid = ?
                          AND ta.assigneetype = ?
                   )";

        $eligibleusers = $DB->get_records_sql($sql, [$taskid, 'team']);

        foreach ($eligibleusers as $user) {
            \local_tubaron_send_notification(
                $user->id,
                'voting_opened',
                get_string('notification_votingopened', 'local_tubaron', $task->title),
                "Votação aberta até " . userdate($task->votingclosedtime)
            );
        }
    }

    /**
     * Notify about voting results
     *
     * @param int $taskid Task ID
     * @param array $ranking Ranking results
     */
    private static function notify_voting_results($taskid, $ranking) {
        global $DB;

        $task = $DB->get_record('local_tubaron_tasks', ['id' => $taskid]);

        foreach ($ranking as $position => $submission) {
            $message = sprintf(
                "Posição %dº (%s pontos)",
                $position + 1,
                $ranking[$position]->points ?? '0'
            );

            if ($submission->submittertype === 'team') {
                $members = $DB->get_records('local_tubaron_team_members', [
                    'teamid' => $submission->submitterid,
                    'status' => 'active'
                ]);

                foreach ($members as $member) {
                    \local_tubaron_send_notification(
                        $member->userid,
                        'results_published',
                        get_string('notification_resultspublished', 'local_tubaron', $task->title),
                        $message
                    );
                }
            } else {
                \local_tubaron_send_notification(
                    $submission->submitterid,
                    'results_published',
                    get_string('notification_resultspublished', 'local_tubaron', $task->title),
                    $message
                );
            }
        }
    }

    /**
     * Schedule voting close (for cron)
     *
     * @param int $taskid Task ID
     * @param int $closetime Close timestamp
     */
    private static function schedule_voting_close($taskid, $closetime) {
        global $DB;

        // This would use Moodle's scheduled task system
        // For now, cron will check tasks with votingclosedtime <= now
    }
}

