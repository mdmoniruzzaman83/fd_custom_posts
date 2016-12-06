<?php
/**
* Plugin Name: FD Custom Post
* Plugin URI: http://mypluginuri.com/
* Description: A brief description about your plugin.
* Version: 2.2.0
* Author: Foundation Digital
* Author URI: http://mypluginuri.com/
* License: GPLv2 or later
Text Domain: fdcp
*/
add_action('admin_menu', 'fd_custom_post');
function fd_custom_post(){
	add_menu_page('CP Test Settings', 'CP Text Settings', 'administrator', __FILE__, 'fd_custom_post_settings' , plugins_url('/images/icon.png', __FILE__) );
	
	add_action( 'admin_init', 'register_fd_custom_post_settings' );
}
function register_fd_custom_post_settings(){
	$post_types = get_post_types();
	foreach ( get_post_types( '', 'names' ) as $post_type ) {
	  register_setting( 'fd-custom-meta-group', $post_type );
	}
}
function fd_custom_post_settings(){
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
?>
    <style>
      .custom_meta_box{ width:100%; clear:both; overflow:hidden; margin-top:30px;}
	  .custom_meta_box label{ display:inline-block; float:left; min-width:200px;}
    </style>
    <div class="wrap">
    <h1>Custom Post Text Settings</h1>
    <form method="post" action="options.php">
        <?php settings_fields( 'fd-custom-meta-group' ); ?>
        <?php do_settings_sections( 'fd-custom-meta-group' ); ?>
        <div class="custom_meta_box">
		<?php	
            $post_types = get_post_types();
            foreach ( get_post_types( '', 'names' ) as $post_type ) {
			   if(get_option( $post_type )==1){ $sel = 'checked="checked"';}else{ $sel = ''; }
               echo '<label for="' . $post_type . '"><input '.$sel.' type="checkbox" name="' . $post_type . '" id="' . $post_type . '" value="1">' . $post_type . '</label>';
            }	
        ?>
        </div>
		<?php submit_button(); ?>
    </form>
    </div>
<?php	
global $wpdb;
 $querystr = "SELECT meta_key FROM wp_postmeta ORDER BY meta_id DESC";
 $pageposts = $wpdb->get_results($querystr);
 //$array = json_decode(json_encode($pageposts), true));
 //array_unique();
}
//Get Selected custom post types
function get_select_post_types(){
	$post_types = get_post_types();
	$types = array();
	foreach ( get_post_types( '', 'names' ) as $post_type ) {
	   if(get_option( $post_type )==1){ 
	     $types[] = $post_type;
	   }else{ 
	     // 
	   }	
	}
	return $types;
}
/**
 * Adds Foo_Widget widget.
 */
class Custom_Post_Text_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'custom_post_text_widget', // Base ID
			esc_html__( 'Custom Post Text', 'fdcp' ), // Name
			array( 'description' => esc_html__( 'Adds a new Widget "Custom Post Text"', 'fdcp' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		wp_reset_query();
		global $post;
		$title    = get_post_meta($post->ID, 'title', true);
		$textarea = get_post_meta($post->ID, 'textarea', true);
		//$args    = array( 'p' => $post->ID, 'post_type' => 'any' );		
		//$sliders = get_posts( $args );
		if($textarea!='' && !is_archive()){
		echo $args['before_widget'];
		
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
			//echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		echo $textarea;
		
		echo $args['after_widget'];
		}
		wp_reset_query();
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'New title', 'fdcp' );
		?>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'fdcp' ); ?></label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

} // class Foo_Widget

// register Foo_Widget widget
function register_custom_post_text_widget() {
    register_widget( 'Custom_Post_Text_Widget' );
}
add_action( 'widgets_init', 'register_custom_post_text_widget' );
//
add_action( 'add_meta_boxes', 'add_custom_post_text_metaboxes' );
//
function add_custom_post_text_metaboxes() {
	$cpt = get_select_post_types();
	if(!empty($cpt)){
	  foreach($cpt as $ptype){
		add_meta_box(
			 'wpt_custom_post_text_location', 
			 'Widget - Custom Post Text', 
			 'wpt_custom_post_text_location', 
			 $ptype, 
			 'normal', 
			 'high'
		);
	  }
	}
}
//
function wpt_custom_post_text_location() {
	global $post;
	echo '<input type="hidden" name="cpt_noncename" id="cpt_noncename" value="' . 
	wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
	$title    = get_post_meta($post->ID, 'title', true);
	$textarea = get_post_meta($post->ID, 'textarea', true);
	// Echo out the field
	echo '<label><strong>Title:</strong><br><input type="text" name="title" id="title" value="' . $title  . '" class="widefat" /><label><br>';	
	echo '<label><strong>Textarea:</strong><br><textarea name="textarea" id="textarea" style="width:100%;">'.$textarea.'</textarea></label for="title">';
	
}
// Save the Metabox Data

function wpt_save_cpt_meta($post_id, $post) {
	
	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times
	if ( !wp_verify_nonce( $_POST['cpt_noncename'], plugin_basename(__FILE__) )) {
	return $post->ID;
	}

	// Is the user allowed to edit the post or page?
	if ( !current_user_can( 'edit_post', $post->ID ))
		return $post->ID;

	// OK, we're authenticated: we need to find and save the data
	// We'll put it into an array to make it easier to loop though.
	
	$events_meta['title']    = $_POST['title'];
	$events_meta['textarea'] = $_POST['textarea'];
	
	// Add values of $events_meta as custom fields
	
	foreach ($events_meta as $key => $value) { // Cycle through the $events_meta array!
		if( $post->post_type == 'revision' ) return; // Don't store custom data twice
		$value = implode(',', (array)$value); // If $value is an array, make it a CSV (unlikely)
		if(get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
			update_post_meta($post->ID, $key, $value);
		} else { // If the custom field doesn't have a value
			add_post_meta($post->ID, $key, $value);
		}
		if(!$value) delete_post_meta($post->ID, $key); // Delete if blank
	}

}

add_action('save_post', 'wpt_save_cpt_meta', 1, 2); // save the custom fields