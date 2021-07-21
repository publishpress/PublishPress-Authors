<?php namespace wordpress;

use MultipleAuthors\Classes\Objects\Author;

class get_avatar_urlCest
{
    public function tryToGetTheDefaultAvatarURLForAnAuthorMappedToUserWithoutCustomAvatar(\WpunitTester $I)
    {
        $userEmail = sprintf('user_%s@example.com', time());
        $emailHash = md5(strtolower(trim($userEmail)));
        $userID = $I->factory('a new user')->user->create(['role' => 'author', 'user_email' => $userEmail]);
        $author = Author::create_from_user($userID);
        $gravatarServer = hexdec($emailHash[0]) % 3;

        $avatarUrl = get_avatar_url($userID);

        $I->assertEquals(
            sprintf('http://%d.gravatar.com/avatar/%s?s=96&d=mm&r=g', $gravatarServer, $emailHash),
            $avatarUrl
        );
    }

    public function tryToGetTheAvatarURLForAnAuthorMappedToUserWithCustomAvatar(\WpunitTester $I)
    {
        $userID = $I->factory('a user')->user->create(['role' => 'author']);

        $author = Author::create_from_user($userID);

        $attachmentId = $I->factory('an attachment')->attachment->create_upload_object(
            realpath(TESTS_ROOT_PATH . '/_data/avatar1.png')
        );
        update_term_meta($author->term_id, 'avatar', $attachmentId);
        $expectedAvatarUrl = wp_get_attachment_image_url($attachmentId, 96);

        $avatarUrl = get_avatar_url($userID);

        $I->assertEquals(
            $expectedAvatarUrl,
            $avatarUrl
        );
    }

    public function tryToGetTheAvatarURLForAGuestAuthor(\WpunitTester $I)
    {
        $author = $I->createGuestAuthor();

        $attachmentId = $I->factory('a new attachment for the avatar image')->attachment->create_upload_object(
            realpath(TESTS_ROOT_PATH . '/_data/avatar1.png')
        );
        update_term_meta($author->term_id, 'avatar', $attachmentId);
        $expectedAvatarUrl = wp_get_attachment_image_url($attachmentId, 96);

        $avatarUrl = get_avatar_url($author->ID);

        $I->assertEquals(
            $expectedAvatarUrl,
            $avatarUrl
        );
    }
}
