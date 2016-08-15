Feature: Test 'dotenv list' sub-command.

  Scenario: It lists all defined vars in the environment file
    Given an empty directory
    And a .env file:
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

  Scenario: It does not include any commented-out variables in the list.
    Given an empty directory
    And a .env file:
      """
      FOO=BAR
      #BAR=BAZ
      """
    And I run `wp dotenv list`
    Then STDOUT should be a table containing rows:
      | key  | value  |
      | FOO  | BAR    |
    And STDOUT should not contain:
      """
      BAZ
      """

  Scenario: It can limit the listed variables using a glob pattern.
    Given an empty directory
    And a .env file:
      """
      FOO=BAR
      FB_KEY=asdf
      FOOD=hot bar
      """
    When I run `wp dotenv list FOO*`
    Then STDOUT should be a table containing rows:
      | key       | value               |
      | FOO       | BAR                 |
      | FOOD      | hot bar             |
    And STDOUT should not contain:
      """
      FB_KEY
      """

  Scenario: It can limit the listed variables using multiple glob patterns.
    Given an empty directory
    And a .env file:
      """
      HEY=there
      GREETING=howdy
      FOOT=Big
      FB_KEY=secret
      FOOD=hot bar
      """
    When I run `wp dotenv list HEY FOO? *EE*`
    Then STDOUT should be a table containing rows:
      | key       | value               |
      | HEY       | there               |
      | GREETING  | howdy               |
      | FOOT      | Big                 |
      | FOOD      | hot bar             |
    And STDOUT should not contain:
      """
      FB_KEY
      """
