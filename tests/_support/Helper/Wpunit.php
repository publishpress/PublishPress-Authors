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

    public function havePostsWithDifferentAuthors($number)
    {
        $wpLoader = $this->getModule('WPLoader');

        $ids = [];

        for ($i = 0; $i < $number; $i++) {
            $userId = $wpLoader->factory('create a new user')->user->create(
                [
                    'role' => 'author',
                ]
            );

            $ids[] = $wpLoader->factory('create a new post')->post->create(
                [
                    'post_author' => $userId
                ]
            );
        }

        return $ids;
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

    public function getCorePostAuthorFromPosts($postIds)
    {
        $postAuthors = [];

        foreach ($postIds as $postId) {
            $post = get_post($postId);

            $postAuthors[] = (int)$post->post_author;
        }

        return $postAuthors;
    }

    public function assertUsersHaveAuthorTerm($users)
    {
        $failedUsers = [];

        foreach ($users as $userId) {
            $author = Author::get_by_user_id($userId);

            if (empty($author)) {
                $failedUsers[] = (int)$userId;
            }
        }

        if (!empty($failedUsers)) {
            $this->fail(
                sprintf(
                    'Failed asserting the users [%s] have author term',
                    implode(', ', $failedUsers)
                )
            );
        }

        return empty($failedUsers);
    }

    public function assertPostsHaveAuthorTerms($postIds)
    {
        foreach ($postIds as $postId) {
            $post = get_post($postId);
            $postAuthors = wp_get_post_terms($postId, 'author');

            if (count($postAuthors) !== 1) {
                $this->fail(
                    sprintf(
                        'The post %d should have one author term',
                        $postId
                    )
                );

                return false;
            }

            $postAuthorUserId = get_term_meta($postAuthors[0]->term_id, 'user_id', true);

            if ($post->post_author != $postAuthorUserId) {
                $this->fail(
                    sprintf(
                        'The post_author of post %d should be the same as the author term user_id %d, but found %d',
                        $postId,
                        $postAuthors[0]->user_id,
                        $post->post_author
                    )
                );

                return false;
            }
        }

        return true;
    }
}
