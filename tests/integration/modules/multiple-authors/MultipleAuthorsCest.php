<?php namespace core\Classes\Objects;

use Codeception\Example;
use MultipleAuthors\Classes\Objects\Author;

class MultipleAuthorsCest
{
    // ===============================================
    // Meta: aim
    // ===============================================

    public function tryToGetAuthorAimUsingTheFunctionGet_the_author_metaForMappedToUserAuthorAndNoMetaForTheTerm(
        \WpunitTester $I
    ) {
        $expected = 'myaim';

        $userID = $I->factory()->user->create(['role' => 'author']);
        $author = Author::create_from_user($userID);

        update_user_meta($userID, 'aim', $expected);

        $meta = get_the_author_meta('aim', $userID);

        $I->assertEquals(
            $expected,
            $meta,
            'For mapped to user authors with only user meta, we return the user meta'
        );
    }

    public function tryToGetAuthorAimUsingTheFunctionGet_the_author_metaForMappedToUserAuthorAndMetaOverrideForTheTerm(
        \WpunitTester $I
    ) {
        $expected = 'my-aim';

        $userID = $I->factory()->user->create(['role' => 'author']);
        $author = Author::create_from_user($userID);
        update_term_meta($author->term_id, 'aim', $expected);

        $meta = get_the_author_meta('aim', $author->ID);

        $I->assertEquals(
            $expected,
            $meta,
            'For mapped to user authors with meta defined for the term, we return the term meta overriding the user meta'
        );
    }

    // ===============================================

    // ===============================================
    // Meta: description
    // ===============================================

    /**
     * @example {"metaKey": "aim", "expectedValue": "my_aim", "message": "For mapped to user authors with no meta override in the term we should return the user meta aim"}
     * @example {"metaKey": "description", "expectedValue": "my_description", "message": "For mapped to user authors with no meta override in the term we should return the user meta description"}
     * @example {"metaKey": "jabber", "expectedValue": "my_jabber", "message": "For mapped to user authors with no meta override in the term we should return the user meta jabber"}
     * @example {"metaKey": "nickname", "expectedValue": "my_nickname", "message": "For mapped to user authors with no meta override in the term we should return the user meta nickname"}
     * @example {"metaKey": "yim", "expectedValue": "my_yim", "message": "For mapped to user authors with no meta override in the term we should return the user meta yim"}
     * @example {"metaKey": "facebook", "expectedValue": "my_facebook", "message": "For mapped to user authors with no meta override in the term we should return the user meta facebook"}
     * @example {"metaKey": "twitter", "expectedValue": "my_twitter", "message": "For mapped to user authors with no meta override in the term we should return the user meta twitter"}
     * @example {"metaKey": "instagram", "expectedValue": "my_instagram", "message": "For mapped to user authors with no meta override in the term we should return the user meta instagram"}
     * @example {"metaKey": "user_description", "expectedValue": "my_user_description", "message": "For mapped to user authors with no meta override in the term we should return the user meta user_description"}
     */
    public function tryToGetAuthorMetadataUsingTheFunctionGet_the_author_metaForMappedToUserAuthor(
        \WpunitTester $I,
        Example $example
    ) {
        $userID = $I->factory()->user->create(['role' => 'author']);
        $author = Author::create_from_user($userID);

        update_user_meta($userID, $example['metaKey'], $example['expectedValue']);

        $meta = get_the_author_meta($example['metaKey'], $userID);

        $I->assertEquals(
            $example['expectedValue'],
            $meta,
            $example['message']
        );
    }

    /**
     * @example {"metaKey":"aim", "expectedValue": "my_aim", "message": "For mapped to user authors with no meta override in the term we should return the user meta aim"}
     * @example {"metaKey": "description", "expectedValue": "my_description", "message": "For mapped to user authors with no meta override in the term we should return the user meta description"}
     * @example {"metaKey": "jabber", "expectedValue": "my_jabber", "message": "For mapped to user authors with no meta override in the term we should return the user meta jabber"}
     * @example {"metaKey": "nickname", "expectedValue": "my_nickname", "message": "For mapped to user authors with no meta override in the term we should return the user meta nickname"}
     * @example {"metaKey": "yim", "expectedValue": "my_yim", "message": "For mapped to user authors with no meta override in the term we should return the user meta yim"}
     * @example {"metaKey": "facebook", "expectedValue": "my_facebook", "message": "For mapped to user authors with no meta override in the term we should return the user meta facebook"}
     * @example {"metaKey": "twitter", "expectedValue": "my_twitter", "message": "For mapped to user authors with no meta override in the term we should return the user meta twitter"}
     * @example {"metaKey": "instagram", "expectedValue": "my_instagram", "message": "For mapped to user authors with no meta override in the term we should return the user meta instagram"}
     * @example {"metaKey": "user_description", "expectedValue": "my_user_description", "message": "For mapped to user authors with no meta override in the term we should return the user meta user_description"}
     * @example {"metaKey": "user_url", "expectedValue": "my_user_url", "message": "For mapped to user authors with no meta override in the term we should return the user meta user_url"}
     */
    public function tryToGetAuthorMetaUsingTheFunctionGet_the_author_metaForGuestAuthor(
        \WpunitTester $I,
        Example $example
    ) {
        $slug = 'guest_author_' . rand(1, PHP_INT_MAX);

        $author = Author::create(
            [
                'slug'         => $slug,
                'display_name' => strtoupper($slug),
            ]
        );

        update_term_meta($author->term_id, $example['metaKey'], $example['expectedValue']);

        $meta = get_the_author_meta($example['metaKey'], $author->ID);

        $I->assertEquals(
            $example['expectedValue'],
            $meta,
            $example['message']
        );
    }


    public function tryToGetAuthorUser_urlUsingTheFunctionGet_the_author_metaForMappedToUserAuthor(
        \WpunitTester $I
    ) {
        $expected = 'http://test.example.com';

        $userID = $I->factory()->user->create(['role' => 'author', 'user_url' => $expected]);
        $author = Author::create_from_user($userID);

        $meta = get_the_author_meta('url', $userID);

        $I->assertEquals(
            $expected,
            $meta,
            'The user_url should be returned if you ask for the url meta'
        );

        $meta = get_the_author_meta('user_url', $userID);

        $I->assertEquals(
            $expected,
            $meta,
            'The user_url should be returned'
        );
    }

    public function tryToGetAuthorUser_urlUsingTheFunctionGet_the_author_metaForGuestAuthor(
        \WpunitTester $I
    ) {
        $expected = 'http://test.example.com';

        $slug = 'guest_author_' . rand(1, PHP_INT_MAX);

        $author = Author::create(
            [
                'slug'         => $slug,
                'display_name' => strtoupper($slug),
            ]
        );

        update_term_meta($author->term_id, 'user_url', $expected);

        $meta = get_the_author_meta('url', $author->ID);

        $I->assertEquals(
            $expected,
            $meta,
            'The user_url should be returned if you ask for the url meta'
        );

        $meta = get_the_author_meta('user_url', $author->ID);

        $I->assertEquals(
            $expected,
            $meta,
            'The user_url should be returned'
        );
    }


// 'nicename', 'email'
// 'user_nicename', 'user_email', 'user_url', 'display_name', 'first_name', 'last_name'
//
//    public function tryToGetAuthorDisplay_nameUsingTheFunctionGet_the_author_metaForMappedToUserAuthor(\WpunitTester $I)
//    {
//        $this->testMetaForMappedToUserAuthor(
//            $I,
//            'display_name',
//            'my-display_name',
//            'For mapped to user authors with no meta override in the term we should return the user\'s meta display_name'
//        );
//    }
//
//    public function tryToGetAuthorDisplay_nameUsingTheFunctionGet_the_author_metaForGuestAuthor(\WpunitTester $I)
//    {
//        $this->testMetaForGuestAuthor(
//            $I,
//            'guestAuthorB3',
//            'display_name',
//            'my-display_name',
//            'For guest authors we should return the term\'s meta: display_name'
//        );
//    }
}
