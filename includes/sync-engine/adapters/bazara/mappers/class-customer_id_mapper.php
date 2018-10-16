<?php

/**
 * Created by PhpStorm.
 * User: aghili
 * Date: 9/27/2018
 * Time: 11:55 AM
 */
class customer_id_mapper
{
    private static $instance;
    private $id_list = [];
    private $tablename;


    private function __construct()
    {
        global $wpdb;
        $this->tablename = DB_NAME_CUSTOMER_ID_MAPPER;

        //$this->load();
    }

    public static function  &get_instance()
    {
        if (is_null(customer_id_mapper::$instance)) {
            customer_id_mapper::$instance = new self();
        }
        return customer_id_mapper::$instance;
    }

    public function add($customer_id, $mahak_id)
    {
        $result = [];
        //$this->id_list[$mahak_id] = $product_id;
        global $wpdb;
        $result['insert'] = $wpdb->insert($this->tablename, ['id_customer' => $customer_id, 'id_mahak' => $mahak_id]);
        if (!empty($wpdb->last_error))
            $result['insert'] = 'cant insert to table ' . $this->tablename . "\n$wpdb->last_error\n$wpdb->last_query";
        return $result;
    }

//    public function save(){
//        //update_option('wpmbt_product_id_mapping',product_id_mapper::$ip_list,true);
//    }

//    public function &get_product_mapper_list()
//    {
//        return $this->id_list;
//
//    }

    public function get_id($mahak_id)
    {
        //if ( !array_key_exists( $mahak_id, $this->id_list ) ) {
        //    return null;
        //}
        global $wpdb;
        $result = $wpdb->get_row("SELECT * FROM $this->tablename WHERE `id_mahak` = $mahak_id ");
        if (!empty($wpdb->last_error))
            throw new Exception('cant get query from table ' . $this->tablename . "\n$wpdb->last_error\n$wpdb->last_query");
        if (!$result) {
            return null;
        }
        return $result->id_customer;
    }

//    public function add_and_save($product_id,$mahak_id)
//    {
//        $this->add($product_id,$mahak_id);
//        $this->save();
//    }

    private function load()
    {
        $this->id_list = [];
        global $wpdb;
        $result = $wpdb->get_results("SELECT * FROM $this->tablename");
        if (!empty($wpdb->last_error))
            throw new Exception('cant get query from table ' . $this->tablename . "\n$wpdb->last_error\n$wpdb->last_query");
        //var_dump($result);
        if (is_null($result)) return;
        foreach ($result as $record) {
            $this->id_list[$record->id_bazara] = $record->id_customer;
        }
    }
}