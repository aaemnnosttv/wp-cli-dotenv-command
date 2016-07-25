Feature: Test 'dotenv list' sub-command.

  Scenario: It lists all defined vars in the environment file
    Given a directory with a .env file containing:
      """
      FOO=BAR
      BAR = Drinks
      WEATHER="HOT"
      DRINKS = Sound really good.
      """
    And I run `wp dotenv list`
    Then STDOUT should be a table containing rows:
      | key       | value               |
      | FOO       | BAR                 |
      | BAR       | Drinks              |
      | WEATHER   | HOT                 |
      | DRINKS    | Sound really good.  |
