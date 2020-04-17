<?php


namespace PPAuthors\YoastSEO;


use WPSEO_Schema_Utils;

abstract class SchemaUtils
{
    /**
     * The fallback for the conditional in case the method add_piece_language is not found was implemented based on the
     * code found in the Yoast SEO Premium 13.4.1
     *
     * @param array $data
     *
     * @return array
     */
    public static function addPieceLanguage($data)
    {
        if (class_exists('\\WPSEO_Schema_Utils') && method_exists('\\WPSEO_Schema_Utils', 'add_piece_language')) {
            return WPSEO_Schema_Utils::add_piece_language($data);
        } else {
            /**
             * Filter: 'wpseo_schema_piece_language' - Allow changing the Schema piece language.
             *
             * @api string $type The Schema piece language.
             */
            $data['inLanguage'] = apply_filters('wpseo_schema_piece_language', get_bloginfo('language'), $data);
        }
    }
}