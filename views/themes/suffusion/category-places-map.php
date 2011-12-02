<?php
/**
 * Used when you are looking at all posts in a category.
 *
 * @package Suffusion
 * @subpackage Templates
 */



global $suffusion_unified_options, $suf_cat_info_enabled, $suf_category_excerpt, $suffusion;
global $wlPlaces;
$wlPlaces->loadDomainStylesScripts();
global $wp_query;
$wlecCatObject = get_category( $wp_query->query_vars['cat']);

$suffusion->set_content_layout($suf_category_excerpt);
get_header();

/* include  */
$places_list_include = WP_PLUGIN_DIR . '/' .$wlPlaces->pluginDir . '/views/includes/category-map-include.php';
include($places_list_include);

suffusion_query_posts();
?>


    <div id="main-col">
<?php
suffusion_before_begin_content();
?>



      <div id="content" class="hfeed">
<!-- the map selector -->
				<div id="map_content" class="post">
					<!-- FOUND theme: suffusion -->
					<div><h2 class="entry-title"><?php echo $wlecCatObject->name; ?></h2></div>
					<div id="map_all"></div>
					<div id="map_canvas"></div>
					<div id="items">
						<ol id="selectable"></ol>
					</div>
				</div>      
      
      
<?php
if ($suf_category_excerpt == 'list') {
	get_template_part('layouts/layout-list');
}
else if ($suf_category_excerpt == 'tiles') {
	suffusion_after_begin_content();
	get_template_part('layouts/layout-tiles');
}
else if ($suf_category_excerpt == 'mosaic') {
	//suffusion_after_begin_content();
	get_template_part('layouts/layout-mosaic');
}
else {
	suffusion_after_begin_content();
	get_template_part('layouts/layout-blog');
}
?>
      </div><!-- content -->
    </div><!-- main col -->
<?php get_footer(); ?>