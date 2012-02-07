<?php
if (!class_exists('WelocallyPlaces_TagProcessor')) {
    
    class WelocallyPlaces_TagProcessor {
        
        public function __construct() {
        }
        
        private function queryPlace($tag) {
            if (!$tag->id)
                return false;

            $url = sprintf('%s/geodb/place/1_0/%s.json', wl_server_base(), $tag->id);
            
            // TODO: wl_do_curl_get() does not use the CURLOPT_RETURNTRANSFER flag so the output
            // gets printed instead of returned. i'm using ob_* as a hack around this for the moment.
            ob_start();
            wl_do_curl_get($url);
            $json_response = ob_get_contents();
            ob_end_clean();

            $response = json_decode($json_response);
            return $response[0];
        }
        
        public function processTag($tag, $postId=0) {
            global $wpdb;
            global $wlPlaces;
            
            if (!$tag->id)
                return false;
                
            $postId = intval($postId);
            
            $place = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wl_places WHERE wl_id = %s", $tag->id));
            
            if ($place == null) {
                $place_info = $this->queryPlace($tag);

                if ($wpdb->insert("{$wpdb->prefix}wl_places", array('wl_id' => $tag->id,
                                                                    'place' => json_encode($place_info))) ) {
                                                                        
                    $place = $wpdb->get_row( 
                        $wpdb->prepare("SELECT * FROM {$wpdb->prefix}wl_places WHERE wl_id = %s", $tag->id) );

                }
            }
            
            if (!$place)
                return false;
            
            if ($postId) {
                $wpdb->insert("{$wpdb->prefix}wl_places_posts", array('place_id' => $place->id,
                                                                      'post_id' => $postId));
                
                if ($tag->categories && get_post_type($postId) != 'page') {
                    $customCategories = array();
                    $customCategories[] = $wlPlaces->create_category_if_not_exists();
                    
                    foreach ($tag->categories as $customCategory) {
                        $customCategories[] = get_cat_ID($customCategory) ? get_cat_ID($customCategory) : wp_create_category($customCategory);
                    }
                    
                    $postCategories = wp_get_post_categories($postId);
                    $newCategories = array_merge($postCategories, $customCategories);
                    wp_set_post_categories($postId, $newCategories);
                }
                
                update_post_meta($this->postId, '_isWLPlace', true);
                
                // delete post metadata from previous versions of the plugin
                delete_post_meta($post_id, '_PlaceSelected');
                delete_post_meta($post_id, '_WLPlaces');
            }
            
            return $place;

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