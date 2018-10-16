<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://www.mostafa.aghili.ir
 * @since      1.0.0
 *
 * @package    Wpmbt
 * @subpackage Wpmbt/includes
 */
/**
 * The class responsible for orchestrating the actions and filters of the
 * core plugin.
 */
require_once 'definations.php';

require_once 'class-base-info.php';

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
 * @package    Wpmbt
 * @subpackage Wpmbt/includes
 * @author     mostafa aghili <aghili.mostafa@gmail.com>
 */
class Wpmbt extends base_info
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wpmbt_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;
	private $options;

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
		if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
			base_info::$version = PLUGIN_NAME_VERSION;
		} else {
			base_info::$version = '1.0.0';
		}
		base_info::$plugin_name = 'wpmbt';

		$this->load_dependencies();

		$this->options = &wpmbt_options::GetInstance();
		//var_dump($this->options);
		if (!array_key_exists('first_conf_done', $this->options->options)) {

			$this->options->options['last_sync_time'] = 0;
			$this->options->options['sync_period_time'] = 10;
			$this->options->options['first_conf_done'] = false;
		}

		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wpmbt_Loader. Orchestrates the hooks of the plugin.
	 * - Wpmbt_i18n. Defines internationalization functionality.
	 * - Wpmbt_Admin. Defines all hooks for the admin area.
	 * - Wpmbt_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {


		require_once "log/class-log.php";
		require_once 'sync-engine/class-sync.php';
		require_once 'data/class-options.php';
		require_once 'REST-API/API-V1.php';
		/**
		 * Check if WooCommerce is active
		 **/
		$shop_fined = false;
		foreach (suported_shops as $key => $shop) {
			if (in_array($shop['file'], apply_filters('active_plugins', get_option('active_plugins')))) {
				require_once plugin_dir_path(dirname(__FILE__)) . "includes/wp_shops/class-$key.php";
				$shop_fined = true;
				break;
			}
		}

		//todo: if shop class for this website didn't fined do something
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpmbt-loader.php';

		/**
		 * The class responsible for sync data from bazara.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/sync-engine/class-sync.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpmbt-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wpmbt-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wpmbt-public.php';

		$this->loader = new Wpmbt_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wpmbt_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wpmbt_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

// Register the rest route here.

		$this->loader->add_action('rest_api_init', $this, 'init_api_route');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Wpmbt_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Add menu item
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );

// Add Settings link to the plugin
		$plugin_basename = plugin_basename(plugin_dir_path(__DIR__) . Wpmbt::$plugin_name . '.php');
		$this->loader->add_filter( 'plugin_action_links_' . $plugin_basename, $plugin_admin, 'add_action_links' );
// Save/Update our plugin options
		$this->loader->add_action('admin_init', $plugin_admin, 'options_update');
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return Wpmbt::$plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return Wpmbt::$version;
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wpmbt_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		// todo: add filter to show product for update product in need
		//$this->loader->add_action( 'woocommerce_before_shop_loop_item',$this, 'action_woocommerce_before_shop_loop_item', 10, 0 );



	}

	static function init_api_route()
	{

		register_rest_route('wpmbt/v1', 'hit-update_records', array(

				'methods' => 'GET',
				'callback' => 'hit_update_records',
				'json' => true

		), true);
		register_rest_route('wpmbt/v1', 'test', array(

				'methods' => 'GET',
				'callback' => 'test',
				'json' => true

		), true);

		register_rest_route('wpmbt/v1', 'hit-sync', array(

				'methods' => 'GET',
				'callback' => 'hit_sync',
				'json' => true

		), true);

		register_rest_route('wpmbt/v1', 'get-update-info', array(

				'methods' => 'GET',
				'callback' => 'get_update_info',
				'json' => true

		), true);

		register_rest_route('wpmbt/v1', 'get-sync-log-list', array(

				'methods' => 'GET',
				'callback' => 'get_sync_log_list',
				'json' => true

		), true);
	}

	static function  action_woocommerce_before_shop_loop_item(  ) {

		$product_id = get_post()->ID;
		//var_dump($product_id);
		$options = wpmbt_options::GetInstance();
		if (!array_key_exists('sync_list', $options->options)) return;
		if (!array_key_exists($product_id, $options->options['sync_list'])) return;
		$item = $options->options['sync_list'][$product_id];
		$product = wc_get_product($product_id);
		if($product) {
			if($item->Deleted)
				$product->set_status(server_product_status::trash);
			else {
				$attributes = $product->get_attributes();
//			foreach($attributes as $key => $attribute)
//			if(array_key_exists()){
//				$attributes['medium-quantity-price']['value'] = $updatedPrices[$sku][4];
				//$attributes['low-quantity-price']['value'] = $item[''];
				//  $attributes['v-low-quantity-price']['value'] = $updatedPrices[$sku][2];
				$product->set_name( $item->Name );
				$product->set_description( $item->Comment );
				$product->set_date_created($item->CreateDate);
				$product->set_date_modified($item->UpdateDate);
			}
			//update_post_meta($id, '_product_attributes', $attributes);
//		}

		}
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
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wpmbt_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

}
