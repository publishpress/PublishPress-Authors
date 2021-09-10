<?php

namespace core\Classes\Objects;

use Codeception\Example;
use MultipleAuthors\Classes\Objects\Author;
use WpunitTester;

class get_multiple_authorsCest
{
    private function serializeArrayOfAuthors($authorsArray)
    {
        $data = [];

        foreach ($authorsArray as $author) {
            $data[] = [
                'term_id'      => $author->term_id,
                'user_id'      => $author->user_id,
                'display_name' => $author->display_name,
                'slug'         => $author->slug,
            ];
        }

        return maybe_serialize($data);
    }

    public function testGetMultipleAuthors_WithPostAsObject_ReturnListOfAuthors(WpunitTester $I)
    {
        $userId0 = $I->factory('create user0')->user->create();
        $userId1 = $I->factory('create user1')->user->create();
        $userId2 = $I->factory('create user2')->user->create();

        $author0 = Author::create_from_user($userId0);
        $author1 = Author::create_from_user($userId1);
        $author2 = Author::create_from_user($userId2);

        $postId = $I->factory('create a new post')->post->create();
        $post   = get_post($postId);

        wp_set_post_terms($postId, [$author0->term_id, $author1->term_id, $author2->term_id], 'author');

        $authorsList = get_multiple_authors($post, false, false);

        $I->assertIsArray($authorsList);
        $I->assertCount(3, $authorsList);
        $I->assertInstanceOf(Author::class, $authorsList[0]);
        $I->assertInstanceOf(Author::class, $authorsList[1]);
        $I->assertInstanceOf(Author::class, $authorsList[2]);
        $I->assertEquals($author0->term_id, $authorsList[0]->term_id);
        $I->assertEquals($author1->term_id, $authorsList[1]->term_id);
        $I->assertEquals($author2->term_id, $authorsList[2]->term_id);
    }

    public function testGetMultipleAuthors_CalledMultipleTimes_ReturnSameListOfCachedAuthors(WpunitTester $I)
    {
        $userId0 = $I->factory('create user0')->user->create();
        $userId1 = $I->factory('create user1')->user->create();
        $userId2 = $I->factory('create user2')->user->create();

        $author0 = Author::create_from_user($userId0);
        $author1 = Author::create_from_user($userId1);
        $author2 = Author::create_from_user($userId2);

        $postId = $I->factory('create a new post')->post->create();

        wp_set_post_terms($postId, [$author0->term_id, $author1->term_id, $author2->term_id], 'author');

        $authorsList = get_multiple_authors($postId, false, false);
        get_multiple_authors($postId, false, false);
        get_multiple_authors($postId, false, false);

        $cacheKey   = $postId . ':0:0';
        $cachedList = wp_cache_get($cacheKey, 'get_multiple_authors:authors');

        $I->assertIsArray($authorsList);
        $I->assertCount(3, $authorsList);
        $I->assertCount(3, $cachedList);
        $I->assertInstanceOf(Author::class, $authorsList[0]);
        $I->assertInstanceOf(Author::class, $authorsList[1]);
        $I->assertInstanceOf(Author::class, $authorsList[2]);
        $I->assertEquals($author0->term_id, $authorsList[0]->term_id);
        $I->assertEquals($author1->term_id, $authorsList[1]->term_id);
        $I->assertEquals($author2->term_id, $authorsList[2]->term_id);
        $I->assertEquals(maybe_serialize($authorsList), maybe_serialize($cachedList));
    }

    public function testGetMultipleAuthors_WithPostAsInt_ReturnListOfAuthors(WpunitTester $I)
    {
        $userId0 = $I->factory('create user0')->user->create();
        $userId1 = $I->factory('create user1')->user->create();
        $userId2 = $I->factory('create user2')->user->create();

        $author0 = Author::create_from_user($userId0);
        $author1 = Author::create_from_user($userId1);
        $author2 = Author::create_from_user($userId2);

        $postId = $I->factory('create a new post')->post->create();

        wp_set_post_terms($postId, [$author0->term_id, $author1->term_id, $author2->term_id], 'author');

        $authorsList = get_multiple_authors($postId, false, false);

        $I->assertIsArray($authorsList);
        $I->assertCount(3, $authorsList);
        $I->assertInstanceOf(Author::class, $authorsList[0]);
        $I->assertInstanceOf(Author::class, $authorsList[1]);
        $I->assertInstanceOf(Author::class, $authorsList[2]);
        $I->assertEquals($author0->term_id, $authorsList[0]->term_id);
        $I->assertEquals($author1->term_id, $authorsList[1]->term_id);
        $I->assertEquals($author2->term_id, $authorsList[2]->term_id);
    }

    public function testGetMultipleAuthors_WithPostAsString_ReturnListOfAuthors(WpunitTester $I)
    {
        $userId0 = $I->factory('create user0')->user->create();
        $userId1 = $I->factory('create user1')->user->create();
        $userId2 = $I->factory('create user2')->user->create();

        $author0 = Author::create_from_user($userId0);
        $author1 = Author::create_from_user($userId1);
        $author2 = Author::create_from_user($userId2);

        $postId = $I->factory('create a new post')->post->create();

        wp_set_post_terms($postId, [$author0->term_id, $author1->term_id, $author2->term_id], 'author');

        $authorsList = get_multiple_authors("$postId", false, false);

        $I->assertIsArray($authorsList);
        $I->assertCount(3, $authorsList);
        $I->assertInstanceOf(Author::class, $authorsList[0]);
        $I->assertInstanceOf(Author::class, $authorsList[1]);
        $I->assertInstanceOf(Author::class, $authorsList[2]);
        $I->assertEquals($author0->term_id, $authorsList[0]->term_id);
        $I->assertEquals($author1->term_id, $authorsList[1]->term_id);
        $I->assertEquals($author2->term_id, $authorsList[2]->term_id);
    }

    /**
     * @example [0]
     * @example [false]
     * @example [null]
     * @example [""]
     */
    public function testGetMultipleAuthors_WithGlobalPost_ReturnListOfAuthors(WpunitTester $I, Example $example)
    {
        $userId0 = $I->factory('create user0')->user->create();
        $userId1 = $I->factory('create user1')->user->create();
        $userId2 = $I->factory('create user2')->user->create();

        $author0 = Author::create_from_user($userId0);
        $author1 = Author::create_from_user($userId1);
        $author2 = Author::create_from_user($userId2);

        $postId = $I->factory('create a new post')->post->create();
        $post   = get_post($postId);

        wp_set_post_terms($postId, [$author0->term_id, $author1->term_id, $author2->term_id], 'author');

        $GLOBALS['post'] = $post;

        $authorsList = get_multiple_authors($example[0], false, false);

        $I->assertIsArray($authorsList);
        $I->assertCount(3, $authorsList);
        $I->assertInstanceOf(Author::class, $authorsList[0]);
        $I->assertInstanceOf(Author::class, $authorsList[1]);
        $I->assertInstanceOf(Author::class, $authorsList[2]);
        $I->assertEquals($author0->term_id, $authorsList[0]->term_id);
        $I->assertEquals($author1->term_id, $authorsList[1]->term_id);
        $I->assertEquals($author2->term_id, $authorsList[2]->term_id);
    }

    public function testGetMultipleAuthors_WithArchiveParam_ShouldReturnCurrentAuthor(WpunitTester $I)
    {
        $userId0 = $I->factory('create user0')->user->create();

        $author0 = Author::create_from_user($userId0);

        set_query_var('author_name', $author0->slug);

        $authorsList = get_multiple_authors(0, false, true);

        $I->assertIsArray($authorsList);
        $I->assertCount(1, $authorsList);
        $I->assertInstanceOf(Author::class, $authorsList[0]);
        $I->assertEquals($author0->term_id, $authorsList[0]->term_id);
    }

    public function testGetMultipleAuthors_WithNoArgumentsForMultiplePosts_isDetectingTheCorrectGlobalPost(
        WpunitTester $I
    ) {
        $userId0 = $I->factory('create user0')->user->create();
        $userId1 = $I->factory('create user1')->user->create();
        $userId2 = $I->factory('create user2')->user->create();

        $author0 = Author::create_from_user($userId0);
        $author1 = Author::create_from_user($userId1);
        $author2 = Author::create_from_user($userId2);

        $postId0 = $I->factory('create post0')->post->create();
        $postId1 = $I->factory('create post1')->post->create();
        $postId2 = $I->factory('create post2')->post->create();

        $post0 = get_post($postId0);
        $post1 = get_post($postId1);
        $post2 = get_post($postId2);

        wp_set_post_terms($postId0, [$author0->term_id, $author1->term_id, $author2->term_id], 'author');
        wp_set_post_terms($postId1, [$author0->term_id], 'author');
        wp_set_post_terms($postId2, [$author0->term_id, $author1->term_id], 'author');

        $GLOBALS['post'] = $post0;
        $authors         = get_multiple_authors();

        $I->assertCount(3, $authors);

        $GLOBALS['post'] = $post1;
        $authors         = get_multiple_authors();

        $I->assertCount(1, $authors);

        $GLOBALS['post'] = $post2;
        $authors         = get_multiple_authors();

        $I->assertCount(2, $authors);
    }

    public function testGetMultipleAuthors_WithNoAuthorTermsForThePostButAnAuthorWithTaxonomy_shouldReturnAnAuthorInstance(
        WpunitTester $I
    ) {
        global $wpdb;

        $userId = $I->factory('create user0')->user->create();

        $author = Author::create_from_user($userId);

        $postId = $I->factory('create post0')->post->create(
            ['post_type' => 'post', 'post_author' => $userId]
        );

        // Force to remove the author term relationship to the post.
        wp_remove_object_terms($postId, [$author->term_id], 'author');

        $authors = get_multiple_authors($postId);

        $I->assertInstanceOf('MultipleAuthors\\Classes\\Objects\\Author', $authors[0]);
    }

    public function testGetMultipleAuthors_WithNoAuthorRelationshipForPostAndNoAuthorTermForNotSelectedPostType_shouldNotCreateTheAuthorBasedOnUser(
        WpunitTester $I
    ) {
        $I->setPluginSettingsPostTypes(['post']);
        $I->setPluginSettingsAuthorForNewUsers([]);

        $userId = $I->haveAUser();
        $postId = $I->haveAPageForUser($userId);

        $I->makeSurePostDoesntHaveAuthorPosts($postId);

        $I->getMultipleAuthorsForPost($postId);

        $author = $I->getAuthorByUserId($userId);

        $I->assertFalse($author);
    }

    public function testGetMultipleAuthors_WithNoAuthorRelationshipForPostAndNoAuthorTermForNotSelectedPostType_shouldReturnEmptyArray(
        WpunitTester $I
    ) {
        $I->setPluginSettingsPostTypes(['post']);
        $I->setPluginSettingsAuthorForNewUsers([]);

        $userId = $I->haveAUser();
        $postId = $I->haveAPageForUser($userId);

        $I->makeSurePostDoesntHaveAuthorPosts($postId);

        $authors = $I->getMultipleAuthorsForPost($postId);

        $I->assertEmpty($authors);
    }

    public function testGetMultipleAuthors_WithNoAuthorRelationshipForPostAndNoAuthorTermForSelectedPostType_shouldCreateTheAuthorBasedOnUser(
        WpunitTester $I
    ) {
        $userId = $I->haveAUser();
        $postId = $I->haveAPostForUser($userId);
        $I->setPluginSettingsPostTypes(['post']);

        $authors = $I->getMultipleAuthorsForPost($postId);

        $I->assertNotEmpty($authors);

        $firstAuthor = $authors[0];

        $I->assertInstanceOf('MultipleAuthors\\Classes\\Objects\\Author', $firstAuthor);
        $I->assertFalse($firstAuthor->is_guest());
        $I->assertNotEmpty($firstAuthor->term_id);
        $I->assertNotEmpty($firstAuthor->display_name);
    }

    public function testGetMultipleAuthors_WithNoAuthorRelationshipForPostAndNoAuthorTerm_shouldCreateTheAuthorRelationshipForThePost(
        WpunitTester $I
    ) {
        $userId = $I->haveAUser();
        $postId = $I->haveAPostForUser($userId);
        $I->setPluginSettingsPostTypes(['post']);

        $authors = $I->getMultipleAuthorsForPost($postId);

        $postTerms   = wp_get_post_terms($postId, 'author');
        $firstAuthor = $authors[0];

        $I->assertNotEmpty(count($postTerms), 'There is no author terms for the post');
        $I->assertNotEmpty($authors);
        $I->assertInstanceOf('MultipleAuthors\\Classes\\Objects\\Author', $firstAuthor);
    }

    public function testGetMultipleAuthors_WhenThereIsNoNoAuthorRelationshipForPostAndPostAuthorIsZero_shouldReturnAnEmptyArray(
        WpunitTester $I
    ) {
        $I->setPluginSettingsPostTypes(['post']);

        $postId = $I->factory('Post with post_author = 0')->post->create(
            [
                'post_author' => 0,
            ]
        );

        $post = get_post($postId);

        $I->assertEquals(0, $post->post_author);

        $authors = get_multiple_authors($postId);

        $I->assertIsArray($authors);
        $I->assertEmpty($authors);
    }
}
