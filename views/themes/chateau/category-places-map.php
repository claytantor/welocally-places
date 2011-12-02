<!-- include1 -->
<?php 
global $wlPlaces;
$wlPlaces->loadDomainStylesScripts();
global $wp_query;
$wlecCatObject = get_category( $wp_query->query_vars['cat']);
?>

<?php get_header(); ?>

<!-- include2 -->
<?php 
$places_list_include = WP_PLUGIN_DIR . '/' .$wlPlaces->pluginDir . '/views/includes/category-map-include.php';
include($places_list_include);
?>


<div id="main">
<div id="content">

<!-- the map selector -->
				<div id="map_content">
					<!-- FOUND theme: chateau -->
					<div><h1 class="entry-title"><?php echo $wlecCatObject->name ?></h1></div>
					<div id="map_all"></div>
					<div id="map_canvas"></div>
					<div id="items">
						<ol id="selectable"></ol>
					</div>
				</div>

	<div class="morePosts">
		
		<?php if (have_posts()) : ?>
		
		<?php $post = $posts[0]; // Hack. Set $post so that the_date() works. ?>
		<?php /* If this is a category archive */ if (is_category()) { ?>
		<h3>Archive for the &#8216;<?php single_cat_title(); ?>&#8217; Category</h3>
		<?php /* If this is a tag archive */ } elseif( is_tag() ) { ?>
		<h3>Posts Tagged with &#8216;<?php single_tag_title(); ?>&#8217;</h3>
		<?php /* If this is a daily archive */ } elseif (is_day()) { ?>
		<h3>Archive for <?php the_time('F jS, Y'); ?></h3>
		<?php /* If this is a monthly archive */ } elseif (is_month()) { ?>
		<h3>Archive for <?php the_time('F, Y'); ?></h3>
		<?php /* If this is a yearly archive */ } elseif (is_year()) { ?>
		<h3>Archive for <?php the_time('Y'); ?></h3>
		<?php /* If this is an author archive */ } elseif (is_author()) { ?>
		<h3>Author Archive</h3>
		<?php /* If this is a paged archive */ } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?>
		<h3>Blog Archives</h3>
		<?php } ?>
		
		<ul>
		
			<?php while (have_posts()) : the_post(); ?>

			<li <?php post_class() ?>>
				<h4><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h4>
				<span><?php the_time('d M Y'); ?></span>
				<p><?php echo wl_get_post_excerpt( $post->ID )  ?></p>
				<p><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">Continue reading &raquo;</a></p>
			</li>

			<?php endwhile; ?>

		</ul>
	</div>

	<div id="pageNavigation">
		<p id="prevPosts"><?php next_posts_link('&laquo; Older Articles') ?></p>
		<p id="nextPosts"><?php previous_posts_link('Newer Articles &raquo;') ?></p>
	</div>
		
	<?php else :

		if ( is_category() ) { // If this is a category archive
			printf("<h3>Sorry, but there aren't any articles in the %s category yet.</h3>", single_cat_title('',false));
		} else if ( is_date() ) { // If this is a date archive
			echo("<h3>Sorry, but there aren't any articles with this date.</h3>");
		} else if ( is_author() ) { // If this is a category archive
			$userdata = get_userdatabylogin(get_query_var('author_name'));
			printf("<h3>Sorry, but there aren't any articles by %s yet.</h3>", $userdata->display_name);
		} else {
			echo("<h3>No articles found.</h3>");
		}

	endif;
?>

</div>

<?php get_sidebar(); ?>

</div>

<?php get_footer(); ?>