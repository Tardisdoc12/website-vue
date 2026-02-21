<?php
defined('ABSPATH') || exit;
get_header();
?>
<div class="vue-root"
     data-module="event-page"
     data-post-id="<?php echo get_the_ID(); ?>">
</div>

<?php
get_footer();