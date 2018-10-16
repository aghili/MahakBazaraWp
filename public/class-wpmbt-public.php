<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.mostafa.aghili.ir
 * @since      1.0.0
 *
 * @package    Wpmbt
 * @subpackage Wpmbt/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wpmbt
 * @subpackage Wpmbt/public
 * @author     mostafa aghili <aghili.mostafa@gmail.com>
 */
class Wpmbt_Public extends base_info
{

	public $Error;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */


	public function __construct( $plugin_name, $version ) {

		Wpmbt_Public::$plugin_name = $plugin_name;
		Wpmbt_Public::$version = $version;

		$option = wpmbt_options::GetInstance();
		$sync = sync::GetInstance();

		if ($sync->need_sync()) {
			try {
				$sync->get_sync('website');
			} catch (Exception $ex) {
				$this->Error = $ex;
			}

			if ($sync->has_record_for_update())
				$sync->update_records();
		}//echo "test";
		//var_dump($option);
		$option->save();
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
		 * defined in Wpmbt_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wpmbt_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style(Wpmbt_Public::$plugin_name, plugin_dir_url(__FILE__) . 'css/wpmbt-public.css', array(), Wpmbt_Public::$version, 'all');

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
		 * defined in Wpmbt_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wpmbt_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script(Wpmbt_Public::$plugin_name, plugin_dir_url(__FILE__) . 'js/wpmbt-public.js', array('jquery'), Wpmbt_Public::$version, false);

	}

	protected function  get_plugin_name()
	{
		return Wpmbt_Public::$plugin_name;
	}

	protected function get_version()
	{
		return Wpmbt_Public::$version;
	}

}
