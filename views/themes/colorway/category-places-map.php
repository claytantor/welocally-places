<?php
/**
 * The template for displaying Archive pages.
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */
 
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
?>
		<?php get_header(); ?>
<!--Start Content Grid-->
<div class="grid_24 content">
  <div class="grid_16 alpha">
    <div class="content-wrap">
      <div class="content-info">
        <?php if (function_exists('inkthemes_breadcrumbs')) inkthemes_breadcrumbs(); ?>
      </div>
      <div class="blog" id="blogmain">
        <?php
			/* Queue the first post, that way we know
			 * what date we're dealing with (if that is the case).
			 *
			 * We reset this later so we can run the loop
			 * properly with a call to rewind_posts().
			 */
			if ( have_posts() )
				the_post();
		?>
        <h1>
          <?php if ( is_day() ) : ?>
          <?php printf('Daily Archives: %s', get_the_date() ); ?>
          <?php elseif ( is_month() ) : ?>
          <?php printf('Monthly Archives: %s', get_the_date('F Y') ); ?>
          <?php elseif ( is_year() ) : ?>
          <?php printf('Yearly Archives: %s', get_the_date('Y') ); ?>
          <?php else : ?>
          <?php echo ('Blog Archives'); ?>
          <?php endif; ?>
        </h1>
        
        <!-- the map selector -->
				<div id="map_content">
					<!-- FOUND theme: twentyeleven -->
					<div><h1 class="entry-title"><?php echo $wlecCatObject->name ?></h1></div>
					<div id="map_all"></div>
					<div id="map_canvas"></div>
					<div id="items" style="margin:20px 26px 0;">
						<ol id="selectable"></ol>
					</div>
				</div>
        <?php
			/* Since we called the_post() above, we need to
			 * rewind the loop back to the beginning that way
			 * we can run the loop properly, in full.
			 */
			rewind_posts();
			/* Run the loop for the archives page to output the posts.
			 * If you want to overload this in a child theme then include a file
			 * called loop-archives.php and that will be used instead.
			 */
			 get_template_part( 'loop', 'archive' );
		?>
      </div>
      <?php inkthemes_content_nav( 'nav-below' ); ?>
    </div>
  </div>
  <?php get_sidebar(); ?>
</div>
<div class="clear"></div>
<!--End Content Grid-->
</div>
<!--End Container Div-->
<?php get_footer(); ?>

<?php /* For custom template builders...
	   * The following init method should be called before any other loop happens.
	   */
$wp_query->init(); ?>		



