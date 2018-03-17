<?php


function  frontier_user_post_list($fpost_sc_parms = array())
	{
	//extract($fpost_sc_parms);
	 
	 // To reset task query var, to allow for inline ad of post form
	 unset($_REQUEST['task']); 
	
	
	global $post;
	$current_user = wp_get_current_user();
	

	$tmp_p_id = get_the_id();
	
	
	$pagenum	= isset( $_GET['pagenum'] ) ? intval( $_GET['pagenum'] ) : 1;
	
	$tmp_default_status = array('draft', 'pending', 'publish', 'private', 'future');
	
	
	if (count($fpost_sc_parms['frontier_user_status'])>0)
		$tmp_status_list = array_intersect($fpost_sc_parms['frontier_user_status'], $tmp_default_status );
	else 
		$tmp_status_list = $tmp_default_status;

	$args = array(
			'post_type' 		=> $fpost_sc_parms['frontier_list_post_types'],
			'post_status' 		=> $tmp_status_list,
			'order'				=> 'DESC',
			'orderby' 			=> 'post_date', 
			'posts_per_page'    => $fpost_sc_parms['frontier_ppp'],
			'paged'				=> $pagenum,
			);
	
	// add category from shortcode to limit posts
	if ( $fpost_sc_parms['frontier_list_cat_id'] > 0) 
		$args["cat"] = implode(",", $fpost_sc_parms['frontier_list_cat_id']);

	
	//List all published posts
	if ( fp_bool($fpost_sc_parms['frontier_list_all_posts']) )
		{
		// limit list to status=publish to the list, if users do not have private posts (editors & admins)
		if (!current_user_can( 'edit_private_posts' ))
			{
			$args["post_status"] = "publish";
			}
		}
	else
		{
		$args["author"] = $current_user->ID;
		}
	
	// List pending posts
	if ( fp_bool($fpost_sc_parms['frontier_list_pending_posts']) )
		{
		if ( !current_user_can( 'edit_others_posts' ) )
			{
			echo '<br><div id="frontier-post-alert">'.__("You do not have access to other users pending posts", "frontier-post").'</div><br>';
			return;
			}
		$args["post_status"] = "pending";
		if ( array_key_exists("author", $args) )
			unset($args['author']);
		}
	
	// List pending posts
	if ( fp_bool($fpost_sc_parms['frontier_list_draft_posts']) )
		{
		if ( !current_user_can( 'edit_others_posts' ) )
			{
			echo '<br><div id="frontier-post-alert">'.__("You do not have access to other users draft posts", "frontier-post").'</div><br>';
			return;
			}
		$args["post_status"] = "draft";
		if ( array_key_exists("author", $args) )
			unset($args['author']);
		}
		
	$user_posts 	= new WP_Query( $args );

	$fp_show_icons 	= fp_get_option_bool('fps_use_icons');
	$fp_list_form 	= fp_get_option("fps_default_list", "list");



	//echo "SC List form: ".$fpost_sc_parms['frontier_list_form']."<br>";
	
	$allowed_forms = array("simple", "list", "excerpt", "full_post" );
	if ( in_array($fpost_sc_parms['frontier_list_form'], $allowed_forms) )
		$fp_list_form = $fpost_sc_parms['frontier_list_form'];
		
	//echo "List form: ".$fp_list_form."<br>";
	
	switch ($fp_list_form)
		{
		case 'simple':
			include_once(frontier_load_form("frontier_post_form_list.php"));
			break;
		
		case 'theme':
			include_once(frontier_load_form("frontier_post_form_list_theme.php"));
			break;
			
		default:
			include_once(frontier_load_form("frontier_post_form_list_detail.php"));
			break;
		}
	
	
	}  
?>