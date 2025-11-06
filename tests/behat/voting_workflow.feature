# features/voting_workflow.feature
@local @local_tubaron @javascript
Feature: Voting workflow in Tubaron
  In order to validate completed tasks
  As a team member
  I need to be able to vote on tasks

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | creator1 | Douglas   | Leonardo | creator@example.com  |
      | voter1   | Maria     | Silva    | voter1@example.com   |
      | voter2   | João      | Santos   | voter2@example.com   |
      | voter3   | Ana       | Costa    | voter3@example.com   |
    And the following "tubaron seasons" exist:
      | name         | startdate  | enddate    | status |
      | Season 2025  | ##today##  | ##+30days##| active |
    And the following "tubaron teams" exist:
      | name           | leaderid | status |
      | Alpha Team     | creator1 | active |
    And the following "team members" exist:
      | teamname   | username | role   |
      | Alpha Team | creator1 | leader |
      | Alpha Team | voter1   | member |
      | Alpha Team | voter2   | member |
      | Alpha Team | voter3   | member |

  @rating_voting
  Scenario: Vote on task using rating method
    Given I am logged in as "creator1"
    And I navigate to "Tubaron > Create Task" in site administration
    When I set the following fields to these values:
      | Title          | Implement Feature X    |
      | Description    | Complete implementation|
      | Task Type      | individual             |
      | Points         | 100                    |
      | Voting Method  | rating                 |
    And I press "Create Task"
    And I press "Submit for Voting"
    Then I should see "Task submitted for voting"
    
    # First voter casts vote
    And I log out
    And I am logged in as "voter1"
    And I navigate to "Tubaron > My Tasks"
    And I click on "Implement Feature X" "link"
    And I set the field "Rating" to "8"
    And I press "Cast Vote"
    Then I should see "Vote recorded successfully"
    
    # Second voter casts vote
    And I log out
    And I am logged in as "voter2"
    And I navigate to "Tubaron > My Tasks"
    And I click on "Implement Feature X" "link"
    And I set the field "Rating" to "9"
    And I press "Cast Vote"
    Then I should see "Vote recorded successfully"
    
    # Third voter casts vote
    And I log out
    And I am logged in as "voter3"
    And I navigate to "Tubaron > My Tasks"
    And I click on "Implement Feature X" "link"
    And I set the field "Rating" to "10"
    And I press "Cast Vote"
    Then I should see "Vote recorded successfully"
    
    # Close voting and verify result
    And I log out
    And I am logged in as "creator1"
    And I navigate to "Tubaron > Admin Dashboard"
    And I click on "Close Voting" "button" in the "Implement Feature X" "table_row"
    Then I should see "Final Rating: 9.0"
    And I should see "Status: Approved"

  @approval_voting
  Scenario: Vote on task using approval method
    Given I am logged in as "creator1"
    And I navigate to "Tubaron > Create Task" in site administration
    When I set the following fields to these values:
      | Title          | Deploy Application     |
      | Description    | Production deployment  |
      | Task Type      | individual             |
      | Points         | 150                    |
      | Voting Method  | approval               |
    And I press "Create Task"
    And I press "Submit for Voting"
    
    # Voters cast approval votes
    And I log out
    And I am logged in as "voter1"
    And I navigate to "Tubaron > My Tasks"
    And I click on "Deploy Application" "link"
    And I click on "Approve" "button"
    Then I should see "Vote recorded successfully"
    
    And I log out
    And I am logged in as "voter2"
    And I navigate to "Tubaron > My Tasks"
    And I click on "Deploy Application" "link"
    And I click on "Approve" "button"
    
    And I log out
    And I am logged in as "voter3"
    And I navigate to "Tubaron > My Tasks"
    And I click on "Deploy Application" "link"
    And I click on "Reject" "button"
    
    # Close voting and verify 67% approval
    And I log out
    And I am logged in as "creator1"
    And I navigate to "Tubaron > Admin Dashboard"
    And I click on "Close Voting" "button" in the "Deploy Application" "table_row"
    Then I should see "Approval Rate: 67%"

  @duplicate_voting_prevention
  Scenario: Prevent duplicate voting
    Given I am logged in as "creator1"
    And I navigate to "Tubaron > Create Task" in site administration
    And I set the following fields to these values:
      | Title          | Code Review            |
      | Task Type      | individual             |
      | Points         | 50                     |
      | Voting Method  | rating                 |
    And I press "Create Task"
    And I press "Submit for Voting"
    
    And I log out
    And I am logged in as "voter1"
    And I navigate to "Tubaron > My Tasks"
    And I click on "Code Review" "link"
    And I set the field "Rating" to "8"
    And I press "Cast Vote"
    Then I should see "Vote recorded successfully"
    
    # Try to vote again
    When I reload the page
    Then I should not see "Cast Vote"
    And I should see "You have already voted"
    And I should see "Your vote: 8"

  @creator_cannot_vote
  Scenario: Task creator cannot vote on their own task
    Given I am logged in as "creator1"
    And I navigate to "Tubaron > Create Task" in site administration
    And I set the following fields to these values:
      | Title          | My Own Task            |
      | Task Type      | individual             |
      | Points         | 100                    |
      | Voting Method  | rating                 |
    And I press "Create Task"
    And I press "Submit for Voting"
    
    When I navigate to "Tubaron > My Tasks"
    And I click on "My Own Task" "link"
    Then I should not see "Cast Vote"
    And I should see "You cannot vote on your own task"

  @voting_deadline
  Scenario: Voting deadline enforcement
    Given I am logged in as "creator1"
    And I navigate to "Tubaron > Create Task" in site administration
    And I set the following fields to these values:
      | Title            | Urgent Task            |
      | Task Type        | individual             |
      | Points           | 80                     |
      | Voting Method    | rating                 |
      | Voting Deadline  | ##tomorrow##           |
    And I press "Create Task"
    And I press "Submit for Voting"
    
    # Fast-forward time past deadline (test helper)
    And I set system time to "##tomorrow + 1 hour##"
    
    And I log out
    And I am logged in as "voter1"
    And I navigate to "Tubaron > My Tasks"
    And I click on "Urgent Task" "link"
    Then I should not see "Cast Vote"
    And I should see "Voting deadline has passed"

  @voting_results_display
  Scenario: Display voting results after voting closes
    Given I am logged in as "creator1"
    And I navigate to "Tubaron > Create Task" in site administration
    And I set the following fields to these values:
      | Title          | Feature Complete       |
      | Task Type      | individual             |
      | Points         | 120                    |
      | Voting Method  | rating                 |
    And I press "Create Task"
    And I press "Submit for Voting"
    
    # Cast 3 votes
    And I log out
    And I am logged in as "voter1"
    And I navigate to "Tubaron > My Tasks"
    And I click on "Feature Complete" "link"
    And I set the field "Rating" to "7"
    And I press "Cast Vote"
    
    And I log out
    And I am logged in as "voter2"
    And I navigate to "Tubaron > My Tasks"
    And I click on "Feature Complete" "link"
    And I set the field "Rating" to "8"
    And I press "Cast Vote"
    
    And I log out
    And I am logged in as "voter3"
    And I navigate to "Tubaron > My Tasks"
    And I click on "Feature Complete" "link"
    And I set the field "Rating" to "9"
    And I press "Cast Vote"
    
    # Close voting
    And I log out
    And I am logged in as "creator1"
    And I navigate to "Tubaron > Admin Dashboard"
    And I click on "Close Voting" "button" in the "Feature Complete" "table_row"
    
    # Verify results display
    When I click on "View Results" "link" in the "Feature Complete" "table_row"
    Then I should see "Voting Results"
    And I should see "Total Votes: 3"
    And I should see "Average Rating: 8.0"
    And I should see "Min: 7 | Max: 9"
    And I should see the following votes:
      | Voter        | Rating |
      | Maria Silva  | 7      |
      | João Santos  | 8      |
      | Ana Costa    | 9      |

  @minimum_voters_threshold
  Scenario: Require minimum number of voters before closing
    Given I am logged in as "creator1"
    And the season "Season 2025" has minimum voters set to "3"
    And I navigate to "Tubaron > Create Task" in site administration
    And I set the following fields to these values:
      | Title          | Important Task         |
      | Task Type      | individual             |
      | Points         | 100                    |
      | Voting Method  | rating                 |
    And I press "Create Task"
    And I press "Submit for Voting"
    
    # Only 1 vote cast
    And I log out
    And I am logged in as "voter1"
    And I navigate to "Tubaron > My Tasks"
    And I click on "Important Task" "link"
    And I set the field "Rating" to "8"
    And I press "Cast Vote"
    
    # Try to close with insufficient votes
    And I log out
    And I am logged in as "creator1"
    And I navigate to "Tubaron > Admin Dashboard"
    When I click on "Close Voting" "button" in the "Important Task" "table_row"
    Then I should see "Error: Minimum 3 voters required"
    And I should see "Current votes: 1"

  @voting_notification
  Scenario: Receive notification when voting opens
    Given I am logged in as "creator1"
    And I navigate to "Tubaron > Create Task" in site administration
    And I set the following fields to these values:
      | Title          | Review Code            |
      | Task Type      | individual             |
      | Points         | 60                     |
      | Voting Method  | rating                 |
    And I press "Create Task"
    And I press "Submit for Voting"
    
    # Voters should receive notification
    And I log out
    And I am logged in as "voter1"
    When I click on the notifications icon
    Then I should see "New voting opened"
    And I should see "Review Code"
    And I should see "Your opinion is important. Vote now!"

  @anonymous_voting
  Scenario: Anonymous voting hides voter identity
    Given I am logged in as "creator1"
    And I navigate to "Tubaron > Create Task" in site administration
    And I set the following fields to these values:
      | Title           | Anonymous Task         |
      | Task Type       | individual             |
      | Points          | 100                    |
      | Voting Method   | rating                 |
      | Anonymous Voting| Yes                    |
    And I press "Create Task"
    And I press "Submit for Voting"
    
    # Cast anonymous votes
    And I log out
    And I am logged in as "voter1"
    And I navigate to "Tubaron > My Tasks"
    And I click on "Anonymous Task" "link"
    And I set the field "Rating" to "8"
    And I press "Cast Vote"
    
    And I log out
    And I am logged in as "voter2"
    And I navigate to "Tubaron > My Tasks"
    And I click on "Anonymous Task" "link"
    And I set the field "Rating" to "9"
    And I press "Cast Vote"
    
    # Close voting and verify anonymity
    And I log out
    And I am logged in as "creator1"
    And I navigate to "Tubaron > Admin Dashboard"
    And I click on "Close Voting" "button" in the "Anonymous Task" "table_row"
    And I click on "View Results" "link"
    Then I should see "Anonymous voting enabled"
    And I should not see "Maria Silva"
    And I should not see "João Santos"
    And I should see "Anonymous Voter 1"
    And I should see "Anonymous Voter 2"

  @weighted_voting
  Scenario: Expert votes have higher weight
    Given I am logged in as "creator1"
    And the user "voter1" has expert status with weight "2.0"
    And I navigate to "Tubaron > Create Task" in site administration
    And I set the following fields to these values:
      | Title          | Technical Review       |
      | Task Type      | individual             |
      | Points         | 150                    |
      | Voting Method  | rating                 |
      | Use Weights    | Yes                    |
    And I press "Create Task"
    And I press "Submit for Voting"
    
    # Expert vote (weight 2.0)
    And I log out
    And I am logged in as "voter1"
    And I navigate to "Tubaron > My Tasks"
    And I click on "Technical Review" "link"
    And I set the field "Rating" to "10"
    And I press "Cast Vote"
    Then I should see "Vote recorded (Expert weight: 2.0)"
    
    # Regular vote (weight 1.0)
    And I log out
    And I am logged in as "voter2"
    And I navigate to "Tubaron > My Tasks"
    And I click on "Technical Review" "link"
    And I set the field "Rating" to "6"
    And I press "Cast Vote"
    
    # Close and verify weighted average: (10*2.0 + 6*1.0)/(2.0+1.0) = 8.67
    And I log out
    And I am logged in as "creator1"
    And I navigate to "Tubaron > Admin Dashboard"
    And I click on "Close Voting" "button" in the "Technical Review" "table_row"
    Then I should see "Weighted Average: 8.67"

  @voting_statistics
  Scenario: View detailed voting statistics
    Given I am logged in as "creator1"
    And I navigate to "Tubaron > Create Task" in site administration
    And I set the following fields to these values:
      | Title          | Stats Task             |
      | Task Type      | individual             |
      | Points         | 100                    |
      | Voting Method  | rating                 |
    And I press "Create Task"
    And I press "Submit for Voting"
    
    # Cast multiple votes
    And the following votes are cast:
      | voter  | rating |
      | voter1 | 7      |
      | voter2 | 8      |
      | voter3 | 9      |
    
    # View statistics
    And I navigate to "Tubaron > Admin Dashboard"
    And I click on "Stats Task" "link"
    And I click on "Statistics" "tab"
    Then I should see the following statistics:
      | Metric         | Value |
      | Total Votes    | 3     |
      | Average        | 8.0   |
      | Median         | 8     |
      | Min            | 7     |
      | Max            | 9     |
      | Std Deviation  | 0.82  |

  @bulk_voting_close
  Scenario: Close multiple voting tasks at once
    Given I am logged in as "creator1"
    And the following tasks are in voting status:
      | title    | votes_count |
      | Task A   | 5           |
      | Task B   | 4           |
      | Task C   | 3           |
    And I am logged in as "creator1"
    And I navigate to "Tubaron > Admin Dashboard"
    When I select all tasks in voting
    And I click on "Bulk Close Voting" "button"
    Then I should see "3 tasks voting closed successfully"
    And all tasks should have status "approved"

  @voting_export
  Scenario: Export voting results to CSV
    Given I am logged in as "creator1"
    And a task "Export Task" has completed voting with the following results:
      | voter        | rating |
      | voter1       | 8      |
      | voter2       | 9      |
      | voter3       | 7      |
    And I navigate to "Tubaron > Admin Dashboard"
    And I click on "Export Task" "link"
    And I click on "Export Votes" "button"
    Then I should download a "votes_export.csv" file
    And the CSV should contain:
      """
      Task,Voter,Method,Value,Date
      Export Task,Maria Silva,rating,8,2025-11-06
      Export Task,João Santos,rating,9,2025-11-06
      Export Task,Ana Costa,rating,7,2025-11-06
      """

  @voting_revision
  Scenario: Revise task and restart voting
    Given I am logged in as "creator1"
    And a task "Revision Task" has been rejected with average rating "4.5"
    And I am logged in as "creator1"
    And I navigate to "Tubaron > My Tasks"
    And I click on "Revision Task" "link"
    When I click on "Revise and Resubmit" "button"
    And I update the description to "Improved implementation based on feedback"
    And I press "Resubmit for Voting"
    Then I should see "Task resubmitted for voting"
    And the task status should be "voting"
    And all previous votes should be cleared
    And voters should receive "Voting reopened" notification

