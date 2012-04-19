<?php
/**
 * WordPress Plugin Sniffer
 *
 * @author Benjamin J. Balter <ben@balter.com>
 * @package
 */

class WP_Plugin_Sniffer {

	public $api = 'http://api.wordpress.org/plugins/info/1.0/';
	public $count = 100;
	public $timeout = 30;
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
	 * Grab an array of the most popular plugins
	 * @return array of plugins
	 */
	function fetch_popular() {
		
		$this->count = (int) ( isset( $_GET['count'] ) ) ? $_GET['count'] : $this->count;
		
		if ( $cache = get_transient( 'top_' . $this->count . '_popular_plugins' ) )
			return $cache;
	
		$plugins = plugins_api( 'query_plugins', array( 
			'browse' => 'popular', 
			'per_page' => $this->count, 
			'fields' => array() 
			)
		);
		
		if ( is_wp_error( $plugins ) )
			wp_die( "Can't retrieve plugin list" );
	
		//don't overload MySQL buffer	
		if ( $this->count < 100 )
			set_transient( 'top_' . $this->count . '_popular_plugins', $plugins->plugins, $this->ttl );
		
		return $plugins->plugins;

	}


	/**
	 * Check a given site
	 * @param string $home the home URL of the site
	 */
	function sniff() {
		
		$this->home = trailingslashit( esc_url( ( isset( $_GET['home'] ) ) ? $_GET['home'] : $this->home ) );

		$plugins = $this->fetch_popular();
				
		foreach ( $plugins as $plugin ) {

			$response = wp_remote_head( trailingslashit( $this->home . $this->dir . $plugin->slug ), array( 'timeout' => $this->timeout ) );
			
			//either the directory exists, in which case we'd get either a 403 (denied) or 200 (a listing)
			//or it doesn't exist, in which case the plugin doesn't exist
			if ( wp_remote_retrieve_response_code( $response ) == 404 )
				continue;
				
			$this->plugins[] = $plugin->slug;
	
		}
		
		$this->num_found = count( $this->plugins );
		
		return $this->plugins;
		
	}


}