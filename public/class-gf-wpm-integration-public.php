<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/davesuy
 * @since      1.0.0
 *
 * @package    Gf_Wpm_Integration
 * @subpackage Gf_Wpm_Integration/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Gf_Wpm_Integration
 * @subpackage Gf_Wpm_Integration/public
 * @author     Dave Ramirez <davesuywebmaster@gmail.com>
 */
class Gf_Wpm_Integration_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/* Gf and Wpm */

	public $project_endpoints;

	
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $project_endpoints) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->project_endpoints = $project_endpoints;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Gf_Wpm_Integration_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Gf_Wpm_Integration_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/gf-wpm-integration-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Gf_Wpm_Integration_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Gf_Wpm_Integration_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/gf-wpm-integration-public.js', array( 'jquery' ), $this->version, false );

	}


	/* Gf and Wpm Integration function */

	public function wp_head_func() {
 
		if( function_exists('get_field')) {
	
			$args = array('orderby' => 'display_name');
	
			$wp_user_query = new WP_User_Query($args);
			
			$authors = $wp_user_query->get_results();
		
			if (!empty($authors)) {
		   
				foreach ($authors as $author) {
				 
					$author_info = get_userdata($author->ID);
					$username = $author_info->display_name;
	
					
					if (function_exists('get_field')) {
	
						$task_color = get_field('task_color', 'user_'.$author->ID );
					
		
						if( $task_color ) {
					
					
							?>
							<style>
		
								.cpm-assigned-user a[title="<?php echo  $username; ?>"]:before {
									background: <?php echo  $task_color; ?>;
									content: "";
									width: 100%;
									height: 100%;
									position: absolute;
									top: 0;
									opacity: 0.5;
									left: 0;
								}
		
							</style>
		
							<?php
		
						} else {
						
						}

					}
					//echo '<pre>'.print_r($author_info->user_nicename, true).'</pre>';
				}
			 
			} 
	
	
		}
	
	}

	public function save_post_type( $task, $request ) {

		// if ( !isset( $request['privacy'] ) ){
		//     return ;
		// }

	
		/* Production */
		$project_id = $this->project_endpoints->get_project_id_boab_aiml_community();
	
		/* Local */
	   // $project_id = 3;
	  
		if($request['project_id'] == $project_id) {
			
	
			$endpoint = $this->project_endpoints->get_endpoint_save_post_type();
	
	
			$task_url = get_bloginfo('url').'/#/projects/'.$request['project_id'].'/task-lists/tasks/'.$task->id;
			
			$body = [
				'task_title'  => $task->title,
				'task_url' => $task_url,
				'task_id' => $task->id,
				'project_id' => $request['project_id']
			];
			
			$body = wp_json_encode( $body );
			
			$options = [
				'body'        => $body,
				'headers'     => [
					'Content-Type' => 'application/json',
				],
				'timeout'     => 60,
				'redirection' => 5,
				'blocking'    => true,
				'httpversion' => '1.0',
				'sslverify'   => false,
				'data_format' => 'body',
			];
	
			wp_remote_post( $endpoint, $options );
	
		}
	   
	}

	/*** For Testing ***/

	public function test_data() {

		
		//$b = $this->project_endpoints;
			
		global $wpdb;
	
		$wpdb->show_errors();
	
		$table_boardables = $wpdb->prefix . 'pm_boardables'; 

		$board_type = "task_list";
		
	
		$result = $wpdb->get_results("SELECT * FROM $table_boardables WHERE  board_type = '{$board_type}'  ORDER BY id DESC");
	
		//return $result[0]->order + 1; 

		//$a = $gf_wpm_integration->get_project_endpoints();
		//echo '<pre>'.print_r($result, true).'x</pre>';
		//echo '<pre>'.print_r($a, true).'</pre>';


	}
	
	public function boab_define_approver_to_field( $feedback, $entry, $assignee, $new_status, $form, $step ) {

	
		$step_id_approval = $this->project_endpoints->boab_step_id_approval;
	
		//Modify this to match the field ID of the user field that you add to your form. It will be what your post-approval step gets assigned to.
		$field_id_user = $this->project_endpoints->field_user;
	
	 
	
		if ( $step->get_id() !== $step_id_approval ) {
			return $feedback;
		}
	
		if ( $new_status == 'approved' ) {
	
			$user = get_user_by( 'ID', $assignee->get_id() );
			
			if ( $user ) {
	
				GFAPI::update_entry_field( $entry['id'], $field_id_user, $user->ID );
				
				$note = sprintf( esc_html__( 'Updating Post-Approval Step Assignee to: %s (%s)' ), $user->display_name, $user->ID );
	
				$step->add_note( $note, true );    
	
			}
	
			global $wpdb;
	
			$task_title = $entry[1];
			$task_desc = $entry[2];
			$task_url = $entry[5];
			$task_id = $entry[3];
			$project_id = $entry[4];
	
			$table_name = $this->project_endpoints->db_table_assignees();    
		
			$date = date('Y-m-d H:i:s'); 
	   
			$current_user = wp_get_current_user();
	
		   // $step->add_note( $current_user->ID.' task id '.$task_id, true );  
	
			$exists = $wpdb->insert( $table_name, array(
				'task_id' => $task_id,
				'assigned_to' => $current_user->ID,
				'status' => 0,
				'created_by' => 1,
				'updated_by' => 1,
				'assigned_at' => $date,
				'started_at' => NULL,
				'completed_at' => NULL,
				'project_id' => $project_id,
				'created_at' => $date,
				'updated_at' => $date,
			
			));
			//exit();
	
			
			/****** Incoming Webhook for the Task Approver *******/

			$endpoint = $this->project_endpoints->get_endpoint_approver_field();
	
	
			$body = [
				'user_id'   => $current_user->user_login,
				'username'  => $current_user->ID,
				'email' =>  $current_user->user_email
			
			];
			
			$body = wp_json_encode( $body );
			
			$options = [
				'body'        => $body,
				'headers'     => [
					'Content-Type' => 'application/json',
				],
				'timeout'     => 60,
				'redirection' => 5,
				'blocking'    => true,
				'httpversion' => '1.0',
				'sslverify'   => false,
				'data_format' => 'body',
			];
	
			wp_remote_post( $endpoint, $options );
	
		}
	
		return $feedback;
	}

	public function gform_populate_user_role($value){

		$user = wp_get_current_user();
		$role = $user->roles;
		return reset($role);

	}

	public function add_readonly_script( $form ) {
		?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				/* apply only to a input with a class of gf_readonly */
				jQuery(".gf_readonly input").attr("readonly","readonly");
				jQuery(".gf_readonly input[type=checkbox]").attr("onclick","return false;");
				jQuery(".gf_readonly input[type=radio]").attr("onclick","return false;");
				/*jQuery("option:not(:selected)").prop("disabled","return true;");*/
			});
		</script>
		<?php
		return $form;
	}

	public function gw_prepopluate_merge_tags( $form ) {

		global $gw_filter_names;
	
		$gw_filter_names = array();
	
		foreach( $form['fields'] as &$field ) {
	
			if( ! rgar( $field, 'allowsPrepopulate' ) ) {
				continue;
			}
	
			// complex fields store inputName in the "name" property of the inputs array
			if( is_array( rgar( $field, 'inputs' ) ) && $field['type'] != 'checkbox' ) {
				foreach( $field->inputs as $input ) {
					if( $input['name'] ) {
						$gw_filter_names[ $input['name'] ] = GFCommon::replace_variables_prepopulate( $input['name'] );
					}
				}
			} else {
				$gw_filter_names[ $field->inputName ] = GFCommon::replace_variables_prepopulate( $field->inputName );
			}
	
		}
	
		foreach( $gw_filter_names as $filter_name => $filter_value ) {
	
			if( $filter_value && $filter_name != $filter_value ) {
				add_filter( "gform_field_value_{$filter_name}", function( $value, $field, $name ) {
					global $gw_filter_names;
					$value = $gw_filter_names[ $name ];
					/** @var GF_Field $field  */
					if( $field->get_input_type() == 'list' ) {
						remove_all_filters( "gform_field_value_{$name}" );
						$value = GFFormsModel::get_parameter_value( $name, array( $name => $value ), $field );
					}
					return $value;
				}, 10, 3 );
			}
	
		}
	
		return $form;
	}

	public function add_task( $entry, $form ) {

		$task_title = rgar( $entry, '1' );
		$task_description  = rgar( $entry, '2' );
		$task_project_id = 6;
		$assigned_to  = rgar( $entry, '4' );
		$start_at = rgar( $entry, '6' );
		$due_date = rgar( $entry, '5' );
	   
	
		$this->project_endpoints->add_task_from_gf($task_title, $task_description, $task_project_id, $assigned_to, $start_at, $due_date);
	
	}


	
	public function title_task_population_function( $value ) {

		$task_id = $_GET['task_id'];
	
		global $wpdb;
	
		$wpdb->show_errors();
	
		$table_tasks = $this->project_endpoints->db_table_tasks();  
		
	
		$result = $wpdb->get_results("SELECT * FROM $table_tasks WHERE  id = '{$task_id}' ");
	
	
		//return '<pre>'.print_r($result, true).'</pre>';
	
		return $result[0]->title;
		
	}

	public function description_task_population_function( $value ) {

		$task_id = $_GET['task_id'];
	
		global $wpdb;
	
		$wpdb->show_errors();
	
		$table_tasks = $this->project_endpoints->db_table_tasks();  
		
	
		$result = $wpdb->get_results("SELECT * FROM $table_tasks WHERE  id = '{$task_id}' ");
	
	
		//return '<pre>'.print_r($result, true).'</pre>';
	
		return $result[0]->description;
		
	}

	public function user_task_population_function( $value ) {

		$task_id = $_GET['task_id'];
	
		global $wpdb;
	
		$wpdb->show_errors();
	
		$table_assignees = $this->project_endpoints->db_table_assignees();    
		
	
		$results = $wpdb->get_results("SELECT assigned_to FROM $table_assignees WHERE task_id = '{$task_id}'
		");
	
	
	   // return '<pre>'.print_r($results, true).'</pre>';
	
		return $results[0]->assigned_to;
		
	}
	
	public function start_at_task_population_function( $value ) {

		$task_id = $_GET['task_id'];

		global $wpdb;

		$wpdb->show_errors();

		$table_tasks = $this->project_endpoints->db_table_tasks();
		

		$result = $wpdb->get_results("SELECT start_at FROM $table_tasks WHERE  id = '{$task_id}' ");


		//return '<pre>'.print_r($result, true).'</pre>';

		$start_at_format = date('m/d/Y',  strtotime($result[0]->start_at));


		$start_at_format_out = '';
		
		if (strtotime($result[0]->start_at) != "") {

			$start_at_format_out =   $start_at_format;

		} 

		return  $start_at_format_out;
		
	}

	
	public function due_date_population_function( $value ) {

		$task_id = $_GET['task_id'];

		global $wpdb;

		$wpdb->show_errors();

		$table_tasks = $this->project_endpoints->db_table_tasks(); 
		
		$result = $wpdb->get_results("SELECT * FROM $table_tasks WHERE  id = '{$task_id}' ");


		//return '<pre>'.print_r($result, true).'</pre>';

		$due_date_format = date('m/d/Y',  strtotime($result[0]->due_date));


		$due_date_format_out = '';
		
		if (strtotime($result[0]->due_date) != "") {

			$due_date_format_out = $due_date_format;

		} 

		return $due_date_format_out;
		
	}

	
	public function update_form_task( $entry, $form ) {

		$task_id = $_GET['task_id'];

		$task_title = rgar( $entry, '1' );
		$task_description  = rgar( $entry, '2' );
		$task_project_id = 6;
		$assigned_to  = rgar( $entry, '4' );
		$start_at = rgar( $entry, '6' );
		$due_date = rgar( $entry, '5' );

		global $wpdb;  

		$wpdb->show_errors();
	
		$table_tasks = $this->project_endpoints->db_table_tasks(); 
		$table_assignees = $this->project_endpoints->db_table_assignees(); 

		$task_id = $_GET['task_id'];

		$task_title = rgar( $entry, '1' );
		$task_description  = rgar( $entry, '2' );
		$task_project_id = 6;
		$assigned_to  = rgar( $entry, '4' );
		$start_at = rgar( $entry, '6' );
		$due_date = rgar( $entry, '5' );

		
		$query = $wpdb->prepare("
		UPDATE $table_tasks INNER JOIN $table_assignees
		SET $table_tasks.title = '{$task_title}', $table_tasks.description = '{$task_description}', $table_tasks.start_at = '{$start_at}', $table_tasks.due_date = '{$due_date}', $table_assignees.assigned_to = '{$assigned_to}' WHERE $table_assignees.task_id = '{$task_id}' AND $table_tasks.id = {$task_id}
		");

		$results = $wpdb->get_results( $query );

	}

	public function incoming_webhook_add_task( $entry, $form ) {

		$instance_proj_end = new Gf_Wpm_Projects_Endpoints;


		$workflow_api_key = $instance_proj_end->workflow_api_key;
		$workflow_api_secret = $instance_proj_end->workflow_api_secret;

		$entry_id = $entry['id'];
	
		$task_title = rgar( $entry, '1' );
		$task_description  = rgar( $entry, '2' );
		$task_project_id = $instance_proj_end->get_project_id_boab_aiml_community();
		$assigned_to  = rgar( $entry, '4' );
		$start_at = rgar( $entry, '6' );
		$due_date = rgar( $entry, '5' );
	
		$endpoint = get_bloginfo('url').'/wp-json/gf/v2/entries/'.$entry_id.'/workflow-hooks';
	
		$body = [
			'workflow-api-key' => $workflow_api_key,
			'workflow-api-secret' =>  $workflow_api_secret,
			'task_title'  => $task_title,
			'task_description' => $task_description,
			'task_id' => $task->id,
			'task_project_id' =>  $task_project_id,
			'assign_user' =>  $assigned_to
		];
		
		$body = wp_json_encode( $body );
		
		$options = [
			'body'        => $body,
			'headers'     => [
				'Content-Type' => 'application/json',
			],
			'timeout'     => 60,
			'redirection' => 5,
			'blocking'    => true,
			'httpversion' => '1.0',
			'sslverify'   => false,
			'data_format' => 'body',
		];
	
		$response = wp_remote_post( $endpoint, $options );

	
		// $my_post = array(
		//     'ID'           => 2191,
		//     'post_content' => 'sdasd'
		// );
	
		// // Update the post into the database
		// wp_update_post( $my_post );
	
	
		if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
	
			return false;
	
		} else {
			
			$instance_proj_end->add_task_from_gf($task_title, $task_description, $task_project_id, $assigned_to, $start_at, $due_date);
	
		}
	
	}
	
}
