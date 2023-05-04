<div class="pp-multiple-authors-wrapper pp-multiple-authors-index alignwide <?php esc_attr_e($context['css_class']); ?> pp-multiple-authors-layout-<?php esc_attr_e($context['layout']); ?>">
<?php if (isset($context['shortcode']['search_box'])) : ?>
        <div class="pp-multiple-authors-searchbox searchbox">
            <form action="" method="GET">
                <input class="widefat" id="authors-search-input" name="seach_query" type="search"
                    value="<?php esc_attr_e($context['template_options']['search_query']); ?>" placeholder="<?php esc_attr_e($context['template_options']['search_placeholder']); ?>">
                <?php if ($context['template_options']['filter_fields']) : ?>
                    <select id="authors-search-filter" name="search_field">
                        <?php foreach (($context['template_options']['filter_fields']) as $option => $label) : ?>
                            <option value="<?php esc_attr_e($option); ?>"
                            <?php selected($option, $context['template_options']['selected_option']); ?>
                            ><?php esc_html_e($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
                <input type="submit" class="button search-submit" id="" name="submit" value="<?php esc_attr_e($context['template_options']['search_submit']); ?>"/>
            </form>
        </div>
    <?php endif; ?>
    <ul class="author-index-navigation">
        <li class="page-item active" data-item="all"><a class="page-link " href="#"><?php esc_html_e($context['all_text']); ?></a></li>
        <?php foreach ($context['results'] as $key => $value) : ?>
            <li class="page-item" data-item="<?php esc_attr_e($key); ?>">
                <a class="page-link" href="#"><?php esc_html_e(strtoupper($key)); ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php 
    $currentUserIndex = 0;
    foreach ($context['results'] as $alphabet => $users) : ?>
        <div class="author-index-group author-index-group-<?php esc_attr_e($alphabet); ?>">
            <div class="author-index-header">
                <h4 class="author-list-head author-list-head-<?php esc_attr_e($alphabet); ?>"><?php esc_html_e(strtoupper($alphabet)); ?></h4>
            </div>
            <div class="author-index-authors author-index-<?php esc_attr_e($alphabet); ?>">
                <ul>
                    <?php foreach ($users as $author) : 
                        $currentUserIndex = $currentUserIndex + 1;
                        ?>
                        <li class="author-index-item author_index_<?php esc_attr_e($currentUserIndex); ?> author_<?php esc_attr_e($author->slug); ?>">
                            <div class="tease-author">
                                <div class="author-index-author-name">
                                    <a href="<?php echo esc_url($author->link); ?>" class="<?php esc_attr_e($context['item_class']); ?>" rel="author" title="<?php esc_attr_e($author->display_name); ?>">
                                        <?php echo $author->display_name; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                    </a>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if ($context['pagination']) : ?>
        <nav class="footer-navigation navigation pagination">
            <div class="nav-links">
            <?php echo $context['pagination']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        </nav>
    <?php endif; ?>

</div>
