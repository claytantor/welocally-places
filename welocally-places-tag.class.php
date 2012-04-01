<?php
if (!class_exists('WelocallyPlaces_Tag')) {
    
    class WelocallyPlaces_Tag {
        
        const TAG_PATTERN = "/\[\s?\bwelocally\b.*?\/{0,1}\]/uis";
        const TAG_ARGS_PATTERN = '/\b(id|postId|categories|category)\b\s?=\s?"{1}(.*?)\s?"{1}/uis';


		/**
		* Seraches a single string $str for a [welocally] tag.
		* @param string $str a string with a [welocally] tag
		* @return bool|object returns FALSE if none or multiple tags are found or a WelocallyPlaces_Tag when one tag is found.
		*/        
		static function parseTagString($str) {
            if (preg_match_all(self::TAG_PATTERN, $str, $_matches) !== 1)
                return FALSE;
            
		    $args = array();
		    
            if (preg_match_all(self::TAG_ARGS_PATTERN, $str, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $m) {
                    if ($m[1] == "categories" || $m[1] == "category") {
                        $args[$m[1]] = preg_split('/,+/', $m[2], -1, PREG_SPLIT_NO_EMPTY);
                    } else {
                        $args[$m[1]] = trim($m[2]);
                    }
                }
            }

            return new WelocallyPlaces_Tag($args);
		}

        /**
         * Searches $text for [welocally] tags.
         * @param string $text text string with possibly multiple [welocally] tags
         * @return array() an array with a WelocallyPlaces_Tag instance for each tag found.
         */
		static function parseText($text) {
		    $res = array();
		    
		    if (preg_match_all(self::TAG_PATTERN, $text, $matches)) {
		        if ($matches) {
		            foreach ($matches[0] as $tag_string) {
		                if ($tag = self::parseTagString($tag_string))
		                    $res[] = $tag;
		            }
		        }

		    }
		    
		    return $res;
		}
		
		static function searchAndReplace($text, $callback, $optargs=array()) {
		    $res = $text;
		    
		    if (preg_match_all(self::TAG_PATTERN, $text, $matches)) {
		        if ($matches) {
		            foreach ($matches[0] as $tag_string) {
                        if ($tag = self::parseTagString($tag_string)) {
                            $res = preg_replace('/' . preg_quote($tag_string, '/') . '/', call_user_func($callback, $tag, $tag_string, &$optargs), $res, 1);                            
                        }
		            }
		        }
		    }
		    
            return $res;
		}


        /**
         * Creates a new object representing a [welocally] tag.
         * @param array|string $args_or_id an array of arguments or the tag id
         * @return object a WelocallyPlaces_Tag instance
         */
		function __construct($args_or_id) {
			$id = null;
			$categories = null;

			if (is_array($args_or_id)) {
				$id = isset($args_or_id['id']) ? $args_or_id['id'] : null;
				$categories = isset($args_or_id['categories']) ? $args_or_id['categories'] : null;

				// for backwards compat we also support the "category" argument
				if (!$categories && isset($args_or_id['category']))
					$categories = $args_or_id['category'];
			} else if (is_string($args_or_id)) {
				$id = $args_or_id;
			} else {
				throw new Exception('Invalid tag arguments.');
			}
			

			$this->id = $id;
			$this->categories = $categories;
			$this->type = is_null($id) && is_array($categories) ? 'category': 'post';
		}
		
		/**
		 * Returns a string representation of this tag (i.e. a [welocally] tag) that could be used inside
		 * a WordPress post.
		 * @return string
		 */
		function getTagString() {
			switch ($this->type) {
				case 'category':
					return sprintf('[welocally categories="%s" /]', join(',', $this->categories));				
				case 'post':
				default:
					return spritnf('[welocally id="%s" /]', $this->id);
			}
		}
        
    }

}