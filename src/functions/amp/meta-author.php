<?php $coauthors = get_post_authors($this->get('post_id')); ?>
<li class="amp-wp-byline">
    <?php multiple_authors(); ?>
</li>
