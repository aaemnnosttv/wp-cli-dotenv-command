Feature: Test 'dotenv init' command.

  Scenario: It can create a .env file in the working directory
    Given an empty directory
    When I run `wp dotenv init`
    Then the .env file should exist

  Scenario: It can create a .env file using a different filename
    Given an empty directory
    When I run `wp dotenv init --file=.secrets`
    Then the .secrets file should exist
