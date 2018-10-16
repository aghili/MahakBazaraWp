<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.mostafa.aghili.ir
 * @since      1.0.0
 *
 * @package    Wpmbt
 * @subpackage Wpmbt/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wpmbt
 * @subpackage Wpmbt/admin
 * @author     mostafa aghili <aghili.mostafa@gmail.com>
 */
class Wpmbt_Admin extends base_info
{

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */

	protected $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		Wpmbt_Admin::$plugin_name = $plugin_name;
		Wpmbt_Admin::$version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wpmbt_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wpmbt_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style(Wpmbt_Admin::$plugin_name, plugin_dir_url(__FILE__) . 'css/wpmbt-admin.css', array(), Wpmbt_Admin::$version, 'all');

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wpmbt_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wpmbt_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script(Wpmbt_Admin::$plugin_name, plugin_dir_url(__FILE__) . 'js/wpmbt-admin.js', array('jquery'), Wpmbt_Admin::$version, false);

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */

	public function add_plugin_admin_menu() {

		/*
         * Add a settings page for this plugin to the Settings menu.
         *
         * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
         *
         *        Administration Menus: http://codex.wordpress.org/Administration_Menus
         *
         */
		add_options_page('bazara plugin defines', 'defines', 'manage_options', Wpmbt_Admin::$plugin_name, array($this, 'display_plugin_setup_page')
		);
	}

	/**
	 *
	 * admin/class-wp-cbf-admin.php - Don't add this
	 *
	 **/

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */

	public function add_action_links( $links ) {
		/*
        *  Documentation : https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
        */
		$settings_link = array(
				'<a href="' . admin_url('options-general.php?page=' . Wpmbt_Admin::$plugin_name) . '">' . __('Settings', Wpmbt_Admin::$plugin_name) . '</a>',
		);
		return array_merge(  $settings_link, $links );

	}

	public function display_plugin_setup_page() {
		$this->options = wpmbt_options::GetInstance();
		include_once( 'partials/wpmbt-admin-display.php' );
	}

	public function validate($input) {
		// All checkboxes inputs
		$valid = array();

		//Cleanup
		$valid['bz_user_id'] = (isset($input['bz_user_id']) && !empty($input['bz_user_id'])) ? $input['bz_user_id'] : "";
		$valid['bz_pass_code'] = (isset($input['bz_pass_code']) && !empty($input['bz_pass_code'])) ? $input['bz_pass_code'] : "";
		$valid['bz_db_id'] = (isset($input['bz_db_id']) && !empty($input['bz_db_id'])) ? $input['bz_db_id'] : "";
		$valid['bz_mahak_url'] = (isset($input['bz_mahak_url']) && !empty($input['bz_mahak_url'])) ? $input['bz_mahak_url'] : "";
		$valid['sync_peri	od_time'] = (isset($input['sync_period_time']) && !empty($input['sync_period_time']) && $input['sync_period_time'] > 0) ? $input['sync_period_time'] : "1";
		$valid['update_good_per_hint'] = (isset($input['update_good_per_hint']) && !empty($input['update_good_per_hint']) && $input['update_good_per_hint'] > -2) ? $input['update_good_per_hint'] : "1";
		$valid['first_conf_done'] = true;

		return $valid;
	}

	public function options_update() {
		register_setting(Wpmbt_Admin::$plugin_name, Wpmbt_Admin::$plugin_name, array($this, 'validate'));
	}

	protected function  get_plugin_name()
	{
		return Wpmbt_Admin::$plugin_name;
	}

	protected function get_version()
	{
		return Wpmbt_Admin::$version;
	}
}
