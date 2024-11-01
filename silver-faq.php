<?php
/*
Plugin Name: Silver FAQ
Plugin URI: http://silverboy.ir
Description: 
Version: 1.0
Author: Hossein Rezazadeh
Author URI: http://silverboy.ir
License: GPLv2
*/


// Call function when plugin is activated
register_activation_hook( __FILE__, 'silver_faq_install' );

function silver_faq_install() {
	
    

    //save our default option values
//     update_option( 'silver_faq_options', array() );
	
}


// Action hook to initialize the plugin
add_action( 'init', 'silver_faq_init' );

//Initialize the Halloween Store
function silver_faq_init() {

	$ret =  load_plugin_textdomain('silver_faq-plugin', false, dirname(plugin_basename(__FILE__)). '/languages');
	
	//register the products custom post type
	$labels = array(
		'name' => __( 'Questions', 'silver_faq-plugin' ),
		'singular_name' => __( 'Question', 'silver_faq-plugin' ),
		'add_new' => __( 'Add New', 'silver_faq-plugin' ),
		'add_new_item' => __( 'Add New Question', 'silver_faq-plugin' ),
		'edit_item' => __( 'Answer Question', 'silver_faq-plugin' ),
		'new_item' => __( 'New Question', 'silver_faq-plugin' ),
		'all_items' => __( 'All Questions', 'silver_faq-plugin' ),
		'view_item' => __( 'View Question', 'silver_faq-plugin' ),
		'search_items' => __( 'Search Questions', 'silver_faq-plugin' ),
		'not_found' =>  __( 'No Questions found', 'silver_faq-plugin' ),
		'not_found_in_trash' => __( 'No Question found in Trash', 'silver_faq-plugin' ),
		'menu_name' => __( 'FAQ', 'silver_faq-plugin' )
	  );
	
	  $args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true, 
		'show_in_menu' => true, 
		'query_var' => true,
		'rewrite' => array('slug' => 'question'),
		'capability_type' => 'post',
		'has_archive' => true, 
		'hierarchical' => false,
		'menu_position' => null,
// 	  	'menu_icon' => 'comments',
	  	'taxonomies' => array('category'),
		'supports' => array( 'title', 'slug')
	  ); 
	  
	  register_post_type( 'silver_faq-questions', $args );


	 

}
add_filter( 'enter_title_here', 'title_text_input', 3, 4 );
function title_text_input( $title, $post ){
	if($post->post_type == 'silver_faq-questions')
		return $title =  __( 'Enter Question Here', 'silver_faq-plugin' );
	else 
		return $title;
}

add_action('admin_menu', 'notification_bubble_in_admin_menu');

function notification_bubble_in_admin_menu() {
	global $menu,$wpdb;
	$newItem = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}posts as p INNER JOIN {$wpdb->prefix}postmeta as m 
	ON  p.ID = m.post_id WHERE meta_key = '_silver_faq_status' AND meta_value = 'unanswered' AND post_status != 'trash';", '' ) );
	if($newItem == 0)
		return;
	foreach ($menu as $key => $val){
		if($val[0] == 'FAQ')
		{
			$menu[$key][0] .= " <span class='update-plugins count-1'><span class='update-count'>$newItem</span></span>";
		}
	}
	
}
 add_filter('manage_edit-silver_faq-questions_columns', 'silver_faq_table_head');
function silver_faq_table_head($columns) {
    $columns['answer_status'] = __('Answer Status', 'silver_faq-plugin' );
    return $columns;
}
add_action('manage_posts_custom_column', 'silver_faq_show_columns');
function silver_faq_show_columns($name) {
	global $post;
	switch ($name) {
		case 'answer_status':
			$status = get_post_meta($post->ID, '_silver_faq_status', true);
			if($status == 'unanswered' )
				$views = __( 'Unanswered', 'silver_faq-plugin' );
			else 
				$views = __( 'Answered', 'silver_faq-plugin' );
			echo $views;
	}
}

//Action hook to register the Products meta box
add_action( 'add_meta_boxes', 'silver_faq_register_meta_box' );

function silver_faq_register_meta_box() {

	// create our custom meta box
	add_meta_box( 'silver_faq-meta', __( 'Question Setting','silver_faq-plugin' ), 'silver_faq_meta_box', 'silver_faq-questions');

}


function silver_faq_meta_box( $post ) {

	// retrieve our custom meta box values
	$answer = get_post_meta( $post->ID, '_silver_faq_answer', true );
	$status = get_post_meta( $post->ID, '_silver_faq_status', true );
	$asked_by_name = get_post_meta( $post->ID, '_silver_faq_asked_by_name', true );
	$asked_by_email = get_post_meta( $post->ID, '_silver_faq_asked_by_email', true );

	


	//nonce field for security
	wp_nonce_field( 'meta-box-save', 'silver_faq-plugin' );

	// display meta box form
	echo '<div class="misc-pub-section"><label for="silver_faq_answer">' .__('Answer', 'silver_faq-plugin').':</label></div>';

	echo wp_editor( esc_textarea($answer), 'silver_faq_answer', $settings = array() );
	
	
	echo '<div class="misc-pub-section"><label for="silver_faq_status">' .__('Status', 'silver_faq-plugin').':</label>
		<select name="silver_faq_status" id="silver_faq_status">
            <option value="unanswered"' .selected( $status, 'unanswered', false ). '>' .__( 'Unanswered', 'silver_faq-plugin' ). '</option>
            <option value="answered"' .selected( $status, 'answered', false ). '>' .__( 'Answered', 'silver_faq-plugin' ). '</option>
        </select></div>';
	if($post->post_status  != 'auto-draft' && !empty($asked_by_email) && is_email($asked_by_email)){
		echo '<div class="misc-pub-section">
						<label>	' .__('Asked By', 'silver_faq-plugin').' : '.sanitize_text_field($asked_by_email).' ('. sanitize_text_field($asked_by_name) .')</label>
			</div>';
		
		
				echo '<div class="misc-pub-section" id="silver_faq_asked_by_email_holder" style="display : none">
					<label class="selectit" for="silver_faq_send_answer">
						<input type="checkbox" name="silver_faq_send_answer" id="silver_faq_send_answer" value="1" />
						' .__('Send Answer Via Email', 'silver_faq-plugin').'?</label>
				</div>';
				echo '<script type="text/javascript">
								jQuery("#silver_faq_status").change(function(){
										if(jQuery(this).val() == "answered"){
											jQuery("#silver_faq_asked_by_email_holder").fadeIn();
										}
										else
											jQuery("#silver_faq_asked_by_email_holder").fadeOut(); 
								}).change();
						</script>';
	}
	
	
}

add_action( 'save_post','silver_faq_save_meta_box' );

//save meta box data
function silver_faq_save_meta_box( $post_id ) {

	//verify the post type is for Halloween Products and metadata has been posted
	if ( get_post_type( $post_id ) == 'silver_faq-questions' && isset( $_POST['silver_faq_status'] ) ) {

		//if autosave skip saving data
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		//check nonce for security
		check_admin_referer( 'meta-box-save', 'silver_faq-plugin' );
		$currentPostStatus = get_post_meta( $post_id, '_silver_faq_status', true );
		// save the meta box data as post metadata
		update_post_meta( $post_id, '_silver_faq_answer', $_POST['silver_faq_answer']  );
		update_post_meta( $post_id, '_silver_faq_status', sanitize_text_field( $_POST['silver_faq_status'] ) );
		if($currentPostStatus == 'unanswered' && !empty($_POST['silver_faq_send_answer']) || true)
		{
			$postData = get_post($post_id);
			
			$message = '<p>'.sprintf(__("Dear %s", 'silver_faq-plugin'), get_post_meta( $post_id, '_silver_faq_asked_by_name', true )).'</p>
					<p>'.__('Your Question Was Answered , You Can Read Your Answer Below : ', 'silver_faq-plugin').'</p>
					<p><i>'.sprintf( __('Question : %s ; Asked On %s', 'silver_faq-plugin'), $postData->post_title, $postData->post_date).'</i></p><hr />'
					.$_POST['silver_faq_answer'];
			$to = get_post_meta( $post_id, '_silver_faq_asked_by_email', true );
			$subject =  __('Your Question Answered', 'silver_faq-plugin');
			$ret = wp_mail($to ,$subject, $message, array('content-type:text/html'));
		}

	}

}

add_filter('single_template', 'silver_faq_single_template');

function silver_faq_single_template($single) {
	global $wp_query, $post;

	/* Checks for single template by post type */
	
	if ($post->post_type == "silver_faq-questions"){
		$dir = plugin_dir_path( __FILE__ );
		
		if(file_exists($dir. '/single-silver_faq-questions.php')){
			
			return $dir . '/single-silver_faq-questions.php';
		}
	}
	return $single;
}


add_action( 'admin_menu', 'silver_faq_menu' );


function silver_faq_menu() {

	add_options_page( __( 'FAQ Settings Page', 'silver_faq-plugin' ), __( 'FAQ Settings', 'silver_faq-plugin' ), 'manage_options', 'silver_faq-settings', 'silver_faq_settings_page' );

}

//build the plugin settings page
function silver_faq_settings_page() {
	
    //load the plugin options array
    $options_arr = get_option( 'silver_faq_options' );

	//set the option array values to variables
// 	$hs_inventory = ( ! empty( $options_arr['show_inventory'] ) ) ? $options_arr['show_inventory'] : '';
	
    ?>
    <div class="wrap">
    <h2><?php _e( 'FAQ Options', 'silver_faq-plugin' ) ?></h2>

    <form method="post" action="options.php">
        <?php settings_fields( 'silver_faq_group' ); ?>
        <h3><?php _e( 'Recaptcha Setting', 'silver_faq-plugin' ) ?></h3>
        <table class="form-table">
            <tr valign="top">
	            <td scope="row"><label for="silver_faq_public_key"><?php _e( 'Public Key', 'silver_faq-plugin' ) ?></label></td>
	            <td>
	            	<input style="width: 450px;" id="silver_faq_public_key" type="text" name="silver_faq_options[public_key]" value="<?php echo esc_attr($options_arr['public_key']) ?>" />
	            </td>
            </tr>
            <tr valign="top">
	            <td scope="row"><label for="silver_faq_private_key"><?php _e( 'Private Key', 'silver_faq-plugin' ) ?></label></td>
	            <td>
	            	<input style="width: 450px;" id="silver_faq_private_key" type="text" name="silver_faq_options[private_key]" value="<?php echo esc_attr($options_arr['private_key']) ?>" />
	            </td>
            </tr>

          
        </table>

        <p class="submit">
        <input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'silver_faq-plugin' ); ?>" />
        </p>

    </form>
    </div>
<?php
}


add_action( 'admin_init', 'silver_faq_save_settings' );

function silver_faq_save_settings() {

	//register the array of settings
	register_setting( 'silver_faq_group', 'silver_faq_options');

}




add_filter('pre_get_posts', 'silver_faq_query_post_type');
function silver_faq_query_post_type($query) {
	
	if(is_category() || is_tag()) {
		$post_type = get_query_var('post_type');
		if(empty($post_type))
			$post_type = array('post','silver_faq-questions');
		$query->set('post_type',$post_type);
		return $query;
	}
}
add_filter('the_content', 'silver_faq_content');
function silver_faq_content($content){
	$post = get_post();
	if($post->post_type == 'silver_faq-questions')
		return get_post_meta( get_the_ID(), '_silver_faq_answer', true );
	return $content;
}


// Action hook to create plugin widget
add_action( 'widgets_init', 'silver_faq_register_widgets' );

//register the widget
function silver_faq_register_widgets() {

	register_widget( 'silver_faq_widget' );

}

//hs_widget class
class silver_faq_widget extends WP_Widget {

	private $_postData = array();
	private $_errors = false;
	private $_success = false;

	//process our new widget
	function silver_faq_widget() {

		$widget_ops = array(
				'classname'   => 'silver-faq-widget-class',
				'description' => __( 'Display Form For Asking Question','silver_faq-plugin' ) );
		$this->WP_Widget( 'silver_faq_widget', __( 'Ask Question Form','silver_faq-plugin'), $widget_ops );

	}

	//build our widget settings form
	function form( $instance ) {

		$defaults = array(
				'title'           => __( 'Question Form', 'silver_faq-plugin' ),
				);

		$instance = wp_parse_args( (array) $instance, $defaults );
		$title = $instance['title'];
		$number_products = $instance['number_products'];
		?>
            <p><?php _e('Title', 'silver_faq-plugin') ?>: 
				<input class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
        <?php
    }

    //save our widget settings
    function update( $new_instance, $old_instance ) {
		
        $instance = $old_instance;
        $instance['title'] = sanitize_text_field( $new_instance['title'] );
      

        return $instance;
		
    }

     //display our widget
    function widget( $args, $instance ) {
       
		extract( $args );
		if(!empty($_POST['silver_faq'])){
			
			
			if($this->_saveQuestion()){
				if ( ! empty( $title ) ) { echo $before_title . esc_html( $title ) . $after_title; };
				echo '<p>'.__('Thank You , Your Question Submited And Will Be Answered As Soon As Possible, Check Your Email In Next Couple Of Hour For Answer', 'silver_faq-plugin').'</p>';
				return;		
			}
			
			
		}		


      

        echo $before_widget;
        $title = apply_filters( 'widget_title', $instance['title'] );
       

        if ( ! empty( $title ) ) { echo $before_title . esc_html( $title ) . $after_title; };

        
        echo '<p>'.__('Didnt Find Your Answer ? Ask It !', 'silver_faq-plugin').'</p>';

        if(!empty($this->_errors))
        	echo '<p class="bg-danger">'.$this->_errors.'</p>';
		
        echo '<form action="" method="post">';
		wp_nonce_field( '', 'silver_faq-plugin-widget',false );
		echo '<p><label for="silver_faq_name">'.__('Name', 'silver_faq-plugin').' : </label><input required id="silver_faq_name" name="silver_faq[name]" type="text" value="'.esc_attr($this->_postData['name']).'" /></p>';
		
		echo '<p><label for="silver_faq_email">'.__('Email', 'silver_faq-plugin').' : </label><input required id="silver_faq_email" name="silver_faq[email]" type="email"  value="'.esc_attr($this->_postData['email']).'" /></p>';
		
		echo '<p><label for="silver_faq_question">'.__('Your Question', 'silver_faq-plugin').' : </label><textarea required id="silver_faq_question" name="silver_faq[question]" style="width : 90%" row="15">' . esc_textarea($this->_postData['question']).'</textarea></p>';
		
		$options_arr = get_option( 'silver_faq_options' );
		$pluginDirs = plugin_dir_path(__FILE__);
		if(!empty($options_arr['public_key'])){
			require_once($pluginDirs.'recaptchalib.php');
			$publickey = $options_arr['public_key']; // you got this from the signup page
			echo recaptcha_get_html($publickey);		
			
		}
		
		echo '<p><input type="submit" value="'.__('Submit', 'silver_faq-plugin').'" id="submit" name="submit"></p>';
		
		echo '</form>';

        echo $after_widget;
		
    }
    private function _saveQuestion(){
		$this->_postData = $_POST['silver_faq'];
		if(!wp_verify_nonce($_POST['silver_faq-plugin-widget'], ''))
		{
			$this->_errors = __('Error In SendIng Data , Try Agian', 'silver_faq-plugin');
			return false;
		}
		foreach ($this->_postData as $key => $val){
			$this->_postData[$key] = sanitize_text_field($val);
		}
		if(!empty($this->_postData['name']) && !empty($this->_postData['email']) && !empty($this->_postData['question'])){
			if(!is_email($this->_postData['email'])){
				$this->_errors = __('Email Is Not Valid', 'silver_faq-plugin');
				return false;
			}
			$options_arr = get_option( 'silver_faq_options' );
			$pluginDirs = plugin_dir_path(__FILE__);
			if(!empty($options_arr['public_key'])){
				require_once($pluginDirs.'recaptchalib.php');
				$privatekey = $options_arr['private_key'];
				$resp = recaptcha_check_answer ($privatekey,
						$_SERVER["REMOTE_ADDR"],
						$_POST["recaptcha_challenge_field"],
						$_POST["recaptcha_response_field"]);
				
				if (!$resp->is_valid)
				{
					$this->_errors = __('Captcha Is Wrong', 'silver_faq-plugin');
					return false;
				}
			}
			$post = array('post_title' => $this->_postData['question'], 'post_type' => 'silver_faq-questions', 'post_status' => 'pending');
			$pistId = wp_insert_post( $post);
			if($pistId){
				add_post_meta($pistId, '_silver_faq_status', 'unanswered');
				add_post_meta($pistId, '_silver_faq_asked_by_name', $this->_postData['name']);
				add_post_meta($pistId, '_silver_faq_asked_by_email', $this->_postData['email']);
			}
			$this->_postData = array();
			$this->_success = true;
			return true;
			
			
		}
		else {
			$this->_errors = __('Please Fill All Fields', 'silver_faq-plugin');
			return false;
		}
	}
	
}


