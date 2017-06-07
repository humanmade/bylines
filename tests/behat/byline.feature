Feature: Manage bylines.

  Scenario: Byline CLI namespace should be registered
    Given a WP install

    When I run `wp byline`
    Then STDOUT should contain:
      """
      wp byline generate-for-posts <post-id>...
      """
