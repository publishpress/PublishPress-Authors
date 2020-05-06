<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use MultipleAuthors\Classes\Objects\Author;

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
}
