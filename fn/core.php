<?php
/**
 * Plugin core class
 *
 * @since 1.0
 */
class Comment_IP_Trace {
	// Stores the geo IP class
	public $gc;

	// Stores plugin options
	public $options;

	// Stores default options
	public $default_options;

	/**
	 * Class loader function
	 *
	 * @since 1.0
	 * @return void
	 */
	public function Comment_IP_Trace() {
		// Database setup and upgrades
		$db_version = get_option( 'comment-ip-trace-db-version', 0 );
		if ( $db_version < CIT_DB_VERSION ) $this->database_setup();

		// Load geo ip class object
		$this->load_geo_ip ();

		// Load options
		$this->load_options ();

		// Add filters
		add_action( 'comment_post', array ( &$this, 'cache_new_comment_location' ), 2, 1 );
		add_action ( 'deleted_comment', array ( &$this, 'delete_comment_cache_item' ), 2, 1  );
	}

	/**
	 * Loads the plugin options into a class variable [$options]
	 *
	 * @since 1.0
	 * @return void
	 */
	private function load_options () {
		$this->option_defaults = array (
			'front_end_comment_add' => true,
			'show_admin_locations' => true
		);
		$this->options = get_option ( 'comment-ip-trace', $this->option_defaults );
	}

	/**
	 * Updates the plugin options with the input values
	 *
	 * @param array $update
	 * @since 1.1
	 * @return void
	 */
	public function set_options ( $update = array() ) {
		update_option ( 'comment-ip-trace', $update );
		$this->options = $update;
	}

	/**
	 * Loads the Geoplugin class into the class function $gc
	 *
	 * @since 1.0
	 * @return void
	 */
	public function load_geo_ip () {
		include_once CIT_FNS_DIR . '/geoplugin.class.php';
		$this->gc = new geoPlugin();
	}

	/**
	 * Gets comment data and stores it into a cache database
	 *
	 * @param int $commentid
	 * @since 1.0
	 * @return array Contains 'country', 'region', 'city'
	 */
	public function cache_new_comment_location ( $comment_id ) {
		$comment_data = get_comment( $comment_id );

		$author_ip = $comment_data->comment_author_IP;

		$location = $this->get_location ( $author_ip );

		// Add to cache database
		$this->cache_comment_author_location  ( $comment_id, $location ['country'], $location ['region'], $location ['city'] );
		return $location;
	}

	/**
	 * Gets location data by IP address
	 *
	 * @param string $ip
	 * @since 1.0
	 * @return array 'country', 'region', 'city'
	 */
	public function get_location ( $ip ) {
		$this->gc->locate( $ip );

		$location ['country'] = $this->gc->countryName;
		$location ['region'] = $this->gc->region;
		$location ['city'] = $this->gc->city;
		return $location;
	}

	/**
	 * Adds new comment cache data to the location database
	 *
	 * @param int $comment_id
	 * @param string $country
	 * @param string $region
	 * @param string $city
	 * @since 1.0
	 * @return int|false The number of rows inserted, or false on error.
	 */
	public function cache_comment_author_location ( $comment_id, $country, $region, $city ) {
		global $wpdb;

		// Insert data
		$insert = array (
			'comment_id' => $comment_id,
			'country' => $country,
			'region' => $region,
			'city' => $city,
			'date_added' => current_time ('mysql')
		);

		// Add to cache database
		return $wpdb->insert( $wpdb->prefix . 'ip_comment_trace', $insert );
	}

	/**
	 * Gets the location data for a comment from the database or api if database returns null
	 *
	 * @param int $comment_id
	 * @since 1.0
	 * @return array Contains 'country', 'region', 'city'
	 */
	public function get_comment_location ( $comment_id ) {
		global $wpdb;

		$query = "
			SELECT country, region, city
			FROM {$wpdb->prefix}ip_comment_trace
			WHERE comment_id = '{$comment_id}'
			LIMIT 1;
		";

		$results = $wpdb->get_row( $query, ARRAY_A );

		if ( ! is_null( $results ) ) return $results;
		else return $this->cache_new_comment_location ( $comment_id );
	}

	/**
	 * Delets cache data when a comment is deleted
	 *
	 * @param int $comment_id
	 * @since 1.0
	 * @return void
	 */
	public function delete_comment_cache_item ( $comment_id ) {
		global $wpdb;

		$query = "
			DELETE FROM {$wpdb->prefix}ip_comment_trace
			WHERE comment_id = '{$comment_id}';
		";

		$wpdb->query ( $query );
	}

	/**
	 * Whether current user has capability or role.
	 *
	 * @since 1.1
	 *
	 * @param int $user_id The id of the user to test
	 * @param string $capability Capability or role name.
	 * @return bool
	 */
	public function user_can ( $user_id, $capability ) {
		$args = array_slice ( func_get_args (), 1 );
		$args = array_merge ( array ($capability ), $args );
		$user = new WP_User( $user_id);
		return call_user_func_array ( array (&$user, 'has_cap' ), $args );
	}

	/**
	 * Creates and updates tables used in this plugin
	 *
	 * @since 1.0
	 * @return void
	 */
	private function database_setup () {
		global $wpdb;
		if ( ! empty ( $wpdb->charset ) ) $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";

		// Caching Table
		$sql [] = "CREATE TABLE {$wpdb->prefix}ip_comment_trace (
					`id` int(11) NOT NULL AUTO_INCREMENT ,
					`comment_id` int(11) ,
					`country` varchar(255) ,
					`region` varchar(255) ,
					`city` varchar(255) ,
					`date_added` datetime ,
					PRIMARY KEY  (id)
			 	   ) {$charset_collate};";

		// Require upgrade funtions
		require_once ( ABSPATH . 'wp-admin/upgrade-functions.php' );

		// Do updated
		dbDelta ( $sql );

		// Save to updated version
		update_option ('apple-loader-db-version', CIT_DB_VERSION );
	}

} $cit_core = new Comment_IP_Trace();
