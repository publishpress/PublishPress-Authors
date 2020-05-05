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

namespace PublishPressAuthors\ElementorIntegration\Modules\ThemeBuilder\Skins;


use ElementorPro\Modules\Posts\Skins\Skin_Content_Base;
use ElementorPro\Modules\ThemeBuilder\Skins\Posts_Archive_Skin_Base;
use PublishPressAuthors\ElementorIntegration\Modules\Posts\Skins\PostsSkinFullContent;

class ArchivePostsSkinFullContent extends PostsSkinFullContent
{
    use Skin_Content_Base;
    use Posts_Archive_Skin_Base;

    public function get_id()
    {
        return 'archive_full_content_pp_authors';
    }

    /* Remove `posts_per_page` control */
    protected function register_post_count_control()
    {
    }
}