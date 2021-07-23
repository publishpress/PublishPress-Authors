<?php

namespace Steps;


use MultipleAuthors\Classes\Objects\Author;
use MultipleAuthors\Classes\Utils;

trait Posts
{
    /**
     * @Given a post named :postName exists for :authorSlug
     */
    public function aPostNamedExistsForAuthor($postName, $authorSlug)
    {
        $author = Author::get_by_term_slug($authorSlug);

        $postId = $this->factory()->post->create(
            [
                'post_title'  => $postName,
                'post_name'   => $postName,
                'post_author' => $author->user_id,
            ]
        );

        Utils::set_post_authors($postId, [$author]);
    }

    /**
     * @Given a post named :postName exists for :author1Slug and :author2Slug
     */
    public function aPostNamedExistsForAuthor1AndAuthor2($postName, $author1Slug, $author2Slug)
    {
        $author1 = Author::get_by_term_slug($author1Slug);
        $author2 = Author::get_by_term_slug($author2Slug);

        $postId = $this->factory()->post->create(
            [
                'post_title'  => $postName,
                'post_name'   => $postName,
                'post_author' => $author1->user_id,
            ]
        );

        Utils::set_post_authors($postId, [$author1, $author2]);
    }

    /**
     * @Then I see :userSlug as the author of the post :postSlug
     */
    public function iSeeUserIsTheAuthorForThePost($userSlug, $postSlug)
    {
        $post = get_page_by_path($postSlug, OBJECT, 'post');
        $user = get_user_by('nicename', $userSlug);

        $this->seeElement("#post-{$post->ID} td.authors a[data-author-slug=\"{$userSlug}\"]");
    }

    /**
     * @Then I don't see a post with title :postSlug
     */
    public function iDonSeeAPostWithTitle($posTitle)
    {
        $this->dontSee($posTitle);
    }

    /**
     * @Then I see a post with title :postSlug
     */
    public function iSeeAPostWithTitle($posTitle)
    {
        $this->see($posTitle);
    }
}
