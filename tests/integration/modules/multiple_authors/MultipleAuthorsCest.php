<?php namespace modules\multiple_authors;

use MultipleAuthors\Classes\Objects\Author;
use WpunitTester;

class MultipleAuthorsCest
{
    public function testActionSetAuthors_withMultipleAuthorTerms_shouldAddRelationshipBetweenThePostAndAuthors(
        WpunitTester $I
    ) {
        $userId0 = $I->factory('create user0')->user->create();
        $userId1 = $I->factory('create user1')->user->create();
        $userId2 = $I->factory('create user2')->user->create();

        $author0 = Author::create_from_user($userId0);
        $author1 = Author::create_from_user($userId1);
        $author2 = Author::create_from_user($userId2);

        $postId = $I->factory('create a new post')->post->create();

        $authorsList = [$author0, $author1, $author2];
        do_action('publishpress_authors_set_post_authors', $postId, $authorsList);

        $postAuthors = wp_get_post_terms($postId, 'author');

        $I->assertIsArray($postAuthors);
        $I->assertCount(3, $postAuthors);
        $I->assertEquals($author0->term_id, $postAuthors[0]->term_id);
        $I->assertEquals($author1->term_id, $postAuthors[1]->term_id);
        $I->assertEquals($author2->term_id, $postAuthors[2]->term_id);
    }
}
