Feature: Manage WordPress Salts in a .env file.

  Scenario: It can populate vars in the .env file, one for each salt from the WP generator service
    Given an empty directory
    And a .env file
    When I run `wp dotenv salts generate`
    Then STDOUT should be:
      """
      Success: Salts generated.
      """

  Scenario: The generate sub-command will not overwrite existing values unless forced
    Given an empty directory
    And a .env file:
      """
      AUTH_KEY=super-secret
      SECURE_AUTH_KEY=even-more-secret
      """
    When I run `wp dotenv salts generate`
    Then STDOUT should contain:
      """
      The 'AUTH_KEY' already exists, skipping.
      """
    And STDOUT should contain:
      """
      Some keys were already defined in the environment file.
      Use 'dotenv salts regenerate' to update them.
      """
    And the .env file should contain:
      """
      AUTH_KEY=super-secret
      SECURE_AUTH_KEY=even-more-secret
      """
    When I run `wp dotenv salts generate --force`
    Then STDOUT should be:
      """
      Success: Salts generated.
      """
    And the .env file should not contain:
      """
      super-secret
      """
    And the .env file should not contain:
      """
      even-more-secret
      """

  Scenario: The regenerate sub-command updates any existing values
    Given an empty directory
    And a .env file:
      """
      AUTH_KEY='updateme'
      SECURE_AUTH_KEY='updateme'
      LOGGED_IN_KEY='updateme'
      NONCE_KEY='updateme'
      AUTH_SALT='updateme'
      SECURE_AUTH_SALT='updateme'
      LOGGED_IN_SALT='updateme'
      NONCE_SALT='updateme'
      """
    When I run `wp dotenv salts regenerate`
    Then STDOUT should be:
      """
      Success: Salts regenerated.
      """
    And the .env file should not contain:
      """
      'updateme'
      """

  Scenario: The generate command should update all salts, if they are all the same.
    Given an empty directory
    And a .env file:
      """
      AUTH_KEY='updateme'
      SECURE_AUTH_KEY='updateme'
      LOGGED_IN_KEY='updateme'
      NONCE_KEY='updateme'
      AUTH_SALT='updateme'
      SECURE_AUTH_SALT='updateme'
      LOGGED_IN_SALT='updateme'
      NONCE_SALT='updateme'
      """
    When I run `wp dotenv salts generate`
    Then STDOUT should be:
      """
      Success: Salts generated.
      """
    And the .env file should not contain:
      """
      updateme
      """
