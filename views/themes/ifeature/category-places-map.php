<?php
global $wlPlaces;
$wlPlaces->loadDomainStylesScripts();
global $wp_query;
$wlecCatObject = get_category( $wp_query->query_vars['cat']);
?>
<?php get_header(); ?>

<?php 
$places_list_include = WP_PLUGIN_DIR . '/' .$wlPlaces->pluginDir . '/views/includes/category-map-include.php';
include($places_list_include);

$infobox_include = WP_PLUGIN_DIR . '/' .$wlPlaces->pluginDir . '/views/includes/infobox-map-include.php';
include($infobox_include);

global $options;
?>

<div class="container_12">

<?php if (function_exists('chimps_breadcrumbs') && ($options->get($themeslug.'_disable_breadcrumbs') == "1")) { chimps_breadcrumbs(); }?>

	<div id="main">
	
		<div id="content" class="grid_8">
		<!--Begin @Core before_archive hook-->
			<?php chimps_before_archive(); ?>
		<!--End @Core before_archive hook-->
		
            <!-- the map selector -->
    		<div id="map_content">
    			<!-- FOUND theme: iFeature -->
    			<div><h1 class="entry-title"><?php echo $wlecCatObject->name; ?></h1></div>
    			<div id="map_all"></div>
    			<div id="map_canvas"></div>
    			<div id="items">
    				<ol id="selectable"></ol>
    			</div>
    		</div>
		
		<?php if (have_posts()) : ?>
		
			<!--Begin @Core archive hook-->
			<?php chimps_archive_title(); ?>
			<!--End @Core archive hook-->
		
		<?php while (have_posts()) : the_post(); ?>
		
			<!--Begin @Core archive hook-->
				<?php chimps_archive(); ?>
			<!--End @Core archive hook-->
		
		 <?php endwhile; ?>
	 
	 <?php else : ?>

		<h2>Nothing found</h2>

	<?php endif; ?>

		<!--Begin @Core pagination hook-->
			<?php chimps_pagination(); ?>
		<!--End @Core pagination hook-->
		
		<!--Begin @Core after_archive hook-->
			<?php chimps_after_archive(); ?>
		<!--End @Core after_archive hook-->
	
		</div><!--end content_padding-->
		
		<div id="sidebar" class="grid_4">
			<?php get_sidebar(); ?>
		</div>
	
	</div><!--end main-->

</div><!--end container_12-->

<div class='clear'>&nbsp;</div>

<?php get_footer(); ?>