<?php namespace core\Classes\Objects;

use MultipleAuthors\Classes\Author_Utils;
use MultipleAuthors\Classes\Objects\Author;

class Author_UtilsCest
{
    public function methodGet_author_term_id_by_emailShouldReturnTheAuthorWithTheGivenEmailWhenAuthorExists(\WpunitTester $I)
    {
        $userID = $I->factory('a new user')->user->create(['role' => 'author']);
        $author = Author::create_from_user($userID);

        $user = get_user_by('ID', $userID);

        $foundTermId = Author_Utils::get_author_term_id_by_email($user->user_email);

        $I->assertEquals($author->term_id, $foundTermId);
    }

    public function methodGet_author_term_id_by_emailShouldReturnTheGuestAuthorWithTheGivenEmailWhenAuthorExists(\WpunitTester $I)
    {
        $authorSlug = sprintf('guest_author_%s', rand(1, PHP_INT_MAX));
        $author = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );

        $email = sprintf('%s@example.com', $authorSlug);

        update_term_meta($author->term_id, 'user_email', $email);

        $foundTermId = Author_Utils::get_author_term_id_by_email($author->user_email);

        $I->assertEquals($author->term_id, $foundTermId);
    }

    public function methodAuthor_has_custom_avatarShouldReturnFalseIfAuthorDontHaveAvatar(\WpunitTester $I)
    {
        $userID = $I->factory('a new user')->user->create(['role' => 'author']);
        $author = Author::create_from_user($userID);

        $hasAvatar = Author_Utils::author_has_custom_avatar($author->term_id);

        $I->assertFalse($hasAvatar);
    }

    public function methodAuthor_has_custom_avatarShouldReturnTrueIfAuthorHasAnAvatar(\WpunitTester $I)
    {
        $userID = $I->factory('a new user')->user->create(['role' => 'author']);
        $author = Author::create_from_user($userID);

        update_term_meta($author->term_id, 'avatar', '234');

        $hasAvatar = Author_Utils::author_has_custom_avatar($author->term_id);

        $I->assertTrue($hasAvatar);
    }

    public function methodGet_author_metaReturnsValidSingleMeta(\WpunitTester $I)
    {
        $authorSlug = sprintf('guest_author_%s', rand(1, PHP_INT_MAX));
        $author = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );

        $metaKey = 'the_meta';
        $expected = 'Ha9d8sg76A%S%';

        update_term_meta($author->term_id, $metaKey, $expected);

        $meta = Author_Utils::get_author_meta($author->term_id, $metaKey, true);

        $I->assertEquals($expected, $meta);
    }

    public function methodGet_author_metaReturnsValidMultipleMeta(\WpunitTester $I)
    {
        $authorSlug = sprintf('guest_author_%s', rand(1, PHP_INT_MAX));
        $author = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );

        $metaKey = 'the_meta';
        $expected = [
            'value1',
            'value2',
            'value3',
        ];

        add_term_meta($author->term_id, $metaKey, $expected[0]);
        add_term_meta($author->term_id, $metaKey, $expected[1]);
        add_term_meta($author->term_id, $metaKey, $expected[2]);

        $meta = Author_Utils::get_author_meta($author->term_id, $metaKey, false);

        $I->assertIsArray($meta);
        $I->assertEquals($expected, $meta);
    }

    public function methodAuthor_is_guestShouldReturnTrueForGuestAuthor(\WpunitTester $I)
    {
        $authorSlug = sprintf('guest_author_%s', rand(1, PHP_INT_MAX));
        $author = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );

        $isGuest = Author_Utils::author_is_guest($author->term_id);
        $I->assertTrue($isGuest);
    }

    public function methodAuthor_is_guestShouldReturnFalseForAuthorMappedToUser(\WpunitTester $I)
    {
        $userID = $I->factory('a new user')->user->create(['role' => 'author']);
        $author = Author::create_from_user($userID);

        $isGuest = Author_Utils::author_is_guest($author->term_id);
        $I->assertFalse($isGuest);
    }

    public function methodGet_avatar_urlShouldReturnTheAvatarUrlForGuestAuthor(\WpunitTester $I)
    {
        $authorSlug = sprintf('guest_author_%s', rand(1, PHP_INT_MAX));
        $author = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );

        $email = 'imaguest3@example.com';
        update_term_meta($author->term_id, 'user_email', $email);

        $attachmentId = $I->factory('a new attachment for the avatar image')->attachment->create_upload_object(realpath(TESTS_ROOT_PATH . '/_data/avatar1.png'));
        update_term_meta($author->term_id, 'avatar', $attachmentId);
        $expectedAvatarUrl = wp_get_attachment_image_url($attachmentId, 96);

        $avatarUrl = Author_Utils::get_avatar_url($author->term_id);

        $I->assertEquals(
            $expectedAvatarUrl,
            $avatarUrl
        );
    }

    public function methodGet_avatar_urlShouldReturnTheCustomAvatarUrlAuthorMappedToUserWithCustomAvatar(\WpunitTester $I)
    {
        $userID = $I->factory('a new user')->user->create(['role' => 'author']);
        $author = Author::create_from_user($userID);

        $attachmentId = $I->factory('a new attachment for the avatar image')->attachment->create_upload_object(realpath(TESTS_ROOT_PATH . '/_data/avatar1.png'));
        update_term_meta($author->term_id, 'avatar', $attachmentId);
        $expectedAvatarUrl = wp_get_attachment_image_url($attachmentId, 96);

        $avatarUrl = Author_Utils::get_avatar_url($author->term_id);

        $I->assertEquals(
            $expectedAvatarUrl,
            $avatarUrl
        );
    }

    public function methodGet_avatar_urlShouldReturnFalseForAuthorMappedToUserWithNoCustomAvatar(\WpunitTester $I)
    {
        $userID = $I->factory('a new user')->user->create(['role' => 'author']);
        $author = Author::create_from_user($userID);

        $avatarUrl = Author_Utils::get_avatar_url($author->term_id);

        $I->assertFalse($avatarUrl);
    }
}
