<?php
global $wlPlaces;
$index = 0;
?>

<div class="widget sidebar-item dbx-box suf-widget">
<div>
<h3 style="margin-left:5px;"><?php echo $title; ?></h3>
<?php
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

if( $PlaceSelected != '' && $_REQUEST['json'] != 'get_post') : 

$places_list_include = WP_PLUGIN_DIR . '/' .$wlPlaces->pluginDir . '/views/includes/places-list-include.php';
include($places_list_include);
?>

<div style="margin-left:5px; margin-right:5px;">
<div class="wl-place-widget-category" id="plugin-place-category<?php echo $index; ?>" style="{display:none;}"><?php echo $this->get_categories($post->ID, $exclude_cats); ?></div>
<div id="plugin-place<?php echo $index; ?>"></div>
</div>
<?php endif; ?>
<?php
	$index=$index+1;
	endforeach;
endforeach;
?>
</div>
</div>
