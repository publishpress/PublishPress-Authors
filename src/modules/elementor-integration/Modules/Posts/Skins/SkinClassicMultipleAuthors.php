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

use ElementorPro\Modules\Posts\Skins\Skin_Classic;


class SkinClassicMultipleAuthors extends Skin_Classic
{
    public function get_id()
    {
        return 'skin-classic-multiple-authors';
    }

    public function get_title()
    {
        return __('Classic - Multiple Authors');
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
}