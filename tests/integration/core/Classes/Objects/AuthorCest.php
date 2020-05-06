<?php namespace core\Classes\Objects;

use MultipleAuthors\Classes\Objects\Author;

class AuthorCest
{
    public function tryToCreateAnAuthorFromUserAndCheckIfTheUser_idMatches(\WpunitTester $I)
    {
        $userAuthorID = $I->factory()->user->create(['role' => 'author']);

        $author = Author::create_from_user($userAuthorID);

        $I->assertEquals(
            $userAuthorID,
            $author->user_id,
            'For mapped to user authors the user_id property matches the user\'s ID'
        );
    }

    public function tryToCreateAnAuthorFromUserAndCheckIfIs_guestReturnsFalse(\WpunitTester $I)
    {
        $userAuthorID = $I->factory()->user->create(['role' => 'author']);

        $author = Author::create_from_user($userAuthorID);

        $I->assertFalse(
            $author->is_guest(),
            'For mapped to user authors the is_guest method returns false'
        );
    }

    public function tryToCreateAGuestAuthorAndCheckIfUserIdIsEmpty(\WpunitTester $I)
    {
        $author = Author::create(
            [
                'slug'         => 'iamaguest1',
                'display_name' => 'Guest Author1',
            ]
        );

        $I->assertEmpty(
            $author->user_id,
            'For guest authors the user_id property is empty'
        );
    }

    public function tryToCreateAGuestAuthorAndCheckIfIs_guestReturnsTrue(\WpunitTester $I)
    {
        $author = Author::create(
            [
                'slug'         => 'iamaguest2',
                'display_name' => 'Guest Author2',
            ]
        );

        $I->assertTrue(
            $author->is_guest(),
            'For guest authors is_guest returns false'
        );
    }

    public function tryToCreateAnAuthorFromUserAndCheckIfTheIDMatchesUser_id(\WpunitTester $I)
    {
        $userAuthorID = $I->factory()->user->create(['role' => 'author']);

        $author = Author::create_from_user($userAuthorID);

        $I->assertEquals(
            $author->user_id,
            $author->ID,
            'For mapped to user authors the ID property matches the user_id property'
        );
    }

    public function tryToCreateAGuestAuthorAndCheckIfTheIdMatchesTheTermIdButAsNegativeInteger(\WpunitTester $I)
    {
        $author = Author::create(
            [
                'slug'         => 'iamaguest',
                'display_name' => 'Guest Author',
            ]
        );

        $I->assertEquals(
            $author->term_id * -1,
            $author->ID,
            'For guest authors the ID property matches the term_id property but as negative integer'
        );
    }

    public function tryToGetUserMetaFromAuthorMappedToUserAndNoMetaOverrideInTheTerm(\WpunitTester $I)
    {
        $userAuthorID = $I->factory()->user->create(['role' => 'author']);

        $author = Author::create_from_user($userAuthorID);

        $expected = 'the-meta';

        update_user_meta($userAuthorID, 'aim', $expected);

        $I->assertEquals(
            $expected,
            $author->get_meta('aim'),
            'For mapped to user authors with no meta set for the term, we should return the user\'s meta'
        );
    }

    public function tryToGetUserMetaFromAuthorMappedToUserWithMetaOverrideInTheTerm(\WpunitTester $I)
    {
        $userAuthorID = $I->factory()->user->create(['role' => 'author']);

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

    public function tryToGetUserMetaFromAuthorMappedToUserWithZeroAsMetaOverrideInTheTerm(\WpunitTester $I)
    {
        $userAuthorID = $I->factory()->user->create(['role' => 'author']);

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

    public function tryToGetAuthorByEmailAddressForAuthorMappedToUser(\WpunitTester $I)
    {
        $userID = $I->factory()->user->create(['role' => 'author', 'user_email']);
        $author = Author::create_from_user($userID);

        $user = get_user_by('ID', $userID);

        $foundAuthor = Author::get_by_email($user->user_email);

        $I->assertIsObject($foundAuthor);
        $I->assertEquals($user->user_email, $author->user_email);
    }

    public function tryToGetAuthorByEmailAddressForGuestAuthor(\WpunitTester $I)
    {
        $author = Author::create(
            [
                'slug'         => 'iamaguest2',
                'display_name' => 'Guest Author 2',
            ]
        );

        $email = 'imaguest@example.com';

        update_term_meta($author->term_id, 'user_email', $email);

        $foundAuthor = Author::get_by_email($email);

        $I->assertIsObject($foundAuthor);
        $I->assertEquals($email, $author->user_email);
    }
}
