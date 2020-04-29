<?php namespace wordpress;

use MultipleAuthors\Classes\Objects\Author;

class feed_links_extraCest
{
    public function tryToCallFeed_links_extraFromWp_headActionHookAndCheckIfItWorksForGuestAuthors(\WpunitTester $I)
    {
        global $wp_query, $wp_rewrite, $wp_filter;

        $authorSlug = sprintf('guest_author_%s', rand(1, PHP_INT_MAX));
        $authorName = strtoupper($authorSlug);

        $author = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => $authorName,
            ]
        );

        // Force "is_author"
        $wp_query->is_author                 = true;
        $wp_query->query_vars['author_name'] = $authorSlug;

        $I->assertArrayHasKey(
            'MultipleAuthors\Classes\Query::fix_query_pre_get_posts',
            $wp_filter['wp_head'][1],
            'We need a call to fix_query_pre_get_posts in the wp_head hook with priority 1'
        );

        // Backup the required hook and do a clean up.
        $hookToFixTheAuthorQuery = $wp_filter['wp_head'][1]['MultipleAuthors\Classes\Query::fix_query_pre_get_posts'];
        remove_all_actions('wp_head');
        // Restore the required hook.
        add_action('wp_head', ['\\MultipleAuthors\\Classes\\Query', 'fix_query_pre_get_posts'], 1);

        // Add the hook with the call to the function we want to test.
        add_action(
            'wp_head',
            function () {
                echo feed_links_extra();
            },
            3
        );

        ob_start();
        do_action('wp_head');
        $output = ob_get_clean();

        $I->assertNotEmpty($output, 'The output can\'t be empty because the feed link should be printed on it');
        $I->assertStringContainsString(
            sprintf(
                '<link rel="alternate" type="application/rss+xml" title="Test &raquo; Posts by %s Feed" href="http://localhost/?feed=rss2&#038;author=%s" />',
                $authorName,
                $author->ID
            ),
            $output,
            'The output should contains the feed link for the author'
        );
    }
}
