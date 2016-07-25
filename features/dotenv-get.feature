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
    Given a directory with a .env file
    And the .env file has keys and values
    When I try `wp dotenv get ASDFXYZ`
    Then STDERR should contain:
      """
      'ASDFXYZ' not found
      """

  Scenario: It returns the value for the key
    Given a directory with a .env file
    And the .env file has a defined value for GREETING
    When I run `wp dotenv get GREETING`
    Then STDOUT should be:
    """
    Hi there
    """

  Scenario: It returns values without any wrapping quotes, if any
    Given a directory with a .env file
    And some of the keys have quoted values
    When I run `wp dotenv get SINGLEQUOTED && wp dotenv get DOUBLEQUOTED`
    Then STDOUT should be:
      """
      single-quoted value
      double-quoted value
      """
