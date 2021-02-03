<?php namespace wordpress;

use MultipleAuthors\Classes\Objects\Author;
use WpunitTester;

class the_postCest
{
    public function tryToGetTheFirstAuthorDataWhenPostHasMultipleAuthors(WpunitTester $I)
    {
        global $post, $authordata;

        $postId = $I->factory('a new post')->post->create(
            [
                'title' => 'A Fake Post'
            ]
        );

        $post = get_post($postId);

        $user1Id = $I->factory('a new user')->user->create(['role' => 'author']);
        $user2Id = $I->factory('a new user')->user->create(['role' => 'author']);
        $user3Id = $I->factory('a new user')->user->create(['role' => 'author']);

        $author1 = Author::create_from_user($user1Id);
        $author2 = Author::create_from_user($user2Id);
        $author3 = Author::create_from_user($user3Id);

        $authordata = $author1->get_user_object();

        wp_set_post_terms(
            $postId,
            [$author1->term_id, $author2->term_id, $author3->term_id],
            'author'
        );

        do_action('the_post', $post);

        $displayName  = get_the_author_meta('display_name');
        $id           = get_the_author_meta('ID');
        $userNicename = get_the_author_meta('user_nicename');

        $I->assertEquals($author1->display_name, $displayName);
        $I->assertEquals($author1->ID, $id);
        $I->assertEquals($author1->user_nicename, $userNicename);
    }
}
