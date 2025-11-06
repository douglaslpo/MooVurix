<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tubaron Gamification System - Message providers
 *
 * Define message providers para notificações via Moodle Message API
 *
 * @package    local_tubaron
 * @copyright  2025 Tubaron Telecomunicações
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$messageproviders = [
    // Task assigned notification
    'taskassigned' => [
        'capability' => 'local/tubaron:viewtasks',
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ],

    // Voting opened notification
    'votingopened' => [
        'capability' => 'local/tubaron:vote',
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED,
        ],
    ],

    // Voting closed notification
    'votingclosed' => [
        'capability' => 'local/tubaron:viewtasks',
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED,
        ],
    ],

    // Deadline 24h notification
    'deadline24h' => [
        'capability' => 'local/tubaron:viewtasks',
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ],

    // Results published notification
    'resultspublished' => [
        'capability' => 'local/tubaron:viewtasks',
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED,
        ],
    ],

    // Achievement unlocked notification
    'achievementunlocked' => [
        'capability' => 'local/tubaron:viewdashboard',
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED,
        ],
    ],

    // Report ready notification (admin)
    'reportready' => [
        'capability' => 'local/tubaron:viewreports',
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ],

    // Sprint 5 - New notifications
    
    // Team invitation notification
    'teaminvite' => [
        'capability' => 'local/tubaron:jointeam',
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED,
        ],
    ],

    // Task urgent (< 24h) notification
    'taskurgent' => [
        'capability' => 'local/tubaron:viewtasks',
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_DISALLOWED,
        ],
    ],

    // Season starting notification
    'seasonstarting' => [
        'capability' => 'local/tubaron:viewdashboard',
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED,
        ],
    ],

    // Season ending notification
    'seasonending' => [
        'capability' => 'local/tubaron:viewdashboard',
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ],
];

