<?php
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
