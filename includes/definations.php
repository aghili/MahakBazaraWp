<?php
/**
 * Created by PhpStorm.
 * User: aghili
 * Date: 7/15/2018
 * Time: 10:43 AM
 */

$server_product_meta_mapper =[
  '_price'=>'Name'
];

$server_product_mapper =[
    'Name'=>'post_title',
    'Comment'=>'post_content'
];

const suported_shops = [
    'woocommerce' => [
        'file' => 'woocommerce/woocommerce.php'
    ],
    //TODO:ADD other wp shops in here
];
define('PLUGIN_INCLUDE_DIR', plugin_dir_path(__FILE__));

//Database Names:
define('DB_NAME_SYNC_LIST', $wpdb->prefix . 'wpmbt_product_sync_list');
define('DB_NAME_PRODUCT_ID_MAPPER', $wpdb->prefix . 'wpmbt_product_id_mapper');
define('DB_NAME_CUSTOMER_ID_MAPPER', $wpdb->prefix . 'wpmbt_customer_id_mapper');
define('DB_NAME_LOG', $wpdb->prefix . 'wpmbt_log');




abstract class server_product_status{
    const trash = "trash";
    const valid = "inherit";
}