<?php
 /*
	Copyright 2012 clay graham, welocally & RateCred Inc.

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/
if (!class_exists('WelocallyPlaces_TagProcessor')) {
    
    class WelocallyPlaces_TagProcessor {
        
        public function __construct() {
        }
        
        public function processTag($tag, $postId=0) {
        	
            global $wpdb;
            global $wlPlaces;
            
            $postId = intval($postId);
            
            $place = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wl_places WHERE wl_id = %s", $tag->id));
            
            if ($place && $postId) {
                $wpdb->insert("{$wpdb->prefix}wl_places_posts", array('place_id' => $place->id,
                                                                      'post_id' => $postId));
            	
            	//make sure its categorized as place no matter what 
                $postCategories = wp_get_post_categories($postId);                                                      
                $customCategories = array(); 
                array_push($postCategories,$wlPlaces->placeCategory());
                           
       
                if ($tag->categories && get_post_type($postId) != 'page') {                   
                    foreach ($tag->categories as $customCategory) {
                        $customCategories[] = get_cat_ID($customCategory) ? get_cat_ID($customCategory) : wp_create_category($customCategory);
                    }                   
                }
                
                $newCategories = array_merge($postCategories, $customCategories);
                wp_set_post_categories($postId, $newCategories);
            
            } 
            
            
            
        }
        
        public function processTags($tags, $postId=0) {
            global $wpdb;
            
            if (!is_array($tags))
                return true;
                
            if ($postId)
                $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}wl_places_posts WHERE post_id = %d", $postId));
            
            foreach ($tags as $tag) {
                $this->processTag($tag, $postId);
            }
            
            return true;
        }


    }

}
