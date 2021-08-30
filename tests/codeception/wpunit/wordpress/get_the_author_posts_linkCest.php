<?php namespace wordpress;

use MultipleAuthors\Classes\Objects\Author;
use MultipleAuthors\Classes\Utils;

class get_the_author_posts_linkCest
{
    public function tryToGetMultipleAuthorPostsLinksForPostWithMultipleAuthors(\WpunitTester $I)
    {
        $I->setPermalinkStructure('/%postname%/');

        $postId = $I->factory('a new post')->post->create();

        $post = get_post($postId);
        $GLOBALS['post'] = $post;

        $user1Id = $I->factory('a new user')->user->create(['role' => 'author']);
        $user2Id = $I->factory('a new user')->user->create(['role' => 'author']);
        $user3Id = $I->factory('a new user')->user->create(['role' => 'author']);

        $author1 = Author::create_from_user($user1Id);
        $author2 = Author::create_from_user($user2Id);
        $author3 = Author::create_from_user($user3Id);

        Utils::set_post_authors($postId, [$author1, $author2, $author3]);

        // We need to initialize authordata otherwise the link is always empty.
        global $authordata;
        $authordata = get_user_by('ID', $user1Id);

        $postsLink = get_the_author_posts_link();

        $I->assertEquals(
            sprintf(
                '<a href="http://%7$s/author/%1$s/" title="%2$s" rel="author" itemprop="author" itemscope="itemscope" itemtype="https://schema.org/Person">%2$s</a>, ' .
                '<a href="http://%7$s/author/%3$s/" title="%4$s" rel="author" itemprop="author" itemscope="itemscope" itemtype="https://schema.org/Person">%4$s</a>, ' .
                '<a href="http://%7$s/author/%5$s/" title="%6$s" rel="author" itemprop="author" itemscope="itemscope" itemtype="https://schema.org/Person">%6$s</a>',
                $author1->slug,
                $author1->display_name,
                $author2->slug,
                $author2->display_name,
                $author3->slug,
                $author3->display_name,
                $_ENV['TEST_SITE_WP_DOMAIN']
            ),
            $postsLink
        );
    }

    public function tryToGetMultipleAuthorPostsLinksForPostWithMultipleAuthorsIncludingGuestAuthors(\WpunitTester $I)
    {
        $I->setPermalinkStructure('/%postname%/');

        $postId = $I->factory('a new post')->post->create(
            [
                'title' => 'A Fake Post'
            ]
        );

        $post = get_post($postId);
        $GLOBALS['post'] = $post;

        $user1Id = $I->factory('a new user')->user->create(['role' => 'author']);

        $author1 = Author::create_from_user($user1Id);

        $authorSlug = sprintf('guest_author_%d_%s', 2, rand(1, PHP_INT_MAX));
        $author2 = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );

        $authorSlug = sprintf('guest_author_%d_%s', 2, rand(1, PHP_INT_MAX));
        $author3 = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );

        Utils::set_post_authors($postId, [$author1, $author2, $author3]);

        // We need to initialize authordata otherwise the link is always empty.
        global $authordata;
        $authordata = get_user_by('ID', $user1Id);

        $postsLink = get_the_author_posts_link();

        $I->assertEquals(
            sprintf(
                '<a href="http://%7$s/author/%1$s/" title="%2$s" rel="author" itemprop="author" itemscope="itemscope" itemtype="https://schema.org/Person">%2$s</a>, ' .
                '<a href="http://%7$s/author/%3$s/" title="%4$s" rel="author" itemprop="author" itemscope="itemscope" itemtype="https://schema.org/Person">%4$s</a>, ' .
                '<a href="http://%7$s/author/%5$s/" title="%6$s" rel="author" itemprop="author" itemscope="itemscope" itemtype="https://schema.org/Person">%6$s</a>',
                $author1->slug,
                $author1->display_name,
                $author2->slug,
                $author2->display_name,
                $author3->slug,
                $author3->display_name,
                $_ENV['TEST_SITE_WP_DOMAIN']
            ),
            $postsLink
        );
    }
}
