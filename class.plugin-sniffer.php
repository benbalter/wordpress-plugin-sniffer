<?php
/**
 * WordPress Plugin Sniffer
 *
 * @author Benjamin J. Balter <ben@balter.com>
 * @package
 */

class WP_Plugin_Sniffer {

	public $api = 'http://api.wordpress.org/plugins/info/1.0/';
	public $per_page = 50;
	public $count = 100;
	public $timeout = 30;
	public $found = array();
	public $plugins = array();
	public $home = null;
	public $dir = 'wp-content/plugins/';
	public $ttl = 86400;
	public $path_to_wp = '../3.3.1/';
	public $num_found = 0;

	/**
	 * Bookstrap WP
	 */
	function __construct( $home = null, $count = null ) {

		//bootstrap
		include $this->path_to_wp . 'wp-load.php';
		include $this->path_to_wp . 'wp-admin/includes/plugin-install.php';
		require_once( 'tlc-transients/tlc-transients.php' );
		
		set_time_limit( 0 );

		if ( $home != null )
			$this->home = esc_url( $home );
			
		if ( $count != null )
			$this->count = (int) $count;
			
		add_filter( 'http_request_timeout', array( &$this, 'timeout_filter' ) );
		
	}
	
	/**
	 * WP API server is slow to query lots of plugins, give it more time
	 */
	function timeout_filter() {
		return $this->timeout;
	}
	
	/**
	 * Grab a specific page of results
	 * Note: > 100, things get a bit iffy on WP's side
	 * @param int $num the page number to retrieve
	 * @retun array an array of plugin slugs
	 */
	function fetch_page( $page = 1 ) {
	
		$plugins = plugins_api( 'query_plugins', array( 
			'browse'   => 'popular', 
			'per_page' => $this->per_page, 
			'fields'   => array(),
			'page'     => $page, 
			)
		);

		if ( is_wp_error( $plugins ) )
			wp_die( "Can't retrieve plugin list" );
		
		return wp_list_pluck( $plugins->plugins, 'slug' );
	
	}
	
	/**
	 * Get the top N plugin slugs
	 * Note: we fetch full pages of 100 and 
	 * then slice down the array as necessary 
	 * to allow for cleaner caching
	 * @param int $num the number of plugins to retrieve
	 * @return array an array of the top $num plugin slugs
	 */
	function get_plugins( $num = null ) {

		if ( $num == null )
			$num = $this->count;
		
		$num = (int) $num;
		$page = 1;
		$pages = (int) ( ceil( $num / $this->per_page ) );
		$plugins = array();
				
		for( $page = 1; $page <= $pages; $page++ ) {	
				
			$this_page = tlc_transient( 'plugin_sniffer_' . $this->per_page . 'x' . $page )
						->updates_with( array( &$this, 'fetch_page' ), array( $page ) )
						->expires_in( $this->ttl )
						->get();
												
			$plugins = array_merge( $plugins, $this_page );
		
		}
		
		$this->plugins = array_slice( $plugins, 0, $num ); 
		return $this->plugins;
		
	}
	
	/**
	 * Check a given site
	 * @param string $home the home URL of the site
	 * @param int $num number of plugins to check
	 */
	function sniff( $home = null, $num = null ) {
		
		if ( $home == null )
			$home = $this->home;
			
		$this->home = trailingslashit( esc_url( $home ) );
		$this->found = array();
		$plugins = $this->get_plugins( $num );
						
		foreach ( $plugins as $plugin ) {
		
			$response = wp_remote_head( trailingslashit( $this->home . $this->dir . $plugin ), array( 'timeout' => $this->timeout ) );
			
			//either the directory exists, in which case we'd get either a 403 (denied) or 200 (a listing)
			//or it doesn't exist, in which case the plugin doesn't exist
			if ( wp_remote_retrieve_response_code( $response ) == 404 )
				continue;
				
			$this->found[] = $plugin;
	
		}
		
		$this->num_found = count( $this->found );
		
		return $this->found;
		
	}


}