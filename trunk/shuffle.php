<?php
/*
Plugin Name: Shuffle
Description: Re-Order your attachments
Author: Scott Taylor
Version: 0.2
Author URI: http://tsunamiorigami.com
*/

$shuffle_post_id = 0;
$base_params = array(
	'order'       => 'ASC',
	'orderby'     => 'menu_order',
	'post_type'   => 'attachment',
	'post_status' => 'inherit',
	'numberposts' => -1
);

define('ICON', '<div id="icon-upload" class="icon32"><br /></div>');
define('PLUGIN_URL', plugin_dir_url('') . 'shuffle/');

function shuffle_do_link($id, $str, $extra = '') {
	return sprintf('<a href="upload.php?page=shuffle_media&post_id=%d%s">%s</a>', $id, $extra, $str);
}

function shuffle_posts_link($type = 'post', $str = 'Back') {
	if ($type === 'post') {
		return sprintf('<a href="edit.php">%s</a>', $str);
	}

	return sprintf('<a href="edit.php?post_type=%s">%s</a>', $type, $str);
}

function shuffle_back_link() {
	$back = '';
	
	if (isset($_GET['post_id']) && (int) $_GET['post_id'] > 0) {
		$id = (int) $_GET['post_id'];
		$parents =& get_post_ancestors($id);
		
		if (is_array($parents) && (int) $parents[0] > 0) {		
			$back = shuffle_do_link($parents[0], sprintf(__('Back to &#8220;%s&#8221;'), get_the_title($parents[0])));
		} else {
			$type = get_post_type($id);
			$back = shuffle_posts_link($type, sprintf(__('Back to %s list'), ucfirst($type)));
		}	
		unset($parents);
	}
	
	echo $back;
}

function shuffle_get_default_img_id($post_id) {
	global $wpdb;
	
	$meta_key = '_thumbnail_id';
	$query = "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = %s AND post_id = %d";
	$featured_id = $wpdb->get_var($wpdb->prepare($query, $meta_key, $post_id));
		
	return $featured_id;	
}

function shuffle_by_mime_type($type = 'image', $params = 0, $and_featured = '') {
	global $base_params;
	
	$posts = array();

	if (!empty($type)) {
	
		$id = 0;	
		$exclude_posts = array();
		$include_featured = (!empty($and_featured) && $and_featured);
		 
		if (is_array($params)) {	
			if (array_key_exists('post_mime_type', $params)) {
				unset($params['post_mime_type']);
			}		
		
			if (array_key_exists('id', $params)) {
				$id = $params['id'];
			} else {	
				$id = get_the_id();
			}
		
			if ((int) $id > 0) {
				if (strstr($type, 'image') && (
						(array_key_exists('and_featured', $params) && !$params['and_featured']) ||
						!$include_featured
					)
				) {
					$featured = array(shuffle_get_default_img_id($id));
					
					if (array_key_exists('post__not_in', $params)) {
						$save = $params['post__not_in'];
						$params['post__not_in'] = array_merge($exclude_posts, $save, $featured);
						unset($save, $featured);
					}
				}		
		
				$posts =& get_posts(array_merge($base_params, array(
					'post_parent'    => $id,
					'post_mime_type' => $type
				), $params));
			}	
		} else {
			if ((int) $params > 0) {
				$id = $params;
			} else {
				$id = get_the_id();
			}
		
			if ((int) $id > 0 ) {
				if (!$include_featured) {
					$exclude_posts = array(shuffle_get_default_img_id($id));
				}			
				$posts =& get_posts(array_merge($base_params, array(
					'post_parent'    => $id,
					'post_mime_type' => $type,
					'post__not_in' => $exclude_posts
				)));
			}			
		}
	}
	return $posts;
}

function shuffle_images_by_post() {
	global $shuffle_post_id;
	
	$imgs =& shuffle_by_mime_type('image', $shuffle_post_id);	
	$size = count($imgs);	
	
	if ($size > 0): ?>
		<h3><?php _e('Images') ?></h3>
		<ul id="shuffle-images"><?php
		foreach ($imgs as $i): 
			$meta =& wp_get_attachment_image_src($i->ID, array(75, 75), true);
			$tag = '<img src="' . $meta[0] . '" width="' . $meta[1] . '" height="' . $meta[2] . '"/>';
			
			echo '<li data-id="', $i->ID, '" data-orig-order="', $i->menu_order, '">' .
				shuffle_do_link($i->ID, $tag) .
			'</li>';
			
			unset($meta);
		endforeach;
		?></ul>
<?php	
	endif;
	
	unset($imgs);
	return $size;
}

function shuffle_list_item(&$obj) {
	return '<li data-id="' . $obj->ID . '" data-orig-order="' . $obj->menu_order . '">' . 
		shuffle_do_link($obj->ID, apply_filters('the_title', $obj->post_title)) .
	'</li>';
}

function shuffle_audio_by_post() {
	global $shuffle_post_id;
	
	$audio =& shuffle_by_mime_type('audio', $shuffle_post_id);;	
	$size = count($audio);
	
	if ($size > 0): ?>
		<h3><?php _e('Audio') ?></h3>
		<ul id="shuffle-audio"><?php
		foreach ($audio as $i): echo shuffle_list_item($i); endforeach;
		?></ul>
<?php	
	endif;

	unset($audio);
	return $size;
}

function shuffle_video_by_post() {
	global $shuffle_post_id;
	
	$video =& shuffle_by_mime_type('video', $shuffle_post_id);
	$size = count($video);		
	
	if ($size > 0): ?>
		<h3><?php _e('Video') ?></h3>
		<ul id="shuffle-video"><?php
		foreach ($video as $i): echo shuffle_list_item($i); endforeach;
		?></ul>
<?php	
	endif;
	
	unset($video);
	return $size;
}

function shuffle_save_items(&$postdata) {
	global $wpdb;

	if (!empty($postdata)) {
		$items = json_decode(stripslashes($postdata));
		
		foreach ($items as $i) {
			$wpdb->update($wpdb->posts, 
				array('menu_order' => $i->order),
				array('ID' => $i->id)
			);
		} 
		
		unset($items);
	}
}

function shuffle_show() {
	global $shuffle_post_id;
?><div class="wrap" id="shuffle-wrap">	
<?php	
	if ('POST' == $_SERVER['REQUEST_METHOD']) {
		shuffle_save_items($_POST['image_data']); 
		shuffle_save_items($_POST['audio_data']);
		shuffle_save_items($_POST['video_data']);
?><div class="updated fade" id="shuffle-notice">
		<p><?php _e('Your media has been shuffled!') ?></p>
	</div><?php	
	}			
?>
<?php if (isset($_GET['post_id']) && (int) $_GET['post_id'] > 0): 
	$shuffle_post_id = $_GET['post_id']; ?>
	<p><?php shuffle_back_link(); ?></p>
	<?php echo ICON ?>
	<h2><?php printf(__('Shuffle Media for "%s"'), get_the_title($shuffle_post_id)) ?></h2>
<?php if (strstr(get_post_mime_type($shuffle_post_id), 'image')): 
	$meta = wp_get_attachment_image_src($shuffle_post_id, 'medium', true); ?> 
	<img src="<?= $meta[0] ?>" width="<?= $meta[1] ?>" height="<?= $meta[2] ?>"/>	
	<?php endif; ?>
	<p><?php _e("Drag your media to re-order. When you're done dragging, hit the blue button!") ?></p>
<?php	
	$imgs = shuffle_images_by_post();
	$auds = shuffle_audio_by_post();
	$vids = shuffle_video_by_post();
	
	$has_atts = ($imgs + $auds + $vids) > 0;
	
	if ($has_atts): ?>	
	<form id="shuffle-form" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<fieldset>	
			<input type="hidden" id="image-data" name="image_data" />
			<input type="hidden" id="audio-data" name="audio_data" />
			<input type="hidden" id="video-data" name="video_data" />
			<input class="button-primary" type="submit" name="save" value="<?php _e('Click to Re-Order') ?>" />
		</fieldset>
	</form>	
	<?php else:	?>
	<p><strong><?php _e('This item has no attachments.') ?></strong></p>
	<?php endif; ?>
<?php else: ?>
	<?php echo ICON ?>
	<h2><?php _e('Shuffle Media') ?></h2>
	<p><strong><?php _e("Please select an item (Post, Page, Custom Post Type) to shuffle. After you have selected the post, you can reorder it's attachments!") ?></strong></p>
	<p><?php _e('Shuffle modifies/improves your Media Library in a number of ways. Shuffle lets you:') ?></p>
	<ol>
		<li><?php _e('Attach an item (Image, Audio, Video) to anything (Post, Page, Custom Post Type, another Attachment)!') ?></li>
		<li><?php _e("Reorder an item's attachments using a simple Drag and Drop UI") ?></li>
		<li><?php _e('Detach an attachment from an item without deleting the attachment') ?></li>
		<li><?php _e('View all attachments attached to an item') ?></li>
	</ol>
<?php endif; 
?></div><?php
}

function shuffle_page() {
	if ( function_exists('add_submenu_page') )
		add_submenu_page('upload.php', 
			__('Shuffle'), __('Shuffle'), 'edit_posts', 'shuffle_media', 'shuffle_show');
}

function shuffle_init() {
	add_action('admin_menu', 'shuffle_page');
}
add_action('init', 'shuffle_init');

function shuffle_styles() {
	wp_enqueue_style('shuffle-css', PLUGIN_URL . 'shuffle.css');
}
add_action('admin_print_styles', 'shuffle_styles');

function shuffle_scripts() {
	wp_enqueue_script('shuffle-js', PLUGIN_URL . 'shuffle.js', array('json2', 'jquery-ui-sortable'), '');
}
add_action('admin_print_scripts', 'shuffle_scripts');

function add_shuffle_link($actions, $post) {
	global $link_tokens;

    $actions = array_merge($actions, array(
        'shuffle_media' => 
        	shuffle_do_link($post->ID, __('Shuffle Media'))
    ));
    
    return $actions;
}
add_filter('post_row_actions', 'add_shuffle_link', 10, 2);
add_filter('page_row_actions', 'add_shuffle_link', 10, 2);

function shuffle_columns($defaults) {
	$defaults['shuffle'] = __('Detach');
	return $defaults;
}
add_filter('manage_media_columns', 'shuffle_columns');

function shuffle_custom_column($column_name, $id) {
    if ($column_name === 'shuffle') {    
    	$parent = (int) get_post_field('post_parent', (int) $id);

		if ($parent > 0) {
			printf('<a href="admin.php?action=shuffle_detach&amp;post_id=%d">%s</a>', $id, __('Detach'));
		}
    }
}
add_action('manage_media_custom_column', 'shuffle_custom_column', 10, 2);

function shuffle_detach() {
	global $wpdb;

	if (isset($_GET['post_id']) && (int) $_GET['post_id'] > 0) {
		$wpdb->update($wpdb->posts, 
			array(
				'post_parent' => 0,
				'menu_order' => 0
			),
			array('ID' => $_GET['post_id'])	
		);
	}
	
	$sendback = wp_get_referer();
	$sendback = preg_replace('|[^a-z0-9-~+_.?#=&;,/:]|i', '', $sendback);
	wp_redirect($sendback);
	exit(0);	
}
add_action('admin_action_shuffle_detach', 'shuffle_detach');

/**
 *
 *
 *
 *
 * WARNING!!!	
 * OVERRIDING WORDPRESS CORE FUNCTION 
 *
 *
 * This is where we override the "find_posts" WordPress AJAX action
 * to include posts with a post_status of "inherit" (Attachments)
 *
 *
 *
 *
 *
 */

function shuffle_add_attachment_type_callback() {
	/**
	 *
	 *
	 * $wpdb has to be imported since we are in function scope
	 *
	 *
	 */
	global $wpdb;

	/**
	 *
	 *
	 * turn off check_ajax_referrer()
	 * the nonce will be wrong, we are hijacking the action
	 * in Javascript
	 *
	 * check_ajax_referer( 'shuffle_get_attachment_type' );
	 *
	 *
	 */	

	if ( empty($_POST['ps']) )
		exit;

	if ( !empty($_POST['post_type']) && in_array( $_POST['post_type'], get_post_types() ) )
		$what = $_POST['post_type'];
	else
		$what = 'post';

	$s = stripslashes($_POST['ps']);
	preg_match_all('/".*?("|$)|((?<=[\\s",+])|^)[^\\s",+]+/', $s, $matches);
	$search_terms = array_map('_search_terms_tidy', $matches[0]);

	$searchand = $search = '';
	foreach ( (array) $search_terms as $term ) {
		$term = addslashes_gpc($term);
		$search .= "{$searchand}(($wpdb->posts.post_title LIKE '%{$term}%') OR ($wpdb->posts.post_content LIKE '%{$term}%'))";
		$searchand = ' AND ';
	}
	$term = $wpdb->escape($s);
	if ( count($search_terms) > 1 && $search_terms[0] != $s )
		$search .= " OR ($wpdb->posts.post_title LIKE '%{$term}%') OR ($wpdb->posts.post_content LIKE '%{$term}%')";

	/**
	 *
	 *
	 * This is really all that changes in this function
	 * we add "inherit" to the IN query if the post_type is attachment
	 *
	 *
	 */
	
	$extra_type = '';
	if ($what === 'attachment') {
		$extra_type = ", 'inherit'";
	} 
	
	/**
	 * 
	 *
	 * We also need to keep a reference to the original unattached item so that we can't
	 * attach it to itself
	 *
	 *
	 */	
	 
	$not_me = '';
	if (isset($_POST['item_id'])) {
		if (strstr($_POST['item_id'], ',') ) {
			$id_list = $_POST['item_id'];
			$not_me = "AND ID NOT IN ($id_list)";
		} else {
			$not_me = 'AND ID != ' . $_POST['item_id'];		
		}
	} 
  	 
	/**
	 *
	 *
	 * Modified SQL
	 *
	 *
	 */	 
	 
	$posts = $wpdb->get_results( "SELECT ID, post_title, post_status, post_date FROM $wpdb->posts WHERE post_type = '$what' AND post_status IN ('draft', 'publish'$extra_type) AND ($search) $not_me ORDER BY post_date_gmt DESC LIMIT 50" );

	if ( ! $posts ) {
		$posttype = get_post_type_object($what);
		exit($posttype->labels->not_found);
	}

	$html = '<table class="widefat" cellspacing="0"><thead><tr><th class="found-radio"><br /></th><th>'.__('Title').'</th><th>'.__('Date').'</th><th>'.__('Status').'</th></tr></thead><tbody>';
	foreach ( $posts as $post ) {

		switch ( $post->post_status ) {
			case 'publish' :
			case 'private' :
				$stat = __('Published');
				break;
			case 'future' :
				$stat = __('Scheduled');
				break;
			case 'pending' :
				$stat = __('Pending Review');
				break;
			case 'draft' :
				$stat = __('Draft');
				break;
		}

		if ( '0000-00-00 00:00:00' == $post->post_date ) {
			$time = '';
		} else {
			/* translators: date format in table columns, see http://php.net/date */
			$time = mysql2date(__('Y/m/d'), $post->post_date);
		}

		$html .= '<tr class="found-posts"><td class="found-radio"><input type="radio" id="found-'.$post->ID.'" name="found_post_id" value="' . esc_attr($post->ID) . '"></td>';
		$html .= '<td><label for="found-'.$post->ID.'">'.esc_html( $post->post_title ).'</label></td><td>'.esc_html( $time ).'</td><td>'.esc_html( $stat ).'</td></tr>'."\n\n";
	}
	$html .= '</tbody></table>';

	$x = new WP_Ajax_Response();
	$x->add(array(
		'what' => $what,
		'data' => $html
	));
	$x->send();
}
add_action('wp_ajax_shuffle_add_attachment_type', 'shuffle_add_attachment_type_callback'); 
 	
/**
 *
 *
 * THEME FUNCTIONS
 *
 *
 * all of these function will also take an array() as the only argument (same params as query_posts)
 *
 * add 'and_featured' => true 
 * to include the post's featured image / post thumbnail in the result, it is excluded by default
 *
 *
 * if shuffle_by_mime_type() is called directly, you can pass true / false as the 3rd argument to 
 * return the post's featured image / post thumbnail
 *
 */ 	 
 	 
function get_images($id = 0) {
	return shuffle_by_mime_type('image', $id);
}	

function get_audio($id = 0) {
	return shuffle_by_mime_type('audio', $id);	
}

function get_video($id = 0) {
	return shuffle_by_mime_type('video', $id);	
} 
?>
