<?php namespace modules\multiple_authors;

use MultipleAuthors\Factory;
use WpunitTester;

class MultipleAuthorsCest
{
    private function getMultipleAuthorsModule()
    {
        $container = Factory::get_container();

        return $container['module'];
    }

    public function testActionSetAuthors_withMultipleAuthorTerms_shouldAddRelationshipBetweenThePostAndAuthors(
        WpunitTester $I
    ) {
        $authors = $I->haveAuthorsMappedToUsers(3);
        $postId  = $I->haveAPost();

        do_action(
            'publishpress_authors_set_post_authors',
            $postId,
            [$authors[0], $authors[1], $authors[2]]
        );

        $postAuthors = $I->getPostAuthors($postId);

        $I->assertIsArray($postAuthors);
        $I->assertCount(3, $postAuthors);
        $I->assertEquals($authors[0]->term_id, $postAuthors[0]->term_id);
        $I->assertEquals($authors[1]->term_id, $postAuthors[1]->term_id);
        $I->assertEquals($authors[2]->term_id, $postAuthors[2]->term_id);
    }

    public function testFilterWorkflowReceiverPostAuthors_withPostWithAuthorsMappedToUser_shouldAddUserIdsToTheReceiversList(
        WpunitTester $I
    ) {
        $authors = $I->haveAuthorsMappedToUsers(3);
        $postId  = $I->haveAPostWithAuthors($authors);

        $args = [
            'params' => [
                'post_id' => $postId
            ]
        ];

        $receivers = $this->getMultipleAuthorsModule()->filter_workflow_receiver_post_authors([99], 0, $args);

        $I->assertCount(4, $receivers);
        $I->assertEquals(
            [
                99,
                $authors[0]->user_id,
                $authors[1]->user_id,
                $authors[2]->user_id],
            $receivers
        );
    }

    public function testFilterWorkflowReceiverPostAuthors_withPostWithGuestAuthorsWithEmail_shouldAddAuthorEmailsToReceiversList(
        WpunitTester $I
    ) {
        $authorsMappedToUser = $I->haveAuthorsMappedToUsers(1);
        $guestAuthors        = $I->haveGuestAuthors(2, ['user_email' => 'guest_author%d@example.com']);

        $postId = $I->haveAPostWithAuthors(array_merge($authorsMappedToUser, $guestAuthors));

        $args      = [
            'params' => [
                'post_id' => $postId
            ]
        ];
        $receivers = $this->getMultipleAuthorsModule()->filter_workflow_receiver_post_authors([99], 0, $args);

        $I->assertCount(4, $receivers);
        $I->assertEquals(
            [
                99,
                $authorsMappedToUser[0]->user_id,
                'guest_author0@example.com',
                'guest_author1@example.com'
            ],
            $receivers
        );
    }

    public function testFilterWorkflowReceiverPostAuthors_withReceiversListAsEmailString_shouldAddAuthorEmailsToReceiversList(
        WpunitTester $I
    ) {
        $authorsMappedToUser = $I->haveAuthorsMappedToUsers(1);
        $guestAuthors        = $I->haveGuestAuthors(2, ['user_email' => 'guest_author%d@example.com']);

        $postId = $I->haveAPostWithAuthors(array_merge($authorsMappedToUser, $guestAuthors));

        $args      = [
            'params' => [
                'post_id' => $postId
            ]
        ];
        $receivers = $this->getMultipleAuthorsModule()->filter_workflow_receiver_post_authors(
            'current_receiver@example.com',
            0,
            $args
        );

        $I->assertCount(4, $receivers);
        $I->assertEquals(
            [
                'current_receiver@example.com',
                $authorsMappedToUser[0]->user_id,
                'guest_author0@example.com',
                'guest_author1@example.com'
            ],
            $receivers
        );
    }

    public function testFilterWorkflowReceiverPostAuthors_withReceiversListAsUserId_shouldAddAuthorEmailsToReceiversList(
        WpunitTester $I
    ) {
        $authorsMappedToUser = $I->haveAuthorsMappedToUsers(1);
        $guestAuthors        = $I->haveGuestAuthors(2, ['user_email' => 'guest_author%d@example.com']);

        $postId = $I->haveAPostWithAuthors(array_merge($authorsMappedToUser, $guestAuthors));

        $args      = [
            'params' => [
                'post_id' => $postId
            ]
        ];
        $receivers = $this->getMultipleAuthorsModule()->filter_workflow_receiver_post_authors(
            99,
            0,
            $args
        );

        $I->assertCount(4, $receivers);
        $I->assertEquals(
            [
                99,
                $authorsMappedToUser[0]->user_id,
                'guest_author0@example.com',
                'guest_author1@example.com'
            ],
            $receivers
        );
    }
}
