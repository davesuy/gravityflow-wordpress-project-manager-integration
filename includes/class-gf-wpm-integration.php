<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/davesuy
 * @since      1.0.0
 *
 * @package    Gf_Wpm_Integration
 * @subpackage Gf_Wpm_Integration/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Gf_Wpm_Integration
 * @subpackage Gf_Wpm_Integration/includes
 * @author     Dave Ramirez <davesuywebmaster@gmail.com>
 */
class Gf_Wpm_Integration {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Gf_Wpm_Integration_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	protected $project_endpoints;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'GF_WPM_INTEGRATION_VERSION' ) ) {
			$this->version = GF_WPM_INTEGRATION_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'gf-wpm-integration';

		
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();


	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Gf_Wpm_Integration_Loader. Orchestrates the hooks of the plugin.
	 * - Gf_Wpm_Integration_i18n. Defines internationalization functionality.
	 * - Gf_Wpm_Integration_Admin. Defines all hooks for the admin area.
	 * - Gf_Wpm_Integration_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gf-wpm-integration-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gf-wpm-integration-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-gf-wpm-integration-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-gf-wpm-integration-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-gf-wpm-integration-public-shortcodes.php';


		$this->loader = new Gf_Wpm_Integration_Loader();


		/* Gf and Wpm */

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gf-wpm-projects-endpoints.php';


	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Gf_Wpm_Integration_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Gf_Wpm_Integration_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Gf_Wpm_Integration_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Gf_Wpm_Integration_Public( $this->get_plugin_name(), $this->get_version(), $this->get_project_endpoints());

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		/* Gf and Wpm Hooks */

		$this->loader->add_action( 'wp_body_open', $plugin_public, 'wp_head_func' );

		$this->loader->add_action( 'pm_after_create_task',  $plugin_public ,'save_post_type', 10, 2 );

		$this->loader->add_action( 'init',  $plugin_public, 'test_data');

		$this->loader->add_filter( 'gravityflow_feedback_approval',   $plugin_public, 'boab_define_approver_to_field', 10, 6 );

		/**
		* Dynamically Populating User Role
		* http://gravitywiz.com/2012/04/30/dynamically-populating-user-role/
		*/

		$this->loader->add_filter( 'gform_field_value_user_role',   $plugin_public, 'gform_populate_user_role');

		// Gravity forms code to make a field read only
		// change to gform_pre_render_1 (replace '1' with gravity form ID) to specifically
		// target a form.  putting gform_pre_render will do it for all dropdown boxes

		$this->loader->add_filter( 'gform_pre_render', $plugin_public, 'add_readonly_script' );

		/**
		 * Merge Tags as Dynamic Population Parameters
		 * http://gravitywiz.com/dynamic-products-via-post-meta/
		 * @version 1.3
		 */
		$this->loader->add_filter( 'gform_pre_render', $plugin_public, 'gw_prepopluate_merge_tags' );

		/***  Gravity Form Submission Added to Wp Manager Task ***/

		//$this->loader->add_action( 'gform_after_submission_8', $plugin_public, 'add_task', 10, 2 );

		/*** Update Task and populate Form ***/

		$this->loader->add_filter( 'gform_field_value_populate_task_title', $plugin_public, 'title_task_population_function' );

		$this->loader->add_filter( 'gform_field_value_populate_task_description', $plugin_public, 'description_task_population_function' );

		$this->loader->add_filter( 'gform_field_value_populate_task_user', $plugin_public, 'user_task_population_function' );

		$this->loader->add_filter( 'gform_field_value_populate_task_start_at',  $plugin_public, 'start_at_task_population_function' );

		$this->loader->add_filter( 'gform_field_value_populate_task_due_date', $plugin_public, 'due_date_population_function' );

		/*** Update User Form ***/

		$this->loader->add_action( 'gform_after_submission_9', $plugin_public, 'update_form_task', 10, 2 );

		//$this->loader->add_action( 'init',  $plugin_public, 'test_display');

		/*** Incoming Web Hook on Workflow Steps ***/

		$this->loader->add_action( 'gform_after_submission_8', $plugin_public, 'incoming_webhook_add_task', 10, 2 );

		/*** Shortcodes ***/

		$plugin_public_shortcodes = new Gf_Wpm_Integration_Public_Shortcodes($this->get_project_endpoints());

		$this->loader->add_action( 'init', $plugin_public_shortcodes, 'add_shortcode_func');

	}



	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Gf_Wpm_Integration_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	public function get_project_endpoints() {

		$this->project_endpoints = new Gf_Wpm_Projects_Endpoints;

		return $this->project_endpoints;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
