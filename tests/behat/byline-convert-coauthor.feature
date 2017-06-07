Feature: Convert coauthors to bylines

  Scenario: Co-Authors Plus isn't active
    Given a WP install

    When I try `wp byline convert-coauthor 1`
    Then STDERR should be:
      """
      Error: Co-Authors Plus must be installed and active.
      """

  Scenario: Convert coauthors on existing posts
    Given a WP install
    And I run `wp plugin install co-authors-plus --version=3.2 --activate`

    When I run `wp user create testauthor testauthor@example.com --role=author --porcelain`
    Then save STDOUT as {USER_ONE}

    When I run `wp user create testeditor testeditor@example.com --role=editor --porcelain`
    Then save STDOUT as {USER_TWO}

    When I run `wp post create --post_title='Test Post 1' --post_status=publish --post_author={USER_ONE} --porcelain`
    Then save STDOUT as {POST_ONE}

    When I run `wp post term list {POST_ONE} author --fields=slug --format=csv`
    Then STDOUT should be:
      """
      slug
      cap-testauthor
      """

    When I run `wp post term list {POST_ONE} byline --fields=slug --format=csv`
    Then STDOUT should be:
      """
      slug
      """

    When I run `wp post create --post_title='Test Post 2' --post_status=publish --post_author={USER_TWO} --porcelain`
    Then save STDOUT as {POST_TWO}

    When I run `wp post term list {POST_TWO} author --fields=slug --format=csv`
    Then STDOUT should be:
      """
      slug
      cap-testeditor
      """

    When I run `wp post term list {POST_TWO} byline --fields=slug --format=csv`
    Then STDOUT should be:
      """
      slug
      """

    When I run `wp post create --post_title='Test Post 3' --post_status=publish --post_author={USER_ONE} --porcelain`
    Then save STDOUT as {POST_THREE}

    When I run `wp co-authors-plus create-terms-for-posts`
    Then STDOUT should be:
      """
      Now inspecting or updating 5 total posts.
      1/5) Added - Post #1 'Hello world!' now has an author term for: admin
      2/5) Added - Post #2 'Sample Page' now has an author term for: admin
      3/5) Skipping - Post #3 'Test Post 1' already has these terms: testauthor
      4/5) Skipping - Post #4 'Test Post 2' already has these terms: testeditor
      5/5) Skipping - Post #5 'Test Post 3' already has these terms: testauthor
      Updating author terms with new counts
      Success: Done! Of 5 posts, 2 now have author terms.
      """

    When I run `wp byline convert-coauthor $(wp post list --orderby=ID --order=ASC --format=ids)`
    Then STDOUT should be:
      """
      Created 1 byline and assigned to post 1.
      Created 1 byline and assigned to post {POST_ONE}.
      Created 1 byline and assigned to post {POST_TWO}.
      Found 1 existing byline and assigned to post {POST_THREE}.
      Success: Converted 4 of 4 co-author posts.
      """

    When I run `wp post term list {POST_ONE} author --fields=slug --format=csv`
    Then STDOUT should be:
      """
      slug
      cap-testauthor
      """

    When I run `wp post term list {POST_ONE} byline --fields=slug --format=csv`
    Then STDOUT should be:
      """
      slug
      testauthor
      """

    When I run `wp post term list {POST_TWO} author --fields=slug --format=csv`
    Then STDOUT should be:
      """
      slug
      cap-testeditor
      """

    When I run `wp post term list {POST_TWO} byline --fields=slug --format=csv`
    Then STDOUT should be:
      """
      slug
      testeditor
      """

  Scenario: Converting co-authors persists order
    Given a WP install
    And I run `wp plugin install co-authors-plus --version=3.2 --activate`

    When I run `wp user create testauthor testauthor@example.com --role=author --porcelain`
    Then save STDOUT as {USER_ONE}

    When I run `wp user create testeditor testeditor@example.com --role=editor --porcelain`
    Then save STDOUT as {USER_TWO}

    When I run `wp post create --post_title='Test Post 1' --post_status=publish --post_author={USER_ONE} --porcelain`
    Then save STDOUT as {POST_ONE}

    When I run `wp eval "wp_set_object_terms( {POST_ONE}, array( 'cap-testauthor', 'cap-testeditor' ), 'author' );"`
    Then STDERR should be empty

    When I run `wp post term list {POST_ONE} author --fields=slug --format=csv`
    Then STDOUT should be:
      """
      slug
      cap-testauthor
      cap-testeditor
      """

    When I run `wp post term list {POST_ONE} byline --fields=slug --format=csv`
    Then STDOUT should be:
      """
      slug
      """

    When I run `wp byline convert-coauthor {POST_ONE}`
    Then STDOUT should be:
      """
      Created 2 bylines and assigned to post {POST_ONE}.
      Success: Converted 1 of 1 co-author posts.
      """

    When I run `wp post term list {POST_ONE} byline --fields=slug --format=csv`
    Then STDOUT should be:
      """
      slug
      testauthor
      testeditor
      """

  Scenario: Convert a post with guest authors
    Given a WP install
    And I run `wp plugin install co-authors-plus --version=3.2 --activate`

    When I run `wp user create testauthor testauthor@example.com --role=author --porcelain`
    Then save STDOUT as {USER_ONE}

    When I run `wp user create testeditor testeditor@example.com --role=editor --porcelain`
    Then save STDOUT as {USER_TWO}

    When I run `wp post create --post_title='Test Post 1' --post_status=publish --post_author={USER_ONE} --porcelain`
    Then save STDOUT as {POST_ONE}

    When I run `wp eval "wp_set_object_terms( {POST_ONE}, array( 'cap-testauthor', 'cap-testeditor' ), 'author' );"`
    Then STDERR should be empty

    When I run `wp co-authors-plus create-guest-authors`
    Then STDOUT should contain:
      """
      All done! Here are your results:
      """

    When I run `wp user delete testauthor --yes`
    And I run `wp user delete testeditor --yes`
    Then STDERR should be empty

    When I run `wp post term list {POST_ONE} author --fields=slug --format=csv`
    Then STDOUT should be:
      """
      slug
      cap-testauthor
      cap-testeditor
      """

    When I run `wp post term list {POST_ONE} byline --fields=slug --format=csv`
    Then STDOUT should be:
      """
      slug
      """

    When I run `wp byline convert-coauthor {POST_ONE}`
    Then STDOUT should be:
      """
      Created 2 bylines and assigned to post {POST_ONE}.
      Success: Converted 1 of 1 co-author posts.
      """

    When I run `wp post term list {POST_ONE} byline --fields=slug --format=csv`
    Then STDOUT should be:
      """
      slug
      testauthor
      testeditor
      """

    When I run `wp term list byline --slug=testauthor --field=term_id`
    Then save STDOUT as {TERM_ONE}

    When I run `wp term meta get {TERM_ONE} user_email`
    Then STDOUT should be:
      """
      testauthor@example.com
      """
