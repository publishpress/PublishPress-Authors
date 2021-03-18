<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use MultipleAuthors\Classes\Objects\Author;
use MultipleAuthors\Classes\Utils;

class Wpunit extends \Codeception\Module
{
    public function createGuestAuthor()
    {
        $authorSlug = sprintf('guest_author_%s', rand(1, PHP_INT_MAX));

        return Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );
    }

    public function haveAuthorsMappedToUsers($number)
    {
        $wpLoader = $this->getModule('WPLoader');

        $authors = [];
        for ($currentNumber = 0; $currentNumber < $number; $currentNumber++) {
            $userId = $wpLoader->factory('create user' . $currentNumber)->user->create();

            $authors[$currentNumber] = Author::create_from_user($userId);
        }

        return $authors;
    }

    public function haveGuestAuthors($number, $metaData = null)
    {
        $authors = [];
        for ($currentNumber = 0; $currentNumber < $number; $currentNumber++) {
            $author = Author::create(
                [
                    'slug'         => 'author' . $currentNumber . '_' . microtime(),
                    'display_name' => 'Author ' . $currentNumber . ' ' . microtime(),
                ]
            );

            if (!empty($metaData) && is_array($metaData)) {
                foreach ($metaData as $metaDataKey => $metaDataValue) {
                    update_term_meta(
                        $author->term_id,
                        $metaDataKey,
                        sprintf($metaDataValue, $currentNumber)
                    );
                }
            }

            $authors[$currentNumber] = $author;
        }

        return $authors;
    }

    public function haveAPost()
    {
        $wpLoader = $this->getModule('WPLoader');

        return $wpLoader->factory('create a new post')->post->create();
    }

    public function getPostAuthors($postId)
    {
        return wp_get_post_terms($postId, 'author');
    }

    public function haveAPostWithAuthors($authorsList)
    {
        $postId = $this->haveAPost();

        Utils::set_post_authors($postId, $authorsList);

        return $postId;
    }
}
