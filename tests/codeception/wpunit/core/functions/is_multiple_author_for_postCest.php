<?php namespace core\Classes\Objects;

use MultipleAuthors\Classes\Objects\Author;
use WpunitTester;

class is_multiple_author_for_postCest
{
    public function testIsMultipleAuthorForPost_ForAUserThatIsTheAuthorButHasNoAuthorTerm_ShouldReturnTrue(
        WpunitTester $I
    ) {
        $userId = $I->factory('create user0')->user->create();

        $postId = $I->factory('create post0')->post->create(
            ['post_type' => 'post', 'post_author' => $userId]
        );

        $isAuthor = is_multiple_author_for_post($userId, $postId);

        $I->assertTrue($isAuthor);
    }

    public function testIsMultipleAuthorForPost_ForAUserThatIsTheAuthor_ShouldReturnTrue(WpunitTester $I)
    {
        $userId0 = $I->factory('create user0')->user->create();
        $userId1 = $I->factory('create user1')->user->create();

        $author0 = Author::create_from_user($userId0);
        $author1 = Author::create_from_user($userId1);

        $postId = $I->factory('create post0')->post->create(
            ['post_type' => 'post', 'post_author' => $userId0]
        );

        wp_set_post_terms($postId, [$author0->term_id, $author1->term_id], 'author');

        $I->assertTrue(is_multiple_author_for_post($userId0, $postId));
        $I->assertTrue(is_multiple_author_for_post($userId1, $postId));
    }

    public function testIsMultipleAuthorForPost_ForAUserThatIsNotTheAuthor_ShouldReturnFalse(WpunitTester $I)
    {
        global $wpdb;

        $userId0 = $I->factory('create user0')->user->create();
        $userId1 = $I->factory('create user1')->user->create();

        $author0 = Author::create_from_user($userId0);
        $author1 = Author::create_from_user($userId1);

        $postId = $I->factory('create post0')->post->create(
            ['post_type' => 'post', 'post_author' => $userId0]
        );

        wp_set_post_terms($postId, [$author0->term_id], 'author');

        $I->assertFalse(is_multiple_author_for_post($userId1, $postId));
    }
}
