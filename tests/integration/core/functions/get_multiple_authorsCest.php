<?php namespace core\Classes\Objects;

use Codeception\Example;
use MultipleAuthors\Classes\Objects\Author;
use WpunitTester;

class get_multiple_authorsCest
{

    //function get_multiple_authors($post = 0, $filter_the_author = true, $archive = false)


    public function testGetMultipleAuthors_WithPostAsObject_ReturnListOfAuthors(WpunitTester $I)
    {
        global $multipleAuthorsForPost;

        $multipleAuthorsForPost = [];

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

    public function testGetMultipleAuthors_WithPostAsInt_ReturnListOfAuthors(WpunitTester $I)
    {
        global $multipleAuthorsForPost;

        $multipleAuthorsForPost = [];

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
        global $multipleAuthorsForPost;

        $multipleAuthorsForPost = [];

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
        global $multipleAuthorsForPost;

        $multipleAuthorsForPost = [];

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

    public function testGetMultipleAuthors_WithFilterAuthor_ShouldFilterTheAuthorsDisplayName(WpunitTester $I)
    {
        global $multipleAuthorsForPost, $testFilterAdded;

        $multipleAuthorsForPost = [];

        $userId0 = $I->factory('create user0')->user->create();
        $userId1 = $I->factory('create user1')->user->create();
        $userId2 = $I->factory('create user2')->user->create();

        $author0 = Author::create_from_user($userId0);
        $author1 = Author::create_from_user($userId1);
        $author2 = Author::create_from_user($userId2);

        $postId = $I->factory('create a new post')->post->create();
        $post   = get_post($postId);

        wp_set_post_terms($postId, [$author0->term_id, $author1->term_id, $author2->term_id], 'author');

        if (empty($testFilterAdded)) {
            \add_filter(
                'the_author',
                function ($displayName) {
                    return $displayName . '--filtered';
                }
            );

            $testFilterAdded = true;
        }

        $authorsList = get_multiple_authors($post, true, false);

        $I->assertIsArray($authorsList);
        $I->assertCount(3, $authorsList);
        $I->assertInstanceOf(Author::class, $authorsList[0]);
        $I->assertInstanceOf(Author::class, $authorsList[1]);
        $I->assertInstanceOf(Author::class, $authorsList[2]);
        $I->assertEquals($author0->display_name . '--filtered', $authorsList[0]->display_name);
        $I->assertEquals($author1->display_name . '--filtered', $authorsList[1]->display_name);
        $I->assertEquals($author2->display_name . '--filtered', $authorsList[2]->display_name);

        remove_all_filters('the_author');
    }

    public function testGetMultipleAuthors_WithArchiveParam_ShouldReturnCurrentAuthor(WpunitTester $I)
    {
        global $multipleAuthorsForPost;

        $multipleAuthorsForPost = [];

        $userId0 = $I->factory('create user0')->user->create();

        $author0 = Author::create_from_user($userId0);

        set_query_var('author_name', $author0->slug);

        $authorsList = get_multiple_authors(0, false, true);

        $I->assertIsArray($authorsList);
        $I->assertCount(1, $authorsList);
        $I->assertInstanceOf(Author::class, $authorsList[0]);
        $I->assertEquals($author0->term_id, $authorsList[0]->term_id);
    }

    protected function serializeArrayOfAuthors($authorsArray)
    {
        $data = [];

        foreach ($authorsArray as $author) {
            $data[] = [
                'term_id' => $author->term_id,
                'user_id' => $author->user_id,
                'display_name' => $author->display_name,
                'slug' => $author->slug,
            ];
        }

        return maybe_serialize($data);
    }

    public function testGetMultipleAuthors_ForCallingSamePostTwice_isCachingAuthorList(WpunitTester $I)
    {
        global $multipleAuthorsForPost;

        $multipleAuthorsForPost = [];

        $userId0 = $I->factory('create user0')->user->create();
        $userId1 = $I->factory('create user1')->user->create();
        $userId2 = $I->factory('create user2')->user->create();

        $author0 = Author::create_from_user($userId0);
        $author1 = Author::create_from_user($userId1);
        $author2 = Author::create_from_user($userId2);

        $postId0 = $I->factory('create post0')->post->create();

        wp_set_post_terms($postId0, [$author0->term_id, $author1->term_id, $author2->term_id], 'author');

        $returnedAuthorList = get_multiple_authors($postId0, false, false);

        // Created a cache entry
        $I->assertCount(1, $multipleAuthorsForPost, 'We called once, so only one item should be in the cache');
        // The returned list is the same as the cached one
        reset($multipleAuthorsForPost);
        $cachedAuthorList = current($multipleAuthorsForPost);
        $I->assertCount(3, $cachedAuthorList, 'Like the expected list, the cached list should have 3 items');
        $I->assertEquals(
            $this->serializeArrayOfAuthors($cachedAuthorList),
            $this->serializeArrayOfAuthors($returnedAuthorList)
        );
        $I->assertEquals($author0, $cachedAuthorList[0]);
        $I->assertEquals($author1, $cachedAuthorList[1]);
        $I->assertEquals($author2, $cachedAuthorList[2]);

        // Call the function again and check if the cached value is still returned
        $returnedAuthorList = get_multiple_authors($postId0, false, false);

        // Created a cache entry
        $I->assertCount(1, $multipleAuthorsForPost, 'We twice, but only one item should be in the cache');
        // The returned list is the same as the cached one
        reset($multipleAuthorsForPost);
        $cachedAuthorList = current($multipleAuthorsForPost);
        $I->assertCount(3, $cachedAuthorList, 'Like the expected list, the cached list should have 3 items');
        $I->assertEquals(
            $this->serializeArrayOfAuthors($cachedAuthorList),
            $this->serializeArrayOfAuthors($returnedAuthorList)
        );
        $I->assertEquals($author0, $cachedAuthorList[0]);
        $I->assertEquals($author1, $cachedAuthorList[1]);
        $I->assertEquals($author2, $cachedAuthorList[2]);
    }

    public function testGetMultipleAuthors_ForCallingSamePostTwiceWithDifferentParams_isCachingBothAuthorLists(
        WpunitTester $I
    ) {
        global $multipleAuthorsForPost;

        $multipleAuthorsForPost = [];

        $userId0 = $I->factory('create user0')->user->create();
        $userId1 = $I->factory('create user1')->user->create();
        $userId2 = $I->factory('create user2')->user->create();

        $author0 = Author::create_from_user($userId0);
        $author1 = Author::create_from_user($userId1);
        $author2 = Author::create_from_user($userId2);

        $postId0 = $I->factory('create post0')->post->create();

        wp_set_post_terms($postId0, [$author0->term_id, $author1->term_id, $author2->term_id], 'author');

        get_multiple_authors($postId0, true, false);
        get_multiple_authors($postId0, false, false);

        reset($multipleAuthorsForPost);

        $I->assertCount(2, $multipleAuthorsForPost, 'Should create 3 entries in the cache');

        $I->assertCount(3, current($multipleAuthorsForPost));
        next($multipleAuthorsForPost);
        $I->assertCount(3, current($multipleAuthorsForPost));
    }

    public function testGetMultipleAuthors_ForDifferentPosts_isCachingBothAuthorList(WpunitTester $I)
    {
        global $multipleAuthorsForPost;

        $multipleAuthorsForPost = [];

        $userId0 = $I->factory('create user0')->user->create();
        $userId1 = $I->factory('create user1')->user->create();
        $userId2 = $I->factory('create user2')->user->create();

        $author0 = Author::create_from_user($userId0);
        $author1 = Author::create_from_user($userId1);
        $author2 = Author::create_from_user($userId2);

        $postId0 = $I->factory('create post0')->post->create();
        $postId1 = $I->factory('create post1')->post->create();

        wp_set_post_terms($postId0, [$author0->term_id, $author1->term_id, $author2->term_id], 'author');
        wp_set_post_terms($postId1, [$author0->term_id], 'author');

        $returnedAuthorList0 = get_multiple_authors($postId0, false, false);

        $I->assertCount(1, $multipleAuthorsForPost, 'We called once, so only one item should be in the cache');
        // The returned list is the same as the cached one
        reset($multipleAuthorsForPost);
        $cachedAuthorList = current($multipleAuthorsForPost);
        $I->assertCount(3, $cachedAuthorList, 'Like the expected list, the cached list should have 3 items');
        $I->assertEquals(
            $this->serializeArrayOfAuthors($cachedAuthorList),
            $this->serializeArrayOfAuthors($returnedAuthorList0)
        );
        $I->assertEquals($author0, $cachedAuthorList[0]);
        $I->assertEquals($author1, $cachedAuthorList[1]);
        $I->assertEquals($author2, $cachedAuthorList[2]);

        // Call the function again for a different post and check if the new author list is cached
        $returnedAuthorList1 = get_multiple_authors($postId1, false, false);

        // Created a cache entry
        $I->assertCount(
            2,
            $multipleAuthorsForPost,
            'We are calling the function for different posts, so we should have 2 cached values'
        );
        // The returned list is the same as the cached one
        reset($multipleAuthorsForPost);
        next($multipleAuthorsForPost);
        $cachedAuthorList = current($multipleAuthorsForPost);
        $I->assertCount(1, $cachedAuthorList, 'Like the expected list, the cached list should have 1 items');
        $I->assertEquals(
            $this->serializeArrayOfAuthors($cachedAuthorList),
            $this->serializeArrayOfAuthors($returnedAuthorList1)
        );
        $I->assertEquals($author0, $cachedAuthorList[0]);
    }

    public function testGetMultipleAuthors_ForArchive_isCachingTheAuthorFromAuthorPage(WpunitTester $I)
    {
        global $multipleAuthorsForPost;

        $multipleAuthorsForPost = [];

        $userId0 = $I->factory('create user0')->user->create();
        $userId1 = $I->factory('create user1')->user->create();
        $userId2 = $I->factory('create user2')->user->create();

        $author0 = Author::create_from_user($userId0);
        $author1 = Author::create_from_user($userId1);
        $author2 = Author::create_from_user($userId2);

        $postId0 = $I->factory('create post0')->post->create();
        $post0   = get_post($postId0);

        $GLOBALS['post'] = $post0;

        wp_set_post_terms($postId0, [$author0->term_id, $author1->term_id, $author2->term_id], 'author');

        // Call without a post id so it gets the global post, similar to the call with the archive param = true.
        get_multiple_authors(0, false, false);

        $I->assertCount(1, $multipleAuthorsForPost, 'We called once, so only one item should be in the cache');
        // The returned list is the same as the cached one
        reset($multipleAuthorsForPost);
        $cachedAuthorList = current($multipleAuthorsForPost);
        $I->assertCount(3, $cachedAuthorList, 'Like the expected list, the cached list should have 3 items');

        // Call the function again for the archive page and check if the new author list is cached
        set_query_var('author_name', $author2->slug);
        $returnedAuthorList1 = get_multiple_authors(0, false, true);

        // Created a cache entry
        $I->assertCount(
            2,
            $multipleAuthorsForPost,
            'We are calling the function for a post and archive, so we should have 2 cached values'
        );
        // The returned list is the same as the cached one
        reset($multipleAuthorsForPost);
        next($multipleAuthorsForPost);
        $cachedAuthorList = current($multipleAuthorsForPost);
        $I->assertCount(
            1,
            $cachedAuthorList,
            'Since we are calling for the author page, only the current author is returned'
        );
        $I->assertEquals(
            $this->serializeArrayOfAuthors($cachedAuthorList),
            $this->serializeArrayOfAuthors($returnedAuthorList1)
        );
        $I->assertEquals($author2, $cachedAuthorList[0]);

        // Try calling the method again and double check if we used the cached value.
        $returnedAuthorList1 = get_multiple_authors(0, false, true);
        $I->assertCount(
            2,
            $multipleAuthorsForPost,
            'We are calling the function archive again, but we should not have a new cached value'
        );
        $I->assertEquals(
            $this->serializeArrayOfAuthors($cachedAuthorList),
            $this->serializeArrayOfAuthors($returnedAuthorList1)
        );
    }

    public function testGetMultipleAuthors_WithNoArgumentsForMultiplePosts_isDetectingTheCorrectGlobalPost(WpunitTester $I)
    {
        global $multipleAuthorsForPost;

        $multipleAuthorsForPost = [];

        $userId0 = $I->factory('create user0')->user->create();
        $userId1 = $I->factory('create user1')->user->create();
        $userId2 = $I->factory('create user2')->user->create();

        $author0 = Author::create_from_user($userId0);
        $author1 = Author::create_from_user($userId1);
        $author2 = Author::create_from_user($userId2);

        $postId0 = $I->factory('create post0')->post->create();
        $postId1 = $I->factory('create post1')->post->create();
        $postId2 = $I->factory('create post2')->post->create();

        $post0   = get_post($postId0);
        $post1   = get_post($postId1);
        $post2   = get_post($postId2);

        wp_set_post_terms($postId0, [$author0->term_id, $author1->term_id, $author2->term_id], 'author');
        wp_set_post_terms($postId1, [$author0->term_id], 'author');
        wp_set_post_terms($postId2, [$author0->term_id, $author1->term_id], 'author');

        $GLOBALS['post'] = $post0;
        $authors = get_multiple_authors();

        $I->assertCount(3, $authors);

        $GLOBALS['post'] = $post1;
        $authors = get_multiple_authors();

        $I->assertCount(1, $authors);

        $GLOBALS['post'] = $post2;
        $authors = get_multiple_authors();

        $I->assertCount(2, $authors);
    }
}
