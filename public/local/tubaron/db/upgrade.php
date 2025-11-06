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

    // Sprint 5: Achievements tables
    if ($oldversion < 2025110606) {

        // Define table local_tubaron_achievements to be created.
        $table = new xmldb_table('local_tubaron_achievements');

        // Adding fields to table local_tubaron_achievements.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('tier', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'bronze');
        $table->add_field('criteriatype', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('criteriarules', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('triggertype', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('iconurl', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('displayorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('active', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_tubaron_achievements.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_tubaron_achievements.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_tubaron_user_achievements to be created.
        $table = new xmldb_table('local_tubaron_user_achievements');

        // Adding fields to table local_tubaron_user_achievements.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('achievementid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timeunlocked', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_tubaron_user_achievements.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_key('achievementid', XMLDB_KEY_FOREIGN, ['achievementid'], 'local_tubaron_achievements', ['id']);

        // Adding indexes to table local_tubaron_user_achievements.
        $table->add_index('userid_achievementid', XMLDB_INDEX_UNIQUE, ['userid', 'achievementid']);

        // Conditionally launch create table for local_tubaron_user_achievements.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_tubaron_streaks to be created.
        $table = new xmldb_table('local_tubaron_streaks');

        // Adding fields to table local_tubaron_streaks.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('streaktype', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('currentcount', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('maxcount', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('lastupdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_tubaron_streaks.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);

        // Adding indexes to table local_tubaron_streaks.
        $table->add_index('userid_streaktype', XMLDB_INDEX_UNIQUE, ['userid', 'streaktype']);

        // Conditionally launch create table for local_tubaron_streaks.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Tubaron savepoint reached.
        upgrade_plugin_savepoint(true, 2025110606, 'local', 'tubaron');
    }

    return true;
}

