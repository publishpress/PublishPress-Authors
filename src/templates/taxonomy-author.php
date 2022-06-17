<?php
/**
 * The template for author taxonomy
 *
 * You can customize this template by having a copy of this file in root theme or child theme's folder
 *
 * @package PublishPress
 */

// Do not allow directly accessing this file.
if (!defined('ABSPATH')) {
    exit('Direct script access denied.');
}

use MultipleAuthors\Classes\Utils;

if (Utils::authors_locate_template(['header.php'])) {
    get_header(); 
}
?>

<div class="ppma-page-header">
    <?php the_archive_title('<h1 class="ppma-page-title">', '</h1>'); ?>
</div><!-- .page-header -->

<div class="ppma-page-content">
    <?php if (have_posts()) : ?>
         <?php while ( have_posts() ) : the_post(); ?>
                <?php
                $featured_image = has_post_thumbnail() 
                ? 'background-image: url("'. wp_get_attachment_image_src(get_post_thumbnail_id(), 'single-post-thumbnail')[0] .'");' : '';
                $post_categories = get_the_category();
                $post_tags        = get_the_tags();
                $post_authors     = get_post_authors();
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('ppma-article'); ?>>
                    <div class="article-content">
					    <div class="article-image">
                            <a class="featured-image-link" href="<?php the_permalink(); ?>" style="<?php esc_attr_e($featured_image); ?>"></a>
                        </div>

                        <div class="article-body">
                            <header class="article-header">
                                <?php if ($post_categories && is_array($post_categories) && !empty($post_categories)) : ?>
                                    <span class="category-links">
                                        <a href="<?php echo esc_url(get_category_link($post_categories[0])); ?>" rel="category tag"><?php echo esc_html($post_categories[0]->cat_name); ?></a>
                                    </span>
                                <?php endif; ?>
                                <h2 class="article-title">
                                    <a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
                                </h2>
                                <div class="article-meta">
                                    <span class="article-meta-item">by <span class="author vcard">
                                        <?php if (!empty($post_authors)) : ?>
                                            <?php foreach ($post_authors as $index => $post_author) : $index++; ?>
                                                <?php $term_link = get_term_link($post_author->term_id); ?>
                                                <a href="<?php echo ($term_link) ? esc_url($term_link) : ''; ?>">
                                                    <?php echo esc_html($post_author->display_name); ?><?php
                                                    if (count($post_authors) !== $index) { 
                                                        echo ',';
                                                    } 
                                                    ?>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </span></span>
                                    <span class="article-meta-item">
                                        <span class="dashicons dashicons-clock"></span>
                                        <a href="<?php the_permalink(); ?>" rel="bookmark">
                                            <time class="article-date published" datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>"><?php echo esc_html(get_the_date()); ?></time>
                                        </a>
                                    </span>
                                    <span class="article-meta-item">
                                        <a href="<?php echo esc_url(the_permalink() . '#comments'); ?>">
                                            <span class="dashicons dashicons-admin-comments"></span><?php echo esc_html(get_comments_number()); ?>
                                        </a>
                                    </span>
                                </div><!-- .article-meta -->
                            </header><!-- .article-header -->
                            <div class="article-entry-excerpt">
                                <?php Utils::ppma_article_excerpt(160, 'content', true); ?>
                            </div>
                            <footer class="article-footer">
                                <?php if ($post_tags && !empty($post_tags)) : ?>
                                        <span class="tags-links">
                                            <?php foreach($post_tags as $post_tag) : ?>
                                                <a href="<?php echo esc_url(get_tag_link($post_tag->term_id)); ?>" rel="tag">
                                                    <?php echo esc_html($post_tag->name); ?>
                                                </a>
                                            <?php endforeach; ?>
                                        </span>
                                <?php endif; ?>
                            </footer><!-- .entry-footer -->
                        </div>
                    </div>
                </article>
         <?php endwhile; ?>
        
        <div class="ppma-article-pagination">
            <?php posts_nav_link(' &nbsp; &nbsp; &nbsp;  ', esc_html__('« Previous Page', 'publishpress-authors'), esc_html__('Next Page »', 'publishpress-authors')); ?>
        </div>
    <?php else : ?>
        <h2><?php esc_html_e('Post not found for the author', 'publishpress-authors'); ?></h2>
    <?php endif; ?>
    <?php 
    if (Utils::authors_locate_template(['sidebar.php'])) {
        get_sidebar(); 
    }
    ?>
</div> <!-- #main-content -->

<?php
if (Utils::authors_locate_template(['footer.php'])) {
    get_footer(); 
}