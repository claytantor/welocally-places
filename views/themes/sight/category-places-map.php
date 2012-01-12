<?php 
global $wlPlaces;
$wlPlaces->loadDomainStylesScripts();
global $wp_query;
$wlecCatObject = get_category( $wp_query->query_vars['cat'])
?>

<?php get_header(); ?>
<!-- include -->
<?php 
$places_list_include = WP_PLUGIN_DIR . '/' .$wlPlaces->pluginDir . '/views/includes/category-map-include.php';
include($places_list_include);

$infobox_include = WP_PLUGIN_DIR . '/' .$wlPlaces->pluginDir . '/views/includes/infobox-map-include.php';
include($infobox_include);

?>

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
				<div id="map_content" style="margin-bottom:20px; border-bottom: 1px; solid #a1a1a1;">
					<div id="map_all"></div>
					<div id="map_canvas" style="margin-left:30px; width:610px;></div>
					<div id="items">
						<ol id="selectable"></ol>
					</div>
				</div>
			</div>	
	  </div>		
<?php get_template_part('loop'); ?> 

<?php get_template_part('pagination'); ?>

<?php get_footer(); ?>
