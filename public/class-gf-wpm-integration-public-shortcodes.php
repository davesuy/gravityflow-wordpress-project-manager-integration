<?php


class Gf_Wpm_Integration_Public_shortcodes extends Gf_Wpm_Integration_Public {

	public $project_endpoints;
	
	public function __construct($project_endpoints) {

		$this->project_endpoints = $project_endpoints;

	}

	
	/*** Shortcodes ***/
	
	public function add_shortcode_func() {

		add_shortcode('gravityflowtab', array($this,'gravityflowtab_func'));
		add_shortcode( 'query_task_user', array($this,'query_task_user_shortcode_func'));

	}

	/*** Callback Method ***/

	public function gravityflowtab_func() {
		 
		$content = '<div id="aiml_tabs">
	
			<ul>
				<li><a href="#inbox">Inbox</a></li>
				<li><a href="#status">Status</a></li>
				<li><a href="#submit">Submit</a></li>
				<li><a href="#reports">Reports</a></li>
			</ul>';
	
			$content .= '<div id="gravityflowtabs">';
	
				$content .= '<div id="inbox">';
					$content .= do_shortcode('[gravityflow page="inbox"]');
				$content .= '</div>';
	
				$content .= '<div id="status">';  
					$content .= do_shortcode("[gravityflow page='status']");
				$content .= '</div>';
	
				$content .= '<div id="submit">'; 
					$content .= do_shortcode('[gravityflow page="submit"]');
				$content .= '</div>'; 
	
				$content .= '<div id="reports">'; 
					$content .= do_shortcode('[gravityflow page="reports"]');
				$content .= '</div>';  
	
			$content .= '</div>'; 
	
		$content .= '</div>'; 
	
	
		return $content;
	
	}

	public function query_task_user_shortcode_func( $atts ) {

		$atts = shortcode_atts( array(
			'project_id' => ''
	
		), $atts, 'query_task_user' );
		
		$gf_wpm_integration = $this->project_endpoints;
	
		$query_tasks = $gf_wpm_integration->query_task_for_user($atts['project_id']);
	
	
	   return $query_tasks;
	
	}

}
