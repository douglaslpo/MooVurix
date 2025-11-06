<?php
// This file is part of MooVurix - Based on Moodle - http://moodle.org/
//
// MooVurix is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Main page
 *
 * @package    local_tubaron
 * @copyright  2025 Tubaron Telecomunicações
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();
require_capability('local/tubaron:viewdashboard', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/tubaron/index.php'));
$PAGE->set_title(get_string('pluginname', 'local_tubaron'));
$PAGE->set_heading(get_string('pluginname', 'local_tubaron'));
$PAGE->set_pagelayout('standard');

// Redirect to dashboard
redirect(new moodle_url('/local/tubaron/dashboard.php'));

