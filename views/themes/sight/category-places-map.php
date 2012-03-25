<?php 
global $wlPlaces;
$wlPlaces->loadDomainStylesScripts();
global $wp_query;
$wlecCatObject = get_category( $wp_query->query_vars['cat'])
?>

<?php get_header(); ?>

<div class="content-title">
    <?php $post = $posts[0]; // Hack. Set $post so that the_date() works. ?>
        <?php /* If this is a category archive */ if (is_category()) { ?>
        <?php printf(__('%s'), single_cat_title('', false)); ?>
        <?php /* If this is a tag archive */ } elseif( is_tag() ) { ?>
        <?php printf(__('Posts tagged &quot;%s&quot;'), single_tag_title('', false) ); ?>
        <?php /* If this is a daily archive */ } elseif (is_day()) { ?>
        <?php printf(_c('Daily archive %s'), get_the_time(__('M j, Y'))); ?>
        <?php /* If this is a monthly archive */ } elseif (is_month()) { ?>
        <?php printf(_c('Monthly archive %s'), get_the_time(__('F, Y'))); ?>
        <?php /* If this is a yearly archive */ } elseif (is_year()) { ?>
        <?php printf(_c('Yearly archive %s'), get_the_time(__('Y'))); ?>
        <?php /* If this is an author archive */ } elseif (is_author()) { ?>
        <?php _e('Author Archive'); ?>
        <?php /* If this is a paged archive */ } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?>
        <?php _e('Blog Archives'); ?>
        <?php } ?>

    <a href="javascript: void(0);" id="mode"<?php if ($_COOKIE['mode'] == 'grid') echo ' class="flip"'; ?>></a>
</div>

		<div id="container" style="margin-bottom:20px; border-bottom: 1px; solid #a1a1a1;">
			<div id="content" role="main">
<!-- the map selector -->
<style type="text/css">
.wl-category-map.in-archive { margin-bottom:20px !important; border-bottom: 1px solid #a1a1a1 !important; }
.wl-category-map.in-archive .map-canvas { margin-left:30px !important; width:610px !important; }
</style>
<?php the_category_map(); ?>
			</div>	
	  </div>		
<?php get_template_part('loop'); ?> 

<?php get_template_part('pagination'); ?>

<?php get_footer(); ?>
