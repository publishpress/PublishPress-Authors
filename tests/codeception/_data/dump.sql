-- Adminer 4.8.0 MySQL 5.5.5-10.5.5-MariaDB-1:10.5.5+maria~focal dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `wp_commentmeta`;
CREATE TABLE `wp_commentmeta`
(
    `meta_id`    bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `comment_id` bigint(20) unsigned NOT NULL                DEFAULT 0,
    `meta_key`   varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    `meta_value` longtext COLLATE utf8mb4_unicode_520_ci     DEFAULT NULL,
    PRIMARY KEY (`meta_id`),
    KEY `comment_id` (`comment_id`),
    KEY `meta_key` (`meta_key`(191))
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_comments`;
CREATE TABLE `wp_comments`
(
    `comment_ID`           bigint(20) unsigned                         NOT NULL AUTO_INCREMENT,
    `comment_post_ID`      bigint(20) unsigned                         NOT NULL DEFAULT 0,
    `comment_author`       tinytext COLLATE utf8mb4_unicode_520_ci     NOT NULL,
    `comment_author_email` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `comment_author_url`   varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `comment_author_IP`    varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `comment_date`         datetime                                    NOT NULL DEFAULT '0000-00-00 00:00:00',
    `comment_date_gmt`     datetime                                    NOT NULL DEFAULT '0000-00-00 00:00:00',
    `comment_content`      text COLLATE utf8mb4_unicode_520_ci         NOT NULL,
    `comment_karma`        int(11)                                     NOT NULL DEFAULT 0,
    `comment_approved`     varchar(20) COLLATE utf8mb4_unicode_520_ci  NOT NULL DEFAULT '1',
    `comment_agent`        varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `comment_type`         varchar(20) COLLATE utf8mb4_unicode_520_ci  NOT NULL DEFAULT 'comment',
    `comment_parent`       bigint(20) unsigned                         NOT NULL DEFAULT 0,
    `user_id`              bigint(20) unsigned                         NOT NULL DEFAULT 0,
    PRIMARY KEY (`comment_ID`),
    KEY `comment_post_ID` (`comment_post_ID`),
    KEY `comment_approved_date_gmt` (`comment_approved`, `comment_date_gmt`),
    KEY `comment_date_gmt` (`comment_date_gmt`),
    KEY `comment_parent` (`comment_parent`),
    KEY `comment_author_email` (`comment_author_email`(10))
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_520_ci;

INSERT INTO `wp_comments` (`comment_ID`, `comment_post_ID`, `comment_author`, `comment_author_email`,
                           `comment_author_url`, `comment_author_IP`, `comment_date`, `comment_date_gmt`,
                           `comment_content`, `comment_karma`, `comment_approved`, `comment_agent`, `comment_type`,
                           `comment_parent`, `user_id`)
VALUES (1, 1, 'A WordPress Commenter', 'wapuu@wordpress.example', 'https://wordpress.org/', '', '2020-04-20 19:10:05',
        '2020-04-20 19:10:05',
        'Hi, this is a comment.\nTo get started with moderating, editing, and deleting comments, please visit the Comments screen in the dashboard.\nCommenter avatars come from <a href=\"https://gravatar.com\">Gravatar</a>.',
        0, '1', '', '', 0, 0);

DROP TABLE IF EXISTS `wp_links`;
CREATE TABLE `wp_links`
(
    `link_id`          bigint(20) unsigned                         NOT NULL AUTO_INCREMENT,
    `link_url`         varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `link_name`        varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `link_image`       varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `link_target`      varchar(25) COLLATE utf8mb4_unicode_520_ci  NOT NULL DEFAULT '',
    `link_description` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `link_visible`     varchar(20) COLLATE utf8mb4_unicode_520_ci  NOT NULL DEFAULT 'Y',
    `link_owner`       bigint(20) unsigned                         NOT NULL DEFAULT 1,
    `link_rating`      int(11)                                     NOT NULL DEFAULT 0,
    `link_updated`     datetime                                    NOT NULL DEFAULT '0000-00-00 00:00:00',
    `link_rel`         varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `link_notes`       mediumtext COLLATE utf8mb4_unicode_520_ci   NOT NULL,
    `link_rss`         varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    PRIMARY KEY (`link_id`),
    KEY `link_visible` (`link_visible`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_options`;
CREATE TABLE `wp_options`
(
    `option_id`    bigint(20) unsigned                         NOT NULL AUTO_INCREMENT,
    `option_name`  varchar(191) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `option_value` longtext COLLATE utf8mb4_unicode_520_ci     NOT NULL,
    `autoload`     varchar(20) COLLATE utf8mb4_unicode_520_ci  NOT NULL DEFAULT 'yes',
    PRIMARY KEY (`option_id`),
    UNIQUE KEY `option_name` (`option_name`),
    KEY `autoload` (`autoload`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_520_ci;

INSERT INTO `wp_options` (`option_id`, `option_name`, `option_value`, `autoload`)
VALUES (1, 'siteurl', 'https://tests.local', 'yes'),
       (2, 'home', 'https://tests.local', 'yes'),
       (3, 'blogname', 'Sandbox', 'yes'),
       (4, 'blogdescription', 'Just another WordPress site', 'yes'),
       (5, 'users_can_register', '0', 'yes'),
       (6, 'admin_email', 'publishpress-dev@example.com', 'yes'),
       (7, 'start_of_week', '1', 'yes'),
       (8, 'use_balanceTags', '0', 'yes'),
       (9, 'use_smilies', '1', 'yes'),
       (10, 'require_name_email', '1', 'yes'),
       (11, 'comments_notify', '1', 'yes'),
       (12, 'posts_per_rss', '10', 'yes'),
       (13, 'rss_use_excerpt', '0', 'yes'),
       (14, 'mailserver_url', 'mail.example.com', 'yes'),
       (15, 'mailserver_login', 'login@example.com', 'yes'),
       (16, 'mailserver_pass', 'password', 'yes'),
       (17, 'mailserver_port', '110', 'yes'),
       (18, 'default_category', '1', 'yes'),
       (19, 'default_comment_status', 'open', 'yes'),
       (20, 'default_ping_status', 'open', 'yes'),
       (21, 'default_pingback_flag', '1', 'yes'),
       (22, 'posts_per_page', '10', 'yes'),
       (23, 'date_format', 'F j, Y', 'yes'),
       (24, 'time_format', 'g:i a', 'yes'),
       (25, 'links_updated_date_format', 'F j, Y g:i a', 'yes'),
       (26, 'comment_moderation', '0', 'yes'),
       (27, 'moderation_notify', '1', 'yes'),
       (28, 'permalink_structure', '', 'yes'),
       (29, 'rewrite_rules', '', 'yes'),
       (30, 'hack_file', '0', 'yes'),
       (31, 'blog_charset', 'UTF-8', 'yes'),
       (32, 'moderation_keys', '', 'no'),
       (33, 'active_plugins',
        'a:2:{i:0;s:37:\"custom-post-type/custom-post-type.php\";i:1;s:45:\"publishpress-authors/publishpress-authors.php\";}',
        'yes'),
       (34, 'category_base', '', 'yes'),
       (35, 'ping_sites', 'http://rpc.pingomatic.com/', 'yes'),
       (36, 'comment_max_links', '2', 'yes'),
       (37, 'gmt_offset', '0', 'yes'),
       (38, 'default_email_category', '1', 'yes'),
       (39, 'recently_edited', '', 'no'),
       (40, 'template', 'twentytwenty', 'yes'),
       (41, 'stylesheet', 'twentytwenty', 'yes'),
       (44, 'comment_registration', '0', 'yes'),
       (45, 'html_type', 'text/html', 'yes'),
       (46, 'use_trackback', '0', 'yes'),
       (47, 'default_role', 'subscriber', 'yes'),
       (48, 'db_version', '51917', 'yes'),
       (49, 'uploads_use_yearmonth_folders', '1', 'yes'),
       (50, 'upload_path', '', 'yes'),
       (51, 'blog_public', '1', 'yes'),
       (52, 'default_link_category', '2', 'yes'),
       (53, 'show_on_front', 'posts', 'yes'),
       (54, 'tag_base', '', 'yes'),
       (55, 'show_avatars', '1', 'yes'),
       (56, 'avatar_rating', 'G', 'yes'),
       (57, 'upload_url_path', '', 'yes'),
       (58, 'thumbnail_size_w', '150', 'yes'),
       (59, 'thumbnail_size_h', '150', 'yes'),
       (60, 'thumbnail_crop', '1', 'yes'),
       (61, 'medium_size_w', '300', 'yes'),
       (62, 'medium_size_h', '300', 'yes'),
       (63, 'avatar_default', 'mystery', 'yes'),
       (64, 'large_size_w', '1024', 'yes'),
       (65, 'large_size_h', '1024', 'yes'),
       (66, 'image_default_link_type', 'none', 'yes'),
       (67, 'image_default_size', '', 'yes'),
       (68, 'image_default_align', '', 'yes'),
       (69, 'close_comments_for_old_posts', '0', 'yes'),
       (70, 'close_comments_days_old', '14', 'yes'),
       (71, 'thread_comments', '1', 'yes'),
       (72, 'thread_comments_depth', '5', 'yes'),
       (73, 'page_comments', '0', 'yes'),
       (74, 'comments_per_page', '50', 'yes'),
       (75, 'default_comments_page', 'newest', 'yes'),
       (76, 'comment_order', 'asc', 'yes'),
       (77, 'sticky_posts', 'a:0:{}', 'yes'),
       (78, 'widget_categories',
        'a:2:{i:2;a:4:{s:5:\"title\";s:0:\"\";s:5:\"count\";i:0;s:12:\"hierarchical\";i:0;s:8:\"dropdown\";i:0;}s:12:\"_multiwidget\";i:1;}',
        'yes'),
       (79, 'widget_text', 'a:0:{}', 'yes'),
       (80, 'widget_rss', 'a:0:{}', 'yes'),
       (81, 'uninstall_plugins', 'a:0:{}', 'no'),
       (82, 'timezone_string', '', 'yes'),
       (83, 'page_for_posts', '0', 'yes'),
       (84, 'page_on_front', '0', 'yes'),
       (85, 'default_post_format', '0', 'yes'),
       (86, 'link_manager_enabled', '0', 'yes'),
       (87, 'finished_splitting_shared_terms', '1', 'yes'),
       (88, 'site_icon', '0', 'yes'),
       (89, 'medium_large_size_w', '768', 'yes'),
       (90, 'medium_large_size_h', '0', 'yes'),
       (91, 'wp_page_for_privacy_policy', '3', 'yes'),
       (92, 'show_comments_cookies_opt_in', '1', 'yes'),
       (94, 'initial_db_version', '45805', 'yes'),
       (95, 'wp_user_roles',
        'a:5:{s:13:\"administrator\";a:2:{s:4:\"name\";s:13:\"Administrator\";s:12:\"capabilities\";a:63:{s:13:\"switch_themes\";b:1;s:11:\"edit_themes\";b:1;s:16:\"activate_plugins\";b:1;s:12:\"edit_plugins\";b:1;s:10:\"edit_users\";b:1;s:10:\"edit_files\";b:1;s:14:\"manage_options\";b:1;s:17:\"moderate_comments\";b:1;s:17:\"manage_categories\";b:1;s:12:\"manage_links\";b:1;s:12:\"upload_files\";b:1;s:6:\"import\";b:1;s:15:\"unfiltered_html\";b:1;s:10:\"edit_posts\";b:1;s:17:\"edit_others_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:10:\"edit_pages\";b:1;s:4:\"read\";b:1;s:8:\"level_10\";b:1;s:7:\"level_9\";b:1;s:7:\"level_8\";b:1;s:7:\"level_7\";b:1;s:7:\"level_6\";b:1;s:7:\"level_5\";b:1;s:7:\"level_4\";b:1;s:7:\"level_3\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:17:\"edit_others_pages\";b:1;s:20:\"edit_published_pages\";b:1;s:13:\"publish_pages\";b:1;s:12:\"delete_pages\";b:1;s:19:\"delete_others_pages\";b:1;s:22:\"delete_published_pages\";b:1;s:12:\"delete_posts\";b:1;s:19:\"delete_others_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:20:\"delete_private_posts\";b:1;s:18:\"edit_private_posts\";b:1;s:18:\"read_private_posts\";b:1;s:20:\"delete_private_pages\";b:1;s:18:\"edit_private_pages\";b:1;s:18:\"read_private_pages\";b:1;s:12:\"delete_users\";b:1;s:12:\"create_users\";b:1;s:17:\"unfiltered_upload\";b:1;s:14:\"edit_dashboard\";b:1;s:14:\"update_plugins\";b:1;s:14:\"delete_plugins\";b:1;s:15:\"install_plugins\";b:1;s:13:\"update_themes\";b:1;s:14:\"install_themes\";b:1;s:11:\"update_core\";b:1;s:10:\"list_users\";b:1;s:12:\"remove_users\";b:1;s:13:\"promote_users\";b:1;s:18:\"edit_theme_options\";b:1;s:13:\"delete_themes\";b:1;s:6:\"export\";b:1;s:19:\"ppma_manage_authors\";b:1;s:22:\"ppma_edit_post_authors\";b:1;}}s:6:\"editor\";a:2:{s:4:\"name\";s:6:\"Editor\";s:12:\"capabilities\";a:35:{s:17:\"moderate_comments\";b:1;s:17:\"manage_categories\";b:1;s:12:\"manage_links\";b:1;s:12:\"upload_files\";b:1;s:15:\"unfiltered_html\";b:1;s:10:\"edit_posts\";b:1;s:17:\"edit_others_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:10:\"edit_pages\";b:1;s:4:\"read\";b:1;s:7:\"level_7\";b:1;s:7:\"level_6\";b:1;s:7:\"level_5\";b:1;s:7:\"level_4\";b:1;s:7:\"level_3\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:17:\"edit_others_pages\";b:1;s:20:\"edit_published_pages\";b:1;s:13:\"publish_pages\";b:1;s:12:\"delete_pages\";b:1;s:19:\"delete_others_pages\";b:1;s:22:\"delete_published_pages\";b:1;s:12:\"delete_posts\";b:1;s:19:\"delete_others_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:20:\"delete_private_posts\";b:1;s:18:\"edit_private_posts\";b:1;s:18:\"read_private_posts\";b:1;s:20:\"delete_private_pages\";b:1;s:18:\"edit_private_pages\";b:1;s:18:\"read_private_pages\";b:1;s:22:\"ppma_edit_post_authors\";b:1;}}s:6:\"author\";a:2:{s:4:\"name\";s:6:\"Author\";s:12:\"capabilities\";a:11:{s:12:\"upload_files\";b:1;s:10:\"edit_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:4:\"read\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:12:\"delete_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:22:\"ppma_edit_post_authors\";b:1;}}s:11:\"contributor\";a:2:{s:4:\"name\";s:11:\"Contributor\";s:12:\"capabilities\";a:6:{s:10:\"edit_posts\";b:1;s:4:\"read\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:12:\"delete_posts\";b:1;s:22:\"ppma_edit_post_authors\";b:1;}}s:10:\"subscriber\";a:2:{s:4:\"name\";s:10:\"Subscriber\";s:12:\"capabilities\";a:2:{s:4:\"read\";b:1;s:7:\"level_0\";b:1;}}}',
        'yes'),
       (96, 'fresh_site', '0', 'yes'),
       (97, 'widget_search', 'a:2:{i:2;a:1:{s:5:\"title\";s:0:\"\";}s:12:\"_multiwidget\";i:1;}', 'yes'),
       (98, 'widget_recent-posts',
        'a:2:{i:2;a:2:{s:5:\"title\";s:0:\"\";s:6:\"number\";i:5;}s:12:\"_multiwidget\";i:1;}', 'yes'),
       (99, 'widget_recent-comments',
        'a:2:{i:2;a:2:{s:5:\"title\";s:0:\"\";s:6:\"number\";i:5;}s:12:\"_multiwidget\";i:1;}', 'yes'),
       (100, 'widget_archives',
        'a:2:{i:2;a:3:{s:5:\"title\";s:0:\"\";s:5:\"count\";i:0;s:8:\"dropdown\";i:0;}s:12:\"_multiwidget\";i:1;}',
        'yes'),
       (101, 'widget_meta', 'a:2:{i:2;a:1:{s:5:\"title\";s:0:\"\";}s:12:\"_multiwidget\";i:1;}', 'yes'),
       (102, 'sidebars_widgets',
        'a:4:{s:19:\"wp_inactive_widgets\";a:0:{}s:9:\"sidebar-1\";a:3:{i:0;s:8:\"search-2\";i:1;s:14:\"recent-posts-2\";i:2;s:17:\"recent-comments-2\";}s:9:\"sidebar-2\";a:3:{i:0;s:10:\"archives-2\";i:1;s:12:\"categories-2\";i:2;s:6:\"meta-2\";}s:13:\"array_version\";i:3;}',
        'yes'),
       (103, 'cron',
        'a:6:{i:1641243686;a:6:{s:32:\"recovery_mode_clean_expired_keys\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}s:18:\"wp_https_detection\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}s:34:\"wp_privacy_delete_old_export_files\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:6:\"hourly\";s:4:\"args\";a:0:{}s:8:\"interval\";i:3600;}}s:16:\"wp_version_check\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}s:17:\"wp_update_plugins\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}s:16:\"wp_update_themes\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}}i:1641243688;a:1:{s:8:\"do_pings\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:2:{s:8:\"schedule\";b:0;s:4:\"args\";a:0:{}}}}i:1641243772;a:1:{s:28:\"wp_update_comment_type_batch\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:2:{s:8:\"schedule\";b:0;s:4:\"args\";a:0:{}}}}i:1641244170;a:2:{s:19:\"wp_scheduled_delete\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}s:25:\"delete_expired_transients\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}}i:1641330086;a:1:{s:30:\"wp_site_health_scheduled_check\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:6:\"weekly\";s:4:\"args\";a:0:{}s:8:\"interval\";i:604800;}}}s:7:\"version\";i:2;}',
        'yes'),
       (104, 'widget_pages', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'),
       (105, 'widget_calendar', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'),
       (106, 'widget_media_audio', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'),
       (107, 'widget_media_image', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'),
       (108, 'widget_media_gallery', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'),
       (109, 'widget_media_video', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'),
       (110, 'widget_tag_cloud', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'),
       (111, 'widget_nav_menu', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'),
       (112, 'widget_custom_html', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'),
       (139, 'db_upgraded', '', 'yes'),
       (142, 'recently_activated', 'a:0:{}', 'yes'),
       (143, '_site_transient_update_plugins',
        'O:8:\"stdClass\":5:{s:12:\"last_checked\";i:1641248344;s:8:\"response\";a:2:{s:45:\"publishpress-authors/publishpress-authors.php\";O:8:\"stdClass\":12:{s:2:\"id\";s:34:\"w.org/plugins/publishpress-authors\";s:4:\"slug\";s:20:\"publishpress-authors\";s:6:\"plugin\";s:45:\"publishpress-authors/publishpress-authors.php\";s:11:\"new_version\";s:6:\"3.14.9\";s:3:\"url\";s:51:\"https://wordpress.org/plugins/publishpress-authors/\";s:7:\"package\";s:70:\"https://downloads.wordpress.org/plugin/publishpress-authors.3.14.9.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:73:\"https://ps.w.org/publishpress-authors/assets/icon-256x256.png?rev=2472504\";s:2:\"1x\";s:73:\"https://ps.w.org/publishpress-authors/assets/icon-128x128.png?rev=2472504\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:76:\"https://ps.w.org/publishpress-authors/assets/banner-1544x500.jpg?rev=2472504\";s:2:\"1x\";s:75:\"https://ps.w.org/publishpress-authors/assets/banner-772x250.jpg?rev=2472504\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"4.7\";s:6:\"tested\";s:5:\"5.8.2\";s:12:\"requires_php\";s:3:\"5.6\";}s:37:\"custom-post-type/custom-post-type.php\";O:8:\"stdClass\":12:{s:2:\"id\";s:30:\"w.org/plugins/custom-post-type\";s:4:\"slug\";s:16:\"custom-post-type\";s:6:\"plugin\";s:37:\"custom-post-type/custom-post-type.php\";s:11:\"new_version\";s:3:\"1.0\";s:3:\"url\";s:47:\"https://wordpress.org/plugins/custom-post-type/\";s:7:\"package\";s:59:\"https://downloads.wordpress.org/plugin/custom-post-type.zip\";s:5:\"icons\";a:1:{s:7:\"default\";s:60:\"https://s.w.org/plugins/geopattern-icon/custom-post-type.svg\";}s:7:\"banners\";a:0:{}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"4.0\";s:6:\"tested\";s:6:\"4.6.21\";s:12:\"requires_php\";b:0;}}s:12:\"translations\";a:0:{}s:9:\"no_update\";a:2:{s:19:\"akismet/akismet.php\";O:8:\"stdClass\":10:{s:2:\"id\";s:21:\"w.org/plugins/akismet\";s:4:\"slug\";s:7:\"akismet\";s:6:\"plugin\";s:19:\"akismet/akismet.php\";s:11:\"new_version\";s:5:\"4.2.1\";s:3:\"url\";s:38:\"https://wordpress.org/plugins/akismet/\";s:7:\"package\";s:56:\"https://downloads.wordpress.org/plugin/akismet.4.2.1.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:59:\"https://ps.w.org/akismet/assets/icon-256x256.png?rev=969272\";s:2:\"1x\";s:59:\"https://ps.w.org/akismet/assets/icon-128x128.png?rev=969272\";}s:7:\"banners\";a:1:{s:2:\"1x\";s:61:\"https://ps.w.org/akismet/assets/banner-772x250.jpg?rev=479904\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"5.0\";}s:9:\"hello.php\";O:8:\"stdClass\":10:{s:2:\"id\";s:25:\"w.org/plugins/hello-dolly\";s:4:\"slug\";s:11:\"hello-dolly\";s:6:\"plugin\";s:9:\"hello.php\";s:11:\"new_version\";s:5:\"1.7.2\";s:3:\"url\";s:42:\"https://wordpress.org/plugins/hello-dolly/\";s:7:\"package\";s:60:\"https://downloads.wordpress.org/plugin/hello-dolly.1.7.2.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:64:\"https://ps.w.org/hello-dolly/assets/icon-256x256.jpg?rev=2052855\";s:2:\"1x\";s:64:\"https://ps.w.org/hello-dolly/assets/icon-128x128.jpg?rev=2052855\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:67:\"https://ps.w.org/hello-dolly/assets/banner-1544x500.jpg?rev=2645582\";s:2:\"1x\";s:66:\"https://ps.w.org/hello-dolly/assets/banner-772x250.jpg?rev=2052855\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"4.6\";}}s:7:\"checked\";a:4:{s:19:\"akismet/akismet.php\";s:5:\"4.2.1\";s:9:\"hello.php\";s:5:\"1.7.2\";s:45:\"publishpress-authors/publishpress-authors.php\";s:19:\"3.14.9-hotfix-552.1\";s:37:\"custom-post-type/custom-post-type.php\";s:5:\"0.1.0\";}}',
        'no'),
       (144, '_site_transient_update_themes',
        'O:8:\"stdClass\":5:{s:12:\"last_checked\";i:1641244202;s:7:\"checked\";a:2:{s:14:\"twentynineteen\";s:3:\"2.1\";s:15:\"twentytwentyone\";s:3:\"1.4\";}s:8:\"response\";a:0:{}s:9:\"no_update\";a:2:{s:14:\"twentynineteen\";a:6:{s:5:\"theme\";s:14:\"twentynineteen\";s:11:\"new_version\";s:3:\"2.1\";s:3:\"url\";s:44:\"https://wordpress.org/themes/twentynineteen/\";s:7:\"package\";s:60:\"https://downloads.wordpress.org/theme/twentynineteen.2.1.zip\";s:8:\"requires\";s:5:\"4.9.6\";s:12:\"requires_php\";s:5:\"5.2.4\";}s:15:\"twentytwentyone\";a:6:{s:5:\"theme\";s:15:\"twentytwentyone\";s:11:\"new_version\";s:3:\"1.4\";s:3:\"url\";s:45:\"https://wordpress.org/themes/twentytwentyone/\";s:7:\"package\";s:61:\"https://downloads.wordpress.org/theme/twentytwentyone.1.4.zip\";s:8:\"requires\";s:3:\"5.3\";s:12:\"requires_php\";s:3:\"5.6\";}}s:12:\"translations\";a:0:{}}',
        'no'),
       (147, 'widget_block', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'),
       (148, 'theme_mods_twentytwenty', 'a:1:{s:18:\"custom_css_post_id\";i:-1;}', 'yes'),
       (151, 'disallowed_keys', '', 'no'),
       (152, 'comment_previously_approved', '1', 'yes'),
       (153, 'auto_plugin_theme_update_emails', 'a:0:{}', 'no'),
       (154, 'auto_update_core_dev', 'enabled', 'yes'),
       (155, 'auto_update_core_minor', 'enabled', 'yes'),
       (156, 'auto_update_core_major', 'unset', 'yes'),
       (157, 'wp_force_deactivated_plugins', 'a:0:{}', 'yes'),
       (158, 'finished_updating_comment_type', '0', 'yes'),
       (185, '_site_transient_update_core',
        'O:8:\"stdClass\":4:{s:7:\"updates\";a:1:{i:0;O:8:\"stdClass\":10:{s:8:\"response\";s:6:\"latest\";s:8:\"download\";s:59:\"https://downloads.wordpress.org/release/wordpress-5.8.2.zip\";s:6:\"locale\";s:5:\"en_US\";s:8:\"packages\";O:8:\"stdClass\":5:{s:4:\"full\";s:59:\"https://downloads.wordpress.org/release/wordpress-5.8.2.zip\";s:10:\"no_content\";s:70:\"https://downloads.wordpress.org/release/wordpress-5.8.2-no-content.zip\";s:11:\"new_bundled\";s:71:\"https://downloads.wordpress.org/release/wordpress-5.8.2-new-bundled.zip\";s:7:\"partial\";s:0:\"\";s:8:\"rollback\";s:0:\"\";}s:7:\"current\";s:5:\"5.8.2\";s:7:\"version\";s:5:\"5.8.2\";s:11:\"php_version\";s:6:\"5.6.20\";s:13:\"mysql_version\";s:3:\"5.0\";s:11:\"new_bundled\";s:3:\"5.6\";s:15:\"partial_version\";s:0:\"\";}}s:12:\"last_checked\";i:1641244203;s:15:\"version_checked\";s:5:\"5.8.2\";s:12:\"translations\";a:0:{}}',
        'no'),
       (186, 'widget_multiple_authors_widget', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'),
       (187, 'widget_multiple_authors_list_widget', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'),
       (188, 'publishpress-authors_wp_reviews_installed_on', '2022-01-03 22:20:23', 'yes'),
       (189, 'multiple_authors_version', '3.14.9-hotfix-552.1', 'yes'),
       (190, 'multiple_authors_modules_settings_options',
        'O:8:\"stdClass\":2:{s:7:\"enabled\";s:2:\"on\";s:11:\"loaded_once\";b:1;}', 'yes'),
       (191, 'multiple_authors_settings_options',
        'O:8:\"stdClass\":2:{s:7:\"enabled\";s:2:\"on\";s:11:\"loaded_once\";b:1;}', 'yes'),
       (192, 'multiple_authors_multiple_authors_options',
        'O:8:\"stdClass\":10:{s:7:\"enabled\";s:2:\"on\";s:10:\"post_types\";a:2:{s:4:\"post\";s:2:\"on\";s:4:\"page\";s:2:\"on\";}s:17:\"append_to_content\";s:3:\"yes\";s:20:\"author_for_new_users\";a:0:{}s:6:\"layout\";s:5:\"boxed\";s:18:\"force_empty_author\";s:2:\"no\";s:24:\"username_in_search_field\";s:2:\"no\";s:28:\"default_author_for_new_posts\";N;s:22:\"author_page_post_types\";a:0:{}s:11:\"loaded_once\";b:1;}',
        'yes'),
       (193, 'multiple_authors_default_layouts_options',
        'O:8:\"stdClass\":2:{s:7:\"enabled\";s:2:\"on\";s:11:\"loaded_once\";b:1;}', 'yes'),
       (194, 'multiple_authors_rest_api_options',
        'O:8:\"stdClass\":2:{s:7:\"enabled\";s:2:\"on\";s:11:\"loaded_once\";b:1;}', 'yes'),
       (195, 'multiple_authors_pro_placeholders_options',
        'O:8:\"stdClass\":2:{s:7:\"enabled\";s:2:\"on\";s:11:\"loaded_once\";b:1;}', 'yes'),
       (196, 'multiple_authors_polylang_integration_options',
        'O:8:\"stdClass\":2:{s:7:\"enabled\";s:2:\"on\";s:11:\"loaded_once\";b:1;}', 'yes'),
       (197, 'multiple_authors_reviews_options',
        'O:8:\"stdClass\":2:{s:7:\"enabled\";s:2:\"on\";s:11:\"loaded_once\";b:1;}', 'yes'),
       (198, 'PP_AUTHORS_VERSION', '3.14.9-hotfix-552.1', 'yes'),
       (199, 'publishpress_multiple_authors_settings_migrated_3_0_0', '1', 'yes'),
       (202, '_transient_doing_cron', '1648056851.0514230728149414062500', 'yes'),
       (203, 'admin_email_lifespan', '1663608850', 'yes');

DROP TABLE IF EXISTS `wp_postmeta`;
CREATE TABLE `wp_postmeta`
(
    `meta_id`    bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `post_id`    bigint(20) unsigned NOT NULL                DEFAULT 0,
    `meta_key`   varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    `meta_value` longtext COLLATE utf8mb4_unicode_520_ci     DEFAULT NULL,
    PRIMARY KEY (`meta_id`),
    KEY `post_id` (`post_id`),
    KEY `meta_key` (`meta_key`(191))
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_520_ci;

INSERT INTO `wp_postmeta` (`meta_id`, `post_id`, `meta_key`, `meta_value`)
VALUES (1, 2, '_wp_page_template', 'default'),
       (2, 3, '_wp_page_template', 'default'),
       (3, 1, 'ppma_authors_name', 'admin'),
       (4, 2, 'ppma_authors_name', 'admin'),
       (5, 3, 'ppma_authors_name', 'admin'),
       (6, 4, 'ppma_authors_name', 'admin');

DROP TABLE IF EXISTS `wp_posts`;
CREATE TABLE `wp_posts`
(
    `ID`                    bigint(20) unsigned                         NOT NULL AUTO_INCREMENT,
    `post_author`           bigint(20) unsigned                         NOT NULL DEFAULT 0,
    `post_date`             datetime                                    NOT NULL DEFAULT '0000-00-00 00:00:00',
    `post_date_gmt`         datetime                                    NOT NULL DEFAULT '0000-00-00 00:00:00',
    `post_content`          longtext COLLATE utf8mb4_unicode_520_ci     NOT NULL,
    `post_title`            text COLLATE utf8mb4_unicode_520_ci         NOT NULL,
    `post_excerpt`          text COLLATE utf8mb4_unicode_520_ci         NOT NULL,
    `post_status`           varchar(20) COLLATE utf8mb4_unicode_520_ci  NOT NULL DEFAULT 'publish',
    `comment_status`        varchar(20) COLLATE utf8mb4_unicode_520_ci  NOT NULL DEFAULT 'open',
    `ping_status`           varchar(20) COLLATE utf8mb4_unicode_520_ci  NOT NULL DEFAULT 'open',
    `post_password`         varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `post_name`             varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `to_ping`               text COLLATE utf8mb4_unicode_520_ci         NOT NULL,
    `pinged`                text COLLATE utf8mb4_unicode_520_ci         NOT NULL,
    `post_modified`         datetime                                    NOT NULL DEFAULT '0000-00-00 00:00:00',
    `post_modified_gmt`     datetime                                    NOT NULL DEFAULT '0000-00-00 00:00:00',
    `post_content_filtered` longtext COLLATE utf8mb4_unicode_520_ci     NOT NULL,
    `post_parent`           bigint(20) unsigned                         NOT NULL DEFAULT 0,
    `guid`                  varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `menu_order`            int(11)                                     NOT NULL DEFAULT 0,
    `post_type`             varchar(20) COLLATE utf8mb4_unicode_520_ci  NOT NULL DEFAULT 'post',
    `post_mime_type`        varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `comment_count`         bigint(20)                                  NOT NULL DEFAULT 0,
    PRIMARY KEY (`ID`),
    KEY `post_name` (`post_name`(191)),
    KEY `type_status_date` (`post_type`, `post_status`, `post_date`, `ID`),
    KEY `post_parent` (`post_parent`),
    KEY `post_author` (`post_author`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_520_ci;

INSERT INTO `wp_posts` (`ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`,
                        `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`,
                        `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`,
                        `menu_order`, `post_type`, `post_mime_type`, `comment_count`)
VALUES (1, 1, '2020-04-20 19:10:05', '2020-04-20 19:10:05',
        '<!-- wp:paragraph -->\n<p>Welcome to WordPress. This is your first post. Edit or delete it, then start writing!</p>\n<!-- /wp:paragraph -->',
        'Hello world!', '', 'publish', 'open', 'open', '', 'hello-world', '', '', '2020-04-20 19:10:05',
        '2020-04-20 19:10:05', '', 0, 'https://tests.local/?p=1', 0, 'post', '', 1),
       (2, 1, '2020-04-20 19:10:05', '2020-04-20 19:10:05',
        '<!-- wp:paragraph -->\n<p>This is an example page. It\'s different from a blog post because it will stay in one place and will show up in your site navigation (in most themes). Most people start with an About page that introduces them to potential site visitors. It might say something like this:</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\"><p>Hi there! I\'m a bike messenger by day, aspiring actor by night, and this is my website. I live in Los Angeles, have a great dog named Jack, and I like pi&#241;a coladas. (And gettin\' caught in the rain.)</p></blockquote>\n<!-- /wp:quote -->\n\n<!-- wp:paragraph -->\n<p>...or something like this:</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\"><p>The XYZ Doohickey Company was founded in 1971, and has been providing quality doohickeys to the public ever since. Located in Gotham City, XYZ employs over 2,000 people and does all kinds of awesome things for the Gotham community.</p></blockquote>\n<!-- /wp:quote -->\n\n<!-- wp:paragraph -->\n<p>As a new WordPress user, you should go to <a href=\"https://tests.local/wp-admin/\">your dashboard</a> to delete this page and create new pages for your content. Have fun!</p>\n<!-- /wp:paragraph -->',
        'Sample Page', '', 'publish', 'closed', 'open', '', 'sample-page', '', '', '2020-04-20 19:10:05',
        '2020-04-20 19:10:05', '', 0, 'https://tests.local/?page_id=2', 0, 'page', '', 0),
       (3, 1, '2020-04-20 19:10:05', '2020-04-20 19:10:05',
        '<!-- wp:heading --><h2>Who we are</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Our website address is: http://localhost:32880.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>What personal data we collect and why we collect it</h2><!-- /wp:heading --><!-- wp:heading {\"level\":3} --><h3>Comments</h3><!-- /wp:heading --><!-- wp:paragraph --><p>When visitors leave comments on the site we collect the data shown in the comments form, and also the visitor&#8217;s IP address and browser user agent string to help spam detection.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>An anonymized string created from your email address (also called a hash) may be provided to the Gravatar service to see if you are using it. The Gravatar service privacy policy is available here: https://automattic.com/privacy/. After approval of your comment, your profile picture is visible to the public in the context of your comment.</p><!-- /wp:paragraph --><!-- wp:heading {\"level\":3} --><h3>Media</h3><!-- /wp:heading --><!-- wp:paragraph --><p>If you upload images to the website, you should avoid uploading images with embedded location data (EXIF GPS) included. Visitors to the website can download and extract any location data from images on the website.</p><!-- /wp:paragraph --><!-- wp:heading {\"level\":3} --><h3>Contact forms</h3><!-- /wp:heading --><!-- wp:heading {\"level\":3} --><h3>Cookies</h3><!-- /wp:heading --><!-- wp:paragraph --><p>If you leave a comment on our site you may opt-in to saving your name, email address and website in cookies. These are for your convenience so that you do not have to fill in your details again when you leave another comment. These cookies will last for one year.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>If you visit our login page, we will set a temporary cookie to determine if your browser accepts cookies. This cookie contains no personal data and is discarded when you close your browser.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>When you log in, we will also set up several cookies to save your login information and your screen display choices. Login cookies last for two days, and screen options cookies last for a year. If you select &quot;Remember Me&quot;, your login will persist for two weeks. If you log out of your account, the login cookies will be removed.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>If you edit or publish an article, an additional cookie will be saved in your browser. This cookie includes no personal data and simply indicates the post ID of the article you just edited. It expires after 1 day.</p><!-- /wp:paragraph --><!-- wp:heading {\"level\":3} --><h3>Embedded content from other websites</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Articles on this site may include embedded content (e.g. videos, images, articles, etc.). Embedded content from other websites behaves in the exact same way as if the visitor has visited the other website.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>These websites may collect data about you, use cookies, embed additional third-party tracking, and monitor your interaction with that embedded content, including tracking your interaction with the embedded content if you have an account and are logged in to that website.</p><!-- /wp:paragraph --><!-- wp:heading {\"level\":3} --><h3>Analytics</h3><!-- /wp:heading --><!-- wp:heading --><h2>Who we share your data with</h2><!-- /wp:heading --><!-- wp:heading --><h2>How long we retain your data</h2><!-- /wp:heading --><!-- wp:paragraph --><p>If you leave a comment, the comment and its metadata are retained indefinitely. This is so we can recognize and approve any follow-up comments automatically instead of holding them in a moderation queue.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>For users that register on our website (if any), we also store the personal information they provide in their user profile. All users can see, edit, or delete their personal information at any time (except they cannot change their username). Website administrators can also see and edit that information.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>What rights you have over your data</h2><!-- /wp:heading --><!-- wp:paragraph --><p>If you have an account on this site, or have left comments, you can request to receive an exported file of the personal data we hold about you, including any data you have provided to us. You can also request that we erase any personal data we hold about you. This does not include any data we are obliged to keep for administrative, legal, or security purposes.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Where we send your data</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Visitor comments may be checked through an automated spam detection service.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Your contact information</h2><!-- /wp:heading --><!-- wp:heading --><h2>Additional information</h2><!-- /wp:heading --><!-- wp:heading {\"level\":3} --><h3>How we protect your data</h3><!-- /wp:heading --><!-- wp:heading {\"level\":3} --><h3>What data breach procedures we have in place</h3><!-- /wp:heading --><!-- wp:heading {\"level\":3} --><h3>What third parties we receive data from</h3><!-- /wp:heading --><!-- wp:heading {\"level\":3} --><h3>What automated decision making and/or profiling we do with user data</h3><!-- /wp:heading --><!-- wp:heading {\"level\":3} --><h3>Industry regulatory disclosure requirements</h3><!-- /wp:heading -->',
        'Privacy Policy', '', 'draft', 'closed', 'open', '', 'privacy-policy', '', '', '2020-04-20 19:10:05',
        '2020-04-20 19:10:05', '', 0, 'https://tests.local/?page_id=3', 0, 'page', '', 0),
       (4, 1, '2020-04-20 19:10:16', '0000-00-00 00:00:00', '', 'Auto Draft', '', 'auto-draft', 'open', 'open', '', '',
        '', '', '2020-04-20 19:10:16', '0000-00-00 00:00:00', '', 0, 'https://tests.local/?p=4', 0, 'post', '', 0);

DROP TABLE IF EXISTS `wp_termmeta`;
CREATE TABLE `wp_termmeta`
(
    `meta_id`    bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `term_id`    bigint(20) unsigned NOT NULL                DEFAULT 0,
    `meta_key`   varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    `meta_value` longtext COLLATE utf8mb4_unicode_520_ci     DEFAULT NULL,
    PRIMARY KEY (`meta_id`),
    KEY `term_id` (`term_id`),
    KEY `meta_key` (`meta_key`(191))
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_520_ci;

INSERT INTO `wp_termmeta` (`meta_id`, `term_id`, `meta_key`, `meta_value`)
VALUES (9, 3, 'user_id_1', 'user_id'),
       (10, 3, 'user_id', '1'),
       (11, 3, 'first_name', ''),
       (12, 3, 'last_name', ''),
       (13, 3, 'user_email', 'publishpress-dev@example.com'),
       (14, 3, 'user_login', 'admin'),
       (15, 3, 'user_url', ''),
       (16, 3, 'description', '');

DROP TABLE IF EXISTS `wp_terms`;
CREATE TABLE `wp_terms`
(
    `term_id`    bigint(20) unsigned                         NOT NULL AUTO_INCREMENT,
    `name`       varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `slug`       varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `term_group` bigint(10)                                  NOT NULL DEFAULT 0,
    PRIMARY KEY (`term_id`),
    KEY `slug` (`slug`(191)),
    KEY `name` (`name`(191))
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_520_ci;

INSERT INTO `wp_terms` (`term_id`, `name`, `slug`, `term_group`)
VALUES (1, 'Uncategorized', 'uncategorized', 0),
       (3, 'admin', 'admin', 0);

DROP TABLE IF EXISTS `wp_term_relationships`;
CREATE TABLE `wp_term_relationships`
(
    `object_id`        bigint(20) unsigned NOT NULL DEFAULT 0,
    `term_taxonomy_id` bigint(20) unsigned NOT NULL DEFAULT 0,
    `term_order`       int(11)             NOT NULL DEFAULT 0,
    PRIMARY KEY (`object_id`, `term_taxonomy_id`),
    KEY `term_taxonomy_id` (`term_taxonomy_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_520_ci;

INSERT INTO `wp_term_relationships` (`object_id`, `term_taxonomy_id`, `term_order`)
VALUES (1, 1, 0),
       (1, 3, 0),
       (2, 3, 0),
       (3, 3, 0),
       (4, 3, 0);

DROP TABLE IF EXISTS `wp_term_taxonomy`;
CREATE TABLE `wp_term_taxonomy`
(
    `term_taxonomy_id` bigint(20) unsigned                        NOT NULL AUTO_INCREMENT,
    `term_id`          bigint(20) unsigned                        NOT NULL DEFAULT 0,
    `taxonomy`         varchar(32) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `description`      longtext COLLATE utf8mb4_unicode_520_ci    NOT NULL,
    `parent`           bigint(20) unsigned                        NOT NULL DEFAULT 0,
    `count`            bigint(20)                                 NOT NULL DEFAULT 0,
    PRIMARY KEY (`term_taxonomy_id`),
    UNIQUE KEY `term_id_taxonomy` (`term_id`, `taxonomy`),
    KEY `taxonomy` (`taxonomy`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_520_ci;

INSERT INTO `wp_term_taxonomy` (`term_taxonomy_id`, `term_id`, `taxonomy`, `description`, `parent`, `count`)
VALUES (1, 1, 'category', '', 0, 1),
       (2, 2, 'author', '', 0, 1),
       (3, 3, 'author', '', 0, 2);

DROP TABLE IF EXISTS `wp_usermeta`;
CREATE TABLE `wp_usermeta`
(
    `umeta_id`   bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `user_id`    bigint(20) unsigned NOT NULL                DEFAULT 0,
    `meta_key`   varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    `meta_value` longtext COLLATE utf8mb4_unicode_520_ci     DEFAULT NULL,
    PRIMARY KEY (`umeta_id`),
    KEY `user_id` (`user_id`),
    KEY `meta_key` (`meta_key`(191))
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_520_ci;

INSERT INTO `wp_usermeta` (`umeta_id`, `user_id`, `meta_key`, `meta_value`)
VALUES (1, 1, 'nickname', 'admin'),
       (2, 1, 'first_name', ''),
       (3, 1, 'last_name', ''),
       (4, 1, 'description', ''),
       (5, 1, 'rich_editing', 'true'),
       (6, 1, 'syntax_highlighting', 'true'),
       (7, 1, 'comment_shortcuts', 'false'),
       (8, 1, 'admin_color', 'fresh'),
       (9, 1, 'use_ssl', '0'),
       (10, 1, 'show_admin_bar_front', 'true'),
       (11, 1, 'locale', ''),
       (12, 1, 'wp_capabilities', 'a:1:{s:13:\"administrator\";b:1;}'),
       (13, 1, 'wp_user_level', '10'),
       (14, 1, 'dismissed_wp_pointers', ''),
       (15, 1, 'show_welcome_panel', '1'),
       (16, 1, 'session_tokens',
        'a:1:{s:64:\"a78d13111ad081983cd00e7f0065d656b3e1c72e8b9fd8903288016cf15397bf\";a:4:{s:10:\"expiration\";i:1641416970;s:2:\"ip\";s:10:\"172.18.0.1\";s:2:\"ua\";s:105:\"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36\";s:5:\"login\";i:1641244170;}}'),
       (17, 1, 'wp_user-settings', 'libraryContent=browse&editor=html&mfold=o'),
       (18, 1, 'wp_user-settings-time', '1587409811'),
       (19, 1, 'wp_dashboard_quick_press_last_post_id', '4'),
       (20, 1, 'community-events-location', 'a:1:{s:2:\"ip\";s:10:\"172.18.0.0\";}'),
       (21, 2, 'nickname', '_admin_user623b5909816d2'),
       (22, 2, 'first_name', ''),
       (23, 2, 'last_name', ''),
       (24, 2, 'description', ''),
       (25, 2, 'rich_editing', 'true'),
       (26, 2, 'syntax_highlighting', 'true'),
       (27, 2, 'comment_shortcuts', 'false'),
       (28, 2, 'admin_color', 'fresh'),
       (29, 2, 'use_ssl', '0'),
       (30, 2, 'show_admin_bar_front', 'true'),
       (31, 2, 'locale', ''),
       (32, 2, 'wp_capabilities', 'a:1:{s:13:\"administrator\";b:1;}'),
       (33, 2, 'wp_user_level', '10'),
       (34, 2, 'dismissed_wp_pointers', ''),
       (35, 2, 'session_tokens',
        'a:1:{s:64:\"161897136068c59af05aab0949388ca12230777db32a513040a99b7276445a7a\";a:4:{s:10:\"expiration\";i:1648229385;s:2:\"ip\";s:10:\"172.18.0.1\";s:2:\"ua\";s:112:\"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/99.0.4844.74 Safari/537.36\";s:5:\"login\";i:1648056585;}}');

DROP TABLE IF EXISTS `wp_users`;
CREATE TABLE `wp_users`
(
    `ID`                  bigint(20) unsigned                         NOT NULL AUTO_INCREMENT,
    `user_login`          varchar(60) COLLATE utf8mb4_unicode_520_ci  NOT NULL DEFAULT '',
    `user_pass`           varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `user_nicename`       varchar(50) COLLATE utf8mb4_unicode_520_ci  NOT NULL DEFAULT '',
    `user_email`          varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `user_url`            varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `user_registered`     datetime                                    NOT NULL DEFAULT '0000-00-00 00:00:00',
    `user_activation_key` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `user_status`         int(11)                                     NOT NULL DEFAULT 0,
    `display_name`        varchar(250) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    PRIMARY KEY (`ID`),
    KEY `user_login_key` (`user_login`),
    KEY `user_nicename` (`user_nicename`),
    KEY `user_email` (`user_email`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_520_ci;

INSERT INTO `wp_users` (`ID`, `user_login`, `user_pass`, `user_nicename`, `user_email`, `user_url`, `user_registered`,
                        `user_activation_key`, `user_status`, `display_name`)
VALUES (1, 'admin', '$P$BiHs2lVx75mgbZ2kBapRn49vUbDQKl0', 'admin', 'publishpress-dev@example.com', '',
        '2020-04-20 19:10:05', '', 0, 'admin'),
       (2, '_admin_user623b5909816d2', '$P$BZqneg29ctTYkUqwe4lmLhHMeGFYTG1', '_admin_user623b5909816d2',
        '_admin_user623b5909816d2@example.com', '', '2022-03-23 17:29:45', '', 0, '_admin_user623b5909816d2');

-- 2022-03-23 17:34:14
