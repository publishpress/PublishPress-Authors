<?php namespace core\Classes\Legacy;

use Codeception\Example;
use MultipleAuthors\Classes\Legacy\Util;
use WP_Query;
use WP_Screen;
use WpunitTester;

class UtilCest
{
    /**
     * @before cleanup
     */
    public function testGetPostPostType_WithExistingPostId_ReturnsThePostTypeForThePost(WpunitTester $I)
    {
        $expectedPostType = 'page';

        $postId = $I->factory('a new post')->post->create(['post_type' => $expectedPostType]);

        $postType = Util::getPostPostType($postId);

        $I->assertEquals($expectedPostType, $postType);
    }

    /**
     * @before cleanup
     */
    public function testGetPostPostType_WithExistingPost_ReturnsThePostTypeForThePost(WpunitTester $I)
    {
        $expectedPostType = 'page';

        $newPostId = $I->factory('a new post')->post->create(['post_type' => $expectedPostType]);
        $newPost   = get_post($newPostId);

        $postType = Util::getPostPostType($newPost);

        $I->assertEquals($expectedPostType, $postType);
    }

    /**
     * @example [9999999999]
     * @example [0]
     * @example [""]
     * @example ["0"]
     * @example [-1]
     * @example ["-1"]
     * @example [false]
     * @example [null]
     *
     * @before cleanup
     */
    public function testGetPostPostType_WithNonExistingPostId_ReturnsFalse(WpunitTester $I, Example $example)
    {
        $postType = Util::getPostPostType($example[0]);

        $I->assertFalse($postType);
    }

    /**
     * @before cleanup
     */
    public function testGetCurrentPostType_ForPostInTheGlobalVar_ReturnsPostPostType(WpunitTester $I)
    {
        $expectedPostType = 'page';

        $newPostId = $I->factory('a new post')->post->create(['post_type' => $expectedPostType]);
        $newPost   = get_post($newPostId);

        global $post;
        $post = $newPost;

        $postType = Util::getCurrentPostType();

        $I->assertEquals($expectedPostType, $postType);
    }

    /**
     * @before cleanup
     */
    public function testGetCurrentPostType_ForTypenowGlobalVar_ReturnsPostPostType(WpunitTester $I)
    {
        global $typenow;

        $typenow = 'page';

        $postType = Util::getCurrentPostType();

        $I->assertEquals($typenow, $postType);
    }

    /**
     * @before cleanup
     */
    public function testGetCurrentPostType_ForCurrentScreenPostType_ReturnsPostPostType(WpunitTester $I)
    {
        global $current_screen;

        $expectedPostType = 'page';

        $current_screen            = WP_Screen::get('pp_authors_test');
        $current_screen->post_type = $expectedPostType;

        $postType = Util::getCurrentPostType();

        $I->assertEquals($expectedPostType, $postType);
    }

    /**
     * @before cleanup
     */
    public function testGetCurrentPostType_ForRequestVar_ReturnsPostPostType(WpunitTester $I)
    {
        $expectedPostType = 'page';

        $_REQUEST['post_type'] = $expectedPostType;

        $postType = Util::getCurrentPostType();

        $I->assertEquals($expectedPostType, $postType);
    }

    /**
     * @before cleanup
     */
    public function testGetCurrentPostType_ForPostPagenow_ReturnsPostPostType(WpunitTester $I)
    {
        global $pagenow;

        $pagenow          = 'post.php';
        $expectedPostType = 'page';

        $_REQUEST['post'] = $I->factory('a new post')->post->create(['post_type' => $expectedPostType]);

        $postType = Util::getCurrentPostType();

        $I->assertEquals($expectedPostType, $postType);
    }

    /**
     * @before cleanup
     */
    public function testGetCurrentPostType_ForEditPagenowWithNoGetParam_ReturnsPost(WpunitTester $I)
    {
        global $pagenow;

        $pagenow = 'edit.php';

        $postType = Util::getCurrentPostType();

        $I->assertEquals('post', $postType);
    }

    /**
     * @before cleanup
     */
    public function testGetCurrentPostType_ForAuthorPage_ReturnsPost(WpunitTester $I)
    {
        global $wp_query;

        $wp_query            = new WP_Query();
        $wp_query->is_author = true;

        $postType = Util::getCurrentPostType();

        $I->assertEquals('post', $postType);
    }

    protected function cleanUp(WpunitTester $I)
    {
        global $post, $typenow, $pagenow, $current_screen;

        $post           = null;
        $typenow        = null;
        $pagenow        = null;
        $current_screen = null;

        $_REQUEST['post_type'] = null;
        $_REQUEST['post']      = null;
    }
}
