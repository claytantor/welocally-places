<?php
global $wlPlaces;
$index = 0;
echo '<aside class="widget sidebar-item"><h3 class="widget-title">'.$title.'</h3><div><ul class="wl-places-list">';

foreach ($posts as $post):
    foreach (get_post_places($post) as $place):
?>

<?php /**
 * This is the template for the output of the events list widget. 
 * All the items are turned on and off through the widget admin.
 * There is currently no default styling, which is highly needed.
 * @return string
 */
$PlaceSelected	= $place;


//inefficient
if( $PlaceSelected != '' && $_REQUEST['json'] != 'get_post' && $index<$instance['limit'] ) : 

$places_list_include = WP_PLUGIN_DIR . '/' .$wlPlaces->pluginDir . '/views/includes/places-list-include.php';
include($places_list_include);
?>

<aside>
<div class="wl-place-widget-category" id="plugin-place-category<?php echo $index; ?>" style="{display:none;}"><?php echo $this->get_categories($post->ID, $exclude_cats); ?></div>
<div id="plugin-place<?php echo $index; ?>"></div>
</aside>
<?php endif; ?>
<?php
	$index=$index+1;
	endforeach;
endforeach;
echo "</ul></div></aside>";
?>	
