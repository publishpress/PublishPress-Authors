<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

   use \Steps\Login;
   use \Steps\Authors;
   use \Steps\Users;
   use \Steps\Posts;
   use \Steps\AdminPages;
   use \Steps\Interaction;
   use \Steps\Settings;
   use \Steps\Theme;
   use \Steps\FrontendPages;
}
