<?php namespace wordpress;

use Codeception\Example;
use MultipleAuthors\Classes\Objects\Author;

class get_the_author_metaCest
{
    public function tryToGetTheAuthorTermMetaWhenAuthorIsMappedToUserAndHasTheMetaForTheAuthorTerm(
        \WpunitTester $I
    ) {
        $expected = 'my-aim';

        $userID = $I->factory('a new user')->user->create(['role' => 'author']);
        $author = Author::create_from_user($userID);
        update_term_meta($author->term_id, 'aim', $expected);

        $meta = get_the_author_meta('aim', $author->ID);

        $I->assertEquals(
            $expected,
            $meta,
            'The returned meta value should match the term meta'
        );
    }

    // ===============================================

    // ===============================================
    // Meta: description
    // ===============================================

    /**
     * @example {"metaKey": "aim"}
     * @example {"metaKey": "description"}
     * @example {"metaKey": "jabber"}
     * @example {"metaKey": "nickname"}
     * @example {"metaKey": "yim"}
     * @example {"metaKey": "facebook"}
     * @example {"metaKey": "twitter"}
     * @example {"metaKey": "instagram"}
     * @example {"metaKey": "user_description"}
     */
    public function tryToGetTheUserMetaWhenAuthorIsMappedToUserAndDoesntHaveTheMetaForTheAuthorTerm(
        \WpunitTester $I,
        Example $example
    ) {
        $expectedMetaValue = sprintf('meta_%s', $example['metaKey']);

        $userID = $I->factory('a new user')->user->create(['role' => 'author']);
        $author = Author::create_from_user($userID);

        update_user_meta($userID, $example['metaKey'], $expectedMetaValue);

        $metaValue = get_the_author_meta($example['metaKey'], $author->ID);

        $I->assertEquals(
            $expectedMetaValue,
            $metaValue,
            'The returned meta value should match the user meta'
        );
    }

    /**
     * @example {"metaKey": "aim"}
     * @example {"metaKey": "description"}
     * @example {"metaKey": "jabber"}
     * @example {"metaKey": "nickname"}
     * @example {"metaKey": "yim"}
     * @example {"metaKey": "facebook"}
     * @example {"metaKey": "twitter"}
     * @example {"metaKey": "instagram"}
     * @example {"metaKey": "user_description"}
     */
    public function tryToGetAuthorTermMetaWhenAuthorIsGuest(\WpunitTester $I, Example $example)
    {
        $authorSlug        = sprintf('guest_author_%s', rand(1, PHP_INT_MAX));
        $expectedMetaValue = sprintf('meta_%s', $example['metaKey']);

        $author = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );

        update_term_meta($author->term_id, $example['metaKey'], $expectedMetaValue);

        $metaValue = get_the_author_meta($example['metaKey'], $author->ID);

        $I->assertEquals(
            $expectedMetaValue,
            $metaValue,
            'The returned meta value doesnt match the term meta'
        );
    }


    /**
     * @example {"metaKey": "url", "expectedValue": "http://test.example.com"}
     * @example {"metaKey": "user_url", "expectedValue": "http://test.example.com"}
     * @example {"metaKey": "email", "expectedValue": "##slug##@example.com"}
     * @example {"metaKey": "user_email", "expectedValue": "##slug##@example.com"}
     * @example {"metaKey": "nicename", "expectedValue": "##slug##"}
     * @example {"metaKey": "user_nicename", "expectedValue": "##slug##"}
     * @example {"metaKey": "first_name", "expectedValue": "MyFirstName"}
     * @example {"metaKey": "last_name", "expectedValue": "MyLastName"}
     * @example {"metaKey": "display_name", "expectedValue": "MyDisplayName"}
     */
    public function tryToGeMetaWhenAuthorIsMappedToUser(\WpunitTester $I, Example $example)
    {
        $authorSlug               = sprintf('author_%s', rand(1, PHP_INT_MAX));
        $example['expectedValue'] = str_replace('##slug##', $authorSlug, $example['expectedValue']);

        $userProperty = $example['metaKey'];
        if (in_array($userProperty, ['url', 'email', 'nicename'])) {
            $userProperty = sprintf('user_%s', $userProperty);
        }

        $userID = $I->factory('a new user')->user->create(
            [
                'user_nicename' => $authorSlug,
                'role'          => 'author',
                $userProperty   => $example['expectedValue']
            ]
        );
        $author = Author::create_from_user($userID);

        $metaValue = get_the_author_meta($example['metaKey'], $author->ID);

        $I->assertEquals(
            $example['expectedValue'],
            $metaValue
        );
    }

    /**
     * @example {"metaKey": "url", "expectedValue": "http://test.example.com"}
     * @example {"metaKey": "user_url", "expectedValue": "http://test.example.com"}
     * @example {"metaKey": "email", "expectedValue": "##slug##@example.com"}
     * @example {"metaKey": "user_email", "expectedValue": "##slug##@example.com"}
     * @example {"metaKey": "nicename", "expectedValue": "##slug##"}
     * @example {"metaKey": "user_nicename", "expectedValue": "##slug##"}
     * @example {"metaKey": "first_name", "expectedValue": "MyFirstName"}
     * @example {"metaKey": "last_name", "expectedValue": "MyLastName"}
     * @example {"metaKey": "display_name", "expectedValue": "##display_name##"}
     */
    public function tryToGetMetaWhenAuthorIsGuest(\WpunitTester $I, Example $example)
    {
        $authorSlug = sprintf('guest_author_%s', rand(1, PHP_INT_MAX));
        $authorName = strtoupper($authorSlug);

        $example['expectedValue'] = str_replace('##slug##', $authorSlug, $example['expectedValue']);
        $example['expectedValue'] = str_replace('##display_name##', $authorName, $example['expectedValue']);

        $author = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => $authorName,
            ]
        );

        if ('nicename' !== $example['metaKey']) {
            $metaKey = $example['metaKey'];
            if (in_array($metaKey, ['url', 'email'])) {
                $metaKey = sprintf('user_%s', $metaKey);
            }

            update_term_meta(
                $author->term_id,
                $metaKey,
                $example['expectedValue']
            );
        }

        $metaValue = get_the_author_meta($example['metaKey'], $author->ID);

        $I->assertEquals(
            $example['expectedValue'],
            $metaValue
        );
    }

    public function tryToGetIdForWhenAuthorIsMappedToUser(\WpunitTester $I)
    {
        $userID = $I->factory('a new user')->user->create(['role' => 'author']);
        $author = Author::create_from_user($userID);

        $metaValue = get_the_author_meta('ID', $author->ID);

        $I->assertEquals(
            $userID,
            $metaValue,
            'The ID should match the user ID'
        );
    }

    public function tryToGetIdForWhenAuthorIsGuest(\WpunitTester $I)
    {
        $authorSlug = sprintf('guest_author_%s', rand(1, PHP_INT_MAX));

        $author = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );

        $metaValue = get_the_author_meta('ID', $author->ID);

        $I->assertEquals(
            $author->term_id * -1,
            $metaValue,
            'The ID should match the term_id as negative integer'
        );
    }

    public function tryToGetMetaWithoutSpecifyingTheAuthorIDForAuthorMappedToUser(\WpunitTester $I)
    {
        $I->wantToTest(
            'if get_the_author_meta returns the author meta of the correct author (mapped to user) when we do not provide the author ID'
        );

        global $post, $authordata;

        $postId = $I->factory('a new post')->post->create(
            [
                'title' => 'A Fake Post'
            ]
        );

        $post = get_post($postId);

        $user1Id           = $I->factory('a new user')->user->create(['role' => 'author']);
        $user              = get_user_by('ID', $user1Id);
        $user->description = 'A Nice User';
        wp_update_user($user);

        $author1 = Author::create_from_user($user1Id);

        $authorSlug = sprintf('guest_author_%d_%s', 2, rand(1, PHP_INT_MAX));
        $author2    = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );

        $authordata = $author1;

        wp_set_post_terms(
            $postId,
            [$author1->term_id, $author2->term_id],
            'author'
        );

        $authorId          = get_the_author_meta('ID');
        $authorDescription = get_the_author_meta('description');

        $I->assertEquals($author1->ID, $authorId);
        $I->assertEquals($user->description, $authorDescription);
    }

    public function tryToGetMetaWithoutSpecifyingTheAuthorIDForGuestAuthor(\WpunitTester $I)
    {
        $I->wantToTest(
            'if get_the_author_meta returns the author meta of the correct guest author when we do not provide the author ID'
        );

        global $post, $authordata;

        $postId = $I->factory('a new post')->post->create(
            [
                'title' => 'A Fake Post'
            ]
        );

        $post = get_post($postId);

        $authorSlug = sprintf('guest_author_%d_%s', 1, rand(1, PHP_INT_MAX));
        $author1    = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );
        $author1Description = 'A nice Author';
        update_term_meta($author1->term_id, 'description', $author1Description);

        $authorSlug = sprintf('guest_author_%d_%s', 2, rand(1, PHP_INT_MAX));
        $author2    = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );

        $authordata = $author1;

        wp_set_post_terms(
            $postId,
            [$author1->term_id, $author2->term_id],
            'author'
        );

        $authorId          = get_the_author_meta('ID');
        $authorDescription = get_the_author_meta('description');

        $I->assertEquals($author1->ID, $authorId);
        $I->assertEquals($author1Description, $authorDescription);
    }
}
