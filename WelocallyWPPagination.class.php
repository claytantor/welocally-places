<?php
require_once (dirname(__FILE__) . "/pagination-allowed.php");	
if (!class_exists('WelocallyWPPagination')) { 

	class WelocallyWPPagination {
		const VERSION = '1.1.4';
		const PAGESIZE = 10;
		const OPTIONNAME = 'wl_pager_options';

		function __construct() {
			
		}
		
		public function loadDomainStylesScripts() {
			
			$baseurl = trailingslashit( WP_PLUGIN_URL ) . trailingslashit( plugin_basename( dirname( __FILE__ ) ) );
			
			wp_enqueue_script('jquery' , 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js');			
					
			//welocally
			wp_enqueue_script('wl_pager', WP_PLUGIN_URL.'/wl-pager/js/wl_pager_wp.min.js', array('jquery'), WelocallyWPPagination::VERSION);
			
			global $wp_styles;
		
			wp_enqueue_style( 'wl_pager',WP_PLUGIN_URL.'/wl-pager/css/wl_pager.css', array(), WelocallyWPPagination::VERSION, 'screen' );
			wp_register_style('wl_pager-ie-only', WP_PLUGIN_URL.'/wl-pager/css/ie/wl_pager.css');
			$wp_styles->add_data('wl_pager-ie-only', 'conditional', 'IE');
			wp_enqueue_style('wl_pager-ie-only');
			
			$options = $this->getOptions();
			if(empty($options['theme']))
				$options['theme'] = 'basic'; 

			wp_enqueue_style( 'wl_pager_'.$options['theme'],WP_PLUGIN_URL.'/wl-pager/css/wl_pager_'.$options['theme'].'.css', array(), WelocallyWPPagination::VERSION, 'screen' );
			wp_register_style('wl_pager_'.$options['theme'].'-ie-only', WP_PLUGIN_URL.'/wl-pager/css/ie/wl_pager_'.$options['theme'].'.css');
			$wp_styles->add_data('wl_pager_'.$options['theme'].'-ie-only', 'conditional', 'IE');
			wp_enqueue_style('wl_pager_'.$options['theme'].'-ie-only');

		}
		
		public function loadAdminDomainStylesScripts() {
			
			global $wp_styles;
								
			wp_enqueue_style( 'wl_pager_admin',WP_PLUGIN_URL.'/wl-pager/css/wl_pager_admin.css', array(), WelocallyWPPagination::VERSION, 'screen' );
			wp_register_style('wl_pager_admin-ie-only', WP_PLUGIN_URL.'/wl-pager/css/ie/wl_pager_admin.css');
			$wp_styles->add_data('wl_pager_admin-ie-only', 'conditional', 'IE');
			wp_enqueue_style('wl_pager_admin-ie-only');	
			
			wp_enqueue_style( 'font-google-carme','http://fonts.googleapis.com/css?family=Carme' );
			
		}

		//will only work if pluin is installed
		function handleWlPagerShortcode($attrs, $content = null) {	
			if(function_exists('wl_pager_activate' )){
				extract(shortcode_atts(array (
					'table' => null,
					'fields' => null,
					'filter' => null,
					'order_by'=> null,
					'even'=> null,
					'odd'=> null,
					'pagesize'=> WelocallyWPPagination::PAGESIZE,				
				), $attrs));
				
				//strip paragragh tags if exists
				if ( '</p>' == substr( $content, 0, 4 )
				and '<p>' == substr( $content, strlen( $content ) - 3 ) )
					$content = substr( $content, 4, strlen( $content ) - 7 );
				
				//template for javscript
				$t = new StdClass();
				$t->uid = uniqid();
				$t->table = $table;
				$t->fields = $fields;
				$t->filter = $filter;
				$t->orderBy = $order_by;
				$t->odd = $odd;
				$t->even = $even;			
				$t->pagesize = $pagesize;
				$t->content = $content;			
				
				ob_start();
	            include( WP_PLUGIN_DIR.'/wl-pager/pager-template.php');
	            $resultContent = ob_get_contents();
	            ob_end_clean();
	            
	            $t = null;
	            
	            return $resultContent;
			}		
		}
				
		function getMetadata($table=null, $fields=null, $filter=null, $order_by=null, $pagesize=WelocallyWPPagination::PAGESIZE){
			
			
			global $wpdb;
			$query = sprintf("SELECT COUNT(*) FROM %s ",$table);
			
			
			if(!empty($filter))
				$query = $query.$this->makeFilterSQLFromParts($this->makeFilterFromQuerystring(html_entity_decode ($filter)));
			
			
			$total = $wpdb->get_var( $wpdb->prepare( $query ) );
			$per_page = $pagesize;
			$lastpage = ceil($total/$per_page);
	        $lpm1 = $lastpage - 1;
						
			$t = new StdClass();
			$t->total = $total;
			$t->pages = $lastpage;			
			$t->table = $table;
			$t->fields = $fields;
			$t->orderBy = $order_by;
			
			return $t;	
			
		}
		
		function tableAllowed($tableCheck){
			global $tables_allowed;
			$options = $this->getOptions();
			$tables = explode("," , $options['tables']);
			$tables_all = array_merge($tables, $tables_allowed);
			
			foreach($tables_all as $table){
				if(trim($table)==$tableCheck)
					return true;
			}
			 
			return false;
		}
		
		//need to add allowed tables
		function embedPagination(
			$table=null, $fields=null, 
			$filter=null, $order_by=null, 
			$pagesize=WelocallyWPPagination::PAGESIZE, 
			$content=null, $page=0,
			$odd=null, $even=null)
		{
			
			global $wpdb;	
			
			//only select fields we want
			$fieldList = explode(",", $fields);
			$fieldsClean = array();			
			$count = count($fieldList);
			$selectFields = "";
		    for ($i = 0; $i < $count; $i++) {
				$field = trim($fieldList[$i]);
				array_push($fieldsClean,$field);
				$selectFields = $selectFields.$field;
				if($i<$count -1){
					$selectFields = $selectFields.", ";
				}
			}
					
			$query = sprintf("SELECT %s FROM %s",$selectFields, $table);
			
			if(!empty($filter))
				$query = $query.$this->makeFilterSQLFromParts($this->makeFilterFromQuerystring(html_entity_decode ($filter)));
														
			if(!empty($order_by)){
				$query = $query.sprintf(" ORDER BY %s",$order_by); 
			}	
			
			//now manage the page
			$start = ($page-1)*$pagesize;  
			$query = $query.sprintf(" LIMIT %d,%d",$start, $pagesize); 

				
			$results = $wpdb->get_results($query, ARRAY_A);
			$vcontent ="";
			
			$count = count($results);
		    for ($i = 0; $i < $count; $i++) {
		    	$row = $results[$i];					
       			$vcontent = $vcontent.$this->mergeRow($row, $fieldsClean, $content, $i, $odd, $even);
			}		
			return $vcontent;
		}
		
		

		function makeFilterSQLFromParts($parts){
		        $sql = " WHERE";
		
		    $count = count($parts);
		    for ($i = 0; $i < $count; $i++) {
		            $part = $parts[$i];
		            if($i>0)
		                    $sql = $sql." AND";
		
		            if($part->filterType=='STRING')
		                    $value = "'".$part->filterValue."'";
		            else if($part->filterType=='DATE')
		                    $value = "'".$part->filterValue." 00:00:01'";
		            else
		                    $value = $part->filterValue;
		
		            $sql = $sql." ".$part->filterField.$part->filterOperator.$value;
		        }
		
		        return $sql;
		}
		
		function makeFilterFromQuerystring($qs){
		
		        $result = array();
		    	$parts = explode("&",$qs);
		    	
		    	//iterate on parts and make each an clause, field, operator and test
		        foreach($parts as $part){
		                array_push($result, $this->makePartObject($part));
		        }
		
		        return $result;
		}
		
		function makePartObject($part){
		
	        $operator = $this->findOperatorForPart($part);
	        
	        $nv = explode($operator,$part);
	        $t = new StdClass();
	       	$t->filterField = $nv[0];
	        
	        if(preg_match("/\#/", $nv[1]))
	            $t->filterType = "NUMBER";
		    else if(preg_match("/\@/", $nv[1]))
		        $t->filterType = "DATE";
		    else 
		    	$t->filterType = "STRING";
		        		        
		    $t->filterValue = preg_replace("/#|@/", "", $nv[1]);
		    if($operator == "~"){
		    	$t->filterValue = "%".$t->filterValue."%";
		    	$t->filterOperator = " LIKE ";
		    } else 
		    	$t->filterOperator = $operator;

		    return $t;
		}
		
		function findOperatorForPart($part){
		    if(preg_match("/\=/", $part))
		            return "=";
		    if(preg_match("/\~/", $part))
		            return "~";        
		    if(preg_match("/\</", $part))
		            return "<";
		    if(preg_match("/\>/", $part))
		            return ">";
		
		
		}

		
		function mergeRow($row, $fields, $content, $i, $odd, $even){
			
			if(!empty($even) && $i % 2 == 0){
				$content = str_replace("%ROW_TOGGLE%", $even, $content);
			}
			
			if(!empty($odd) && $i % 2 != 0){
				$content = str_replace("%ROW_TOGGLE%", $odd, $content);
			}
							
			foreach($fields as $field){
				$field = trim($field);
				
				
				if($field == "place"){
					$val = str_replace("'", "\'", $row[$field]);
					$content = str_replace("%$field%", "'".$val."'", $content);
				} else {
					$content = str_replace("%$field%", $row[$field], $content);
				}
				
				
			}
			return $content; 
		}
		
		function onActivate(){			
		
		}
		
		/// OPTIONS DATA
		//--------------------------------------------
        public function getOptions() {
            $options = get_option(WelocallyWPPagination::OPTIONNAME, array());
            
            $default_theme = 'basic'; 
                      
            //theme
	    	if ( !array_key_exists( 'theme', $options ) ) { $options[ 'theme' ] = $default_theme; $changed = true; }
	    	if ( !array_key_exists( 'tables', $options ) ) { $options[ 'tables' ] = null; $changed = true; }
	    	
			$this->latestOptions = $options;
		            
            return $this->latestOptions;
        }

		public function getSingleOption( $key ) {
			$options = $this->getOptions();
			return $options[$key];
		}
		        
        public function saveOptions($options) {
           update_option(WelocallyWPPagination::OPTIONNAME, $options);
           $this->latestOptions = $options;
        }
        
        public function deleteOptions() {
            delete_option(WelocallyWPPagination::OPTIONNAME);
        }
		
		function hooks(){
			add_shortcode('wlpager', array ($this,'handleWlPagerShortcode'));			
			add_action( 'init',	array( $this, 'loadDomainStylesScripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'loadAdminDomainStylesScripts' ) );			
		}
				
		

	}
	
	global $wlPager;
	$wlPager = new WelocallyWPPagination();
}
?>
