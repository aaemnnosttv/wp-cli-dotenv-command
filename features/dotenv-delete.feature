Feature: Test 'dotenv delete' sub-command.

  Scenario: It can delete a defined var from the file by the key
    Given a directory with a .env file
    And the .env file contains a line "GREETING = Hello World"
    When I run `wp dotenv delete GREETING`
    Then STDOUT should be:
    """
    Success: Removed 'GREETING'
    """

  Scenario: It can delete all vars listed by key
    Given a directory with a .env file
    And the .env file has vars defined for "ONE, TWO, THREE"
    When I run `wp dotenv delete ONE TWO THREE`
    Then STDOUT should be:
    """
    Success: Removed 'ONE'
    Success: Removed 'TWO'
    Success: Removed 'THREE'
    """
