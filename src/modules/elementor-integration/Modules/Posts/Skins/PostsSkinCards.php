<?php
/**
 * @package PublishPress Authors
 * @author  PublishPress
 *
 * Copyright (C) 2018 PublishPress
 *
 * This file is part of PublishPress Authors
 *
 * PublishPress Authors is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 3 of the License,
 * or (at your option) any later version.
 *
 * PublishPress is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PublishPress.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace PublishPressAuthors\ElementorIntegration\Modules\Posts\Skins;

use ElementorPro\Modules\Posts\Skins\Skin_Cards;


class PostsSkinCards extends Skin_Cards
{
    public function get_id()
    {
        return 'posts_cards_pp_authors';
    }

    public function get_title()
    {
        return __('Cards - PublishPress Authors');
    }

    protected function render_author()
    {
        ?>
        <span class="elementor-post-author">
			<?php
            $authors     = get_multiple_authors();
            $authorNames = [];

            foreach ($authors as $author) {
                $authorNames[] = $author->display_name;
            }

            echo implode(', ', $authorNames);
            ?>
		</span>
        <?php
    }

    protected function render_avatar()
    {
        ?>
        <div class="elementor-post__avatar">
            <?php
            $authors = get_multiple_authors();

            foreach ($authors as $author) {
                echo $author->get_avatar(128);
            }
            ?>
        </div>
        <?php
    }
}