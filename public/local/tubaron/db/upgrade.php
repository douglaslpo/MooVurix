<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Database Upgrade
 *
 * Gerencia upgrades do schema do banco de dados
 * Integrado ao MooVurix LMS Platform
 *
 * @package    local_tubaron
 * @copyright  2025 Tubaron Telecomunicações
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute tubaron upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_tubaron_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    // Upgrade to version 2025110602 (Sprint 2 - Teams CRUD)
    if ($oldversion < 2025110602) {

        // Define table local_tubaron_teams to be modified
        $table = new xmldb_table('local_tubaron_teams');

        // Add field status
        $field = new xmldb_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'active', 'memberscount');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add field description
        $field = new xmldb_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null, 'name');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add field maxmembers
        $field = new xmldb_field('maxmembers', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '10', 'status');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add field avatarurl
        $field = new xmldb_field('avatarurl', XMLDB_TYPE_CHAR, '512', null, null, null, null, 'maxmembers');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add field timemodified
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timecreated');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add field role to team_members
        $table = new xmldb_table('local_tubaron_team_members');
        $field = new xmldb_field('role', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'member', 'userid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Tubaron savepoint reached.
        upgrade_plugin_savepoint(true, 2025110602, 'local', 'tubaron');
    }

    // Upgrade to version 2025110604 (Sprint 2+3 - Tasks fields)
    if ($oldversion < 2025110604) {

        // Define table local_tubaron_tasks to be modified
        $table = new xmldb_table('local_tubaron_tasks');

        // Add field votingmethod
        $field = new xmldb_field('votingmethod', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'rating', 'status');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add field approvalcriteria
        $field = new xmldb_field('approvalcriteria', XMLDB_TYPE_TEXT, null, null, null, null, null, 'votingmethod');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add field votingdeadline
        $field = new xmldb_field('votingdeadline', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'approvalcriteria');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Tubaron savepoint reached.
        upgrade_plugin_savepoint(true, 2025110604, 'local', 'tubaron');
    }

    return true;
}

