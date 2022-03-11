<?php

namespace core\Classes\Objects;

use MultipleAuthors\Classes\Objects\Author;
use WpunitTester;

use function sq;

class get_archive_authorCest
{
    private function setArchiveAuthorAsId($authorId)
    {
        global $wp_query;
        $wp_query->is_author = true;
        set_query_var('author_name', '');
        set_query_var('author', $authorId);
    }

    private function setArchiveAuthorAsSlug($authorSlug)
    {
        global $wp_query;
        $wp_query->is_author = true;
        set_query_var('author_name', $authorSlug);
        set_query_var('author', 0);
    }

    private function setIsNotAuthor()
    {
        global $wp_query;
        $wp_query->is_author = false;
        set_query_var('author_name', '');
        set_query_var('author', 0);
    }

    private function setPlainPermalink()
    {
        global $wp_rewrite;

        $wp_rewrite->init();
        $wp_rewrite->set_permalink_structure(false);
        $wp_rewrite->flush_rules(true);
    }

    private function setPostNamePermalink()
    {
        global $wp_rewrite;

        $wp_rewrite->init();
        $wp_rewrite->set_permalink_structure('/%postname%/');
        $wp_rewrite->flush_rules(true);
    }

    public function testGetArchiveAuthor_OnNotAuthorPage_ShouldReturnFalse(WpunitTester $I)
    {
        $this->setIsNotAuthor();

        $returnedAuthor = get_archive_author();

        $I->assertFalse($returnedAuthor);
    }

    public function testGetArchiveAuthor_OnAuthorPageWithPlainPermalinkAndAuthorMappedToUser_ShouldReturnCurrentAuthor(WpunitTester $I)
    {
        $userId0 = $I->factory('create user0')->user->create();
        $author0 = Author::create_from_user($userId0);

        $this->setPlainPermalink();
        $this->setArchiveAuthorAsId($userId0);

        $returnedAuthor = get_archive_author();

        $I->assertInstanceOf(Author::class, $returnedAuthor);
        $I->assertEquals($author0->term_id, $returnedAuthor->term_id);
    }

    public function testGetArchiveAuthor_OnAuthorPageWithPlainPermalinkAndGuestAuthor_ShouldReturnCurrentAuthor(WpunitTester $I)
    {
        $author0 = Author::create(
            [
                'slug'         => sq('guest_author'),
                'display_name' => sq('Guest Author'),
            ]
        );

        $this->setPlainPermalink();
        $this->setArchiveAuthorAsSlug($author0->slug);

        $returnedAuthor = get_archive_author();

        $I->assertInstanceOf(Author::class, $returnedAuthor);
        $I->assertEquals($author0->term_id, $returnedAuthor->term_id);
    }

    public function testGetArchiveAuthor_OnAuthorPageWithPostNamePermalinkAndAuthorMappedToUser_ShouldReturnCurrentAuthor(WpunitTester $I)
    {
        $userId0 = $I->factory('create user0')->user->create();
        $author0 = Author::create_from_user($userId0);

        $this->setPostNamePermalink();
        $this->setArchiveAuthorAsSlug($author0->slug);

        $returnedAuthor = get_archive_author();

        $I->assertInstanceOf(Author::class, $returnedAuthor);
        $I->assertEquals($author0->term_id, $returnedAuthor->term_id);
    }

    public function testGetArchiveAuthor_OnAuthorPageWithPostNamePermalinkAndGuestAuthor_ShouldReturnCurrentAuthor(WpunitTester $I)
    {
        $author0 = Author::create(
            [
                'slug'         => sq('guest_author'),
                'display_name' => sq('Guest Author'),
            ]
        );

        $this->setPostNamePermalink();
        $this->setArchiveAuthorAsSlug($author0->slug);

        $returnedAuthor = get_archive_author();

        $I->assertInstanceOf(Author::class, $returnedAuthor);
        $I->assertEquals($author0->term_id, $returnedAuthor->term_id);
    }
}
