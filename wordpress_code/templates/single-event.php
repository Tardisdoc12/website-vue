<?php
defined('ABSPATH') || exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<div class="vue-root"
     data-module="event-page"
     data-post-id="<?php echo get_the_ID(); ?>">
</div>

<?php wp_footer(); ?>
</body>
</html>