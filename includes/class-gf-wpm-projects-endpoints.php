<?php

class Gf_Wpm_Projects_Endpoints {


	public $project_id_boab_aiml_community = 6;
	public $boab_step_id_approval = 13;
	public $field_user = 1;
	public $waiting_task_list_id = 86;
	public $waiting_kanboard_id = 90;

	/*** Table Name ***/

	public $db_table_assignees = 'pm_assignees';
	public $db_table_tasks = 'pm_tasks';
	public $db_table_boardables = 'pm_boardables';

	/*** Endpoints URL get_bloginfo('url') for Home URL ***/

	public $endpoint_save_post_type = "/wp-json/gf/v2/workflow/webhooks/6/H6OyUkGjNyl90ZDMF3q56TXew";
	public $endpoint_approver_field = "/wp-json/gf/v2/workflow/webhooks/19/jd5nmLAhJF7uz67PKIo7VmyW6";

	/*** Incoming Web Hook on Workflow Steps API Key and Secret ***/

	public $workflow_api_key = '4af481f2260f99260f5e76a949e31d0e';
	public $workflow_api_secret = 'a4b0d2185c16aa753208d79975f2bd5a';


	public function __construct() {
	

	}

	public function get_project_id_boab_aiml_community() {

		return $this->project_id_boab_aiml_community;

	}	

	public function get_endpoint_save_post_type() {

		$endpoint = get_bloginfo('url') . $this->endpoint_save_post_type;

		return $endpoint;

	}	

	public function get_endpoint_approver_field() {

		$endpoint = get_bloginfo('url') . $this->endpoint_approver_field;

		return $endpoint;

	}

	public function db_table_assignees() {

		global $wpdb;

		$this->db_table_assignees = $wpdb->prefix . $this->db_table_assignees;   
		
		return $this->db_table_assignees;

	}

	public function db_table_tasks() {

		global $wpdb;

		$this->db_table_tasks = $wpdb->prefix . $this->db_table_tasks;   
		
		return $this->db_table_tasks;

	}

	public function db_table_boardables() {

		global $wpdb;

		$this->db_table_boardables = $wpdb->prefix . $this->db_table_boardables;   
		
		return $this->db_table_boardables;

	}

	/*******************/

	/**** Functions ****/

	public function boab_assigned_to() { 
      
		global $wpdb;   
		
		$table_name = $this->db_table_assignees();     
	
		$date = date('Y-m-d H:i:s');     
		$format = array('%d','%d','%d','%d','%d','%s','%s','%s','%d', '%s','%s');
	
	
		$exists = $wpdb->insert( $table_name, array(
			'task_id' => 101,
			'assigned_to' => 2,
			'status' => 0,
			'created_by' => 1,
			'updated_by' => 1,
			'assigned_at' => $date,
			'started_at' => NULL,
			'completed_at' => NULL,
			'project_id' => 2,
			'created_at' => $date,
			'updated_at' => $date,
		  
		));

	} 

		
	public function assigned_user($task_id, $assigned_to, $project_id) {

		global $wpdb;  

		// Assign User
			
		$table_name = $this->db_table_assignees();   

		$date = date('Y-m-d H:i:s');
		$current_user_id = get_current_user_id();

		$exists = $wpdb->insert( $table_name, array(
			'task_id' => $task_id,
			'assigned_to' => $assigned_to,
			'status' => 0,
			'created_by' => $current_user_id,
			'updated_by' => $current_user_id,
			'assigned_at' => $date,
			'started_at' => NULL,
			'completed_at' => NULL,
			'project_id' => $project_id,
			'created_at' => $date,
			'updated_at' => $date,
		
		));

	}

	public function add_task_from_gf($title, $description, $project_id, $assigned_to, $start_at, $due_date) {

		global $wpdb;  
	
		$date = date('Y-m-d H:i:s');     
	
		
		$table_name = $this->db_table_tasks();     
	
		$exists_task_id = $wpdb->insert( $table_name, array(
			'title' => $title,
			'description' => $description,
			'estimation' => 0,
			'start_at' => $start_at,
			'due_date' => $due_date,
			'complexity' => NULL,
			'priority' => 1,
			'payable' => 0,
			'recurrent' => 0,
			'status' => 0,
			'is_private' => 0,
			'project_id' => $project_id,
			'parent_id' => 0,
			'completed_by' => NULL,
			'completed_at' => NULL,
			'created_by' => 1,
			'updated_by' => 1,
			'created_at' => $date,
			'updated_at' => $date,
		));
	
		$table_boardables = $this->db_table_boardables();  
	
		if($exists_task_id) {
	
			$task_id = $wpdb->insert_id;
			
			//$exists_board_id = $wpdb->insert( $table_boardables,);
			//$exists_kanboard_id = $wpdb->insert( $table_boardables, );
	
			$waiting_task_list_id = $this->waiting_task_list_id;
			$waiting_kanboard_id = $this->waiting_kanboard_id;
	
			$order_task_list =  $this->get_data_order($table_boardables, 'task_list');
			$order_kanboard =  $this->get_data_order($table_boardables, 'kanboard');
	
			$rows = array(
				array(
					'board_id' => $waiting_task_list_id,
					'board_type' => 'task_list',
					'boardable_id' => $task_id,
					'boardable_type' => 'task',
					'order' => $order_task_list,
					'created_by' => 1,
					'updated_by' => 1,
					'created_at' => 0,
					'updated_at' => 0,
				),
				array(
					'board_id' => $waiting_kanboard_id,
					'board_type' => 'kanboard',
					'boardable_id' => $task_id ,
					'boardable_type' => 'task',
					'order' => $order_kanboard,
					'created_by' => 1,
					'updated_by' => 1,
					'created_at' => 0,
					'updated_at' => 0,
				)
			);
		
			foreach( $rows as $row )
			{
				$wpdb->insert( $table_boardables, $row);  
			}
	
			$this->assigned_user($task_id, $assigned_to, $project_id);
		   
		}
		
	}
	

	// Latest Order Number

	public function get_data_order($table_boardables, $board_type){
	
		global $wpdb;
	
		$wpdb->show_errors();
	
		//$table_boardables = $this->db_table_boardables(); 
		
		$result = $wpdb->get_results("SELECT * FROM $table_boardables WHERE  board_type = '{$board_type}'  ORDER BY id DESC");
	
		return $result[0]->order + 1; 
	   
	}

	
	// Get Results Task for Loggedin User

	public function query_task_for_user($project_id) {

		global $wpdb;

		$wpdb->show_errors();

		$table_tasks = $this->db_table_tasks();    
		$table_assignees = $this->db_table_assignees();   



		if($_GET['user']) {

			$logged_user_id = $_GET['user'];

		} elseif($_GET['user_add']) {

			$val = preg_replace('/[\s\+]/', ' ', $_GET['user_add']);

			$user = get_users(array('search' =>  $val));
		
			if (!empty($user))
				$logged_user_id  = $user[0]->ID;

		}




		//$results = $wpdb->get_results("SELECT $table_tasks.title, $table_tasks.description, $table_tasks.start_at, $table_tasks.due_date, $table_assignees.task_id FROM $table_tasks 
		//INNER JOIN $table_assignees ON $table_tasks.id = $table_assignees.task_id WHERE $table_assignees.assigned_to = '{$logged_user_id}'
		//");

		

		$results = $wpdb->get_results("SELECT $table_tasks.title, $table_tasks.description, $table_tasks.start_at, $table_tasks.due_date, $table_assignees.task_id, $table_assignees.assigned_to FROM $table_tasks 
		INNER JOIN $table_assignees ON $table_tasks.id = $table_assignees.task_id WHERE $table_assignees.assigned_to = '{$logged_user_id}' ORDER BY $table_tasks.id DESC
		");
		
	// $results = $wpdb->get_results("SELECT * FROM $table_tasks, $table_assignees 
		
	// LEFT JOIN $table_assignees ON  $table_tasks =  $table_assignees.task_id WHERE project_id = '{$project_id}' ORDER BY id DESC");

	// return '<pre>'.print_r($results ,true).'</pre>';

	

		ob_start();
		?>

		<form action="<?php echo get_bloginfo('url') ?>/display-tasks-for-gf/" method="get">
			<div>

				<label for="user"><strong>SELECT USERS:</strong></label>
				<p><select name="user" id="user">
					<option value="">--- Select a User ---</option>
					<?php

					$users = get_users( array( 'fields' => array( 'ID' ) ) );

					foreach($users as $user){

						if ($user = get_userdata($user->ID)) {

							echo '<option '. ($logged_user_id == $user->ID ? 'selected' : '').' value="'.$user->ID.'">'.$user->data->display_name.'</option>';

						}
					}
					?>
			
				</select></p>
			</div>
			<div>
				<button type="submit">Submit</button>
			</div>
		</form>

		<div id="tasks_table">

		<div class="header">Tasks for BOAB AIML Development</div>
		
		<table cellspacing="0">
			<tr>
				<th>Title</th>
				<th>Description</th>
				<th>Start At</th>
				<th>Due Date</th>
				<th>Assigned to</th>
			</tr>

		

		<?php
		


		foreach($results as $result) {

			$start_at_format = date('F d, Y',  strtotime($result->start_at));
			$due_date_format = date('F d, Y', strtotime($result->due_date));
		
			$start_at_format_out = '';
			
			if (strtotime($result->start_at) != "") {

				$start_at_format_out =   $start_at_format;

			} 

			$due_date_format_out = '';
			
			if (strtotime($result->due_date) != "") {

				$due_date_format_out =   $due_date_format;

			} 

			$assigned_to = "";

			if ($user = get_userdata($result->assigned_to)) {
				$assigned_to = $user->data->display_name;
			}
			
		

	
		?>
			<tr>
				<td><?php echo '<a href="'.get_bloginfo('url').'/update-task-for-gf/?task_id='.$result->task_id.'">'.$result->title.'</a>'; ?></td>
				<td><?php echo '<a href="'.get_bloginfo('url').'/update-task-for-gf/?task_id='.$result->task_id.'">'.$result->description.'</a>'; ?></td>
				<td><?php echo  '<a href="'.get_bloginfo('url').'/update-task-for-gf/?task_id='.$result->task_id.'">'.$start_at_format_out.'</a>'; ?></td>
				<td><?php echo  '<a href="'.get_bloginfo('url').'/update-task-for-gf/?task_id='.$result->task_id.'">'.$due_date_format_out.'</a>'; ?></td>
				<td><?php echo  '<a href="'.get_bloginfo('url').'/uupdate-task-for-gf/?task_id='.$result->task_id.'">'.$assigned_to.'</a>'; ?></td>
			</tr>

		<?php
		}
		?>

		</table>
		</div>

		<?php

		$ob_str = ob_get_contents();
		
		ob_end_clean();

		return $ob_str;
		//return $result;

	}

}
