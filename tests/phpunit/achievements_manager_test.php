<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * PHPUnit tests for achievements_manager class
 *
 * @package    local_tubaron
 * @category   test
 * @copyright  2025 Tubaron Telecomunicações
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tubaron;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/tubaron/classes/achievements_manager.php');

/**
 * Test case for achievements_manager
 *
 * @package    local_tubaron
 * @category   test
 */
class achievements_manager_test extends \advanced_testcase {

    /**
     * Test achievement unlock - First Steps
     */
    public function test_unlock_first_steps_achievement() {
        global $DB;
        
        $this->resetAfterTest(true);
        
        // Create test user
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        
        // Create season
        $season = new \stdClass();
        $season->name = 'Test Season';
        $season->startdate = time();
        $season->enddate = time() + (30 * 24 * 3600);
        $season->status = 'active';
        $seasonid = $DB->insert_record('local_tubaron_seasons', $season);
        
        // Create and complete first task
        $task = new \stdClass();
        $task->title = 'First Task';
        $task->description = 'Test task';
        $task->seasonid = $seasonid;
        $task->creatorid = $user->id;
        $task->tasktype = 'individual';
        $task->points = 100;
        $task->status = 'completed';
        $task->timecreated = time();
        $taskid = $DB->insert_record('local_tubaron_tasks', $task);
        
        // Check if achievement unlocked
        $manager = new achievements_manager();
        $unlocked = $manager->check_and_unlock_achievements($user->id, 'task_completed');
        
        $this->assertTrue($unlocked);
        
        // Verify achievement record
        $achievement = $DB->get_record('local_tubaron_achievements', [
            'userid' => $user->id,
            'achievementcode' => 'first_steps'
        ]);
        
        $this->assertNotEmpty($achievement);
        $this->assertEquals(1, $achievement->unlocked);
    }

    /**
     * Test achievement unlock - Team Player
     */
    public function test_unlock_team_player_achievement() {
        global $DB;
        
        $this->resetAfterTest(true);
        
        // Create test user
        $user = $this->getDataGenerator()->create_user();
        
        // Create team
        $team = new \stdClass();
        $team->name = 'Test Team';
        $team->description = 'Test team';
        $team->leaderid = $user->id;
        $team->status = 'active';
        $team->timecreated = time();
        $teamid = $DB->insert_record('local_tubaron_teams', $team);
        
        // Add user to team
        $member = new \stdClass();
        $member->teamid = $teamid;
        $member->userid = $user->id;
        $member->role = 'leader';
        $member->joined = time();
        $DB->insert_record('local_tubaron_team_members', $member);
        
        // Check achievement
        $manager = new achievements_manager();
        $unlocked = $manager->check_and_unlock_achievements($user->id, 'joined_team');
        
        $this->assertTrue($unlocked);
        
        // Verify achievement
        $achievement = $DB->get_record('local_tubaron_achievements', [
            'userid' => $user->id,
            'achievementcode' => 'team_player'
        ]);
        
        $this->assertNotEmpty($achievement);
    }

    /**
     * Test achievement progress tracking
     */
    public function test_achievement_progress_tracking() {
        global $DB;
        
        $this->resetAfterTest(true);
        
        // Create test user
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        
        // Create season
        $season = new \stdClass();
        $season->name = 'Test Season';
        $season->startdate = time();
        $season->enddate = time() + (30 * 24 * 3600);
        $season->status = 'active';
        $seasonid = $DB->insert_record('local_tubaron_seasons', $season);
        
        // Complete 5 tasks (halfway to Active Member - 10 tasks)
        for ($i = 1; $i <= 5; $i++) {
            $task = new \stdClass();
            $task->title = "Task $i";
            $task->description = "Test task $i";
            $task->seasonid = $seasonid;
            $task->creatorid = $user->id;
            $task->tasktype = 'individual';
            $task->points = 50;
            $task->status = 'completed';
            $task->timecreated = time();
            $DB->insert_record('local_tubaron_tasks', $task);
        }
        
        // Get progress
        $manager = new achievements_manager();
        $progress = $manager->get_achievement_progress($user->id, 'active_member');
        
        $this->assertEquals(50, $progress); // 5/10 = 50%
        
        // Complete 5 more tasks
        for ($i = 6; $i <= 10; $i++) {
            $task = new \stdClass();
            $task->title = "Task $i";
            $task->description = "Test task $i";
            $task->seasonid = $seasonid;
            $task->creatorid = $user->id;
            $task->tasktype = 'individual';
            $task->points = 50;
            $task->status = 'completed';
            $task->timecreated = time();
            $DB->insert_record('local_tubaron_tasks', $task);
        }
        
        // Check if achievement unlocked
        $unlocked = $manager->check_and_unlock_achievements($user->id, 'task_completed');
        $this->assertTrue($unlocked);
        
        // Verify progress is now 100%
        $progress = $manager->get_achievement_progress($user->id, 'active_member');
        $this->assertEquals(100, $progress);
    }

    /**
     * Test achievement unlock - Perfect Score
     */
    public function test_unlock_perfect_score_achievement() {
        global $DB;
        
        $this->resetAfterTest(true);
        
        // Create test users
        $creator = $this->getDataGenerator()->create_user();
        $voter = $this->getDataGenerator()->create_user();
        
        // Create season
        $season = new \stdClass();
        $season->name = 'Test Season';
        $season->startdate = time();
        $season->enddate = time() + (30 * 24 * 3600);
        $season->status = 'active';
        $seasonid = $DB->insert_record('local_tubaron_seasons', $season);
        
        // Create task
        $task = new \stdClass();
        $task->title = 'Perfect Task';
        $task->description = 'Test task';
        $task->seasonid = $seasonid;
        $task->creatorid = $creator->id;
        $task->assignedid = $creator->id;
        $task->tasktype = 'individual';
        $task->points = 100;
        $task->status = 'voting';
        $task->votingmethod = 'rating';
        $task->timecreated = time();
        $taskid = $DB->insert_record('local_tubaron_tasks', $task);
        
        // Cast perfect vote (rating 10)
        $vote = new \stdClass();
        $vote->taskid = $taskid;
        $vote->userid = $voter->id;
        $vote->votevalue = 10;
        $vote->votingmethod = 'rating';
        $vote->timecreated = time();
        $DB->insert_record('local_tubaron_votes', $vote);
        
        // Update task as approved
        $task->status = 'approved';
        $task->finalrating = 10.0;
        $DB->update_record('local_tubaron_tasks', $task);
        
        // Check achievement
        $manager = new achievements_manager();
        $unlocked = $manager->check_and_unlock_achievements($creator->id, 'task_approved');
        
        $this->assertTrue($unlocked);
        
        // Verify achievement
        $achievement = $DB->get_record('local_tubaron_achievements', [
            'userid' => $creator->id,
            'achievementcode' => 'perfect_score'
        ]);
        
        $this->assertNotEmpty($achievement);
    }

    /**
     * Test achievement unlock - Streak achievements
     */
    public function test_unlock_streak_achievements() {
        global $DB;
        
        $this->resetAfterTest(true);
        
        // Create test user
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        
        // Create season
        $season = new \stdClass();
        $season->name = 'Test Season';
        $season->startdate = time();
        $season->enddate = time() + (30 * 24 * 3600);
        $season->status = 'active';
        $seasonid = $DB->insert_record('local_tubaron_seasons', $season);
        
        // Create 7 consecutive completed tasks
        $basetime = time() - (7 * 24 * 3600); // Start 7 days ago
        for ($i = 1; $i <= 7; $i++) {
            $task = new \stdClass();
            $task->title = "Streak Task $i";
            $task->description = "Test task $i";
            $task->seasonid = $seasonid;
            $task->creatorid = $user->id;
            $task->assignedid = $user->id;
            $task->tasktype = 'individual';
            $task->points = 50;
            $task->status = 'approved';
            $task->timecreated = $basetime + ($i * 24 * 3600);
            $task->timecompleted = $basetime + ($i * 24 * 3600) + 3600;
            $DB->insert_record('local_tubaron_tasks', $task);
        }
        
        // Check for Streak 7 achievement
        $manager = new achievements_manager();
        $unlocked = $manager->check_and_unlock_achievements($user->id, 'task_completed');
        
        $this->assertTrue($unlocked);
        
        // Verify Streak 3 achievement
        $streak3 = $DB->get_record('local_tubaron_achievements', [
            'userid' => $user->id,
            'achievementcode' => 'streak_3'
        ]);
        $this->assertNotEmpty($streak3);
        
        // Verify Streak 7 achievement
        $streak7 = $DB->get_record('local_tubaron_achievements', [
            'userid' => $user->id,
            'achievementcode' => 'streak_7'
        ]);
        $this->assertNotEmpty($streak7);
    }

    /**
     * Test achievement - Voting Expert
     */
    public function test_unlock_voting_expert_achievement() {
        global $DB;
        
        $this->resetAfterTest(true);
        
        // Create test users
        $voter = $this->getDataGenerator()->create_user();
        $creator = $this->getDataGenerator()->create_user();
        
        // Create season
        $season = new \stdClass();
        $season->name = 'Test Season';
        $season->startdate = time();
        $season->enddate = time() + (30 * 24 * 3600);
        $season->status = 'active';
        $seasonid = $DB->insert_record('local_tubaron_seasons', $season);
        
        // Create 100 tasks and votes
        for ($i = 1; $i <= 100; $i++) {
            // Create task
            $task = new \stdClass();
            $task->title = "Task $i";
            $task->description = "Test task $i";
            $task->seasonid = $seasonid;
            $task->creatorid = $creator->id;
            $task->tasktype = 'individual';
            $task->points = 50;
            $task->status = 'voting';
            $task->votingmethod = 'rating';
            $task->timecreated = time();
            $taskid = $DB->insert_record('local_tubaron_tasks', $task);
            
            // Cast vote
            $vote = new \stdClass();
            $vote->taskid = $taskid;
            $vote->userid = $voter->id;
            $vote->votevalue = rand(7, 10);
            $vote->votingmethod = 'rating';
            $vote->timecreated = time();
            $DB->insert_record('local_tubaron_votes', $vote);
        }
        
        // Check achievement
        $manager = new achievements_manager();
        $unlocked = $manager->check_and_unlock_achievements($voter->id, 'vote_cast');
        
        $this->assertTrue($unlocked);
        
        // Verify achievement
        $achievement = $DB->get_record('local_tubaron_achievements', [
            'userid' => $voter->id,
            'achievementcode' => 'voting_expert'
        ]);
        
        $this->assertNotEmpty($achievement);
    }

    /**
     * Test get user achievements
     */
    public function test_get_user_achievements() {
        global $DB;
        
        $this->resetAfterTest(true);
        
        // Create test user
        $user = $this->getDataGenerator()->create_user();
        
        // Manually unlock some achievements
        $achievements = ['first_steps', 'team_player', 'voter', 'communicator'];
        foreach ($achievements as $code) {
            $achievement = new \stdClass();
            $achievement->userid = $user->id;
            $achievement->achievementcode = $code;
            $achievement->unlocked = 1;
            $achievement->unlockedtime = time();
            $DB->insert_record('local_tubaron_achievements', $achievement);
        }
        
        // Get user achievements
        $manager = new achievements_manager();
        $userachievements = $manager->get_user_achievements($user->id);
        
        $this->assertCount(4, $userachievements);
        
        // Verify all are unlocked
        foreach ($userachievements as $ach) {
            $this->assertEquals(1, $ach->unlocked);
        }
    }

    /**
     * Test achievement notification data
     */
    public function test_get_unlock_notification() {
        $this->resetAfterTest(true);
        
        $manager = new achievements_manager();
        $notification = $manager->get_unlock_notification('first_steps');
        
        $this->assertIsArray($notification);
        $this->assertArrayHasKey('title', $notification);
        $this->assertArrayHasKey('description', $notification);
        $this->assertArrayHasKey('icon', $notification);
        $this->assertArrayHasKey('badge_level', $notification);
    }

    /**
     * Test achievement duplicate prevention
     */
    public function test_prevent_duplicate_achievements() {
        global $DB;
        
        $this->resetAfterTest(true);
        
        // Create test user
        $user = $this->getDataGenerator()->create_user();
        
        // Unlock achievement first time
        $achievement = new \stdClass();
        $achievement->userid = $user->id;
        $achievement->achievementcode = 'first_steps';
        $achievement->unlocked = 1;
        $achievement->unlockedtime = time();
        $DB->insert_record('local_tubaron_achievements', $achievement);
        
        // Try to unlock again
        $manager = new achievements_manager();
        $result = $manager->unlock_achievement($user->id, 'first_steps');
        
        $this->assertFalse($result); // Should return false (already unlocked)
        
        // Verify only one record exists
        $count = $DB->count_records('local_tubaron_achievements', [
            'userid' => $user->id,
            'achievementcode' => 'first_steps'
        ]);
        
        $this->assertEquals(1, $count);
    }

    /**
     * Test achievement legend (100 tasks)
     */
    public function test_unlock_legend_achievement() {
        global $DB;
        
        $this->resetAfterTest(true);
        
        // Create test user
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        
        // Create season
        $season = new \stdClass();
        $season->name = 'Test Season';
        $season->startdate = time();
        $season->enddate = time() + (365 * 24 * 3600);
        $season->status = 'active';
        $seasonid = $DB->insert_record('local_tubaron_seasons', $season);
        
        // Create 100 completed tasks
        for ($i = 1; $i <= 100; $i++) {
            $task = new \stdClass();
            $task->title = "Epic Task $i";
            $task->description = "Test task $i";
            $task->seasonid = $seasonid;
            $task->creatorid = $user->id;
            $task->assignedid = $user->id;
            $task->tasktype = 'individual';
            $task->points = 50;
            $task->status = 'approved';
            $task->timecreated = time() - (100 - $i) * 3600;
            $task->timecompleted = time() - (100 - $i) * 3600 + 1800;
            $DB->insert_record('local_tubaron_tasks', $task);
        }
        
        // Check for Legend achievement
        $manager = new achievements_manager();
        $unlocked = $manager->check_and_unlock_achievements($user->id, 'task_completed');
        
        $this->assertTrue($unlocked);
        
        // Verify Active Member achievement (10 tasks)
        $active = $DB->get_record('local_tubaron_achievements', [
            'userid' => $user->id,
            'achievementcode' => 'active_member'
        ]);
        $this->assertNotEmpty($active);
        
        // Verify Veteran achievement (50 tasks)
        $veteran = $DB->get_record('local_tubaron_achievements', [
            'userid' => $user->id,
            'achievementcode' => 'veteran'
        ]);
        $this->assertNotEmpty($veteran);
        
        // Verify Legend achievement (100 tasks)
        $legend = $DB->get_record('local_tubaron_achievements', [
            'userid' => $user->id,
            'achievementcode' => 'legend'
        ]);
        $this->assertNotEmpty($legend);
    }

    /**
     * Test achievement quality master
     */
    public function test_unlock_quality_master_achievement() {
        global $DB;
        
        $this->resetAfterTest(true);
        
        // Create test users
        $creator = $this->getDataGenerator()->create_user();
        $voter1 = $this->getDataGenerator()->create_user();
        $voter2 = $this->getDataGenerator()->create_user();
        
        // Create season
        $season = new \stdClass();
        $season->name = 'Test Season';
        $season->startdate = time();
        $season->enddate = time() + (365 * 24 * 3600);
        $season->status = 'active';
        $seasonid = $DB->insert_record('local_tubaron_seasons', $season);
        
        // Create 20 tasks with average rating ≥ 9
        for ($i = 1; $i <= 20; $i++) {
            // Create task
            $task = new \stdClass();
            $task->title = "Quality Task $i";
            $task->description = "High quality task $i";
            $task->seasonid = $seasonid;
            $task->creatorid = $creator->id;
            $task->assignedid = $creator->id;
            $task->tasktype = 'individual';
            $task->points = 100;
            $task->status = 'approved';
            $task->votingmethod = 'rating';
            $task->finalrating = 9.0 + (rand(0, 10) / 10); // 9.0 to 10.0
            $task->timecreated = time();
            $task->timecompleted = time() + 3600;
            $DB->insert_record('local_tubaron_tasks', $task);
        }
        
        // Check for Quality Master achievement
        $manager = new achievements_manager();
        $unlocked = $manager->check_and_unlock_achievements($creator->id, 'task_approved');
        
        $this->assertTrue($unlocked);
        
        // Verify achievement
        $achievement = $DB->get_record('local_tubaron_achievements', [
            'userid' => $creator->id,
            'achievementcode' => 'quality_master'
        ]);
        
        $this->assertNotEmpty($achievement);
    }
}

