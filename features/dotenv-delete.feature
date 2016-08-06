Feature: Test 'dotenv delete' sub-command.

  Scenario: It can delete a defined var from the file by the key
    Given an empty directory
    And a .env file:
      """
      GREETING = Hello World
      """
    When I run `wp dotenv delete GREETING`
    Then STDOUT should be:
    """
    Success: Removed 'GREETING'
    """

  Scenario: It can delete all vars listed by key
    Given an empty directory
    And a .env file:
      """
      ONE=uno
      TWO=dos
      THREE=tres
      """
    When I run `wp dotenv delete ONE TWO THREE`
    Then STDOUT should be:
    """
    Success: Removed 'ONE'
    Success: Removed 'TWO'
    Success: Removed 'THREE'
    """
