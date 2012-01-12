<?php
//
//  SETTINGS CONFIGURATION CLASS
//
//  By Olly Benson / v 1.2 / 13 July 2011 / http://code.olib.co.uk
//  Modified / Bugfix by Karl Cohrs / 17 July 2011 / http://karlcohrs.com
//
//  HOW TO USE
//  * add a include() to this file in your plugin.
//  * amend the config class below to add your own settings requirements.
//  * to avoid potential conflicts recommended you do a global search/replace on this page to replace 'welocally_settings' with something unique
//  * Full details of how to use Settings see here: http://codex.wordpress.org/Settings_API
 
class welocally_settings_config {
 
// MAIN CONFIGURATION SETTINGS
 
var $group = "welocally-places-display"; // defines setting groups (should be bespoke to your settings)
var $page_name = "places_display"; // defines which pages settings will appear on. Either bespoke or media/discussion/reading etc
 
//  DISPLAY SETTINGS
//  (only used if bespoke page_name)
 
var $title = "Welocally Places Options";  // page title that is displayed
var $intro_text = "This allows you to configure the places plugin exactly the way you want it"; // text below title
var $nav_title = "Welocally Places"; // how page is listed on left-hand Settings panel
 
//  SECTIONS
//  Each section should be own array within $sections.
//  Should contatin title, description and fields, which should be array of all fields.
//  Fields array should contain:
//  * label: the displayed label of the field. Required.
//  * description: the field description, displayed under the field. Optional
//  * suffix: displays right of the field entry. Optional
//  * default_value: default value if field is empty. Optional
//  * dropdown: allows you to offer dropdown functionality on field. Value is array listed below. Optional
//  * function: will call function other than default text field to display options. Option
//  * callback: will run callback function to validate field. Optional
//  * All variables are sent to display function as array, therefore other variables can be added if needed for display purposes
 
var $sections = array(
  'subscription' => array(
        'title' => "Subscription Options",
        'description' => "Settings to do with how the maps are displayed.",
        'fields' => array (
          'token' => array (
              'label' => "Subscription Token",
              'description' => "Your subscription shown above. Copy and paste it here, then save your settings.",
              'length' => "32",
              'suffix' => "",
              'default_value' => "Please subscribe to welocally places."
              )
          )
     ),
	'maps' => array(
        'title' => "Map Options",
        'description' => "Settings to do with how the maps are displayed.",
        'fields' => array (
          'icon' => array (
              'label' => "Marker",
              'description' => "The icon url for the map marker",
              'length' => "256",
              'suffix' => "",
              'default_value' => "https://www.google.com/mapfiles/marker.png"
              ),
         'css' => array (
              'label' => "Custom CSS",
              'textarea' => "css",
              'description' => "The custom CSS for the Maps",
              'suffix' => "",
              'default_value' => ""
              )
          )
     )
    );
 
 // DROPDOWN OPTIONS
 // For drop down choices.  Each set of choices should be unique array
 // Use key => value to indicate name => display name
 // For default_value in options field use key, not value
 // You can have multiple instances of the same dropdown options
 
var $dropdown_options = array (
    'dd_colour' => array (
        '#f00' => "Red",
        '#0f0' => "Green",
        '#00f' => "Blue",
        '#fff' => "White",
        '#000' => "Black",
        '#aaa' => "Gray",
        )
    );
 
//  end class
};
 
class welocally_settings {
 
function welocally_settings($settings_class) {
    global $welocally_settings;
    $welocally_settings = get_class_vars($settings_class);
 
    if (function_exists('add_action')) :
      add_action('admin_init', array( &$this, 'plugin_admin_init'));
      add_action('admin_menu', array( &$this, 'plugin_admin_add_page'));
      endif;
}
 
function plugin_admin_add_page() {
  global $welocally_settings;
  add_options_page($welocally_settings['title'], 
  	$welocally_settings['nav_title'], 
  	'manage_options', 
  	$welocally_settings['page_name'], 
  	array( &$this,'plugin_options_page'));
  }
 
 
function plugin_admin_init(){
  global $welocally_settings;
  foreach ($welocally_settings["sections"] AS $section_key=>$section_value) :
    add_settings_section($section_key, $section_value['title'], array( &$this, 'plugin_section_text'), $welocally_settings['page_name'], $section_value);
    foreach ($section_value['fields'] AS $field_key=>$field_value) :
      $function = (!empty($field_value['textarea'])) ? array( &$this, 'plugin_setting_textarea' ) : array( &$this, 'plugin_setting_string' );
      $function = (!empty($field_value['dropdown'])) ? array( &$this, 'plugin_setting_dropdown' ) : array( &$this, 'plugin_setting_string' );
      $function = (!empty($field_value['function'])) ? $field_value['function'] : $function;
      $callback = (!empty($field_value['callback'])) ? $field_value['callback'] : NULL;
      add_settings_field($welocally_settings['group'].'_'.$field_key, $field_value['label'], $function, $welocally_settings['page_name'], $section_key,array_merge($field_value,array('name' => $welocally_settings['group'].'_'.$field_key)));
      register_setting($welocally_settings['group'], $welocally_settings['group'].'_'.$field_key,$callback);
      endforeach;
    endforeach;
  }
 
function plugin_section_text($value = NULL) {
  global $welocally_settings;
  printf("
%s
 
",$welocally_settings['sections'][$value['id']]['description']);
}
 
function plugin_setting_string($value = NULL) {
  $options = get_option($value['name']);
  $default_value = (!empty ($value['default_value'])) ? $value['default_value'] : NULL;
  printf('<input id="%s" type="text" name="%1$s[text_string]" value="%2$s" size="40" /> %3$s%4$s',
    $value['name'],
    (!empty ($options['text_string'])) ? $options['text_string'] : $default_value,
    (!empty ($value['suffix'])) ? $value['suffix'] : NULL,
    (!empty ($value['description'])) ? sprintf("<em>%s</em>",$value['description']) : NULL);
  }
  
  
function plugin_setting_textarea($value = NULL) {
  $options = get_option($value['name']);
  $default_value = (!empty ($value['default_value'])) ? $value['default_value'] : NULL;
  printf('%3$s%4$s<br/><textarea id="%s" type="text" name="%1$s[text_string]" rows="4" cols="50">%2$s</textarea>',
    $value['name'],
    (!empty ($options['text_string'])) ? $options['text_string'] : $default_value,
    (!empty ($value['suffix'])) ? $value['suffix'] : NULL,
    (!empty ($value['description'])) ? sprintf("<em>%s</em>",$value['description']) : NULL);
  }  
 
function plugin_setting_dropdown($value = NULL) {
  global $welocally_settings;
  $options = get_option($value['name']);
  $default_value = (!empty ($value['default_value'])) ? $value['default_value'] : NULL;
  $current_value = ($options['text_string']) ? $options['text_string'] : $default_value;
    $chooseFrom = "";
    $choices = $welocally_settings['dropdown_options'][$value['dropdown']];
  foreach($choices AS $key=>$option) :
    $chooseFrom .= sprintf('<option value="%s" %s>%s</option>',
      $key,($current_value == $key ) ? ' selected="selected"' : NULL,$option);
    endforeach;
    printf('
<select id="%s" name="%1$s[text_string]">%2$s</select>
%3$s',$value['name'],$chooseFrom,
  (!empty ($value['description'])) ? sprintf("<em>%s</em>",$value['description']) : NULL);
  }
  
  
  function get_option_value($field_key) {
  	$value = get_option('welocally-places-display_'.$field_key);
  	return $value['text_string'];
  }
  
  
		//----------

// Section HTML, displayed before the first option
function  section_text_fn() {
	echo '<p>Below are some examples of different option controls.</p>';
}

// DROP-DOWN-BOX - Name: plugin_options[dropdown1]
function  setting_dropdown_fn() {
	$options = get_option('plugin_options');
	$items = array("Red", "Green", "Blue", "Orange", "White", "Violet", "Yellow");
	echo "<select id='drop_down1' name='plugin_options[dropdown1]'>";
	foreach($items as $item) {
		$selected = ($options['dropdown1']==$item) ? 'selected="selected"' : '';
		echo "<option value='$item' $selected>$item</option>";
	}
	echo "</select>";
}

// TEXTAREA - Name: plugin_options[text_area]
function setting_textarea_fn() {
	$options = get_option('plugin_options');
	echo "<textarea id='plugin_textarea_string' name='plugin_options[text_area]' rows='7' cols='50' type='textarea'>{$options['text_area']}</textarea>";
}

// TEXTBOX - Name: plugin_options[text_string]
function setting_string_fn() {
	$options = get_option('plugin_options');
	echo "<input id='plugin_text_string' name='plugin_options[text_string]' size='40' type='text' value='{$options['text_string']}' />";
} 

// PASSWORD-TEXTBOX - Name: plugin_options[pass_string]
function setting_pass_fn() {
	$options = get_option('plugin_options');
	echo "<input id='plugin_text_pass' name='plugin_options[pass_string]' size='40' type='password' value='{$options['pass_string']}' />";
}

// CHECKBOX - Name: plugin_options[chkbox1]
function setting_chk1_fn() {
	$options = get_option('plugin_options');
	if($options['chkbox1']) { $checked = ' checked="checked" '; }
	echo "<input ".$checked." id='plugin_chk1' name='plugin_options[chkbox1]' type='checkbox' />";
}

// CHECKBOX - Name: plugin_options[chkbox2]
function setting_chk2_fn() {
	$options = get_option('plugin_options');
	if($options['chkbox2']) { $checked = ' checked="checked" '; }
	echo "<input ".$checked." id='plugin_chk2' name='plugin_options[chkbox2]' type='checkbox' />";
}

// RADIO-BUTTON - Name: plugin_options[option_set1]
function setting_radio_fn() {
	$options = get_option('plugin_options');
	$items = array("Square", "Triangle", "Circle");
	foreach($items as $item) {
		$checked = ($options['option_set1']==$item) ? ' checked="checked" ' : '';
		echo "<label><input ".$checked." value='$item' name='plugin_options[option_set1]' type='radio' /> $item</label><br />";
	}
}  

 
//end class
}
global $welocally_settings_init; 
$welocally_settings_init = new welocally_settings('welocally_settings_config');
?>