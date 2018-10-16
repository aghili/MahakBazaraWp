<?php

/**
 * Fired during plugin activation
 *
 * @link       http://www.mostafa.aghili.ir
 * @since      1.0.0
 *
 * @package    Wpmbt
 * @subpackage Wpmbt/includes
 */
global $wpmbt_db_version;
$jal_db_version = '1.0';

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wpmbt
 * @subpackage Wpmbt/includes
 * @author     mostafa aghili <aghili.mostafa@gmail.com>
 */
class Wpmbt_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		Wpmbt_Activator::db_install();
		Wpmbt_Activator::db_install_data();
	}

	protected static function db_install()
	{
		global $wpdb;
		global $wpmbt_db_version;

		$table_sync_name = DB_NAME_SYNC_LIST;
		$table_product_mapper_name = DB_NAME_PRODUCT_ID_MAPPER;
		$table_customer_mapper_name = DB_NAME_CUSTOMER_ID_MAPPER;
		$table_wpmbt_log = DB_NAME_LOG;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "DROP TABLE IF EXISTS `$table_customer_mapper_name`;
CREATE TABLE `$table_customer_mapper_name` (
  `id_customer` int(9) NOT NULL,
  `id_mahak` int(9) NOT NULL DEFAULT '0',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) $charset_collate;
	DROP TABLE IF EXISTS `$table_wpmbt_log`;
CREATE TABLE `$table_wpmbt_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `value` text NOT NULL,
  `type` char(20) NOT NULL,
  `description` text NOT NULL
) $charset_collate;
	DROP TABLE IF EXISTS `$table_product_mapper_name`;
CREATE TABLE IF NOT EXISTS `$table_product_mapper_name` (
  `id_product` int(9) NOT NULL,
  `id_mahak` int(9) NOT NULL DEFAULT '0',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) $charset_collate;
	DROP TABLE IF EXISTS `$table_sync_name`;
CREATE TABLE IF NOT EXISTS `$table_sync_name` (
  `id` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL
) $charset_collate;

--
-- Indexes for table `wp_wpmbt_customer_id_mapper`
--
ALTER TABLE `$table_customer_mapper_name`
  ADD PRIMARY KEY (`id_customer`,`id_mahak`),
  ADD KEY `id_customer` (`id_customer`),
  ADD KEY `id_mahak` (`id_mahak`);

--
-- Indexes for table `wp_wpmbt_log`
--
ALTER TABLE `$table_wpmbt_log`
  ADD PRIMARY KEY (`id`),
  ADD INDEX(`type`);

--
-- Indexes for table `wp_wpmbt_product_id_mapper`
--
ALTER TABLE `$table_product_mapper_name`
  ADD PRIMARY KEY (`id_product`,`id_mahak`),
  ADD KEY `id_mahak` (`id_mahak`),
  ADD KEY `id_product` (`id_product`);

--
-- Indexes for table `wp_wpmbt_product_sync_list`
--
ALTER TABLE `$table_sync_name`
  ADD PRIMARY KEY (`id`);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		add_option('wpmbt_db_version', $wpmbt_db_version);
	}

	protected static function db_install_data()
	{
		global $wpdb;
//
//		$welcome_name = 'Mr. WordPress';
//		$welcome_text = 'Congratulations, you just completed the installation!';
//
//		$table_name = $wpdb->prefix . 'liveshoutbox';
//
//		$wpdb->insert(
//				$table_name,
//				array(
//						'time' => current_time( 'mysql' ),
//						'name' => $welcome_name,
//						'text' => $welcome_text,
//				)
//		);
	}

	public static function update_db_check()
	{
		global $wpmbt_db_version;
		if (get_site_option('wpmbt_db_version') != $wpmbt_db_version) {
			Wpmbt_Activator::db_install();
		}
	}

}
