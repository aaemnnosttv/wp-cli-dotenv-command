Feature: Test 'dotenv set' sub-command.

  Scenario: It can set a value in the environment file
    Given an empty directory
    And a .env file
    When I run `wp dotenv set SOMEKEY some-value`
    Then the .env file should contain:
      """
      SOMEKEY=some-value
      """

  Scenario: It can set a single quoted value in the environment file
    Given an empty directory
    And a .env file
    When I run `wp dotenv set SOMEKEY some-value --quote-single`
    Then the .env file should contain:
      """
      SOMEKEY='some-value'
      """

  Scenario: It can set a double quoted value in the environment file
    Given an empty directory
    And a .env file
    When I run `wp dotenv set SOMEKEY some-value --quote-double`
    Then the .env file should contain:
      """
      SOMEKEY="some-value"
      """
