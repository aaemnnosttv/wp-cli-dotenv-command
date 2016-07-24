Feature: Commands are registered with WP-CLI.

  Scenario: It registers the dotenv command.
    Given an empty directory
    When I run `wp --help`
    Then STDOUT should contain:
      """
      dotenv
      """

  Scenario: It registers the dotenv salts command.
    Given an empty directory
    When I run `wp help dotenv salts`
    Then STDOUT should contain:
      """
      generate
      """
    And STDOUT should contain:
      """
      regenerate
      """
