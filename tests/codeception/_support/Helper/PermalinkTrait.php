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
}
