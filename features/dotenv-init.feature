Feature: Test 'dotenv init' command.

  Scenario: It can create a .env file in the working directory
    Given an empty directory
    When I run `wp dotenv init`
    Then the .env file should exist

  Scenario: It can create a .env file using a different filename
    Given an empty directory
    When I run `wp dotenv init --file=.secrets`
    Then the .secrets file should exist

  Scenario: It will not overwrite an existing .env file by default
    Given an empty directory
    And a .env file
    When I try `wp dotenv init`
    Then STDERR should contain:
      """
      Environment file already exists
      """
    When I run `wp dotenv init --force`
    Then STDOUT should be:
      """
      Success: .env created.
      """

  Scenario: It can initialize a new environment file from a template.
    Given an empty directory
    And a .env.example file:
      """
      # this is a template!
      FOO=BAR
      """
    When I run `wp dotenv init --template=.env.example`
    Then the .env file should exist
    And the .env file should contain:
      """
      # this is a template!
      FOO=BAR
      """

  Scenario: It can initialize the environment file interactively from the template.
    Given an empty directory
    And a .env.example file:
      """
      DB_NAME=database_name
      DB_USER=database_username
      DB_PASS=database_password
      DB_HOST=database_hostname
      """
    And a session file:
      """
      test
      root
      secret
      localhost
      """
    When I run `wp dotenv init --template=.env.example --interactive < session`
    Then the .env file should be:
      """
      DB_NAME=test
      DB_USER=root
      DB_PASS=secret
      DB_HOST=localhost
      """
