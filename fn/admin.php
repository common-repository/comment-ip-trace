<?php
/**
 * Admin area only functionality
 *
 * @since 1.0
 */
class Comment_IP_Trace_admin {
	// Stores GEO IP data
	private $geo_data;
	
	/**
	 * Loader function to include all the actions and filters
	 * 
	 * @since 1.0
	 * @return void
	 */
	public function Comment_IP_Trace_admin() {
		// Add custom comment columns
		add_action ( 'manage_comments_custom_column', array ( &$this, 'my_comments_columns' ) );
		add_filter ( 'manage_edit-comments_columns', array ( &$this, 'my_custom_columns' ) );
		
		// Add admin menu page
		add_action( 'admin_menu', array ( &$this, 'page_menu' ) );
	}
	
	/**
	 * Do action function to display the authors location on the commetns tab
	 * 
	 * @param string $column
	 * @since 1.0
	 * @return void
	 */
	public function my_comments_columns ( $column ) {
		global $comment, $cit_core;
		$ip = $comment->comment_author_IP;
		if ( $ip == '127.0.0.1' ){
			echo 'Cannot trace a local host IP address.';
		} elseif ( $column == 'trace_ip' && $ip ) {
			$location = $cit_core->get_comment_location ( $comment->comment_ID );
			if ( $location ['city'] ) echo $location ['city'];
			if ( $location ['city'] && $location ['region'] ) echo ', ';
			if ( $location ['region'] ) echo $location ['region'];
			if ( $location ['country'] && $location ['region'] ) echo ', ';
			if ( $location ['country'] ) echo $location ['country'];
		} elseif ( $column == 'trace_ip' && ! $ip ) {
			echo 'IP Cannot be traced.';
		}
	}
	
	/**
	 * Filter function to add the extra column to the comment page
	 * 
	 * @param array $columns
	 * @since 1.0
	 * @return array
	 */
	public function my_custom_columns ( $columns ) {
		$columns = array (
			'cb' => '<input type="checkbox" />',
			'author' => 'Author',
			'trace_ip' => 'Author Location',
			'comment' => 'Comment',
			'response' => 'In Response To'
		);
		return $columns;
	}
	
	/**
	 * Function to add the admin menu to the settings tab
	 * 
	 * @since 1.1
	 * @return void
	 */
	public function page_menu () {
		$settings_page = add_options_page( 'Comment Location Tracker', 'Location Tracker', 'manage_options', 'comment-location-tracker', array( &$this, 'admin_settings_page' ) );
	}
	
	/**
	 * Settings page for this plugin.
	 * 
	 * @since 1.1
	 * @return void
	 */
	public function admin_settings_page () {
		global $cit_core;
		// Check for post saves
		if ( isset( $_POST['comment-ip-trace-save'] ) ) {
			$cit_core->set_options( $_POST['comment-ip-trace'] );
			echo '<div class="updated"><p style="text-align:center;">Options Saved!</p></div>';
		}
		
		// Get plugin options
		$values = $cit_core->options;
		
		
		// Check boxes
		$front_end_comment_add = ( $values ['front_end_comment_add'] == 'show' ) ? ' checked="checked"': null;
		$show_admin_locations = ( $values ['show_admin_locations'] == 'show' ) ? ' checked="checked"': null;
		?>
		<div class="wrap">
		
			<div id="wp-content">
			
				<h2>Comment IP Trace</h2>
					
				<form method="post">
				
					<table class="form-table">
					
						<tr valign="top">
							<th scope="row"><label for="bit-uname">Show location on front-end: </label></th>
							<td><input type="checkbox" id="bit-uname" name="comment-ip-trace[front_end_comment_add]" value="show" <?php echo $front_end_comment_add; ?> /></td>
						</tr>
						
						<tr valign="top">
							<th scope="row"><label for="bit-key">Show admin location on front-end: </label></th>
							<td><input type="checkbox" id="bit-uname" name="comment-ip-trace[show_admin_locations]" value="show" <?php echo $show_admin_locations; ?> /></td>
						</tr>
						
					</table>
					
					<p class="submit">
						<input type="submit" name="comment-ip-trace-save" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
					</p>
					
				</form>
			
			</div> <!-- End wp-content -->
			
		</div> <!-- End Wrap -->
		<?php
	}
} $cit_admin = new Comment_IP_Trace_admin();