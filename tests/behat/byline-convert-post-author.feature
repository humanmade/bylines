Feature: Convert post authors to byline terms.

  Scenario: Generate bylines for existing post authors
    Given a WP install

    When I run `wp term list byline --format=count`
     Then STDOUT should be:
       """
       0
       """

    When I run `wp user create testauthor testauthor@example.com --role=author --porcelain`
    Then save STDOUT as {USER_ONE}

    When I run `wp user create testeditor testeditor@example.com --role=editor --porcelain`
    Then save STDOUT as {USER_TWO}

    When I run `wp post create --post_title='Test Post 1' --post_author={USER_ONE} --porcelain`
    Then save STDOUT as {POST_ONE}

    When I run `wp post create --post_title='Test Post 2' --post_author={USER_TWO} --porcelain`
    Then save STDOUT as {POST_TWO}

    When I run `wp post create --post_title='Test Post 3' --post_author={USER_ONE} --porcelain`
    Then save STDOUT as {POST_THREE}

    When I run `wp byline convert-post-author {POST_ONE} {POST_TWO} {POST_THREE}`
    Then STDOUT should be:
      """
      Created byline and assigned to post {POST_ONE}.
      Created byline and assigned to post {POST_TWO}.
      Found existing byline and assigned to post {POST_THREE}.
      Success: Converted 3 of 3 post authors.
      """

     When I run `wp term list byline --format=count`
     Then STDOUT should be:
       """
       2
       """

  Scenario: Display warning when a post already has bylines
    Given a WP install

    When I run `wp user create testauthor testauthor@example.com --role=author --porcelain`
    Then save STDOUT as {USER_ONE}

    When I run `wp post create --post_title='Test Post 1' --post_author={USER_ONE} --porcelain`
    Then save STDOUT as {POST_ONE}

    When I run `wp byline convert-post-author {POST_ONE}`
    Then STDOUT should not be empty

    When I try `wp byline convert-post-author {POST_ONE}`
    Then STDERR should be:
      """
      Warning: Post {POST_ONE} already has bylines.
      Error: No post authors converted.
      """
    And STDOUT should be empty

  Scenario: Display warning when a post doesn't have an author
    Given a WP install

    When I run `wp post create --post_title='Test Post 1' --porcelain`
    Then save STDOUT as {POST_ONE}

    When I try `wp byline convert-post-author {POST_ONE}`
    Then STDERR should be:
      """
      Warning: Post {POST_ONE} doesn't have an author.
      Error: No post authors converted.
      """
    And STDOUT should be empty
