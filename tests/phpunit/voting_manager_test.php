<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * PHPUnit tests for voting_manager class
 *
 * @package    local_tubaron
 * @category   test
 * @copyright  2025 Tubaron Telecomunicações
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tubaron;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/tubaron/classes/voting_manager.php');

/**
 * Test case for voting_manager
 *
 * @package    local_tubaron
 * @category   test
 */
class voting_manager_test extends \advanced_testcase {

    /**
     * Test cast vote - rating method
     */
    public function test_cast_vote_rating() {
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
        $task->title = 'Test Task';
        $task->description = 'Task for voting';
        $task->seasonid = $seasonid;
        $task->creatorid = $creator->id;
        $task->tasktype = 'individual';
        $task->points = 100;
        $task->status = 'voting';
        $task->votingmethod = 'rating';
        $task->timecreated = time();
        $taskid = $DB->insert_record('local_tubaron_tasks', $task);
        
        // Cast vote
        $manager = new voting_manager();
        $result = $manager->cast_vote($taskid, $voter->id, 8, 'rating');
        
        $this->assertTrue($result);
        
        // Verify vote recorded
        $vote = $DB->get_record('local_tubaron_votes', [
            'taskid' => $taskid,
            'userid' => $voter->id
        ]);
        
        $this->assertNotEmpty($vote);
        $this->assertEquals(8, $vote->votevalue);
        $this->assertEquals('rating', $vote->votingmethod);
    }

    /**
     * Test cast vote - approval method
     */
    public function test_cast_vote_approval() {
        global $DB;
        
        $this->resetAfterTest(true);
        
        // Create test users
        $creator = $this->getDataGenerator()->create_user();
        $voter = $this->getDataGenerator()->create_user();
        
        // Create season and task
        $season = new \stdClass();
        $season->name = 'Test Season';
        $season->startdate = time();
        $season->enddate = time() + (30 * 24 * 3600);
        $season->status = 'active';
        $seasonid = $DB->insert_record('local_tubaron_seasons', $season);
        
        $task = new \stdClass();
        $task->title = 'Approval Task';
        $task->description = 'Task for approval voting';
        $task->seasonid = $seasonid;
        $task->creatorid = $creator->id;
        $task->tasktype = 'individual';
        $task->points = 100;
        $task->status = 'voting';
        $task->votingmethod = 'approval';
        $task->timecreated = time();
        $taskid = $DB->insert_record('local_tubaron_tasks', $task);
        
        // Cast approval vote
        $manager = new voting_manager();
        $result = $manager->cast_vote($taskid, $voter->id, 1, 'approval');
        
        $this->assertTrue($result);
        
        // Verify vote
        $vote = $DB->get_record('local_tubaron_votes', [
            'taskid' => $taskid,
            'userid' => $voter->id
        ]);
        
        $this->assertNotEmpty($vote);
        $this->assertEquals(1, $vote->votevalue); // 1 = approved, 0 = rejected
    }

    /**
     * Test prevent duplicate voting
     */
    public function test_prevent_duplicate_voting() {
        global $DB;
        
        $this->resetAfterTest(true);
        
        // Create test users
        $creator = $this->getDataGenerator()->create_user();
        $voter = $this->getDataGenerator()->create_user();
        
        // Create season and task
        $season = new \stdClass();
        $season->name = 'Test Season';
        $season->startdate = time();
        $season->enddate = time() + (30 * 24 * 3600);
        $season->status = 'active';
        $seasonid = $DB->insert_record('local_tubaron_seasons', $season);
        
        $task = new \stdClass();
        $task->title = 'Test Task';
        $task->description = 'Task for voting';
        $task->seasonid = $seasonid;
        $task->creatorid = $creator->id;
        $task->tasktype = 'individual';
        $task->points = 100;
        $task->status = 'voting';
        $task->votingmethod = 'rating';
        $task->timecreated = time();
        $taskid = $DB->insert_record('local_tubaron_tasks', $task);
        
        // Cast first vote
        $manager = new voting_manager();
        $result1 = $manager->cast_vote($taskid, $voter->id, 8, 'rating');
        $this->assertTrue($result1);
        
        // Try to vote again
        $this->expectException(\moodle_exception::class);
        $manager->cast_vote($taskid, $voter->id, 9, 'rating');
    }

    /**
     * Test calculate average rating
     */
    public function test_calculate_average_rating() {
        global $DB;
        
        $this->resetAfterTest(true);
        
        // Create test users
        $creator = $this->getDataGenerator()->create_user();
        $voters = [];
        for ($i = 0; $i < 5; $i++) {
            $voters[] = $this->getDataGenerator()->create_user();
        }
        
        // Create season and task
        $season = new \stdClass();
        $season->name = 'Test Season';
        $season->startdate = time();
        $season->enddate = time() + (30 * 24 * 3600);
        $season->status = 'active';
        $seasonid = $DB->insert_record('local_tubaron_seasons', $season);
        
        $task = new \stdClass();
        $task->title = 'Test Task';
        $task->description = 'Task for rating';
        $task->seasonid = $seasonid;
        $task->creatorid = $creator->id;
        $task->tasktype = 'individual';
        $task->points = 100;
        $task->status = 'voting';
        $task->votingmethod = 'rating';
        $task->timecreated = time();
        $taskid = $DB->insert_record('local_tubaron_tasks', $task);
        
        // Cast votes: 6, 7, 8, 9, 10 (avg = 8.0)
        $ratings = [6, 7, 8, 9, 10];
        foreach ($voters as $index => $voter) {
            $vote = new \stdClass();
            $vote->taskid = $taskid;
            $vote->userid = $voter->id;
            $vote->votevalue = $ratings[$index];
            $vote->votingmethod = 'rating';
            $vote->timecreated = time();
            $DB->insert_record('local_tubaron_votes', $vote);
        }
        
        // Calculate average
        $manager = new voting_manager();
        $average = $manager->calculate_average_rating($taskid);
        
        $this->assertEquals(8.0, $average);
    }

    /**
     * Test calculate approval rate
     */
    public function test_calculate_approval_rate() {
        global $DB;
        
        $this->resetAfterTest(true);
        
        // Create test users
        $creator = $this->getDataGenerator()->create_user();
        $voters = [];
        for ($i = 0; $i < 10; $i++) {
            $voters[] = $this->getDataGenerator()->create_user();
        }
        
        // Create season and task
        $season = new \stdClass();
        $season->name = 'Test Season';
        $season->startdate = time();
        $season->enddate = time() + (30 * 24 * 3600);
        $season->status = 'active';
        $seasonid = $DB->insert_record('local_tubaron_seasons', $season);
        
        $task = new \stdClass();
        $task->title = 'Approval Task';
        $task->description = 'Task for approval';
        $task->seasonid = $seasonid;
        $task->creatorid = $creator->id;
        $task->tasktype = 'individual';
        $task->points = 100;
        $task->status = 'voting';
        $task->votingmethod = 'approval';
        $task->timecreated = time();
        $taskid = $DB->insert_record('local_tubaron_tasks', $task);
        
        // Cast votes: 7 approved, 3 rejected (70% approval)
        $approvals = [1, 1, 1, 1, 1, 1, 1, 0, 0, 0];
        foreach ($voters as $index => $voter) {
            $vote = new \stdClass();
            $vote->taskid = $taskid;
            $vote->userid = $voter->id;
            $vote->votevalue = $approvals[$index];
            $vote->votingmethod = 'approval';
            $vote->timecreated = time();
            $DB->insert_record('local_tubaron_votes', $vote);
        }
        
        // Calculate approval rate
        $manager = new voting_manager();
        $rate = $manager->calculate_approval_rate($taskid);
        
        $this->assertEquals(70.0, $rate);
    }

    /**
     * Test close voting period
     */
    public function test_close_voting_period() {
        global $DB;
        
        $this->resetAfterTest(true);
        
        // Create test users
        $creator = $this->getDataGenerator()->create_user();
        $voters = [];
        for ($i = 0; $i < 3; $i++) {
            $voters[] = $this->getDataGenerator()->create_user();
        }
        
        // Create season and task
        $season = new \stdClass();
        $season->name = 'Test Season';
        $season->startdate = time();
        $season->enddate = time() + (30 * 24 * 3600);
        $season->status = 'active';
        $seasonid = $DB->insert_record('local_tubaron_seasons', $season);
        
        $task = new \stdClass();
        $task->title = 'Test Task';
        $task->description = 'Task for voting';
        $task->seasonid = $seasonid;
        $task->creatorid = $creator->id;
        $task->assignedid = $creator->id;
        $task->tasktype = 'individual';
        $task->points = 100;
        $task->status = 'voting';
        $task->votingmethod = 'rating';
        $task->timecreated = time();
        $taskid = $DB->insert_record('local_tubaron_tasks', $task);
        
        // Cast votes
        foreach ($voters as $voter) {
            $vote = new \stdClass();
            $vote->taskid = $taskid;
            $vote->userid = $voter->id;
            $vote->votevalue = 8;
            $vote->votingmethod = 'rating';
            $vote->timecreated = time();
            $DB->insert_record('local_tubaron_votes', $vote);
        }
        
        // Close voting
        $manager = new voting_manager();
        $result = $manager->close_voting($taskid);
        
        $this->assertTrue($result);
        
        // Verify task status updated
        $task = $DB->get_record('local_tubaron_tasks', ['id' => $taskid]);
        $this->assertEquals('approved', $task->status);
        $this->assertEquals(8.0, $task->finalrating);
        $this->assertNotEmpty($task->votingclosed);
    }

    /**
     * Test voting threshold - minimum voters
     */
    public function test_voting_threshold_minimum() {
        global $DB;
        
        $this->resetAfterTest(true);
        
        // Create test users
        $creator = $this->getDataGenerator()->create_user();
        $voter = $this->getDataGenerator()->create_user();
        
        // Create season and task
        $season = new \stdClass();
        $season->name = 'Test Season';
        $season->startdate = time();
        $season->enddate = time() + (30 * 24 * 3600);
        $season->status = 'active';
        $season->minvoters = 3; // Require at least 3 voters
        $seasonid = $DB->insert_record('local_tubaron_seasons', $season);
        
        $task = new \stdClass();
        $task->title = 'Test Task';
        $task->description = 'Task for voting';
        $task->seasonid = $seasonid;
        $task->creatorid = $creator->id;
        $task->tasktype = 'individual';
        $task->points = 100;
        $task->status = 'voting';
        $task->votingmethod = 'rating';
        $task->timecreated = time();
        $taskid = $DB->insert_record('local_tubaron_tasks', $task);
        
        // Cast only 1 vote (below threshold)
        $vote = new \stdClass();
        $vote->taskid = $taskid;
        $vote->userid = $voter->id;
        $vote->votevalue = 8;
        $vote->votingmethod = 'rating';
        $vote->timecreated = time();
        $DB->insert_record('local_tubaron_votes', $vote);
        
        // Try to close voting
        $manager = new voting_manager();
        $this->expectException(\moodle_exception::class);
        $manager->close_voting($taskid);
    }

    /**
     * Test get voting results
     */
    public function test_get_voting_results() {
        global $DB;
        
        $this->resetAfterTest(true);
        
        // Create test users
        $creator = $this->getDataGenerator()->create_user();
        $voters = [];
        for ($i = 0; $i < 5; $i++) {
            $voters[] = $this->getDataGenerator()->create_user();
        }
        
        // Create season and task
        $season = new \stdClass();
        $season->name = 'Test Season';
        $season->startdate = time();
        $season->enddate = time() + (30 * 24 * 3600);
        $season->status = 'active';
        $seasonid = $DB->insert_record('local_tubaron_seasons', $season);
        
        $task = new \stdClass();
        $task->title = 'Test Task';
        $task->description = 'Task for voting';
        $task->seasonid = $seasonid;
        $task->creatorid = $creator->id;
        $task->tasktype = 'individual';
        $task->points = 100;
        $task->status = 'voting';
        $task->votingmethod = 'rating';
        $task->timecreated = time();
        $taskid = $DB->insert_record('local_tubaron_tasks', $task);
        
        // Cast votes
        $ratings = [6, 7, 8, 9, 10];
        foreach ($voters as $index => $voter) {
            $vote = new \stdClass();
            $vote->taskid = $taskid;
            $vote->userid = $voter->id;
            $vote->votevalue = $ratings[$index];
            $vote->votingmethod = 'rating';
            $vote->timecreated = time();
            $DB->insert_record('local_tubaron_votes', $vote);
        }
        
        // Get results
        $manager = new voting_manager();
        $results = $manager->get_voting_results($taskid);
        
        $this->assertIsArray($results);
        $this->assertEquals(5, $results['total_votes']);
        $this->assertEquals(8.0, $results['average_rating']);
        $this->assertEquals(6, $results['min_rating']);
        $this->assertEquals(10, $results['max_rating']);
    }

    /**
     * Test voting eligibility - creator cannot vote
     */
    public function test_creator_cannot_vote() {
        global $DB;
        
        $this->resetAfterTest(true);
        
        // Create test user
        $creator = $this->getDataGenerator()->create_user();
        
        // Create season and task
        $season = new \stdClass();
        $season->name = 'Test Season';
        $season->startdate = time();
        $season->enddate = time() + (30 * 24 * 3600);
        $season->status = 'active';
        $seasonid = $DB->insert_record('local_tubaron_seasons', $season);
        
        $task = new \stdClass();
        $task->title = 'Test Task';
        $task->description = 'Task for voting';
        $task->seasonid = $seasonid;
        $task->creatorid = $creator->id;
        $task->tasktype = 'individual';
        $task->points = 100;
        $task->status = 'voting';
        $task->votingmethod = 'rating';
        $task->timecreated = time();
        $taskid = $DB->insert_record('local_tubaron_tasks', $task);
        
        // Try to vote on own task
        $manager = new voting_manager();
        $this->expectException(\moodle_exception::class);
        $manager->cast_vote($taskid, $creator->id, 8, 'rating');
    }

    /**
     * Test voting status check
     */
    public function test_voting_status_check() {
        global $DB;
        
        $this->resetAfterTest(true);
        
        // Create test users
        $creator = $this->getDataGenerator()->create_user();
        $voter = $this->getDataGenerator()->create_user();
        
        // Create season and task
        $season = new \stdClass();
        $season->name = 'Test Season';
        $season->startdate = time();
        $season->enddate = time() + (30 * 24 * 3600);
        $season->status = 'active';
        $seasonid = $DB->insert_record('local_tubaron_seasons', $season);
        
        $task = new \stdClass();
        $task->title = 'Test Task';
        $task->description = 'Task for voting';
        $task->seasonid = $seasonid;
        $task->creatorid = $creator->id;
        $task->tasktype = 'individual';
        $task->points = 100;
        $task->status = 'completed'; // Not in voting status
        $task->votingmethod = 'rating';
        $task->timecreated = time();
        $taskid = $DB->insert_record('local_tubaron_tasks', $task);
        
        // Try to vote on non-voting task
        $manager = new voting_manager();
        $this->expectException(\moodle_exception::class);
        $manager->cast_vote($taskid, $voter->id, 8, 'rating');
    }

    /**
     * Test get user votes
     */
    public function test_get_user_votes() {
        global $DB;
        
        $this->resetAfterTest(true);
        
        // Create test users
        $creator1 = $this->getDataGenerator()->create_user();
        $creator2 = $this->getDataGenerator()->create_user();
        $creator3 = $this->getDataGenerator()->create_user();
        $voter = $this->getDataGenerator()->create_user();
        
        // Create season
        $season = new \stdClass();
        $season->name = 'Test Season';
        $season->startdate = time();
        $season->enddate = time() + (30 * 24 * 3600);
        $season->status = 'active';
        $seasonid = $DB->insert_record('local_tubaron_seasons', $season);
        
        // Create 3 tasks
        $tasks = [];
        foreach ([$creator1, $creator2, $creator3] as $index => $creator) {
            $task = new \stdClass();
            $task->title = "Task " . ($index + 1);
            $task->description = 'Task for voting';
            $task->seasonid = $seasonid;
            $task->creatorid = $creator->id;
            $task->tasktype = 'individual';
            $task->points = 100;
            $task->status = 'voting';
            $task->votingmethod = 'rating';
            $task->timecreated = time();
            $taskid = $DB->insert_record('local_tubaron_tasks', $task);
            $tasks[] = $taskid;
            
            // Cast vote
            $vote = new \stdClass();
            $vote->taskid = $taskid;
            $vote->userid = $voter->id;
            $vote->votevalue = 7 + $index; // 7, 8, 9
            $vote->votingmethod = 'rating';
            $vote->timecreated = time();
            $DB->insert_record('local_tubaron_votes', $vote);
        }
        
        // Get user votes
        $manager = new voting_manager();
        $uservotes = $manager->get_user_votes($voter->id);
        
        $this->assertCount(3, $uservotes);
    }

    /**
     * Test voting weight (future feature)
     */
    public function test_weighted_voting() {
        global $DB;
        
        $this->resetAfterTest(true);
        
        // Create test users
        $creator = $this->getDataGenerator()->create_user();
        $expertvoter = $this->getDataGenerator()->create_user();
        $regularvoter = $this->getDataGenerator()->create_user();
        
        // Create season and task
        $season = new \stdClass();
        $season->name = 'Test Season';
        $season->startdate = time();
        $season->enddate = time() + (30 * 24 * 3600);
        $season->status = 'active';
        $seasonid = $DB->insert_record('local_tubaron_seasons', $season);
        
        $task = new \stdClass();
        $task->title = 'Test Task';
        $task->description = 'Task for weighted voting';
        $task->seasonid = $seasonid;
        $task->creatorid = $creator->id;
        $task->tasktype = 'individual';
        $task->points = 100;
        $task->status = 'voting';
        $task->votingmethod = 'rating';
        $task->timecreated = time();
        $taskid = $DB->insert_record('local_tubaron_tasks', $task);
        
        // Cast votes with different weights
        // Expert vote (weight 2.0)
        $vote1 = new \stdClass();
        $vote1->taskid = $taskid;
        $vote1->userid = $expertvoter->id;
        $vote1->votevalue = 10;
        $vote1->votingmethod = 'rating';
        $vote1->voteweight = 2.0;
        $vote1->timecreated = time();
        $DB->insert_record('local_tubaron_votes', $vote1);
        
        // Regular vote (weight 1.0)
        $vote2 = new \stdClass();
        $vote2->taskid = $taskid;
        $vote2->userid = $regularvoter->id;
        $vote2->votevalue = 6;
        $vote2->votingmethod = 'rating';
        $vote2->voteweight = 1.0;
        $vote2->timecreated = time();
        $DB->insert_record('local_tubaron_votes', $vote2);
        
        // Calculate weighted average: (10*2.0 + 6*1.0) / (2.0 + 1.0) = 26/3 = 8.67
        $manager = new voting_manager();
        $average = $manager->calculate_weighted_average($taskid);
        
        $this->assertEquals(8.67, round($average, 2));
    }

    /**
     * Test anonymous voting option
     */
    public function test_anonymous_voting() {
        global $DB;
        
        $this->resetAfterTest(true);
        
        // Create test users
        $creator = $this->getDataGenerator()->create_user();
        $voter = $this->getDataGenerator()->create_user();
        
        // Create season and task
        $season = new \stdClass();
        $season->name = 'Test Season';
        $season->startdate = time();
        $season->enddate = time() + (30 * 24 * 3600);
        $season->status = 'active';
        $seasonid = $DB->insert_record('local_tubaron_seasons', $season);
        
        $task = new \stdClass();
        $task->title = 'Anonymous Task';
        $task->description = 'Task with anonymous voting';
        $task->seasonid = $seasonid;
        $task->creatorid = $creator->id;
        $task->tasktype = 'individual';
        $task->points = 100;
        $task->status = 'voting';
        $task->votingmethod = 'rating';
        $task->anonymousvoting = 1;
        $task->timecreated = time();
        $taskid = $DB->insert_record('local_tubaron_tasks', $task);
        
        // Cast anonymous vote
        $manager = new voting_manager();
        $result = $manager->cast_vote($taskid, $voter->id, 8, 'rating', true);
        
        $this->assertTrue($result);
        
        // Verify vote is anonymous
        $vote = $DB->get_record('local_tubaron_votes', [
            'taskid' => $taskid,
            'userid' => $voter->id
        ]);
        
        $this->assertNotEmpty($vote);
        $this->assertEquals(1, $vote->anonymous);
    }

    /**
     * Test voting deadline enforcement
     */
    public function test_voting_deadline() {
        global $DB;
        
        $this->resetAfterTest(true);
        
        // Create test users
        $creator = $this->getDataGenerator()->create_user();
        $voter = $this->getDataGenerator()->create_user();
        
        // Create season and task
        $season = new \stdClass();
        $season->name = 'Test Season';
        $season->startdate = time();
        $season->enddate = time() + (30 * 24 * 3600);
        $season->status = 'active';
        $seasonid = $DB->insert_record('local_tubaron_seasons', $season);
        
        $task = new \stdClass();
        $task->title = 'Deadline Task';
        $task->description = 'Task with voting deadline';
        $task->seasonid = $seasonid;
        $task->creatorid = $creator->id;
        $task->tasktype = 'individual';
        $task->points = 100;
        $task->status = 'voting';
        $task->votingmethod = 'rating';
        $task->votingdeadline = time() - 3600; // Deadline passed 1 hour ago
        $task->timecreated = time();
        $taskid = $DB->insert_record('local_tubaron_tasks', $task);
        
        // Try to vote after deadline
        $manager = new voting_manager();
        $this->expectException(\moodle_exception::class);
        $manager->cast_vote($taskid, $voter->id, 8, 'rating');
    }

    /**
     * Test voting participation rate
     */
    public function test_voting_participation_rate() {
        global $DB;
        
        $this->resetAfterTest(true);
        
        // Create test users
        $creator = $this->getDataGenerator()->create_user();
        $voters = [];
        for ($i = 0; $i < 10; $i++) {
            $voters[] = $this->getDataGenerator()->create_user();
        }
        
        // Create season
        $season = new \stdClass();
        $season->name = 'Test Season';
        $season->startdate = time();
        $season->enddate = time() + (30 * 24 * 3600);
        $season->status = 'active';
        $seasonid = $DB->insert_record('local_tubaron_seasons', $season);
        
        // Create task
        $task = new \stdClass();
        $task->title = 'Participation Task';
        $task->description = 'Task for participation tracking';
        $task->seasonid = $seasonid;
        $task->creatorid = $creator->id;
        $task->tasktype = 'individual';
        $task->points = 100;
        $task->status = 'voting';
        $task->votingmethod = 'rating';
        $task->timecreated = time();
        $taskid = $DB->insert_record('local_tubaron_tasks', $task);
        
        // Only 6 out of 10 vote (60% participation)
        for ($i = 0; $i < 6; $i++) {
            $vote = new \stdClass();
            $vote->taskid = $taskid;
            $vote->userid = $voters[$i]->id;
            $vote->votevalue = 8;
            $vote->votingmethod = 'rating';
            $vote->timecreated = time();
            $DB->insert_record('local_tubaron_votes', $vote);
        }
        
        // Calculate participation rate
        $manager = new voting_manager();
        $rate = $manager->calculate_participation_rate($taskid, 10);
        
        $this->assertEquals(60.0, $rate);
    }

    /**
     * Test bulk vote processing
     */
    public function test_bulk_vote_processing() {
        global $DB;
        
        $this->resetAfterTest(true);
        
        // Create test users
        $creator = $this->getDataGenerator()->create_user();
        $voters = [];
        for ($i = 0; $i < 50; $i++) {
            $voters[] = $this->getDataGenerator()->create_user();
        }
        
        // Create season and task
        $season = new \stdClass();
        $season->name = 'Test Season';
        $season->startdate = time();
        $season->enddate = time() + (30 * 24 * 3600);
        $season->status = 'active';
        $seasonid = $DB->insert_record('local_tubaron_seasons', $season);
        
        $task = new \stdClass();
        $task->title = 'Bulk Task';
        $task->description = 'Task for bulk voting';
        $task->seasonid = $seasonid;
        $task->creatorid = $creator->id;
        $task->tasktype = 'individual';
        $task->points = 100;
        $task->status = 'voting';
        $task->votingmethod = 'rating';
        $task->timecreated = time();
        $taskid = $DB->insert_record('local_tubaron_tasks', $task);
        
        // Prepare bulk votes
        $votes = [];
        foreach ($voters as $voter) {
            $votes[] = [
                'taskid' => $taskid,
                'userid' => $voter->id,
                'votevalue' => rand(6, 10),
                'votingmethod' => 'rating'
            ];
        }
        
        // Process bulk votes
        $manager = new voting_manager();
        $result = $manager->process_bulk_votes($votes);
        
        $this->assertTrue($result);
        
        // Verify all votes recorded
        $count = $DB->count_records('local_tubaron_votes', ['taskid' => $taskid]);
        $this->assertEquals(50, $count);
    }

    /**
     * Test voting statistics
     */
    public function test_voting_statistics() {
        global $DB;
        
        $this->resetAfterTest(true);
        
        // Create test users
        $creator = $this->getDataGenerator()->create_user();
        $voters = [];
        for ($i = 0; $i < 10; $i++) {
            $voters[] = $this->getDataGenerator()->create_user();
        }
        
        // Create season and task
        $season = new \stdClass();
        $season->name = 'Test Season';
        $season->startdate = time();
        $season->enddate = time() + (30 * 24 * 3600);
        $season->status = 'active';
        $seasonid = $DB->insert_record('local_tubaron_seasons', $season);
        
        $task = new \stdClass();
        $task->title = 'Stats Task';
        $task->description = 'Task for statistics';
        $task->seasonid = $seasonid;
        $task->creatorid = $creator->id;
        $task->tasktype = 'individual';
        $task->points = 100;
        $task->status = 'voting';
        $task->votingmethod = 'rating';
        $task->timecreated = time();
        $taskid = $DB->insert_record('local_tubaron_tasks', $task);
        
        // Cast votes: 5, 6, 7, 7, 8, 8, 8, 9, 9, 10
        $ratings = [5, 6, 7, 7, 8, 8, 8, 9, 9, 10];
        foreach ($voters as $index => $voter) {
            $vote = new \stdClass();
            $vote->taskid = $taskid;
            $vote->userid = $voter->id;
            $vote->votevalue = $ratings[$index];
            $vote->votingmethod = 'rating';
            $vote->timecreated = time();
            $DB->insert_record('local_tubaron_votes', $vote);
        }
        
        // Get statistics
        $manager = new voting_manager();
        $stats = $manager->get_voting_statistics($taskid);
        
        $this->assertIsArray($stats);
        $this->assertEquals(10, $stats['count']);
        $this->assertEquals(7.7, $stats['average']);
        $this->assertEquals(8, $stats['median']); // Mode of dataset
        $this->assertEquals(5, $stats['min']);
        $this->assertEquals(10, $stats['max']);
        $this->assertGreaterThan(0, $stats['std_deviation']);
    }
}

