Feature: Test 'dotenv get' sub-command.

  Scenario: It errors when trying to get a value when no env file exists
    Given an empty directory
    When I try `wp dotenv get SOMETHING`
    Then STDOUT should be empty
    Then STDERR should contain:
      """
      File does not exist
      """

  Scenario: It errors when trying to get non-existent keys
    Given an empty directory
    And a .env file
    When I try `wp dotenv get ASDFXYZ`
    Then STDERR should contain:
      """
      'ASDFXYZ' not found
      """

  Scenario: It returns the value for the key
    Given an empty directory
    And a .env file:
      """
      GREETING = Hi there
      """
    When I run `wp dotenv get GREETING`
    Then STDOUT should be:
      """
      Hi there
      """

  Scenario: It returns values without any wrapping quotes, if any
    Given an empty directory
    And a .env file:
      """
      SINGLEQUOTED='single-quoted value'
      DOUBLEQUOTED="double-quoted value"
      """
    When I run `wp dotenv get SINGLEQUOTED`
    Then STDOUT should be:
      """
      single-quoted value
      """
    When I run `wp dotenv get DOUBLEQUOTED`
    Then STDOUT should be:
      """
      double-quoted value
      """
