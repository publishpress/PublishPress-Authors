<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use MultipleAuthors\Classes\Objects\Author;
use MultipleAuthors\Classes\Utils;
use MultipleAuthors\Factory;

class Wpunit extends \Codeception\Module
{
    use PermalinkTrait;

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
            $userId = $wpLoader->factory('a new user' . $currentNumber)->user->create();

            $authors[$currentNumber] = Author::create_from_user($userId);
        }

        return $authors;
    }

    public function haveAUser()
    {
        $wpLoader = $this->getModule('WPLoader');

        return $wpLoader->factory('a new user')->user->create();
    }

    public function setPluginSettingsPostTypes($postTypes)
    {
        $legacyPlugin = Factory::getLegacyPlugin();

        $legacyPlugin->modules->multiple_authors->options->post_types = [];
        foreach ($postTypes as $postType) {
            $legacyPlugin->modules->multiple_authors->options->post_types[$postType] = 'on';
        }
    }

    public function setPluginSettingsAuthorForNewUsers($roles)
    {
        $legacyPlugin = Factory::getLegacyPlugin();

        $legacyPlugin->modules->multiple_authors->options->author_for_new_users = $roles;
    }

    public function getMultipleAuthorsForPost($postId)
    {
        return get_multiple_authors($postId);
    }

    public function getAuthorByUserId($userId)
    {
        return Author::get_by_user_id($userId);
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

    public function havePostsWithDifferentAuthors($number, $postType = 'post')
    {
        $wpLoader = $this->getModule('WPLoader');

        $ids = [];

        for ($i = 0; $i < $number; $i++) {
            $userId = $wpLoader->factory('a new user')->user->create(
                [
                    'role' => 'author',
                ]
            );

            $ids[] = $wpLoader->factory('a new post')->post->create(
                [
                    'post_author' => $userId,
                    'post_type'   => $postType,
                ]
            );
        }

        return $ids;
    }

    public function haveAPost()
    {
        $wpLoader = $this->getModule('WPLoader');

        return $wpLoader->factory('a new post')->post->create();
    }

    public function haveAPageForUser($userId)
    {
        $wpLoader = $this->getModule('WPLoader');

        return $wpLoader->factory('a new page')->post->create(
            ['post_type' => 'page', 'post_author' => $userId]
        );
    }

    public function haveAPostForUser($userId)
    {
        $wpLoader = $this->getModule('WPLoader');

        return $wpLoader->factory('a new post')->post->create(
            ['post_type' => 'post', 'post_author' => $userId]
        );
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
            $post        = get_post($postId);
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
            }
        }

        return true;
    }

    public function assertPostsDontHaveAuthorTerms($postIds)
    {
        foreach ($postIds as $postId) {
            $post        = get_post($postId);
            $postAuthors = wp_get_post_terms($postId, 'author');

            if (count($postAuthors) > 0) {
                $this->fail(
                    sprintf(
                        'The post %d should not have one author term',
                        $postId
                    )
                );
            }
        }

        return true;
    }

    public function makeSurePostDoesntHaveAuthorPosts($postId)
    {
        $authors = wp_get_post_terms($postId, 'author');
        $authorTermIDs = [];

        foreach ($authors as $author) {
            $authorTermIDs[] = $author->term_id;
        }

        wp_remove_object_terms($postId, $authorTermIDs, 'author');
    }

    public function haveAuthorTermsForPosts($postIds)
    {
        foreach ($postIds as $postId) {
            $post = get_post($postId);

            $author = Author::create_from_user($post->post_author);

            Utils::set_post_authors($postId, [$author]);
        }
    }

    public function resetTheDatabase()
    {
        global $wpdb;

        $wpdb->query("TRUNCATE TABLE {$wpdb->commentmeta}");
        $wpdb->query("TRUNCATE TABLE {$wpdb->comments}");
        $wpdb->query("TRUNCATE TABLE {$wpdb->postmeta}");
        $wpdb->query("TRUNCATE TABLE {$wpdb->term_relationships}");
        $wpdb->query("TRUNCATE TABLE {$wpdb->posts}");
        $wpdb->query("TRUNCATE TABLE {$wpdb->links}");
        $wpdb->query("DELETE FROM {$wpdb->termmeta} WHERE term_id > 1");
        $wpdb->query("DELETE FROM {$wpdb->terms} WHERE term_id > 1");
        $wpdb->query("DELETE FROM {$wpdb->users} WHERE ID > 1");
        $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE user_id > 1");
    }

    public function echoLastQuery()
    {
        global $wpdb;

        echo $wpdb->last_query;
    }
}
