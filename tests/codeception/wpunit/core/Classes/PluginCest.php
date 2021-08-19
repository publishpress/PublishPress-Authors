<?php

namespace core\Classes;

use Codeception\Example;
use MultipleAuthors\Classes\Objects\Author;
use MultipleAuthors\Factory;
use WpunitTester;

class PluginCest
{
    private function countAuthors()
    {
        $terms = get_terms(
            [
                'taxonomy' => 'author',
                'hide_empty' => false
            ]
        );

        return count($terms);
    }

    /**
     * @example ["author"]
     * @example ["subscriber"]
     * @example ["administrator"]
     */
    public function actionUserRegister_forNoUserRoleSelected_doNotCreateAuthor(WpunitTester $I, Example $example)
    {
        $legacyPlugin = Factory::getLegacyPlugin();

        $countAuthorsBefore = $this->countAuthors();

        add_action(
            'user_register',
            ['MultipleAuthors\\Classes\\Author_Editor', 'action_user_register'],
            20
        );

        $legacyPlugin->modules->multiple_authors->options->author_for_new_users = [];

        $userID = $I->factory("a new {$example[0]} user")->user->create(['role' => $example[0]]);

        $author = Author::get_by_user_id($userID);

        $countAuthorsAfter = $this->countAuthors();

        $I->assertFalse($author);
        $I->assertEquals($countAuthorsBefore, $countAuthorsAfter);
    }

    /**
     * @example ["author"]
     * @example ["subscriber"]
     * @example ["administrator"]
     */
    public function actionUserRegister_forUserRoleNotSelected_doNotCreateAuthor(WpunitTester $I, Example $example)
    {
        $legacyPlugin = Factory::getLegacyPlugin();

        $countAuthorsBefore = $this->countAuthors();

        add_action(
            'user_register',
            ['MultipleAuthors\\Classes\\Author_Editor', 'action_user_register'],
            20
        );

        $legacyPlugin->modules->multiple_authors->options->author_for_new_users = ['contributor'];

        $userID = $I->factory("a new {$example[0]} user")->user->create(['role' => $example[0]]);

        $author = Author::get_by_user_id($userID);

        $countAuthorsAfter = $this->countAuthors();

        $I->assertFalse($author);
        $I->assertEquals($countAuthorsBefore, $countAuthorsAfter);
    }

    /**
     * @example ["author"]
     * @example ["subscriber"]
     * @example ["administrator"]
     */
    public function actionUserRegister_forUserRoleSelected_doNotCreateAuthor(WpunitTester $I, Example $example)
    {
        $legacyPlugin = Factory::getLegacyPlugin();

        add_action(
            'user_register',
            ['MultipleAuthors\\Classes\\Author_Editor', 'action_user_register'],
            20
        );

        $legacyPlugin->modules->multiple_authors->options->author_for_new_users = ['author', 'subscriber', 'administrator'];

        $userID = $I->factory('a new user')->user->create(['role' => 'author']);

        $author = Author::get_by_user_id($userID);

        $I->assertInstanceOf(Author::class, $author);
    }
}
