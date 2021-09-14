<?php
namespace Helper;

trait PermalinkTrait
{
    /**
     * Define custom actions here
     */
    public function setPermalinkStructure($structure)
    {
        global $wp_rewrite;

        $wp_rewrite->init();
        $wp_rewrite->set_permalink_structure($structure);
        $wp_rewrite->flush_rules(true);
    }

    public function getRelativePostPermalink($postId)
    {
        $permalink = get_permalink($postId);

        return preg_replace("#https?://{$_ENV['TEST_SITE_WP_DOMAIN']}#", '', $permalink);
    }

    public function getRelativeAuthorLink($author)
    {
        return preg_replace("#https?://{$_ENV['TEST_SITE_WP_DOMAIN']}#", '', $author->link);
    }
}
