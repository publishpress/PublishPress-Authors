<?php namespace core\Classes\Objects;

use MultipleAuthors\Classes\Objects\Author;
use WpunitTester;

class AuthorCest
{
    public function tryToCreateAnAuthorFromUserAndCheckIfTheUser_idMatches(WpunitTester $I)
    {
        $userAuthorID = $I->factory('a new user')->user->create(['role' => 'author']);

        $author = Author::create_from_user($userAuthorID);

        $I->assertEquals(
            $userAuthorID,
            $author->user_id,
            'For mapped to user authors the user_id property matches the user\'s ID'
        );
    }

    public function tryToCreateAnAuthorFromUserAndCheckIfIs_guestReturnsFalse(WpunitTester $I)
    {
        $userAuthorID = $I->factory('a new user')->user->create(['role' => 'author']);

        $author = Author::create_from_user($userAuthorID);

        $I->assertFalse(
            $author->is_guest(),
            'For mapped to user authors the is_guest method returns false'
        );
    }

    public function tryToCreateAGuestAuthorAndCheckIfUserIdIsEmpty(WpunitTester $I)
    {
        $authorSlug = sprintf('guest_author_%d', rand(1, PHP_INT_MAX));
        $author     = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );

        $I->assertEmpty(
            $author->user_id,
            'For guest authors the user_id property is empty'
        );
    }

    public function tryToCreateAGuestAuthorAndCheckIfIs_guestReturnsTrue(WpunitTester $I)
    {
        $authorSlug = sprintf('guest_author_%d', rand(1, PHP_INT_MAX));
        $author     = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );

        $I->assertTrue(
            $author->is_guest(),
            'For guest authors is_guest returns false'
        );
    }

    public function tryToCreateAnAuthorFromUserAndCheckIfTheIDMatchesUser_id(WpunitTester $I)
    {
        $userAuthorID = $I->factory('a new user')->user->create(['role' => 'author']);

        $author = Author::create_from_user($userAuthorID);

        $I->assertEquals(
            $author->user_id,
            $author->ID,
            'For mapped to user authors the ID property matches the user_id property'
        );
    }

    public function tryToCreateAGuestAuthorAndCheckIfTheIdMatchesTheTermIdButAsNegativeInteger(WpunitTester $I)
    {
        $authorSlug = sprintf('guest_author_%d', rand(1, PHP_INT_MAX));
        $author     = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );

        $I->assertEquals(
            $author->term_id * -1,
            $author->ID,
            'For guest authors the ID property matches the term_id property but as negative integer'
        );
    }

    public function tryToGetUserMetaFromAuthorMappedToUserAndNoMetaOverrideInTheTerm(WpunitTester $I)
    {
        $userAuthorID = $I->factory('a new user')->user->create(['role' => 'author']);

        $author = Author::create_from_user($userAuthorID);

        $expected = 'the-meta';

        update_user_meta($userAuthorID, 'aim', $expected);

        $I->assertEquals(
            $expected,
            $author->get_meta('aim'),
            'For mapped to user authors with no meta set for the term, we should return the user\'s meta'
        );
    }

    public function tryToGetUserMetaFromAuthorMappedToUserWithMetaOverrideInTheTerm(WpunitTester $I)
    {
        $userAuthorID = $I->factory('a new user')->user->create(['role' => 'author']);

        $author = Author::create_from_user($userAuthorID);

        $expected = 'the-meta';

        update_user_meta($userAuthorID, 'aim', 'the-user-meta');
        update_term_meta($author->term_id, 'aim', $expected);

        $I->assertEquals(
            $expected,
            $author->get_meta('aim'),
            'For mapped to user authors with the meta set for the term, we should return the term\'s meta'
        );
    }

    public function tryToGetUserMetaFromAuthorMappedToUserWithZeroAsMetaOverrideInTheTerm(WpunitTester $I)
    {
        $userAuthorID = $I->factory('a new user')->user->create(['role' => 'author']);

        $author = Author::create_from_user($userAuthorID);

        $expected = 0;

        update_user_meta($userAuthorID, 'aim', 'the-user-meta');
        update_term_meta($author->term_id, 'aim', $expected);

        $I->assertEquals(
            $expected,
            $author->get_meta('aim'),
            'For mapped to user authors with 0 as meta set for the term, we should return the term\'s meta'
        );
    }

    public function tryToGetAuthorByEmailAddressForAuthorMappedToUser(WpunitTester $I)
    {
        $userID = $I->factory('a new user')->user->create(['role' => 'author']);
        $author = Author::create_from_user($userID);

        $user = get_user_by('ID', $userID);

        $foundAuthor = Author::get_by_email($user->user_email);

        $I->assertIsObject($foundAuthor);
        $I->assertEquals($user->user_email, $author->user_email);
    }

    public function tryToGetAuthorByEmailAddressForGuestAuthor(WpunitTester $I)
    {
        $authorSlug = sprintf('guest_author_%d', rand(1, PHP_INT_MAX));
        $author     = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );

        $email = 'imaguest@example.com';

        update_term_meta($author->term_id, 'user_email', $email);

        $foundAuthor = Author::get_by_email($email);

        $I->assertIsObject($foundAuthor);
        $I->assertEquals($email, $author->user_email);
    }

    public function tryToGetUserURLForMappedToUserAuthor(WpunitTester $I)
    {
        $expected = 'http://test.example.com';

        $userAuthorID = $I->factory('a new user')->user->create(
            [
                'role'     => 'author',
                'user_url' => $expected,
            ]
        );

        $author = Author::create_from_user($userAuthorID);

        $I->assertEquals(
            $expected,
            $author->user_url
        );
    }

    public function tryToGetUserURLForGuestAuthors(WpunitTester $I)
    {
        $expected = 'http://test.example.com';

        $authorSlug = sprintf('guest_author_%d', rand(1, PHP_INT_MAX));
        $author     = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );

        update_term_meta($author->term_id, 'user_url', $expected);

        $I->assertEquals(
            $expected,
            $author->user_url
        );
    }

    public function tryToGetFirstNameForMappedToUserAuthor(WpunitTester $I)
    {
        $expected = 'TheFirstName';

        $userAuthorID = $I->factory('a new user')->user->create(
            [
                'role'       => 'author',
                'first_name' => $expected,
            ]
        );

        $author = Author::create_from_user($userAuthorID);

        $I->assertEquals(
            $expected,
            $author->first_name
        );
    }

    public function tryToGetFirstNameForGuestAuthors(WpunitTester $I)
    {
        $expected = 'TheFirstName';

        $authorSlug = sprintf('guest_author_%d', rand(1, PHP_INT_MAX));
        $author     = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );

        update_term_meta($author->term_id, 'first_name', $expected);

        $I->assertEquals(
            $expected,
            $author->first_name
        );
    }

    public function tryToGetLinkForMappedToUserAuthor(WpunitTester $I)
    {
        $userAuthorID = $I->factory('a new user')->user->create(
            [
                'role' => 'author',
            ]
        );

        $author = Author::create_from_user($userAuthorID);

        $I->assertEquals(
            get_author_posts_url($userAuthorID),
            $author->link
        );
    }

    public function tryToGetLinkForGuestAuthors(WpunitTester $I)
    {
        $I->setPermalinkStructure('/%postname%/');

        $authorSlug = sprintf('guest_author_%d', rand(1, PHP_INT_MAX));
        $author     = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );

        $I->assertEquals(
            sprintf('%s/author/%s/', set_url_scheme($_ENV['TEST_SITE_WP_URL'], 'http'), $authorSlug),
            $author->link
        );
    }

    public function tryToGetNameForMappedToUserAuthorWithoutChangingAuthorsName(WpunitTester $I)
    {
        $userAuthorID = $I->factory('a new user')->user->create(
            [
                'role' => 'author',
            ]
        );

        $user   = get_user_by('id', $userAuthorID);
        $author = Author::create_from_user($userAuthorID);

        $I->assertEquals(
            $user->display_name,
            $author->name
        );

        $I->assertEquals(
            $user->display_name,
            $author->display_name
        );
    }

    public function tryToGetAuthorsDisplayNameForMappedToUserAuthorChangingAuthorsName(WpunitTester $I)
    {
        $expected = 'Aslam Jorge';

        $userAuthorID = $I->factory('a new user')->user->create(
            [
                'role' => 'author',
            ]
        );

        $user   = get_user_by('id', $userAuthorID);
        $author = Author::create_from_user($userAuthorID);
        wp_update_term($author->term_id, 'author', ['name' => $expected]);

        $I->assertEquals(
            $expected,
            $author->display_name,
        );

        $I->assertEquals(
            $expected,
            $author->name,
        );

        $I->assertNotEquals(
            $user->display_name,
            $author->display_name,
            'If the author has a different display_name, that is the expected value, not the user\'s display_name.'
        );
    }

    public function tryToGetNameForGuestAuthors(WpunitTester $I)
    {
        $authorSlug = sprintf('guest_author_%d', rand(1, PHP_INT_MAX));
        $expected   = strtoupper($authorSlug);
        $author     = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => $expected,
            ]
        );

        $I->assertEquals(
            $expected,
            $author->name
        );
    }

    public function tryToGetSlugForMappedToUserAuthor(WpunitTester $I)
    {
        $userAuthorID = $I->factory('a new user')->user->create(
            [
                'role' => 'author',
            ]
        );

        $user   = get_user_by('id', $userAuthorID);
        $author = Author::create_from_user($userAuthorID);

        $I->assertEquals(
            $user->user_nicename,
            $author->slug
        );
    }

    public function tryToGetSlugForGuestAuthors(WpunitTester $I)
    {
        $authorSlug = sprintf('guest_author_%d', rand(1, PHP_INT_MAX));
        $author     = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );

        $I->assertEquals(
            $authorSlug,
            $author->slug
        );
    }

    public function tryToGetAvatarURLWithoutCustomAvatar(WpunitTester $I)
    {
        $authorSlug = sprintf('guest_author_%d', rand(1, PHP_INT_MAX));
        $author     = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );

        $avatarUrl = $author->get_avatar_url();

        $I->assertFalse(is_null($avatarUrl), 'The avatar URL should not be null');
        $I->assertRegExp(
            '#http[s]?://[0-9]{1,2}\.gravatar\.com/avatar/\?s=96&d=mm&r=g#',
            $avatarUrl
        );
    }
}
