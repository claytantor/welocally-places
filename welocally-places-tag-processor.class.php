<?php
if (!class_exists('WelocallyPlaces_TagProcessor')) {
    
    class WelocallyPlaces_TagProcessor {
        
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
            
            return $json_response;
        }
        
        public function processTag($tag) {
            if ($json = $this->queryPlace($tag)) {
                $response = json_decode($json);
                
                if (!isset($response['errors']) && count($response) == 1) {
                    $place_json = json_encode($response[0]);
                    
                    $post_places = get_post_meta($tag->postId, '_WLPlaces', true);
                    $post_places = array_merge(is_array($post_places) ? $post_places : array(), array("{$tag->id}" => $place_json));
                
                    delete_post_meta($tag->postId, '_WLPlaces');
                    update_post_meta($tag->postId, '_WLPlaces', $post_places);
                    update_post_meta($tag->postId, '_isWLPlace', 'true');
                    
                    // just one instance of a place per post
                    // delete_post_meta($tag->postId, '_WLPlaces', $place_json);
                    // add_post_meta($tag->postId, '_WLPlaces', $place_json);

                    // handle categories
/*                    print_r($tag->categories);
                    die();*/
                    
                    return true;
                } else {
                    // an error occurred
                }
            }
            
            return false;
        }
        
        public function processTags($tags) {
            if (!is_array($tags))
                return false;
                
            foreach ($tags as $tag) {
                $this->processTag($tag);
            }
        }
        
            //  if(isset( $_POST['PlaceCategoriesSelected'] )){
            //      $cat_index = 0;
            //      $custom_cat_ids = array();
            //      foreach ( $custom_categories as $custom_cat ) {
            //          $custom_cat_ids[$cat_index] = $this->create_custom_category_if_not_exists($custom_cat); 
            //          $cat_index = $cat_index+1;                      
            //      }
            //      
            //      // merge place category into this post
            //      $cats = wp_get_object_terms($postId, 'category', array('fields' => 'ids'));
            //      $new_cats1 = array_merge( array( $category_id ), $cats ); 
            //      $new_cats2 = array_merge( $new_cats1, $custom_cat_ids );
            //      wp_set_post_categories( $postId, $new_cats2 );                  
            //  } 

    }

}