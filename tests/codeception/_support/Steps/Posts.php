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

    /**
     * @When I edit the post name :postSlug
     */
    public function iEditPostNamed($postSlug)
    {
        $posts = get_posts(
            [
                'name'           => $postSlug,
                'posts_per_page' => 1,
                'post_type'      => 'post'
            ]
        );

        $this->amEditingPostWithId($posts[0]->ID);
        $this->makeScreenshot('Editing post');
    }

    /**
     * @When I view the post :postSlug
     */
    public function iViewThePost($postSlug)
    {
        $this->amOnPage('/' . $postSlug);
    }

    /**
     * @Then I don't see the post locked modal
     */
    public function iDontSeePostLockedModal()
    {
        $this->dontSeeElementInDOM('.editor-post-locked-modal');
        $this->dontSee('This post is already being edited');
    }

    /**
     * @Then I see the post visual editor
     */
    public function iSeePostVisualEditor()
    {
        $this->dontSeeElement('.edit-post-text-editor');
        $this->seeElement('.edit-post-visual-editor');
    }

    /**
     * @Then I can add blocks in the editor
     */
    public function iCanAddBlocksInTheEditor()
    {
        $paragraphText = 'hello awesome world!';

        $this->executeJS('const block = wp.blocks.createBlock("core/paragraph", {content: "' . $paragraphText . '"} ); wp.data.dispatch("core/editor").insertBlocks(block);');
        $blocks = $this->executeJS('return wp.data.select("core/editor").getBlocks()');

        $this->assertNotEmpty($blocks) ;

        $foundTheBlock = false;
        foreach ($blocks as $block) {
            if ('hello awesome world!' === $block['attributes']['content']) {
                $foundTheBlock = true;
            }
        }

        $this->assertTrue($foundTheBlock, 'We need to see the added block in the editor');
    }
}
