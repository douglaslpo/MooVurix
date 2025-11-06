<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Season Manager
 *
 * @package    local_tubaron
 * @copyright  2025 Tubaron Telecomunicações
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tubaron;

defined('MOODLE_INTERNAL') || die();

/**
 * Season manager class
 *
 * Handles season creation, validation, and management
 */
class season_manager {

    /**
     * Create a new season
     *
     * @param object $data Season data
     * @return int Season ID
     * @throws \moodle_exception
     */
    public static function create_season($data) {
        global $DB, $USER;

        // Validate duration (6-12 months)
        $duration = $data->enddate - $data->startdate;
        $sixmonths = 6 * 30 * 24 * 60 * 60;
        $twelvemonths = 12 * 30 * 24 * 60 * 60;

        if ($duration < $sixmonths || $duration > $twelvemonths) {
            throw new \moodle_exception('seasonduration_error', 'local_tubaron');
        }

        // Check for overlapping active seasons
        // Use positional parameters (?) instead of named parameters
        $overlapping = $DB->get_records_sql(
            "SELECT *
               FROM {local_tubaron_seasons}
              WHERE status = ?
                AND (
                    (startdate BETWEEN ? AND ?)
                    OR
                    (enddate BETWEEN ? AND ?)
                    OR
                    (startdate <= ? AND enddate >= ?)
                )",
            [
                'active',           // status
                $data->startdate,   // start1
                $data->enddate,     // end1
                $data->startdate,   // start2
                $data->enddate,     // end2
                $data->startdate,   // start3
                $data->enddate      // end3
            ]
        );

        if (!empty($overlapping)) {
            throw new \moodle_exception('season_overlap_error', 'local_tubaron');
        }

        // Create season
        $season = new \stdClass();
        $season->name = $data->name;
        $season->startdate = $data->startdate;
        $season->enddate = $data->enddate;
        $season->rules = $data->rules ?? json_encode([
            'individual_points' => 10,
            'team_points' => 20,
            'competitive_points' => [
                '1' => 50,
                '2' => 30,
                '3' => 15,
                'participation' => 5
            ]
        ]);
        $season->status = $data->status ?? 'draft';
        $season->timecreated = time();
        $season->timemodified = time();

        $seasonid = $DB->insert_record('local_tubaron_seasons', $season);

        // Audit log
        \local_tubaron_audit_log('season', $seasonid, 'created', $USER->id, null, $season);

        // Trigger event
        $event = \local_tubaron\event\season_created::create([
            'objectid' => $seasonid,
            'context' => \context_system::instance(),
            'other' => ['name' => $season->name]
        ]);
        $event->trigger();

        return $seasonid;
    }

    /**
     * Close a season and freeze rankings
     *
     * @param int $seasonid Season ID
     * @return bool Success
     * @throws \moodle_exception
     */
    public static function close_season($seasonid) {
        global $DB, $USER;

        $season = $DB->get_record('local_tubaron_seasons', ['id' => $seasonid], '*', MUST_EXIST);

        if ($season->status === 'closed') {
            throw new \moodle_exception('season_already_closed', 'local_tubaron');
        }

        $before = clone $season;

        // Update status
        $season->status = 'closed';
        $season->timeclosed = time();
        $season->timemodified = time();

        $DB->update_record('local_tubaron_seasons', $season);

        // Freeze rankings (final refresh)
        \local_tubaron_refresh_rankings($seasonid);

        // Audit log
        \local_tubaron_audit_log('season', $seasonid, 'closed', $USER->id, $before, $season);

        // Trigger event
        $event = \local_tubaron\event\season_closed::create([
            'objectid' => $seasonid,
            'context' => \context_system::instance(),
            'other' => ['name' => $season->name]
        ]);
        $event->trigger();

        return true;
    }

    /**
     * Get active season
     *
     * @return object|false Active season or false
     */
    public static function get_active_season() {
        return \local_tubaron_get_active_season();
    }

    /**
     * Get all seasons
     *
     * @param string $status Filter by status (optional)
     * @return array Array of season records
     */
    public static function get_seasons($status = null) {
        global $DB;

        $params = [];
        if ($status) {
            $params['status'] = $status;
        }

        return $DB->get_records('local_tubaron_seasons', $params, 'startdate DESC');
    }

    /**
     * Validate season duration
     *
     * @param int $startdate Start timestamp
     * @param int $enddate End timestamp
     * @return bool True if valid
     */
    public static function validate_duration($startdate, $enddate) {
        $duration = $enddate - $startdate;
        $sixmonths = 6 * 30 * 24 * 60 * 60;
        $twelvemonths = 12 * 30 * 24 * 60 * 60;

        return $duration >= $sixmonths && $duration <= $twelvemonths;
    }
}

