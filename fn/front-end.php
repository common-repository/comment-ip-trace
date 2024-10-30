<?php
/**
 * Class for front end functionality displayed to site guest
 * 
 * @since 1.0
 */
class Comment_IP_Trace_front_end {
	
	/**
	 * Constructor fucntion
	 * 
	 * @since 1.0
	 * @return void
	 */
	public function Comment_IP_Trace_front_end () {
		// Add filter
		add_filter ( 'get_comment_author', array ( &$this, 'comment_add_author_name_filter' ) );
	}
	

	/**
	 * Adds the location to the comment loop by the name.
	 * 
	 * @param string $author
	 * @since 1.1
	 * @return string
	 */
	public function comment_add_author_name_filter ( $author ) {
		global $comment, $cit_core;
		
		$ip = $comment->comment_author_IP;
		if ( $ip && $ip != '127.0.0.1' ) {
			
			// Check if allowed to show comments on front end
			if ( $cit_core->options ['front_end_comment_add'] != 'show' ) return $author;
			
			// Check if allowed to show site admin locations on front end
			if ( $cit_core->options ['show_admin_locations'] != 'show' && $cit_core->user_can( $comment->user_id, 'manage_options' ) ) return $author;
			
			$author .= '<span class="comment-author-location">';
			$author .= ' from ';
			$location = $cit_core->get_comment_location ( $comment->comment_ID );
			if ( $location ['city'] ) $author .=  $location ['city'];
			if ( $location ['city'] && $location ['region'] ) $author .= ', ';
			if ( $location ['region'] ) $author .=  $location ['region'];
			if ( $location ['country'] && $location ['region'] ) $author .= ', ';
			if ( $location ['country'] ) $author .=  $location ['country'];
			$author .= '</span>';
		}
		return $author;
	}
	
} $cit_front_end = new Comment_IP_Trace_front_end();