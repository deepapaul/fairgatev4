
Feature: Login to fairgate
    In order to login to fairgate site
    As anybody
    I need to provide the username and password

Scenario: Fairgate login
    Given I am on "/swisstennis/backend/signin"   
    Then I should see "Login to your account"
    When I fill in "_username" with "superadmin" and "_password" with "test"
    And I press "Login"
    Then I should see "Welcome, Fairgate AG"